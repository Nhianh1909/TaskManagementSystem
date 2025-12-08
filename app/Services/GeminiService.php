<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiService
{
    protected string $apiKey;
    protected string $model = 'gemini-1.5-flash';
    protected string $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
    }

    /**
     * Gọi Gemini API để generate content
     */
    public function generateContent(string $prompt): ?string
    {
        if (!$this->apiKey) {
            return 'Lỗi: GEMINI_API_KEY chưa được cấu hình';
        }

        try {
            \Log::info('🔄 Calling Gemini API...');
            
            $response = Http::withHeaders([
                'X-goog-api-key' => $this->apiKey,
            ])->timeout(30)->post(
                "{$this->endpoint}/gemini-2.0-flash:generateContent",
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'topK' => 40,
                        'topP' => 0.95,
                        'maxOutputTokens' => 4096,
                    ],
                ]
            );

            \Log::info('✅ Gemini response status: ' . $response->status());

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                \Log::info('✅ Gemini content received: ' . substr($content, 0, 50) . '...');
                return $content;
            }

            $error = 'Lỗi từ Gemini: ' . $response->status() . ' - ' . $response->body();
            \Log::error($error);
            return $error;
        } catch (\Exception $e) {
            $error = 'Lỗi khi gọi Gemini: ' . $e->getMessage();
            \Log::error($error);
            return $error;
        }
    }

    /**
     * Gọi Gemini với messages (OpenAI-like format)
     */
    public function chat(array $messages): array
    {
        $prompt = $this->formatMessagesToPrompt($messages);
        $content = $this->generateContent($prompt);

        return [
            'choices' => [
                [
                    'message' => [
                        'content' => $content,
                    ],
                ],
            ],
        ];
    }

    /**
     * Convert message array to prompt string
     */
    protected function formatMessagesToPrompt(array $messages): string
    {
        $prompt = '';
        foreach ($messages as $msg) {
            $role = ucfirst($msg['role'] ?? 'user');
            $content = $msg['content'] ?? '';
            $prompt .= "$role: $content\n\n";
        }
        return $prompt;
    }

    /**
     * Build prompt để phân rã US thành subtasks
     */
    public function buildDecomposePrompt($us, $preset = 'minimal', $teamMembers = []): string
    {
        $presetGuide = $preset === 'full'
            ? "Phương án Đầy đủ: Bao gồm tất cả task BE, FE, QA (smoke + regression), DevOps/Monitoring, Security check, Performance test, Docs/Changelog."
            : "Phương án Tối thiểu: Chỉ các task thiết yếu: BE (API/logic), FE (UI/UX), QA smoke test, Docs/Changelog.";

        $epicInfo = $us->epic ? "Epic: {$us->epic->title}\nMô tả Epic: {$us->epic->description}" : 'Không có Epic';
        $sprintInfo = $us->sprint ? "Sprint: {$us->sprint->name}\nGoal: {$us->sprint->goal}" : 'Chưa gán Sprint';

        // Thêm thông tin team members với workload
        $teamInfo = '';
        if (!empty($teamMembers)) {
            $teamInfo = "\nTeam members (sorted by workload - ưu tiên người có workload thấp):\n";
            foreach ($teamMembers as $member) {
                $teamInfo .= "- {$member['name']} (ID: {$member['id']}, Role: {$member['role']}, Workload: {$member['workload']} SP)\n";
            }
        }

        return "Bạn là Scrum Master/Tech Lead chuyên phân rã User Story thành Subtasks.\n\n" .
               "User Story:\n" .
               "- ID: {$us->id}\n" .
               "- Tiêu đề: {$us->title}\n" .
               "- Mô tả: {$us->description}\n" .
               "- Story Points: {$us->storyPoints}\n" .
               "- {$epicInfo}\n" .
               "- {$sprintInfo}\n" .
               $teamInfo . "\n" .
               "Yêu cầu:\n" .
               "- {$presetGuide}\n" .
               "- Mỗi subtask cần: title (ngắn gọn, rõ ràng, có prefix [BE]/[FE]/[QA]/[DevOps] nếu cần), description (1-2 câu mô tả chi tiết việc cần làm), priority (low/medium/high), suggested_assignee_id (ID của người được gợi ý - ưu tiên người có workload thấp nhất và phù hợp với role).\n" .
               "- Nếu mô tả US quá ngắn, hãy đưa ra 4-6 subtask cơ bản nhất.\n" .
               "- CHỈ TRẢ VỀ JSON thuần túy, KHÔNG thêm markdown, text, hoặc giải thích.\n" .
               "- Format JSON bắt buộc:\n" .
               "{\n" .
               "  \"subtasks\": [\n" .
               "    {\n" .
               "      \"title\": \"[BE] Implement login API\",\n" .
               "      \"description\": \"Create POST /api/login endpoint with JWT token generation and return access token\",\n" .
               "      \"priority\": \"high\",\n" .
               "      \"suggested_assignee_id\": " . ($teamMembers[0]['id'] ?? 'null') . "\n" .
               "    }\n" .
               "  ]\n" .
               "}";
    }
}
