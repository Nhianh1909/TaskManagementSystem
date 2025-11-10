<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Tasks;
use App\Models\TasksComments;

class TasksCommentsController extends Controller
{
    /**
     * Lấy tất cả comments của một User Story
     */
    public function index(Tasks $task)
    {
        // Kiểm tra quyền - tất cả team members có thể xem comments
        $user = Auth::user();
        $team = $user->teams()->first();
        
        if (!$team) {
            return response()->json(['message' => 'Bạn chưa thuộc team nào.'], 403);
        }

        // Load comments với user info và replies (đa cấp)
        $comments = $task->comments()
            ->whereNull('parent_id') // Chỉ lấy comments gốc (không phải reply)
            ->with([
                'user',
                'replies.user',
                'replies.replies.user',
                'replies.replies.replies.user',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'comments' => $comments
        ]);
    }

    /**
     * Thêm comment mới cho User Story
     */
    public function store(Request $request, Tasks $task)
    {
        $user = Auth::user();
        $team = $user->teams()->first();

        // Tất cả team members có thể comment
        if (!$team) {
            return response()->json(['message' => 'Bạn chưa thuộc team nào.'], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:2000',
            'parent_id' => 'nullable|exists:tasks_comments,id', // Cho reply
        ]);

        $comment = TasksComments::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
        ]);

        // Load lại với user info
        $comment->load('user');

        return response()->json([
            'message' => 'Comment đã được thêm!',
            'comment' => $comment
        ], 201);
    }

    /**
     * Sửa comment của mình
     */
    public function update(Request $request, TasksComments $comment)
    {
        $user = Auth::user();

        // Chỉ người tạo comment mới được sửa
        if ($comment->user_id !== $user->id) {
            return response()->json(['message' => 'Bạn không có quyền sửa comment này.'], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $comment->update([
            'content' => $validated['content'],
        ]);

        return response()->json([
            'message' => 'Comment đã được cập nhật!',
            'comment' => $comment
        ]);
    }

    /**
     * Xóa comment của mình
     */
    public function destroy(TasksComments $comment)
    {
        $user = Auth::user();
        $team = $user->teams()->first();
        $userRoleInTeam = $team ? $team->users()->find($user->id)?->pivot->roleInTeam : null;

        // Chỉ người tạo hoặc Product Owner/Scrum Master mới được xóa
        if ($comment->user_id !== $user->id && !in_array($userRoleInTeam, ['product_owner', 'scrum_master'])) {
            return response()->json(['message' => 'Bạn không có quyền xóa comment này.'], 403);
        }

        $comment->delete();

        return response()->json([
            'message' => 'Comment đã được xóa!'
        ]);
    }
}
