<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Tasks;
use App\Models\TasksComments;

/**
 * Controller xử lý trang User Profile
 * Hiển thị thông tin cá nhân, thống kê và hoạt động của user đang đăng nhập
 */
class UserProfileController extends Controller
{
    /**
     * Hiển thị trang profile của user
     * Route: GET /profile
     */
    public function index()
    {
        // Lấy thông tin user hiện tại đang đăng nhập
        $user = Auth::user();
        
        // ===== LẤY THÔNG TIN TEAM VÀ ROLE =====
        // Lấy team đầu tiên mà user thuộc về (từ bảng team_members)
        $team = $user->teams()->first();
        // Lấy role của user trong team từ pivot table (product_owner, scrum_master, developer)
        $roleInTeam = $team ? $team->pivot->roleInTeam : null;
        
        // Chuyển đổi role thành dạng hiển thị đẹp hơn
        $roleDisplay = match($roleInTeam) {
            'product_owner' => 'Product Owner',
            'scrum_master' => 'Scrum Master',
            'developer' => 'Developer',
            default => 'No Role Assigned'
        };
        
        // ===== THỐNG KÊ TASKS CỦA USER =====
        // Đếm số lượng tasks đã hoàn thành (status = done)
        $tasksDone = Tasks::where('assigned_to', $user->id)
            ->where('status', 'done')
            ->count();
        
        // Đếm số lượng tasks đang làm (status = inProgress)
        $tasksInProgress = Tasks::where('assigned_to', $user->id)
            ->where('status', 'inProgress')
            ->count();
        
        // Đếm số lượng tasks chưa bắt đầu (status = toDo)
        $tasksTodo = Tasks::where('assigned_to', $user->id)
            ->where('status', 'toDo')
            ->count();
        
        // Tính tổng story points từ các tasks đã hoàn thành
        // ?? 0 nghĩa là nếu null thì trả về 0
        $totalStoryPoints = Tasks::where('assigned_to', $user->id)
            ->where('status', 'done')
            ->sum('storyPoints') ?? 0;
        
        // Tính phần trăm hoàn thành = (số tasks done / tổng số tasks) * 100
        $totalTasks = Tasks::where('assigned_to', $user->id)->count();
        $averageCompletion = $totalTasks > 0 ? round(($tasksDone / $totalTasks) * 100) : 0;
        
        // ===== LẤY DANH SÁCH SUBTASKS =====
        // Lấy các subtasks (tasks có parent_id) được gán cho user
        // with() để eager load luôn thông tin parent task và sprint (tránh N+1 query)
        $subtasks = Tasks::where('assigned_to', $user->id)
            ->whereNotNull('parent_id') // Chỉ lấy subtasks (có parent)
            ->with(['parent:id,title', 'sprint:id,name,endDate']) // Load kèm parent và sprint
            ->orderBy('created_at', 'desc') // Sắp xếp mới nhất trước
            ->limit(10) // Giới hạn 10 subtasks
            ->get();
        
        // ===== LẤY HOẠT ĐỘNG GÂN ĐÂY =====
        // Tạo collection rỗng để chứa các hoạt động
        $activities = collect();
        
        // 1. Lấy các tasks vừa hoàn thành
        $completedTasks = Tasks::where('assigned_to', $user->id)
            ->where('status', 'done')
            ->orderBy('updated_at', 'desc') // Sắp xếp theo thời gian update
            ->limit(5)
            ->get(['id', 'title', 'updated_at']); // Chỉ lấy các field cần thiết
        
        // Thêm vào activities với type = completed
        foreach ($completedTasks as $task) {
            $activities->push([
                'type' => 'completed',
                'message' => 'Completed "' . $task->title . '"',
                'date' => $task->updated_at,
                'icon' => 'check'
            ]);
        }
        
        // 2. Lấy các comments gần đây của user này
        $recentComments = TasksComments::where('user_id', $user->id)
            ->with('task:id,title') // Load kèm thông tin task được comment
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Thêm vào activities với type = commented
        foreach ($recentComments as $comment) {
            $activities->push([
                'type' => 'commented',
                'message' => 'Commented on "' . ($comment->task->title ?? 'Deleted Task') . '"',
                'date' => $comment->created_at,
                'icon' => 'comment'
            ]);
        }
        
        // 3. Lấy các tasks mới được gán (chưa bắt đầu làm)
        $newTasks = Tasks::where('assigned_to', $user->id)
            ->where('status', 'toDo')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'title', 'created_at']);
        
        // Thêm vào activities với type = assigned
        foreach ($newTasks as $task) {
            $activities->push([
                'type' => 'assigned',
                'message' => 'Assigned new task "' . $task->title . '"',
                'date' => $task->created_at,
                'icon' => 'plus'
            ]);
        }
        
        // Sắp xếp tất cả activities theo ngày mới nhất, giới hạn 10 activities
        // values() để reset lại keys của collection
        $activities = $activities->sortByDesc('date')->take(10)->values();
        
        // Trả về view với tất cả dữ liệu đã chuẩn bị
        return view('pages.user-profile', compact(
            'user',              // Thông tin user
            'team',              // Team của user
            'roleDisplay',       // Role hiển thị đẹp
            'tasksDone',         // Số tasks hoàn thành
            'tasksInProgress',   // Số tasks đang làm
            'tasksTodo',         // Số tasks chưa bắt đầu
            'totalStoryPoints',  // Tổng story points
            'averageCompletion', // % hoàn thành
            'subtasks',          // Danh sách subtasks
            'activities'         // Danh sách hoạt động gần đây
        ));
    }
}
