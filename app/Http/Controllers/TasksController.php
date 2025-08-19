<?php
// File: app/Http/Controllers/TasksController.php

namespace App\Http\Controllers;

use App\Models\Sprints;
use App\Models\Tasks;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Gemini\Laravel\Facades\Gemini; // Import Facade để code sạch hơn
class TasksController extends Controller
{
    /**
     * Dữ liệu cho trang Dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        $team = $user->team(); // Lấy team của user

        $activeSprint = null;
        $sprintProgress = ['done' => 0, 'inProgress' => 0, 'toDo' => 0];
        $tasksInProgress = 0;
        $tasksCompletedToday = 0;
        $members = 0;

        if ($team) {
            $activeSprint = $team->activeSprint;
            $members = $team->users()->count();

            if ($activeSprint) {
                $sprintTasks = $activeSprint->tasks()->get();
                $taskCounts = $sprintTasks->countBy('status');

                $sprintProgress['done'] = $taskCounts->get('done', 0);
                $sprintProgress['inProgress'] = $taskCounts->get('inProgress', 0);
                $sprintProgress['toDo'] = $taskCounts->get('toDo', 0);

                $tasksInProgress = $sprintProgress['inProgress'];
                $tasksCompletedToday = $sprintTasks
                    ->where('status', 'done')
                    ->where('updated_at', '>=', now()->startOfDay())
                    ->count();
            }
        }

        $SprintActiveCount = $activeSprint ? 1 : 0;

        // Lấy hoạt động gần đây (ví dụ)
        $recentActivities = Tasks::with('assignee')->latest('updated_at')->take(5)->get()->map(fn($task) => [
            'type' => 'task',
            'description' => 'Task "' . $task->title . '" was updated by ' . ($task->assignee->name ?? 'N/A'),
            'time' => $task->updated_at
        ]);

        return view('pages.dashboard', compact(
            'SprintActiveCount',
            'tasksInProgress',
            'tasksCompletedToday',
            'members',
            'recentActivities',
            'sprintProgress'
        ));
    }

    /**
     * Hiển thị trang Task Board.
     */
    public function taskBoard()
    {
        $user = Auth::user();
        $team = $user->team();

        // Lấy các task chưa thuộc sprint nào (Product Backlog)
        $backlogTasks = Tasks::whereNull('sprint_id')->with('assignee')->orderBy('created_at', 'desc')->get();

        $activeSprint = $team ? $team->activeSprint : null;
        $sprintTasks = $activeSprint ? $activeSprint->tasks()->with('assignee')->get() : collect();
        $teamMembers = $team ? $team->users()->orderBy('name')->get() : collect();

        return view('pages.taskBoard', compact('backlogTasks', 'sprintTasks', 'teamMembers', 'activeSprint'));
    }

    /**
     * Cập nhật trạng thái của Task (sử dụng cho kéo-thả).
     */
    public function updateStatus(Request $request, Tasks $task)
    {
        $user = Auth::user();

        // Chỉ người được giao task, SM, hoặc LeadDev mới có quyền thay đổi status
        if ($user->id !== $task->assigned_to && !in_array($user->role, ['scrum_master', 'leadDeveloper'])) {
            return response()->json(['message' => 'Bạn không có quyền thay đổi trạng thái của task này.'], 403);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['toDo', 'inProgress', 'done'])],
        ]);

        $task->update(['status' => $validated['status']]);

        return response()->json(['message' => 'Cập nhật trạng thái task thành công!']);
    }

    // Các hàm store, edit, update, destroy cho Product Backlog (PO) giữ nguyên như cũ...
    // ... (Code CRUD của bạn đã khá tốt)
    public function store(Request $request)
    {
        if (Auth::user()->role !== 'product_owner') {
            return response()->json(['message' => 'Bạn không có quyền tạo task.'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
            'storyPoints' => 'nullable|integer',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $task = Tasks::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'storyPoints' => $validated['storyPoints'],
            'assigned_to' => $validated['assigned_to'],
            'created_by' => Auth::id(),
            'sprint_id' => null,
            'status' => 'toDo', // Mặc định là 'toDo' nhưng nó nằm trong backlog
        ]);

        return response()->json(['message' => 'Tạo task thành công!', 'task' => $task->load('assignee')], 201);
    }

    public function edit(Tasks $task)
    {
        if (Auth::user()->role !== 'product_owner' || $task->sprint_id !== null) {
            return response()->json(['message' => 'Bạn không có quyền sửa task này.'], 403);
        }
        return response()->json($task);
    }

    public function update(Request $request, Tasks $task)
    {
        if (Auth::user()->role !== 'product_owner' || $task->sprint_id !== null) {
            return response()->json(['message' => 'Bạn không có quyền cập nhật task này.'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
            'storyPoints' => 'nullable|integer',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $task->update($validated);

        return response()->json(['message' => 'Cập nhật task thành công!', 'task' => $task->load('assignee')]);
    }

    public function destroy(Tasks $task)
    {
        if (Auth::user()->role !== 'product_owner' || $task->sprint_id !== null) {
            return response()->json(['message' => 'Bạn không có quyền xóa task này.'], 403);
        }

        $task->delete();

        return response()->json(['message' => 'Xóa task thành công!']);
    }

    /**
     * Gợi ý chi tiết công việc bằng AI.
     */
    public function suggestWithAI(Request $request)
    {
        // 1. Kiểm tra dữ liệu đầu vào
        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        // 2. Lấy API Key từ file config (Không cần thiết nếu đã cấu hình Facade)
        // $apiKey = config('services.gemini.key');

        $taskTitle = $validated['title'];

        // 3. Tạo câu lệnh (Prompt) cho AI
        $prompt = "Based on the task title \"{$taskTitle}\", generate a JSON object with three properties: 'description', 'priority', and 'sub_tasks'.
        - 'description' should be a detailed user story, starting with 'As a user, I want to...'.
        - 'priority' must be one of these three values: 'low', 'medium', or 'high'.
        - 'sub_tasks' should be an array of strings, listing smaller, actionable steps to complete the main task.
        IMPORTANT: Your response must be only the raw JSON object, without any markdown formatting like ```json.";

        // 4. Gửi yêu cầu đến API của Gemini
        try {
            // ===== SỬA LỖI Ở ĐÂY =====
            // Sử dụng phương thức mới: generativeModel() và truyền tên model vào
            // 'gemini-pro' là model mặc định, mạnh mẽ.
            // 'gemini-1.5-flash-latest' là model mới, nhanh và hiệu quả. Bạn có thể dùng 1 trong 2.
            $result = Gemini::gemini('gemini-1.5-flash-latest')
                            ->generateContent($prompt);
            // =========================

            $suggestionJson = $result->text();
            $suggestionData = json_decode($suggestionJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                preg_match('/\{.*\}/s', $suggestionJson, $matches);
                if (isset($matches[0])) {
                    $suggestionData = json_decode($matches[0], true);
                } else {
                    // Nếu AI trả về lỗi, ta sẽ hiển thị lỗi đó
                    throw new \Exception('AI response was not valid JSON. Raw response: ' . $suggestionJson);
                }
            }

            // 5. Trả kết quả về cho frontend
            return response()->json($suggestionData);

        } catch (\Exception $e) {
            // Xử lý nếu có lỗi
            return response()->json(['error' => 'Failed to get suggestions from AI. ' . $e->getMessage()], 500);
        }
    }
}
