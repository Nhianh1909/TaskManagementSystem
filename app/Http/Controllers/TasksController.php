<?php
// File: app/Http/Controllers/TasksController.php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\Models\Sprints;
use App\Models\Tasks;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;
class TasksController extends Controller
{
    /**
     * hàm hiển thị
     */
    public function index()
    {
        $user = Auth::user();
        $team = $user->team(); // Lấy team của user đăng đăng nhập và đã xác thực

        //khởi tạo các biến dùng trong dashboard
        $activeSprint = null;
        $sprintProgress = ['done' => 0, 'inProgress' => 0, 'toDo' => 0];
        $tasksInProgress = 0;
        $tasksCompletedToday = 0;
        $members = 0;

        if ($team) {
            $activeSprint = $team->activeSprint;
            $members = $team->users()->count();//lấy ra các users có trong team và đếm nó lưu vào biến members
            //nếu có sprint đang hoạt động thì ta lấy các task trong sprint đó để tính tiến độ
            if ($activeSprint) {
                $sprintTasks = $activeSprint->tasks()->get();//lây các task trong sprint đang hoạt động
                $taskCounts = $sprintTasks->countBy('status');//lấy số lượng task theo từng trạng thái sau đó gôm lại vào cột taskCounts
                //lưu số lượng task theo từng trạng thái vào biến sprintProgress
                $sprintProgress['done'] = $taskCounts->get('done', 0);
                $sprintProgress['inProgress'] = $taskCounts->get('inProgress', 0);
                $sprintProgress['toDo'] = $taskCounts->get('toDo', 0);
                //luwu số lượng task đang tiến hành vào biến tasksInProgress
                $tasksInProgress = $sprintProgress['inProgress'];
                //lấy các task đã hoàn thành trong ngày hôm nay
                $tasksCompletedToday = $sprintTasks
                    ->where('status', 'done')
                    ->where('updated_at', '>=', now()->startOfDay())
                    ->count();
            }
        }

        $SprintActiveCount = $activeSprint ? 1 : 0;

        // Lấy hoạt động gần đây từ bảng tasks sau đó map để lấy dữ liệu cần thiết gán vào các thông tin như type, description, time
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
        // Lấy người dùng đã đăng nhập
        $user = Auth::user();

        // Lấy ra team mà user đó đang tham gia và có cả roleInTeam
        $team = $user->teams()->first();


        // Nếu người dùng chưa thuộc team nào, chuyển hướng họ
        if (!$team) {
            return redirect()->route('dashboard')->with('error', 'You must be part of a team to view the task board.');
        }

        // Lấy vai trò cụ thể của người dùng trong team đó
        $userRoleInTeam = $team->users()->find($user->id)?->pivot->roleInTeam;
        // dd($userRoleInTeam);
        // Lấy sprint đang hoạt động của team
        $activeSprint = $team->sprints()->where('is_active', true)->first();
        // dd($activeSprint->toArray());
        // Lấy các task trong Product Backlog (chưa thuộc sprint nào)
        $backlogTasks = Tasks::whereNull('sprint_id')
                             ->with('assignee')
                             ->orderBy('created_at', 'desc')
                             ->get();
        // dd($backlogTasks->toArray());
        // Lấy các task trong sprint đang hoạt động và lấy luôn cả người được gán cho task đó, false thì tạo ra 1 collection rỗng
        $sprintTasks = $activeSprint ? $activeSprint->tasks()->with('assignee')->get() : collect();

         // Lấy danh sách thành viên trong team, loại trừ vai trò 'scrum_master'
        $teamMembers = $team->users()->wherePivot('roleInTeam', '!=', 'scrum_master')->get();

        // Gửi tất cả các biến cần thiết sang view
        return view('pages.taskBoard', compact(
            'backlogTasks',
            'sprintTasks',
            'activeSprint',
            'teamMembers',
            'userRoleInTeam'
        ));
    }

    /**
     * Cập nhật trạng thái của Task (sử dụng cho kéo-thả).
     */
    public function updateStatus(Request $request, Tasks $task)
    {
        $user = Auth::user();
        $team = $user->teams()->first();
        $userRoleInTeam = $team ? $team->users()->find($user->id)?->pivot->roleInTeam : null;

        // Chỉ người được giao task hoặc Scrum Master mới có quyền thay đổi status
        if ($user->id !== $task->assigned_to && $userRoleInTeam !== 'scrum_master') {
            return response()->json(['message' => 'Bạn không có quyền thay đổi trạng thái của task này.'], 403);
        }
        //sử dụng Rule để làm gọn hơn thay vì'status' => 'required|in:toDo,inProgress,done'
        $validated = $request->validate([
            'status' => ['required', Rule::in(['toDo', 'inProgress', 'done'])],
        ]);

        $task->update(['status' => $validated['status']]);
        return response()->json(['message' => 'Cập nhật trạng thái task thành công!']);
    }
    //hàm này là hàm tạo task
    public function store(Request $request)
    {
        $user = Auth::user();
        $team = $user->teams()->first();
        $userRoleInTeam = $team ? $team->users()->find($user->id)?->pivot->roleInTeam : null;
        //3 biến trên cũng lấy thong tin user đang đăng nhập và team của user đó và vai trò của user trong team. Sau đó lấy riêng vai trò của họ
        if ($userRoleInTeam !== 'product_owner') {
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

        return response()->json(['message' => 'Tạo task thành công!', 'task' => $task->load('assignee')], 201);
    }
    //hàm này để hiển thị form sửa task
   public function edit(Tasks $task)
    {
        $user = Auth::user();
        $team = $user->teams()->first();
        $userRoleInTeam = $team ? $team->users()->find($user->id)?->pivot->roleInTeam : null;
        //3 biến trên cũng lấy thong tin user đang đăng nhập và team của user đó và vai trò của user trong team. Sau đó lấy riêng vai trò của họ
        if ($userRoleInTeam !== 'product_owner' || $task->sprint_id !== null) {
            return response()->json(['message' => 'Bạn không có quyền sửa task này.'], 403);
        }
        return response()->json($task);
    }
    //hàm này để cập nhật task với POST
    public function update(Request $request, Tasks $task)
    {
        $user = Auth::user();
        $team = $user->teams()->first();
        $userRoleInTeam = $team ? $team->users()->find($user->id)?->pivot->roleInTeam : null;

        if ($userRoleInTeam !== 'product_owner' || $task->sprint_id !== null) {
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
    //hàm này để xóa task
    public function destroy(Tasks $task)
    {
        $user = Auth::user();
        $team = $user->teams()->first();
        $userRoleInTeam = $team ? $team->users()->find($user->id)?->pivot->roleInTeam : null;

        if ($userRoleInTeam !== 'product_owner' || $task->sprint_id !== null) {
            return response()->json(['message' => 'Bạn không có quyền xóa task này.'], 403);
        }

        $task->delete();
        return response()->json(['message' => 'Xóa task thành công!']);
    }

    //Hàm này gợi ý task bằng AI
    public function suggestAllWithAI(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);
        // Lấy API Key từ biến môi trường
        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            return response()->json(['error' => 'GEMINI_API_KEY is not set.'], 500);
        }
        //gọi API của google gemini
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}";
        //gán tiêu đề task đã xác thực vào biến taskTitle
        $taskTitle = $validated['title'];

        // --- Bắt đầu logic tìm người thực hiện (assignee) ---
        $suggestedAssigneeId = null;
        $user = Auth::user();
        $team = $user->team();

        //2 biến trên lấy user hiện tại và team của user đó có cả pivot
        if ($team) {
            $teamMembers = $team->users()
                ->where('roleInTeam', 'developer') // Chỉ tìm developer
                ->withCount(['tasks as total_story_points' => function ($query) {//đếm tổng story points của từng member
                    $query->select(DB::raw('sum(storyPoints)'));//truy vấn để tính tổng story points của từng member bằng câu lệnh sql
                }])
                ->get();
                //nếu teamMembers ko có story points thì ưu tiên chọn người đó làm assignee
            if ($teamMembers->isNotEmpty()) {
                // Ưu tiên người rảnh (story points = 0 hoặc null)
                $freeMembers = $teamMembers->where('total_story_points', '<=', 0);
                //nếu có nhiều người rãnh thì chọn ngẫu nhiên 1 người để giao task
                if ($freeMembers->isNotEmpty()) {
                    $suggestedAssigneeId = $freeMembers->random()->id;
                } else {
                    // Nếu không có ai rảnh, chọn người có tổng story points nhỏ nhất
                    $suggestedAssigneeId = $teamMembers->sortBy('total_story_points')->first()->id;
                }
            }
        }
        // --- Kết thúc logic tìm assignee ---

        // Prompt mới yêu cầu AI trả về thêm storyPoints
        $payload = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => "Analyze the task title \"{$taskTitle}\" and generate a JSON object with 'description', 'priority', 'storyPoints', and 'sub_tasks'.

                            Follow these steps for reasoning:
                            1.  **Estimate Complexity**: Based on the title, determine if the task is 'Simple', 'Medium', or 'Complex'.
                            2.  **Assign Story Points**:
                                - If 'Simple', assign a storyPoints value of 1, 2, or 3.
                                - If 'Medium', assign a storyPoints value of 5 or 8.
                                - If 'Complex', assign a storyPoints value of 13.
                            3.  **Set Priority**: Determine the priority as 'low', 'medium', or 'high'.
                            4.  **Write Description**: Create a user story starting with 'As a user, I want to...'.
                            5.  **List Sub-tasks**: Create an array of smaller, actionable steps.

                            Your final output must be ONLY the raw JSON object, without any markdown formatting like ```json."
                        ]
                    ]
                ]
            ]
        ];


        try {
            //gửi yêu cầu post bằng protocal Http đến link của google gemini với payload đã tạo
            $response = Http::post($url, $payload);

            if (!$response->successful()) {
                return response()->json(['error' => 'API request failed.', 'details' => $response->json()], 500);
            }
            //lấy kết quả trả về từ AI và parse ra json. Vì AI ban đầu hiểu theo dạng text nên ta cần lấy phần text trong đó ra
            $result = $response->json();

            //biến $suggestionJson để chỉ lấy các phần cần thiết trong kết quả trả về và làm gọn lại thông tin từ AI trả về. Nhiệm vụ
            //trích xuất dữ liệu và đảm bảo chống lỗi nếu có null
            $suggestionJson = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

            // Xóa các ký tự markdown JSON vì AI được huấn luyện từ prompt mình đề xuất có các markdown nên khi trả về phải xóa để có khung đẹp
            $suggestionJson = trim(str_replace(['```json', '```'], '', $suggestionJson));
            // Chuyển đổi chuỗi JSON thành mảng PHP dùng true để trả về mảng kết hợp ['key' => 'value']
            $suggestionData = json_decode($suggestionJson, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                 return response()->json(['error' => 'AI response was not valid JSON.', 'raw_response' => $suggestionJson], 500);
            }

            // Gắn ID của người được gợi ý vào kết quả trả về
            $suggestionData['suggested_assignee_id'] = $suggestedAssigneeId;

            return response()->json($suggestionData);

        } catch (\Exception $e) {
            return response()->json(['error' => 'An exception occurred.', 'message' => $e->getMessage()], 500);
        }
    }
}
