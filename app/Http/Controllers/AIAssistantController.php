<?php

namespace App\Http\Controllers;

use App\Models\AIChatSession;
use App\Models\AIChatMessage;
use App\Models\Epics;
use App\Models\Teams;
use App\Models\Tasks;
use App\Models\TaskStatus;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AIAssistantController extends Controller
{
    protected $gemini;

    public function __construct(GeminiService $gemini)
    {
        $this->gemini = $gemini;
    }

    // Khởi tạo session AI (mở chat)
    public function startSession(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Vui lòng đăng nhập trước'], 401);
        }
        
        $team = $user->teams()->first();
        
        $session = AIChatSession::create([
            'user_id' => $user->id,
            'team_id' => $team?->id,
            'epic_id' => $request->input('epic_id'),
            'sprint_id' => $request->input('sprint_id'),
            'workflow_stage' => $request->input('workflow_stage', 'backlog'),
            'context' => null,
            'status' => 'active',
        ]);

        // Welcome message
        AIChatMessage::create([
            'session_id' => $session->id,
            'role' => 'ai',
            'content' => 'Chào bạn! Tôi có thể giúp bạn tạo User Stories từ Epic, phân rã subtask, hoặc tạo sprint. Hãy cho tôi biết bạn cần gì.',
            'suggestions' => [
                ['label' => 'Tạo US từ Epic', 'action' => 'generate_us_from_epic'],
                ['label' => 'Tạo Future Sprint', 'action' => 'create_sprint'],
            ],
        ]);

        return response()->json([
            'session_id' => $session->id,
        ]);
    }

    // Gửi tin nhắn
    public function sendMessage(Request $request, $sessionId)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $session = AIChatSession::with(['epic', 'team'])->findOrFail($sessionId);

        // Lưu message user
        AIChatMessage::create([
            'session_id' => $session->id,
            'role' => 'user',
            'content' => $request->message,
        ]);

        // Phân tích intent của user
        $intent = $this->detectIntent($request->message, $session);

        // Xử lý theo intent
        $response = $this->handleIntent($intent, $session, $request->message);

        // Lưu response AI
        AIChatMessage::create([
            'session_id' => $session->id,
            'role' => 'ai',
            'content' => $response['message'],
            'suggestions' => $response['suggestions'] ?? null,
        ]);

        return response()->json($response);
    }

    // Xử lý phân rã US thành subtasks
    protected function handleDecomposeSubtasks($session, $userMessage)
    {
        // Extract US ID hoặc tên từ message: "Phân rã US #123" hoặc "Phân rã US: Đăng nhập"
        $usId = null;
        if (preg_match('/#(\d+)/', $userMessage, $matches)) {
            $usId = (int) $matches[1];
        }

        if (!$usId) {
            // Thử tìm bằng tên
            if (preg_match('/(?:phân rã|decompose)\s+us[:\s]+(.+)/i', $userMessage, $matches)) {
                $usTitle = trim($matches[1]);
                $us = Tasks::where('title', 'like', '%' . $usTitle . '%')
                    ->whereNull('parent_id')
                    ->first();
                if ($us) {
                    $usId = $us->id;
                }
            }
        }

        if (!$usId) {
            return [
                'message' => '❌ Không tìm thấy User Story. Vui lòng nhập "Phân rã US #id" hoặc "Phân rã US: Tên US".',
                'suggestions' => [],
            ];
        }

        return [
            'message' => '🔄 Đang phân tích User Story và tạo gợi ý subtasks...',
            'action' => 'decompose',
            'us_id' => $usId,
        ];
    }

    // Phát hiện ý định user
    protected function detectIntent($message, $session)
    {
        $lower = strtolower($message);

        if (str_contains($lower, 'tạo us') || str_contains($lower, 'user stor')) {
            // Extract Epic name từ message: "Tạo US cho Epic Payment" -> "Payment"
            $epicFound = null;
            if (preg_match('/epic\s+([a-zA-Z0-9\s\-_]+?)(?:\s*$|[\.\,\!])/i', $message, $matches)) {
                $epicName = trim($matches[1]);
                $epic = Epics::where('title', 'like', '%' . $epicName . '%')
                    ->orWhere('title', '=', $epicName)
                    ->first();
                if ($epic) {
                    $epicFound = $epic;
                    $session->update(['epic_id' => $epic->id]);
                }
            }

            // Nếu không tìm thấy Epic → trả về list Epic để user chọn
            if (!$epicFound && preg_match('/epic/i', $message)) {
                return 'choose_epic';
            }

            return 'generate_us';
        }
        if (str_contains($lower, 'tạo sprint') || str_contains($lower, 'future sprint')) {
            return 'create_sprint';
        }
        if (str_contains($lower, 'subtask') || str_contains($lower, 'phân rã')) {
            return 'generate_subtasks';
        }

        // Mặc định
        return 'general';
    }

    // Xử lý intent
    protected function handleIntent($intent, $session, $userMessage)
    {
        switch ($intent) {
            case 'choose_epic':
                return $this->showEpicSelection($session);

            case 'generate_us':
                return $this->generateUSFromEpic($session);

            case 'create_sprint':
                return [
                    'message' => 'Tạo future sprint đang được phát triển. Bạn muốn tiếp tục gì?',
                    'suggestions' => [
                        ['label' => 'Tạo thêm US', 'action' => 'generate_us'],
                    ],
                ];

            case 'generate_subtasks':
                return $this->handleDecomposeSubtasks($session, $userMessage);

            default:
                return [
                    'message' => 'Tôi có thể giúp bạn tạo User Stories, Sprint, hoặc Subtask. Bạn muốn làm gì?',
                    'suggestions' => [
                        ['label' => 'Tạo US từ Epic', 'action' => 'generate_us'],
                        ['label' => 'Tạo Future Sprint', 'action' => 'create_sprint'],
                    ],
                ];
        }
    }

    // Hiển thị danh sách Epic để chọn
    protected function showEpicSelection($session)
    {
        $epics = Epics::where('team_id', $session->team_id)
            ->select('id', 'title')
            ->get();

        if ($epics->isEmpty()) {
            return [
                'message' => 'Chưa có Epic nào. Vui lòng tạo Epic trước.',
                'suggestions' => [],
            ];
        }

        $suggestions = $epics->map(function($epic) {
            return [
                'label' => 'Tạo US cho ' . $epic->title,
                'action' => 'select_epic',
                'epic_id' => $epic->id,
                'epic_title' => $epic->title, // Raw title for matching
            ];
        })->toArray();

        return [
            'message' => '📋 Chọn một Epic:',
            'suggestions' => $suggestions,
        ];
    }

    // Generate US từ Epic (gọi Gemini)
    protected function generateUSFromEpic($session)
    {
        if (!$session->epic) {
            $epics = Epics::where('team_id', $session->team_id)
                ->select('id', 'title')
                ->get();

            if ($epics->isEmpty()) {
                return [
                    'message' => '❌ Chưa có Epic nào. Vui lòng tạo Epic trước.',
                    'suggestions' => [],
                ];
            }

            $suggestions = $epics->map(function($epic) {
                return [
                    'label' => $epic->title,
                    'action' => 'select_epic',
                    'epic_id' => $epic->id,
                ];
            })->toArray();

            return [
                'message' => '❌ Bạn chưa chọn Epic. Vui lòng chọn từ danh sách:',
                'suggestions' => $suggestions,
            ];
        }

        return $this->generateUSFromEpicForAPI($session, $session->epic);
    }

    // Helper: Generate US từ Epic object (dùng cho API)
    public function generateUSFromEpicForAPI($session, $epic)
    {
        $approaches = $this->callGeminiForApproaches($epic, $session->team);

        if (!$approaches) {
            return [
                'message' => 'Lỗi khi gọi Gemini. Vui lòng thử lại.',
                'suggestions' => [
                    ['label' => '🔄 Thử lại', 'action' => 'regenerate_us'],
                ],
            ];
        }

        return [
            'message' => "✨ Tôi đề xuất 3 phương án User Stories cho Epic '{$epic->title}':",
            'approaches' => $approaches,
            'suggestions' => [
                ['label' => '✏️ Chỉnh sửa & Lưu', 'action' => 'edit_us'],
                ['label' => '🔄 Tạo lại', 'action' => 'regenerate_us'],
            ],
        ];
    }

    // Gọi Gemini để generate 3 approaches
    protected function callGeminiForApproaches($epic, $team)
    {
        $prompt = $this->buildPromptForEpic($epic, $team);
        
        try {
            $content = $this->gemini->generateContent($prompt);
            
            if (!$content) {
                return null;
            }

            // Parse JSON từ Gemini response
            $approaches = $this->parseApproachesFromResponse($content);
            return $approaches;
        } catch (\Exception $e) {
            \Log::error('Gemini error: ' . $e->getMessage());
            return null;
        }
    }

    // Parse Gemini response thành array approaches
    protected function parseApproachesFromResponse(string $response): ?array
    {
        // Thử extract JSON từ response
        if (preg_match('/\[[\s\S]*\]/m', $response, $matches)) {
            try {
                $data = json_decode($matches[0], true);
                if (is_array($data)) {
                    return $data;
                }
            } catch (\Exception $e) {
                \Log::error('JSON parse error: ' . $e->getMessage());
            }
        }

        // Fallback: tạo placeholder dari response
        return [
            [
                'name' => 'Simple Approach',
                'description' => 'Phương án đơn giản',
                'stories' => [
                    ['title' => 'US 1: ' . substr($response, 0, 50), 'points' => 3],
                ],
                'total_points' => 3,
            ],
            [
                'name' => 'Standard Approach',
                'description' => 'Phương án tiêu chuẩn',
                'stories' => [
                    ['title' => 'US 1: ' . substr($response, 0, 50), 'points' => 3],
                    ['title' => 'US 2: Thêm chi tiết', 'points' => 5],
                ],
                'total_points' => 8,
            ],
            [
                'name' => 'Comprehensive Approach',
                'description' => 'Phương án toàn diện',
                'stories' => [
                    ['title' => 'US 1: ' . substr($response, 0, 50), 'points' => 3],
                    ['title' => 'US 2: Thêm chi tiết', 'points' => 5],
                    ['title' => 'US 3: Thêm tính năng', 'points' => 5],
                ],
                'total_points' => 13,
            ],
        ];
    }

    // Build prompt cho Gemini
    protected function buildPromptForEpic($epic, $team)
    {
        return "Bạn là Product Owner Expert chuyên phân rã Epic thành User Stories.\n\n" .
               "Phân rã Epic sau thành 3 approaches (Simple, Standard, Comprehensive):\n\n" .
               "Epic: {$epic->title}\n" .
               "Description: {$epic->description}\n\n" .
               "Yêu cầu:\n" .
               "1. Mỗi approach có 3-5 User Stories\n" .
               "2. Simple: bao gồm tính năng cơ bản (3-8 points)\n" .
               "3. Standard: bao gồm tính năng chính (8-13 points)\n" .
               "4. Comprehensive: bao gồm tất cả tính năng (13+ points)\n\n" .
               "Trả về JSON array gồm 3 objects, mỗi object có structure:\n" .
               "{\n" .
               "  \"name\": \"Simple/Standard/Comprehensive Approach\",\n" .
               "  \"description\": \"Mô tả ngắn\",\n" .
               "  \"stories\": [\n" .
               "    {\"title\": \"US title\", \"points\": 3}\n" .
               "  ],\n" .
               "  \"total_points\": 8\n" .
               "}\n\n" .
               "Trả về chỉ JSON, không có text khác.";
    }

    // Lưu User Stories vào database
    public function saveUserStories(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:ai_chat_sessions,id',
            'stories' => 'required|array',
            'approach_name' => 'required|string',
        ]);

        $session = AIChatSession::findOrFail($request->session_id);
        
        if (!$session->epic) {
            return response()->json(['error' => 'Epic không tồn tại'], 400);
        }

        $user = Auth::user();
        $epic = $session->epic;
        $createdStories = [];

        try {
            foreach ($request->stories as $story) {
                $defaultStatus = TaskStatus::where('name', 'To Do')->first();
                $task = Tasks::create([
                    'epic_id' => $epic->id,
                    'title' => $story['title'] ?? 'Untitled',
                    'description' => $story['description'] ?? $request->approach_name,
                    'status_id' => $defaultStatus?->id ?? TaskStatus::first()?->id,
                    'story_point' => $story['points'] ?? 0,
                    'created_by' => $user->id,
                    'team_id' => $session->team_id,
                ]);
                $createdStories[] = $task;
            }

            // Lưu session history
            AIChatMessage::create([
                'session_id' => $session->id,
                'role' => 'ai',
                'content' => "✅ Đã lưu " . count($createdStories) . " User Stories từ approach '{$request->approach_name}' vào Epic '{$epic->title}'.",
                'suggestions' => [
                    ['label' => 'Tạo Sprint', 'action' => 'create_sprint'],
                    ['label' => 'Tạo thêm US', 'action' => 'generate_us'],
                ],
            ]);

            return response()->json([
                'message' => "✅ Đã lưu " . count($createdStories) . " User Stories thành công!",
                'stories' => $createdStories,
                'suggestions' => [
                    ['label' => 'Tạo Sprint', 'action' => 'create_sprint'],
                    ['label' => 'Tạo thêm US', 'action' => 'generate_us'],
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error saving stories: ' . $e->getMessage());
            return response()->json(['error' => 'Lỗi khi lưu: ' . $e->getMessage()], 500);
        }
    }

    // API endpoint: Phân rã US thành subtasks
    public function decomposeUS(Request $request)
    {
        $validated = $request->validate([
            'us_id' => 'required|integer|exists:tasks,id',
            'preset' => 'nullable|string|in:minimal,full',
        ]);

        $usId = $validated['us_id'];
        $preset = $validated['preset'] ?? 'minimal';

        $us = Tasks::with(['epic', 'sprint'])->findOrFail($usId);

        if ($us->parent_id) {
            return response()->json([
                'error' => 'Không thể phân rã subtask. Chỉ phân rã được User Story (task cha).'
            ], 400);
        }

        // Lấy team members với workload
        $user = Auth::user();
        $team = $user->team();
        $teamMembers = [];

        if ($team) {
            $teamMembers = $team->users()
                ->where('roleInTeam', '!=', 'product_owner')
                ->withCount(['tasks as total_story_points' => function ($query) {
                    $query->select(\DB::raw('sum(storyPoints)'));
                }])
                ->get()
                ->map(function ($member) {
                    return [
                        'id' => $member->id,
                        'name' => $member->name,
                        'role' => $member->pivot->roleInTeam ?? 'developer',
                        'workload' => (int) ($member->total_story_points ?? 0),
                    ];
                })
                ->sortBy('workload')
                ->values()
                ->toArray();
        }

        // Gọi Gemini để sinh subtasks
        $prompt = $this->gemini->buildDecomposePrompt($us, $preset, $teamMembers);
        $rawResponse = $this->gemini->generateContent($prompt);

        // Parse JSON từ response
        $subtasks = $this->parseSubtasksFromResponse($rawResponse);

        if (empty($subtasks)) {
            return response()->json([
                'error' => 'Không tạo được danh sách subtasks. Vui lòng thử lại.'
            ], 500);
        }

        return response()->json([
            'us' => [
                'id' => $us->id,
                'title' => $us->title,
                'description' => $us->description,
                'storyPoints' => $us->storyPoints,
                'epic_title' => $us->epic?->title,
                'sprint_id' => $us->sprint_id, // 🔥 Thêm sprint_id để frontend tạo subtasks với đúng sprint
            ],
            'subtasks' => $subtasks,
            'preset' => $preset,
            'team_members' => $teamMembers,
        ]);
    }

    // Parse subtasks từ Gemini response
    protected function parseSubtasksFromResponse($rawResponse)
    {
        // Loại bỏ HTML nếu có
        if (preg_match('/<!DOCTYPE/i', $rawResponse) || preg_match('/<html/i', $rawResponse)) {
            \Log::error('Gemini returned HTML instead of JSON: ' . substr($rawResponse, 0, 200));
            return [];
        }

        // Tìm JSON block trong response (có thể có markdown code block)
        $json = null;
        if (preg_match('/```json\s*(\{.*?\})\s*```/s', $rawResponse, $matches)) {
            $json = $matches[1];
        } elseif (preg_match('/```\s*(\{.*?\})\s*```/s', $rawResponse, $matches)) {
            $json = $matches[1];
        } elseif (preg_match('/(\{[\s\S]*"subtasks"[\s\S]*?\})/s', $rawResponse, $matches)) {
            $json = $matches[1];
        } else {
            // Thử parse toàn bộ response
            $json = trim($rawResponse);
        }

        try {
            $data = json_decode($json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                \Log::error('JSON decode error: ' . json_last_error_msg() . ' | JSON: ' . substr($json, 0, 300));
                return [];
            }
            return $data['subtasks'] ?? [];
        } catch (\Exception $e) {
            \Log::error('Failed to parse subtasks JSON: ' . $e->getMessage() . ' | Response: ' . substr($rawResponse, 0, 300));
            return [];
        }
    }
}