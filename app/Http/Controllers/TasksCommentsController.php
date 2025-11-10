<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Tasks;
use App\Models\TasksComments;

class TasksCommentsController extends Controller
{
    /**
     * Lưu bình luận cho 1 task.
     */
    public function store(Request $request, Tasks $task)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:5000',
            'parent_id' => 'nullable|exists:tasks_comments,id',
        ]);

        $comment = TasksComments::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
        ]);

        $comment->load('user');

        return response()->json([
            'message' => 'Comment added successfully.',
            'comment' => [
                'id' => $comment->id,
                'content' => $comment->content,
                'created_at' => $comment->created_at->toDateTimeString(),
                'user' => [
                    'id' => $comment->user->id ?? null,
                    'name' => $comment->user->name ?? 'Unknown',
                ],
            ],
        ], 201);
    }

    /**
     * Lấy danh sách bình luận của task (mới nhất trước), hỗ trợ cursor bằng tham số 'before'.
     */
    public function index(Request $request, Tasks $task)
    {
        $limit = (int) $request->query('limit', 10);
        $limit = max(1, min(20, $limit));
        $beforeId = $request->query('before');

        $query = $task->comments()->with('user')->orderByDesc('id');
        if ($beforeId) {
            $query->where('id', '<', $beforeId);
        }

        // Lấy dư 1 để biết còn nữa không
        $comments = $query->take($limit + 1)->get();
        $hasMore = $comments->count() > $limit;
        $comments = $comments->take($limit);
        $nextBefore = $hasMore ? $comments->last()->id : null;

        $payload = $comments->map(function ($c) {
            return [
                'id' => $c->id,
                'content' => $c->content,
                'created_at' => $c->created_at->toDateTimeString(),
                'user' => [
                    'id' => $c->user->id ?? null,
                    'name' => $c->user->name ?? 'Unknown',
                ],
            ];
        });

        return response()->json([
            'data' => $payload,
            'has_more' => $hasMore,
            'next_before' => $nextBefore,
        ]);
    }
}
