<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Teams;
use App\Models\Sprints;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
class ReportController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $team = $user->teams()->first(); // Lấy team đầu tiên của user

        if(!$team){
            // Nếu người dùng chưa thuộc team nào, trả về view với dữ liệu trống
            return view('pages.reports', [
                'burndownChartData' => [],
                'velocityChartData' => [],
                'teamPerformance' => [],
                'activeSprintName' => 'N/A'
            ]);
        }
        $activeSprint = $team->sprints()->where('is_active', true)->first();
        // Lấy dữ liệu cho các biểu đồ
        $burndownChartData = $this->getBurndownChartData($activeSprint);
        $velocityChartData = $this->getVelocityChartData($team);
        $teamPerformance = $this->getTeamPerformance($activeSprint);
        $activeSprintName = $activeSprint ? $activeSprint->name : 'No Active Sprint';

        // Trả về view với đầy đủ dữ liệu
        return view('pages.reports', compact('burndownChartData', 'velocityChartData', 'teamPerformance', 'activeSprintName'));
    }
    /**
     * Lấy dữ liệu cho biểu đồ Burndown.
     * Biểu đồ này cho thấy lượng "story points" còn lại qua từng ngày của sprint.
     */
    private function getBurndownChartData($sprint)
    {
        if (!$sprint) {
            return [];
        }

        // Tạo một chuỗi các ngày từ ngày bắt đầu đến ngày kết thúc sprint
        $period = CarbonPeriod::create($sprint->start_date, $sprint->end_date);
        $dates = collect($period)->map(fn ($date) => $date->format('M d'));

        // Tính tổng story points của toàn bộ sprint
        $totalStoryPoints = $sprint->tasks()->sum('storyPoints');

        // Tính "đường burndown lý tưởng" - tức là mỗi ngày nên hoàn thành bao nhiêu điểm
        $idealPointsPerDay = $totalStoryPoints / ($dates->count() > 1 ? $dates->count() - 1 : 1);
        $idealData = $dates->map(function ($date, $index) use ($totalStoryPoints, $idealPointsPerDay) {
            return round(max(0, $totalStoryPoints - ($idealPointsPerDay * $index)));
        });

        // Lấy các task đã hoàn thành và nhóm chúng theo ngày
        $tasksDoneByDate = $sprint->tasks()
            ->where('status', 'done')
            ->orderBy('updated_at')
            ->get()
            ->groupBy(fn ($task) => $task->updated_at->format('M d'));

        // Tính "đường burndown thực tế" - dựa trên các task đã hoàn thành
        $remainingPoints = $totalStoryPoints;
        $actualData = $dates->map(function ($date) use (&$remainingPoints, $tasksDoneByDate) {
            if (isset($tasksDoneByDate[$date])) {
                $remainingPoints -= $tasksDoneByDate[$date]->sum('storyPoints');
            }
            return $remainingPoints;
        });

        return [
            'labels' => $dates,
            'idealData' => $idealData,
            'actualData' => $actualData,
        ];
    }
    /**
     * Lấy dữ liệu cho biểu đồ Velocity.
     * Biểu đồ này cho thấy tổng số "story points" team đã hoàn thành trong các sprint gần đây.
     */
    private function getVelocityChartData(Teams $team)
    {
        //lấy ra 5 srpints gần đây nhất của team
        $completedSprints = $team->sprints()
            ->where('is_active', false)
            ->where('status', 'completed')
            ->orderBy('end_date')
            ->take(5)
            ->get()
            ->reverse();

        if($completedSprints->isEmpty()){
            return[];
        }

        $label = $completedSprints->pluck('name');
        $data = $completedSprints->map(function($sprint){
            //Tính tổng số story points của các task đã hoàn thành trong sprint đó
            return $sprint->tasks()->where('status', 'done')->sum('storyPoints');
        });

        return [
            'labels' => $label,
            'data' => $data,
        ];
    }
    /**
     * Lấy dữ liệu về hiệu suất của từng thành viên trong sprint hiện tại.
     */
    private function getTeamPerformance($sprint)
    {
        if (!$sprint) {
            return [];
        }

        // Lấy tất cả thành viên trong team của sprint
        $teamMembers = $sprint->team->users;

        return $teamMembers->map(function ($member) use ($sprint) {
            // Lấy các task của sprint này mà được giao cho thành viên này
            $tasksInSprint = $sprint->tasks()->where('assigned_to', $member->id)->get();
            $tasksCompleted = $tasksInSprint->where('status', 'done');

            $totalTasks = $tasksInSprint->count();
            $completedCount = $tasksCompleted->count();
            $storyPoints = $tasksCompleted->sum('storyPoints');
            // Tính hiệu suất (tỷ lệ hoàn thành)
            $efficiency = ($totalTasks > 0) ? round(($completedCount / $totalTasks) * 100) : 0;

            return [
                'name' => $member->name,
                'tasks_completed' => $completedCount,
                'story_points' => $storyPoints,
                'efficiency' => $efficiency,
            ];
        });
    }
}
