<?php

namespace App\Http\Controllers;

use App\Models\TaskStatus;
use App\Models\Tasks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaskStatusController extends Controller
{
    /**
     * Lấy danh sách tất cả status columns (theo team)
     */
    public function index()
    {
        $user = Auth::user();
        $team = $user->teams()->first();

        if (!$team) {
            return response()->json(['message' => 'Bạn chưa thuộc team nào.'], 403);
        }

        // Lấy các status của team, sắp xếp theo order_index
        $statuses = TaskStatus::where('team_id', $team->id)
            ->orderBy('order_index', 'asc')
            ->get();

        return response()->json(['statuses' => $statuses]);
    }

    /**
     * Tạo status column mới
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $team = $user->teams()->first();
        $userRole = $team ? $team->users()->find($user->id)?->pivot->roleInTeam : null;

        // Chỉ Scrum Master hoặc Product Owner mới được tạo cột
        if (!in_array($userRole, ['scrum_master', 'product_owner'])) {
            return response()->json(['message' => 'Bạn không có quyền tạo cột.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color_class' => 'nullable|string|max:100',
            'is_done' => 'nullable|boolean',
        ]);

        // Kiểm tra tên có trùng không (trong cùng team)
        $exists = TaskStatus::where('team_id', $team->id)
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Tên cột đã tồn tại.'], 422);
        }

        // Tính order_index mới (max + 1)
        $maxOrder = TaskStatus::where('team_id', $team->id)->max('order_index');
        $nextOrder = is_null($maxOrder) ? 1 : $maxOrder + 1;

        $status = TaskStatus::create([
            'name' => $validated['name'],
            'order_index' => $nextOrder,
            'is_done' => $validated['is_done'] ?? false,
            'color_class' => $validated['color_class'] ?? 'border-gray-300',
            'team_id' => $team->id,
        ]);

        return response()->json([
            'message' => 'Tạo cột thành công!',
            'status' => $status
        ], 201);
    }

    /**
     * Sắp xếp lại thứ tự các cột (drag & drop columns)
     */
    public function reorder(Request $request)
    {
        $user = Auth::user();
        $team = $user->teams()->first();
        $userRole = $team ? $team->users()->find($user->id)?->pivot->roleInTeam : null;

        if (!in_array($userRole, ['scrum_master', 'product_owner'])) {
            return response()->json(['message' => 'Bạn không có quyền sắp xếp cột.'], 403);
        }

        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:task_statuses,id',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['order'] as $index => $statusId) {
                TaskStatus::where('id', $statusId)->update(['order_index' => $index + 1]);
            }
        });

        return response()->json(['message' => 'Sắp xếp cột thành công!']);
    }

    /**
     * Chuyển tất cả task từ cột này sang cột khác (trước khi xóa)
     */
    public function moveTasks(Request $request, TaskStatus $taskStatus)
    {
        $user = Auth::user();
        $team = $user->teams()->first();
        $userRole = $team ? $team->users()->find($user->id)?->pivot->roleInTeam : null;

        if (!in_array($userRole, ['scrum_master', 'product_owner'])) {
            return response()->json(['message' => 'Bạn không có quyền di chuyển task.'], 403);
        }

        $validated = $request->validate([
            'move_to_status_id' => 'required|exists:task_statuses,id',
        ]);

        $taskCount = Tasks::where('status_id', $taskStatus->id)->count();

        if ($taskCount === 0) {
            return response()->json(['message' => 'Cột này không có task nào.'], 422);
        }

        // Di chuyển tất cả task sang cột mới
        Tasks::where('status_id', $taskStatus->id)
            ->update(['status_id' => $validated['move_to_status_id']]);

        return response()->json([
            'message' => "Đã chuyển {$taskCount} task sang cột mới.",
            'moved_count' => $taskCount
        ]);
    }

    /**
     * Xóa status column (có thể di chuyển tasks sang cột khác)
     */
    public function destroy(Request $request, TaskStatus $taskStatus)
    {
        $user = Auth::user();
        $team = $user->teams()->first();
        $userRole = $team ? $team->users()->find($user->id)?->pivot->roleInTeam : null;

        if (!in_array($userRole, ['scrum_master', 'product_owner'])) {
            return response()->json(['message' => 'Bạn không có quyền xóa cột.'], 403);
        }

        // Kiểm tra còn task không
        $taskCount = Tasks::where('status_id', $taskStatus->id)->count();

        if ($taskCount > 0) {
            // Nếu user provide move_to_status_id, di chuyển tasks trước
            $moveToStatusId = $request->input('move_to_status_id');

            if (!$moveToStatusId) {
                return response()->json([
                    'message' => "Cột này còn {$taskCount} task. Vui lòng chọn cột để chuyển task.",
                    'task_count' => $taskCount,
                    'can_delete' => false
                ], 422);
            }

            // Validate target status tồn tại
            $targetStatus = TaskStatus::find($moveToStatusId);
            if (!$targetStatus || $targetStatus->team_id !== $team->id) {
                return response()->json(['message' => 'Cột đích không hợp lệ.'], 422);
            }

            // Di chuyển tất cả tasks
            DB::transaction(function () use ($taskStatus, $moveToStatusId) {
                Tasks::where('status_id', $taskStatus->id)
                    ->update(['status_id' => $moveToStatusId]);
            });
        }

        $taskStatus->delete();

        return response()->json(['message' => 'Xóa cột thành công!']);
    }
}
