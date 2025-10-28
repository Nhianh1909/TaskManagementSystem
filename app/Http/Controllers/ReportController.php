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
        return view('pages.reports', compact('burndownChartData', 'velocityChartData', 'teamPerformance', 'activeSprintName'));//đóng gói mảng dữ liệu truyền vào view
    }
    /**
     * Lấy dữ liệu cho biểu đồ Burndown.
     * Biểu đồ này cho thấy lượng "story points" còn lại qua từng ngày của sprint.
     */
    private function getBurndownChartData($sprint)
    {
        if (!$sprint) {
            return [];
        }//ko có srpint thì trả về mảng rỗng

        // Tạo một chuỗi các ngày từ ngày bắt đầu đến ngày kết thúc sprint bằng sử dụng thư viện CarbonPeriod đỡ được 1 vòng lặp
        $period = CarbonPeriod::create($sprint->start_date, $sprint->end_date);
        //dùng collect để gôm chuỗi các ngày vào mảng sau đó dùng map để duyệt từng phần tử bằng $date và định dạng lại thành M d
        $dates = collect($period)->map(fn ($date) => $date->format('M d'));

        // Tính tổng story points của toàn bộ sprint
        $totalStoryPoints = $sprint->tasks()->sum('storyPoints');

        /**
         * Tính toán đường burndown lý tưởng cho sprint
         *
         * Công thức: Tổng story points / (Tổng số ngày - 1)
         *
         * Giải thích:
         * - Lấy tổng số story points của sprint
         * - Chia cho (tổng số ngày trong sprint - 1)
         * - Trừ 1 vì ngày cuối cùng của sprint phải có giá trị là 0 (hoàn thành 100%)
         * - Kết quả cho biết số story points cần hoàn thành mỗi ngày để đạt mục tiêu
         *
         * Ví dụ: Sprint 10 ngày, 90 story points
         * - Burndown lý tưởng mỗi ngày = 90 / (10 - 1) = 10 points/ngày
         * - Ngày 1: 90 points, Ngày 2: 80 points, ..., Ngày 10: 0 points
         */
        $idealPointsPerDay = $totalStoryPoints / ($dates->count() > 1 ? $dates->count() - 1 : 1);
        $idealData = $dates->map(function ($date, $index) use ($totalStoryPoints, $idealPointsPerDay) {
            return round(max(0, $totalStoryPoints - ($idealPointsPerDay * $index)));
        });//công thức này sẽ duyệt qua từng này và sử dụng tổng storypoints và điểm lý tưởng mỗi ngày đạt được để tính ra số điểm lý tưởng của ngày đó
        //sau đó sẽ làm tròn bằng hàm round

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
        });//hàm nãy sẽ hoạt động bằng cách duyệt qua từng ngày trong mảng dates và kiểm tra xem có task nào đã hoàn thành vào ngày đó không
        //nếu có thì trừ đi số story points của task đó và trả về số story points còn lại sau khi trừ
        //kết quả sẽ lưu vào mảng actualData sau mỗi vòng lặp

        return [
            'labels' => $dates,
            'idealData' => $idealData,
            'actualData' => $actualData,
        ];//return mảng dữ liệu cho biểu đồ burndown gồm nhãn ngày, dữ liệu lý tưởng và dữ liệu thực tế
    }
    /**
     * Lấy dữ liệu cho biểu đồ Velocity.
     * Biểu đồ này cho thấy tổng số "story points" team đã hoàn thành trong các sprint gần đây.
     * Biểu đồ này cho thấy tiến độ hoàn thành công việc của team qua các sprint.
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
            ->reverse();// dùng reverse() để đảo ngược mảng, vì lấy ra 5 sprint gần nhất nhưng muốn hiển thị từ cũ đến mới

        if($completedSprints->isEmpty()){
            return[];
        }
        //lấy ra chỉ một thuộc tính cột name lưu vào biến $label
        $label = $completedSprints->pluck('name');
        //dùng map để duyệt từng sprint trong mảng completedSprints để tính tổng story points đã hoàn thành trong mỗi sprint đó
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

        // Lấy tất cả thành viên trong team của sprint thông qua tính chất bắt cầu (relationship) giữa sprint và team và từ team đến users
        $teamMembers = $sprint->team->users;

        return $teamMembers->map(function ($member) use ($sprint) {
            // Lấy các task của sprint được gán cho thành viên hiện tại của task đó
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
