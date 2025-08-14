<?php

namespace App\Http\Controllers;
use App\Models\Tasks;
use Illuminate\Http\Request;
use App\Models\Sprints;
use App\Models\User;

class TasksController extends Controller
{
    public function index()
    {
        $tasksToDo = Tasks::where('status', 'toDo')->count();
        $tasksInProgress = Tasks::where('status', 'inProgress')->count();
        $tasksCompletedToday = Tasks::whereDate('updated_at', now())
                                    ->where('status', 'done')
                                    ->count();
        $SprintActive = Sprints::where('is_active', true)->count();
        $members = User::where('role', 'developer')->count();


        //lấy activity từ tasks (hoàn thành)
        // 1. Lấy activity từ tasks (hoàn thành)
    $taskActivities = Tasks::where('status', 'done')
        ->orderBy('updated_at', 'desc')
        ->get()
        ->map(function ($task) {
            return [
                'type' => 'task',
                'description' => 'Task "' . $task->title . '" completed',
                'time' => $task->updated_at
            ];
        });

    // 2. Lấy activity từ sprints (tạo mới)
    $sprintActivities = Sprints::orderBy('created_at', 'desc')
        ->get()
        ->map(function ($sprint) {
            return [
                'type' => 'sprint',
                'description' => 'New sprint "' . $sprint->name . '" created',
                'time' => $sprint->created_at
            ];
        });

    // 3. Lấy activity từ users (thêm mới)
    $userActivities = User::orderBy('created_at', 'desc')
        ->get()
        ->map(function ($user) {
            return [
                'type' => 'team',
                'description' => $user->name . ' joined the team',
                'time' => $user->created_at
            ];
        });

    // 4. Gộp tất cả & sắp xếp
    $recentActivities = $taskActivities
        ->merge($sprintActivities)
        ->merge($userActivities)
        ->sortByDesc('time')
        ->take(3)
        ->values();
        // dd($recentActivities);
    return view('pages.dashboard', compact('tasksToDo', 'tasksInProgress', 'tasksCompletedToday', 'SprintActive', 'members', 'recentActivities'));
    }
}
