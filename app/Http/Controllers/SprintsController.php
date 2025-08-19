<?php
// File: app/Http/Controllers/SprintsController.php
// Controller này đã khá tốt, chỉ cần chỉnh sửa nhỏ để code sạch hơn

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Tasks;
use App\Models\Sprints;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SprintsController extends Controller
{
    public function create()
    {
        $user = Auth::user();
        if (!in_array($user->role, ['scrum_master', 'leadDeveloper'])) {
            return redirect()->route('dashboard')->with('error', 'Bạn không có quyền truy cập trang này.');
        }

        $team = $user->team();

        if (!$team) {
            return redirect()->route('dashboard')->with('error', 'Bạn chưa thuộc team nào.');
        }

        $activeSprint = $team->activeSprint;

        $backlogTasks = collect();
        if (!$activeSprint) {
            // Lấy các task chưa thuộc bất kỳ sprint nào
            $backlogTasks = Tasks::whereNull('sprint_id')->orderBy('priority')->get();
        }

        return view('pages.sprintPlanning', compact('backlogTasks', 'activeSprint'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['scrum_master', 'leadDeveloper'])) {
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
            $team = $user->team();
            if (!$team) {
                throw new \Exception('User is not assigned to any team.');
            }

            // Hủy các sprint đang hoạt động của team này
            $team->sprints()->where('is_active', true)->update(['is_active' => false]);

            // Tạo sprint mới
            $sprint = $team->sprints()->create([
                'name' => $validated['name'],
                'goal' => $validated['goal'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'is_active' => true,
            ]);

            // Cập nhật các task được chọn vào sprint mới
            Tasks::whereIn('id', $validated['task_ids'])
                ->update([
                    'sprint_id' => $sprint->id,
                    'status' => 'toDo',
                ]);

            DB::commit();

            return response()->json([
                'message' => 'Sprint created successfully',
                'redirect' => route('tasksboard')
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

        $team = $user->team();
        $activeSprint = $team ? $team->activeSprint : null;

        if (!$activeSprint) {
            return back()->with('error', 'Không có Sprint nào đang hoạt động để hủy.');
        }

        DB::beginTransaction();
        try {
            // Đưa các task chưa "Done" trở lại Product Backlog
            $activeSprint->tasks()
                 ->where('status', '!=', 'done')
                 ->update(['sprint_id' => null, 'status' => 'toDo']);

            // Hủy kích hoạt sprint
            $activeSprint->is_active = false;
            $activeSprint->status = 'completed'; // Hoặc một trạng thái 'cancelled' nếu bạn muốn
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
