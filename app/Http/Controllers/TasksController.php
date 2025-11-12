<?php
// File: app/Http/Controllers/TasksController.php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\Models\Sprints;
use App\Models\Tasks;
use App\Models\TasksComments;
use App\Models\User;
use App\Models\Epics;

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
     * Hiển thị trang Product Backlog
     */
    public function productBacklog()
    {
        $user = Auth::user();
        $team = $user->teams()->first();
        if(!$team ) {
            return redirect()->route('dashboard')->with('error', 'You must be part of a team to view the product backlog.');
        }
        //lấy ra các epic thuộc về $team mà sau khi đã lấy ra team đó
        $getEpics = $team->epics()
                  ->with(['userStories' => function($query) {
                      $query->orderBy('order_index', 'asc');
                  }])
                  ->get();
        $tasksWithoutEpic = Tasks::whereNull('parent_id') // 1. Chỉ lấy User Story (task cha)
                         ->whereNull('epic_id')      // 2. Chưa thuộc Epic nào
                         ->whereNull('sprint_id')    // 3. Nằm trong backlog (chưa vào sprint)
                         ->with('assignee')        // Tải kèm thông tin người được gán (nếu có)
                         ->orderBy('priority')     // Sắp xếp theo độ ưu tiên
                         ->get();
        $futureSprints = $team->sprints()
                         ->where('status', 'planning')
                         ->where('is_active', false)
                         ->with(['tasks' => function($query) {
                             $query->orderBy('order_index', 'asc');
                         }])
                         ->orderBy('created_at', 'desc')
                         ->get();

        return view('pages.product-backlog', compact('getEpics', 'tasksWithoutEpic', 'team', 'futureSprints'));
    }
    // FEATURE: Future Sprint Management

    public function storeFutureSprint(Request $request)
    {
        $user = Auth::user();
        $team = $user->teams()->first();
        $userRoleInTeam = $team->users()->find($user->id)?->pivot->roleInTeam;

        // Check quyền: Product Owner HOẶC Scrum Master
        if (!in_array($userRoleInTeam, ['product_owner', 'scrum_master'])) {
            return response()->json([
                'message' => 'Bạn không có quyền tạo Future Sprint. Chỉ Product Owner hoặc Scrum Master mới được phép.'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'goal' => 'nullable|string',
            'start_date' => 'nullable|date|after_or_equal:today',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $futureSprint = $team->sprints()->create([
            'name' => $validated['name'],
            'goal' => $validated['goal'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'is_active' => false,
            'status' => 'planning',
        ]);

        return response()->json([
            'message' => 'Tạo Future Sprint thành công!',
            'sprint' => $futureSprint
        ], 201);
    }

    //Gán 1 user story vào future sprint
    public function assignFutureSprint(Request $request, Tasks $task){
        $user = Auth::user();
        $team = $user->teams()->first();
        $userRoleInTeam = $team->users()->find($user->id)?->pivot->roleInTeam;
        if($userRoleInTeam !== 'product_owner'){
            return response()->json([
                'message'=>'Bạn không có quyền gán User Story vào Future Sprint. Chỉ Product Owner mới được phép.'
            ], 403);
        }
        $validated = $request->validate([
            'sprint_id'=>'required|exists:sprints,id',
        ]);

        //sprint phải thuộc team, đang planing và ko active
        $sprint = Sprints::where('id', $validated['sprint_id'])
                ->where('team_id', $team->id)
                ->where('status', 'planning')
                ->where('is_active', false)
                ->first();
        if(!$sprint){
            return response()->json([
                'message'=>'Sprint không hợp lệ, không phải planning và ko thuộc team'
            ], 422);
        }
        
        // Đảm bảo story thuộc cùng team: nếu có epic thì check team theo epic
        if($task->epic_id){
            $epic = Epics::find($task->epic_id);
            if(!$epic || $epic->team_id !== $team->id){
                return response()->json([
                    'message'=>'User Story không thuộc team của bạn.'
                ], 422);
            }
        }
        
        // Nếu story đã nằm trong sprint này rồi thì không làm gì
        if($task->sprint_id === $sprint->id){
            return response()->json([
                'message'=>'User Story đã nằm trong Sprint này rồi.',
                'story'=>$task,
            ]);
        }
        
        //Set order_index = max + 1 trong sprint
        $maxOrder = Tasks::where('sprint_id', $sprint->id)->max('order_index');
        $nextOrder = is_null($maxOrder) ? 1 : $maxOrder + 1;
        $task->update([
            'sprint_id'=>$sprint->id,
            'order_index'=>$nextOrder,
        ]);
        return response()->json([
            'message'=>'Gán User Story vào Future Sprint thành công!',
            'story'=>$task,
        ]);
    }
        //REORDER stories trong cùng 1 epic hoặc cùng 1 sprint
        public function reorderUserStories(Request $request){
            $user = Auth::user();
            $team = $user->teams()->first();
            $userRoleInTeam = $team->users()->find($user->id)?->pivot->roleInTeam;
            if($userRoleInTeam !== 'product_owner'){
                return response()->json([
                    'message'=>'Bạn không có quyền sắp xếp lại User Stories. Chỉ Product Owner mới được phép.'
                ], 403);
            }
            $data = $request->validate([
                'scope'    => ['required', Rule::in(['epic', 'sprint'])],
                'scope_id' => 'required|integer',
                'ids'      => 'required|array|min:1',
                'ids.*'    => 'integer|exists:tasks,id',
            ]);
            
            // Xác thực scope thuộc team của user
            if($data['scope'] === 'sprint'){
                $sprint = Sprints::where('id', $data['scope_id'])
                    ->where('team_id', $team->id)
                    ->first();
                if(!$sprint){
                    return response()->json([
                        'message'=>'Sprint không hợp lệ hoặc không thuộc team của bạn.'
                    ], 422);
                }
            } else if($data['scope'] === 'epic'){
                $epic = Epics::where('id', $data['scope_id'])
                    ->where('team_id', $team->id)
                    ->first();
                if (!$epic) {
                    return response()->json(['message' => 'Epic không thuộc team.'], 403);
                }
            }

            // Kiểm tra tất cả task đều thuộc đúng scope
            $tasks = Tasks::whereIn('id', $data['ids']);
            if($data['scope'] === 'sprint'){
                $tasks->where('sprint_id', $data['scope_id']);
            } else {
                $tasks->where('epic_id', $data['scope_id']);
            }
            
            if($tasks->count() !== count($data['ids'])){
                return response()->json([
                    'message' => 'Một hoặc nhiều User Story không thuộc đúng Epic/Sprint này.'
                ], 422);
            }

            DB::transaction(function () use ($data) {
                foreach ($data['ids'] as $index => $taskId) {
                    $update = ['order_index' => $index + 1];
                    if ($data['scope'] === 'sprint') {
                        // đảm bảo task nằm đúng scope
                        Tasks::where('id', $taskId)->where('sprint_id', $data['scope_id'])->update($update);
                    } else {
                        Tasks::where('id', $taskId)->where('epic_id', $data['scope_id'])->update($update);
                    }
                }
            });

            return response()->json(['message' => 'Cập nhật thứ tự thành công!']);
        }
        //UPDATE Future Sprint

        public function updateFutureSprint(Request $request, Sprints $sprint)
        {
            $user = Auth::user();
            $team = $user->teams()->first();
            $userRoleInTeam = $team->users()->find($user->id)?->pivot->roleInTeam;

            // 1. Check quyền
            if (!in_array($userRoleInTeam, ['product_owner', 'scrum_master'])) {
                return response()->json([
                    'message' => 'Bạn không có quyền sửa Future Sprint. Chỉ Product Owner hoặc Scrum Master mới được phép.'
                ], 403);
            }

            // 2. ✅ THÊM: Check chỉ cho sửa Planning Sprint
            if ($sprint->is_active === true || $sprint->status !== 'planning') {
                return response()->json([
                    'message' => 'Không thể sửa Sprint đang hoạt động hoặc đã hoàn thành. Chỉ có thể sửa Future Sprint (Planning).'
                ], 422);
            }

            // 3. Validate
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'goal' => 'nullable|string',
                'start_date' => 'nullable|date|after_or_equal:today',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            // 4. Update
            $sprint->update([
                'name' => $validated['name'],
                'goal' => $validated['goal'] ?? null,
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
            ]);

            // 5. Return response
            return response()->json([
                'message' => 'Cập nhật Future Sprint thành công!',
                'sprint' => $sprint  // ← Trả về model đã update
            ]);
        }
        public function destroyFutureSprint(Sprints $sprint){
            $user = Auth::user();
            $team = $user->teams()->first();
            $userRoleInTeam = $team->users()->find($user->id)?->pivot->roleInTeam;
            if(!in_array($userRoleInTeam, ['product_owner', 'scrum_master'])) {
                return response()->json([
                    'message' => 'Bạn không có quyền xóa Future Sprint. Chỉ Product Owner hoặc Scrum Master mới được phép.'
                ], 403);
            }
           //check ko cho xóa Active sprint
           if($sprint->is_active === true){
                return response()->json([
                    'message' => 'Không thể xóa Sprint đang hoạt động.'
                ], 422);
           }
           // 3. Xử lý User Stories trong Sprint: Đưa về backlog (set sprint_id = NULL)
            $sprint->tasks()->update([
                'sprint_id' => null
            ]);
            // 4. Xóa Sprint
            $sprint->delete();

            return response()->json([
                'message' => 'Xóa Future Sprint thành công.'
            ]);
        }









    /**
     * add a new Epic.
     */
    public function storeEpic(Request $request)
    {
        //Kiểm tra quyền
        $user = Auth::user();
        $team = $user->teams()->first();
        $userRoleInTeam = $team->users()->find($user->id)?->pivot->roleInTeam;

        // Nếu KHÔNG phải Product Owner → từ chối
        if ($userRoleInTeam !== 'product_owner') {
            return response()->json([
                'message' => 'Bạn không có quyền tạo Epic.'
            ], 403);  // 403 = Forbidden (Cấm)
        }
        // ===== 2. VALIDATE DỮ LIỆU =====
        $validated = $request->validate([
            'title' => 'required|string|max:255',  // Bắt buộc, tối đa 255 ký tự
            'description' => 'nullable|string',    // Tùy chọn
        ]);
        $epic = Epics::create([
            'team_id' => $team->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? '',
        ]);

         // ===== 4. TRẢ VỀ KẾT QUẢ CHO JAVASCRIPT =====
        return response()->json([
            'message' => 'Tạo Epic thành công!',
            'epic' => $epic  // Trả về Epic vừa tạo (có id, title, description...)
        ], 201);  // 201 = Created (Đã tạo)
    }

    /**
     * Update an existing Epic.
     */
    public function updateEpic(Request $request, Epics $epic)
    {
        // Kiểm tra quyền
        $user = Auth::user();
        $team = $user->teams()->first();
        $userRoleInTeam = $team ? $team->users()->find($user->id)?->pivot->roleInTeam : null;

        // Chỉ Product Owner mới được sửa Epic
        if ($userRoleInTeam !== 'product_owner') {
            return response()->json([
                'message' => 'Bạn không có quyền sửa Epic.'
            ], 403);
        }

        // Validate dữ liệu
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Cập nhật Epic
        $epic->update($validated);

        return response()->json([
            'message' => 'Epic updated successfully!',
            'epic' => $epic
        ]);
    }

    /**
     * Delete an Epic.
     */
    public function destroyEpic(Epics $epic)
    {
        // Kiểm tra quyền
        $user = Auth::user();
        $team = $user->teams()->first();
        $userRoleInTeam = $team ? $team->users()->find($user->id)?->pivot->roleInTeam : null;

        // Chỉ Product Owner mới được xóa Epic
        if ($userRoleInTeam !== 'product_owner') {
            return response()->json([
                'message' => 'Bạn không có quyền xóa Epic.'
            ], 403);
        }

        // Xóa Epic
        $epic->delete();

        return response()->json([
            'message' => 'Epic deleted successfully!'
        ]);
    }

    /**
     * add a new User Story.
     */
    public function storeUserStory(Request $request)
    {
        // Kiểm tra quyền
        $user = Auth::user();
        $team = $user->teams()->first();
        $userRoleInTeam = $team ? $team->users()->find($user->id)?->pivot->roleInTeam : null;

        // Chỉ Product Owner mới được tạo User Story
        if ($userRoleInTeam !== 'product_owner') {
            return response()->json([
                'message' => 'Bạn không có quyền tạo User Story.'
            ], 403);
        }

        // Validate dữ liệu
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => ['required', Rule::in(['toDo', 'inProgress', 'done'])],
            'storyPoints' => 'nullable|integer|min:0',
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
            'assigned_to' => 'nullable|exists:users,id',
            'epic_id' => 'required|exists:epics,id',
        ]);

        // Tính order_index mới cho epic
        $maxOrder = Tasks::where('epic_id', $validated['epic_id'])->max('order_index');
        $nextOrder = is_null($maxOrder) ? 1 : $maxOrder + 1;

        // Tạo User Story (Task với parent_id = null)
        $userStory = Tasks::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? '',
            'status' => $validated['status'],
            'storyPoints' => $validated['storyPoints'],
            'priority' => $validated['priority'],
            'assigned_to' => $validated['assigned_to'],
            'epic_id' => $validated['epic_id'],
            'created_by' => Auth::id(),
            'sprint_id' => null, // Mặc định chưa thuộc sprint nào
            'parent_id' => null, // Đây là User Story (task cha)
            'order_index' => $nextOrder,
        ]);

        return response()->json([
            'message' => 'Tạo User Story thành công!',
            'story' => $userStory->load('assignee', 'epic')
        ], 201);
    }

    /**
     * Update an existing User Story.
     */
    public function updateUserStory(Request $request, Tasks $task)
    {
        // Kiểm tra quyền
        $user = Auth::user();
        $team = $user->teams()->first();
        $userRoleInTeam = $team ? $team->users()->find($user->id)?->pivot->roleInTeam : null;

        // Chỉ Product Owner mới được sửa User Story
        if ($userRoleInTeam !== 'product_owner') {
            return response()->json([
                'message' => 'Bạn không có quyền sửa User Story.'
            ], 403);
        }

        // Validate dữ liệu
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => ['required', Rule::in(['toDo', 'inProgress', 'done'])],
            'storyPoints' => 'nullable|integer|min:0',
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        // Cập nhật User Story
        $task->update($validated);

        return response()->json([
            'message' => 'User Story updated successfully!',
            'story' => $task->load('assignee', 'epic')
        ]);
    }

    /**
     * Delete a User Story.
     */
    public function destroyUserStory(Tasks $task)
    {
        // Kiểm tra quyền
        $user = Auth::user();
        $team = $user->teams()->first();
        $userRoleInTeam = $team ? $team->users()->find($user->id)?->pivot->roleInTeam : null;

        // Chỉ Product Owner mới được xóa User Story
        if ($userRoleInTeam !== 'product_owner') {
            return response()->json([
                'message' => 'Bạn không có quyền xóa User Story.'
            ], 403);
        }

        // Xóa User Story
        $task->delete();

        return response()->json([
            'message' => 'User Story deleted successfully!'
        ]);
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
                 ->withCount('comments')
                             ->orderBy('created_at', 'desc')
                             ->get();
        // dd($backlogTasks->toArray());
        // Lấy các task trong sprint đang hoạt động và lấy luôn cả người được gán cho task đó, false thì tạo ra 1 collection rỗng
    $sprintTasks = $activeSprint ? $activeSprint->tasks()->with('assignee')->withCount('comments')->get() : collect();

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

        // Kiểm tra quyền: Product Owner HOẶC Scrum Master/Developer (khi tạo subtask)
        $isCreatingSubtask = $request->has('parent_id') && !empty($request->parent_id);
        
        if (!$isCreatingSubtask && $userRoleInTeam !== 'product_owner') {
            return response()->json(['message' => 'Bạn không có quyền tạo task.'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
            'storyPoints' => 'nullable|integer',
            'assigned_to' => 'nullable|exists:users,id',
            'parent_id' => 'nullable|exists:tasks,id', // Cho phép tạo subtask
            'sprint_id' => 'nullable|exists:sprints,id',
            'status' => ['nullable', Rule::in(['toDo', 'inProgress', 'done'])],
        ]);

        $task = Tasks::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'priority' => $validated['priority'],
            'storyPoints' => $validated['storyPoints'] ?? null,
            'assigned_to' => $validated['assigned_to'] ?? null,
            'created_by' => Auth::id(),
            'parent_id' => $validated['parent_id'] ?? null,
            'sprint_id' => $validated['sprint_id'] ?? null,
            'status' => $validated['status'] ?? 'toDo',
        ]);

        return response()->json([
            'message' => $isCreatingSubtask ? 'Tạo subtask thành công!' : 'Tạo task thành công!', 
            'task' => $task->load('assignee')
        ], 201);
    }

    /**
     * Load danh sách tasks (dùng để lấy subtasks)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTasks(Request $request)
    {
        /** @var Request $request */
        
        $query = Tasks::with('assignee');

        // Lọc theo parent_id nếu có (để lấy subtasks của một User Story)
        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }

        $tasks = $query->get();
        return response()->json($tasks);
    }

    /**
     * Hiển thị form sửa task
     * 
     * @param Tasks $task
     * @return \Illuminate\Http\JsonResponse
     */
   public function edit(Tasks $task)
    {
        /** @var Tasks $task - Route model binding */
        
        $user = Auth::user();
        $team = $user->teams()->first();
        $userRoleInTeam = $team ? $team->users()->find($user->id)?->pivot->roleInTeam : null;
        
        // Cho phép Product Owner xem subtasks trong sprint
        $isSubtask = !is_null($task->parent_id);
        
        // Chỉ Product Owner mới có quyền xem thông tin để edit
        if ($userRoleInTeam !== 'product_owner') {
            return response()->json(['message' => 'Bạn không có quyền sửa task này.'], 403);
        }
        
        // Nếu là User Story (không phải subtask), chỉ cho phép edit nếu chưa vào sprint
        if (!$isSubtask && $task->sprint_id !== null) {
            return response()->json(['message' => 'Không thể sửa User Story đã vào sprint.'], 403);
        }
        
        // Load relationship để trả về đầy đủ thông tin
        return response()->json(['task' => $task->load('assignee', 'epic')]);
    }

    /**
     * Cập nhật task (bao gồm subtasks)
     * 
     * @param Request $request
     * @param Tasks $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Tasks $task)
    {
        /** @var Tasks $task - Route model binding */
        /** @var Request $request */
        
        $user = Auth::user();
        $team = $user->teams()->first();
        $userRoleInTeam = $team ? $team->users()->find($user->id)?->pivot->roleInTeam : null;

        // Cho phép Product Owner cập nhật subtasks trong sprint
        $isSubtask = !is_null($task->parent_id);
        
        // Kiểm tra quyền: PO có thể update subtasks, hoặc User Stories chưa vào sprint
        if ($userRoleInTeam !== 'product_owner') {
            return response()->json(['message' => 'Bạn không có quyền cập nhật task này.'], 403);
        }
        
        if (!$isSubtask && $task->sprint_id !== null) {
            return response()->json(['message' => 'Không thể cập nhật User Story đã vào sprint.'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
            'status' => ['nullable', Rule::in(['toDo', 'inProgress', 'done'])],
            'storyPoints' => 'nullable|integer',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $task->update($validated);
        return response()->json(['message' => 'Cập nhật task thành công!', 'task' => $task->load('assignee')]);
    }

    /**
     * Xóa task (bao gồm subtasks)
     * 
     * @param Tasks $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Tasks $task)
    {
        /** @var Tasks $task - Route model binding */
        
        $user = Auth::user();
        $team = $user->teams()->first();
        $userRoleInTeam = $team ? $team->users()->find($user->id)?->pivot->roleInTeam : null;

        // Cho phép Product Owner xóa subtasks trong sprint
        $isSubtask = !is_null($task->parent_id);
        
        // Kiểm tra quyền: PO có thể xóa subtasks, hoặc User Stories chưa vào sprint
        if ($userRoleInTeam !== 'product_owner') {
            return response()->json(['message' => 'Bạn không có quyền xóa task này.'], 403);
        }
        
        if (!$isSubtask && $task->sprint_id !== null) {
            return response()->json(['message' => 'Không thể xóa User Story đã vào sprint.'], 403);
        }

        $task->delete();
        return response()->json(['message' => 'Xóa task thành công!']);
    }

    /**
     * Gợi ý task bằng AI
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function suggestAllWithAI(Request $request)
    {
        /** @var Request $request */
        
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
