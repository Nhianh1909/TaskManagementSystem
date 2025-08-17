<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Tasks;
use Illuminate\Support\Facades\DB;
use App\Models\Sprints;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Stmt\TryCatch;

class SprintsController extends Controller
{
    // trong file: app/Http/Controllers/SprintsController.php

    public function create()
    {
        $user = Auth::user();
        if (!in_array($user->role, ['scrum_master', 'leadDeveloper'])) {
            return redirect()->route('dashboard')->with('error', 'Bạn không có quyền truy cập trang này.');
        }

        $teamId = $user->teams()->first()->id ?? null;

        // Kiểm tra xem có sprint nào đang hoạt động cho team này không
        $activeSprint = Sprints::where('team_id', $teamId)->where('is_active', true)->first();

        // Khởi tạo backlogTasks là một collection rỗng
        $backlogTasks = collect();

        // Chỉ lấy backlog nếu không có sprint nào đang chạy
        if (!$activeSprint) {
            // SỬA LẠI DÒNG NÀY: Bỏ điều kiện where('team_id', $teamId)
            $backlogTasks = Tasks::whereNull('sprint_id')->orderBy('priority')->get();
        }

        // Luôn gửi cả 2 biến sang view
        return view('pages.sprintPlanning', compact('backlogTasks', 'activeSprint'));
    }

    public function store(Request $request){
        $user = Auth::user();
        if(!in_array($user->role, ['scrum_master', 'leadDeveloper'])){
            return response()->json(['message' => 'Bạn không có quyền thực hiện hành động này.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'goal' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:tasks,id',
        ]);

        DB::beginTransaction();
        try {
            // Lấy team_id từ user đang đăng nhập
            // Giả sử mỗi user chỉ thuộc về một team thông qua bảng team_members
            $teamId = $user->teams()->first()->id ?? null;

            // Nếu người dùng không thuộc team nào, không cho phép tạo sprint
            if (!$teamId) {
                throw new \Exception('User is not assigned to any team.');
            }

            // Hủy kích hoạt các sprint đang hoạt động của team này
            Sprints::where('team_id', $teamId)->where('is_active', true)->update(['is_active' => false]);

            // Tạo sprint mới và kích hoạt nó
            $sprint = Sprints::create([
                'name' => $validated['name'],
                'goal' => $validated['goal'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'is_active' => true,
                'team_id' => $teamId, // <-- Gán team_id cho sprint
            ]);

            // Cập nhật các task được chọn
            Tasks::whereIn('id', $validated['task_ids'])
                ->update([
                    'sprint_id'=> $sprint->id,
                    'status' => 'toDo',
                ]);

            DB::commit();

            return response()->json([
                'message'=>'Sprint created successfully',
                'redirect'=> route('tasksboard')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating sprint: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while starting the sprint.'], 500);
        }
    }
    public function cancel()
    {
        $user = Auth::user();
        if (!in_array($user->role, ['scrum_master', 'leadDeveloper'])) {
            return back()->with('error', 'Bạn không có quyền thực hiện hành động này.');
        }

        $teamId = $user->teams()->first()->id ?? null;
        $activeSprint = Sprints::where('team_id', $teamId)->where('is_active', true)->first();

        if (!$activeSprint) {
            return back()->with('error', 'Không có Sprint nào đang hoạt động để hủy.');
        }

        DB::beginTransaction();
        try {
            // 1. Đưa các task chưa "Done" trở lại Product Backlog
            Tasks::where('sprint_id', $activeSprint->id)
                 ->where('status', '!=', 'done')
                 ->update(['sprint_id' => null]);

            // 2. Hủy kích hoạt sprint
            $activeSprint->is_active = false;
            $activeSprint->save();

            DB::commit();
            return redirect()->route('sprint.create')->with('success', 'Sprint đã được hủy thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error cancelling sprint: ' . $e->getMessage());
            return back()->with('error', 'Đã có lỗi xảy ra khi hủy Sprint.');
        }
    }
}
