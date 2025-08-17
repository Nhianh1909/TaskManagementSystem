<?php

namespace App\Http\Controllers;

use App\Models\Sprints;
use App\Models\Tasks;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TasksController extends Controller
{
    /**
     * Dữ liệu cho trang Dashboard.
     */
    // // trong file: app/Http/Controllers/TasksController.php

public function index()
{
    // Lấy thông tin user và team hiện tại
    $user = Auth::user();
    $teamId = $user->teams()->first()->id ?? null;

    // Lấy Sprint đang hoạt động của team
    $activeSprint = Sprints::where('team_id', $teamId)->where('is_active', true)->first();

    // Khởi tạo các biến đếm
    $tasksInProgress = 0;
    $tasksCompletedToday = 0;
    $sprintProgress = [
        'done' => 0,
        'inProgress' => 0,
        'toDo' => 0,
    ];

    // Chỉ tính toán nếu có sprint đang hoạt động
    if ($activeSprint) {
        // Lấy tất cả task của sprint này
        $sprintTasks = Tasks::where('sprint_id', $activeSprint->id)->get();

        // Đếm số task theo từng trạng thái để vẽ biểu đồ
        $taskCounts = $sprintTasks->countBy('status');
        $sprintProgress['done'] = $taskCounts->get('done', 0);
        $sprintProgress['inProgress'] = $taskCounts->get('inProgress', 0);
        $sprintProgress['toDo'] = $taskCounts->get('toDo', 0);

        // Cập nhật các thẻ số liệu
        $tasksInProgress = $sprintProgress['inProgress'];
        $tasksCompletedToday = $sprintTasks
            ->where('status', 'done')
            ->where('updated_at', '>=', now()->startOfDay())
            ->count();
    }

    // --- Phần lấy Recent Activity có thể giữ nguyên hoặc tối ưu lại ---
    $tasksToDo = Tasks::where('status', 'toDo')->count(); // Giữ lại cho thẻ thống kê chung nếu muốn
    $SprintActiveCount = $activeSprint ? 1 : 0;
    $members = $teamId ? User::whereHas('teams', fn($q) => $q->where('teams.id', $teamId))->count() : 0;

    $taskActivities = Tasks::where('status', 'done')->latest('updated_at')->take(5)->get()->map(fn($task) => ['type' => 'task', 'description' => 'Task "' . $task->title . '" completed', 'time' => $task->updated_at]);
    $sprintActivities = Sprints::latest()->take(5)->get()->map(fn($sprint) => ['type' => 'sprint', 'description' => 'New sprint "' . $sprint->name . '" created', 'time' => $sprint->created_at]);
    $userActivities = User::latest()->take(5)->get()->map(fn($user) => ['type' => 'team', 'description' => $user->name . ' joined the team', 'time' => $user->created_at]);
    $recentActivities = $taskActivities->merge($sprintActivities)->merge($userActivities)->sortByDesc('time')->take(3)->values();

    return view('pages.dashboard', compact(
        'SprintActiveCount', // Đổi tên biến để tránh nhầm lẫn
        'tasksInProgress',
        'tasksCompletedToday',
        'members',
        'recentActivities',
        'sprintProgress' // <-- Gửi dữ liệu biểu đồ sang view
    ));
}

    /**
     * Hiển thị trang Task Board với dữ liệu.
     */
    // trong file: app/Http/Controllers/TasksController.php

    public function taskBoard()
    {
        $user = Auth::user();
        $teamId = $user->teams()->first()->id ?? null;

        $activeSprint = Sprints::where('team_id', $teamId)->where('is_active', true)->first();

        // SỬA LẠI DÒNG NÀY: Bỏ điều kiện where('team_id', $teamId)
        $backlogTasks = Tasks::whereNull('sprint_id')->with('user')->orderBy('created_at', 'desc')->get();

        $sprintTasks = $activeSprint ? Tasks::where('sprint_id', $activeSprint->id)->with('user')->get() : collect();

        $teamMembers = $teamId ? User::whereHas('teams', fn($q) => $q->where('teams.id', $teamId))->orderBy('name')->get() : collect();

        return view('pages.taskBoard', compact('backlogTasks', 'sprintTasks', 'teamMembers', 'activeSprint'));
    }

    /**
     * Lưu một task mới vào Product Backlog.
     */
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
            'status' => 'toDo',
        ]);

        return response()->json(['message' => 'Tạo task thành công!', 'task' => $task->load('user')], 201);
    }

    /**
     * Lấy dữ liệu của một task để chỉnh sửa.
     */
    public function edit(Tasks $task)
    {
        if (Auth::user()->role !== 'product_owner' || $task->sprint_id !== null) {
            return response()->json(['message' => 'Bạn không có quyền sửa task này.'], 403);
        }
        return response()->json($task);
    }

    /**
     * Cập nhật thông tin task trong Product Backlog.
     */
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

        return response()->json(['message' => 'Cập nhật task thành công!', 'task' => $task->load('user')]);
    }

    /**
     * Xóa một task khỏi Product Backlog.
     */
    public function destroy(Tasks $task)
    {
        if (Auth::user()->role !== 'product_owner' || $task->sprint_id !== null) {
            return response()->json(['message' => 'Bạn không có quyền xóa task này.'], 403);
        }

        $task->delete();

        return response()->json(['message' => 'Xóa task thành công!']);
    }
}
