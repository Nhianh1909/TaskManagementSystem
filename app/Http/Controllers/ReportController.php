<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Teams;
use App\Models\Sprints;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
class ReportController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = Auth::user();

            $team = $user->teams()->first(); // Lấy team đầu tiên của user

            if(!$team){
                // Nếu người dùng chưa thuộc team nào, trả về view với dữ liệu trống
                return view('pages.reports', [
                    'burndownChartData' => [],
                    'velocityChartData' => [],
                    'teamPerformance' => [],
                    'activeSprintName' => 'N/A',
                    'recentSprints' => [],
                    'selectedSprintId' => null
                ]);
            }

            // Lấy 3 sprints gần nhất: 1 đang chạy + 2 vừa hoàn thành
            $activeSprint = $team->sprints()->where('is_active', true)->first();
            $completedSprints = $team->sprints()
                ->where('status', 'completed')
                ->orderBy('end_date', 'desc')
                ->take(2)
                ->get();

            // Gộp active sprint và completed sprints
            $recentSprints = collect([]);
            if ($activeSprint) {
                $recentSprints->push($activeSprint);
            }
            $recentSprints = $recentSprints->merge($completedSprints);

            // Lấy sprint được chọn từ request, mặc định là sprint đầu tiên
            $selectedSprintId = $request->input('sprint_id');
            $currentSprint = null;

            if ($selectedSprintId) {
                $currentSprint = $recentSprints->firstWhere('id', $selectedSprintId);
            }

            // Nếu không có sprint được chọn hoặc không tìm thấy, lấy sprint đầu tiên
            if (!$currentSprint) {
                $currentSprint = $recentSprints->first();
            }

            // Lấy dữ liệu cho các biểu đồ
            $burndownChartData = $this->getBurndownChartData($currentSprint);
            $velocityChartData = $this->getVelocityChartData($team);
            $teamPerformance = $this->getTeamPerformance($currentSprint);
            $activeSprintName = $currentSprint ? $currentSprint->name : 'No Sprint Available';

            // Trả về view với đầy đủ dữ liệu
            return view('pages.reports', compact(
                'burndownChartData',
                'velocityChartData',
                'teamPerformance',
                'activeSprintName',
                'recentSprints',
                'selectedSprintId'
            ));
        } catch (\Exception $e) {
            // Log lỗi để debug
            \Log::error('Report page error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            // Trả về view với dữ liệu trống và thông báo lỗi
            return view('pages.reports', [
                'burndownChartData' => [],
                'velocityChartData' => [],
                'teamPerformance' => [],
                'activeSprintName' => 'Error loading sprint data',
                'recentSprints' => [],
                'selectedSprintId' => null
            ])->with('error', 'An error occurred while loading report data: ' . $e->getMessage());
        }
    }
    /**
     * Lấy dữ liệu cho biểu đồ Burndown.
     *
     * ✅ LOGIC MỚI (theo Scrum chuẩn):
     * - Chỉ tính Story Points từ User Stories (task cha, parent_id = null)
     * - Subtasks KHÔNG có story points riêng (points nằm ở US)
     * - Burndown chỉ "đốt cháy" khi User Story done (tức là tất cả subtasks xong)
     *
     * Ví dụ:
     * - US-A (5 SP): Có 4 subtasks. Khi 3/4 done → Vẫn 0 SP burned
     * - US-A: Khi 4/4 subtasks done + PO chuyển US sang "Done" → Burn 5 SP
     */
    private function getBurndownChartData($sprint)
    {
        if (!$sprint) {
            return [];
        }//ko có srpint thì trả về mảng rỗng

        // Kiểm tra start_date và end_date hợp lệ
        if (!$sprint->start_date || !$sprint->end_date) {
            return [];
        }

        // // Tạo một chuỗi các ngày từ ngày bắt đầu đến ngày kết thúc sprint bằng sử dụng thư viện CarbonPeriod đỡ được 1 vòng lặp
        $period = CarbonPeriod::create($sprint->start_date, $sprint->end_date);

        // Giới hạn tối đa 90 ngày để tránh hết memory, //dùng collect để gôm chuỗi các ngày vào mảng sau đó dùng map để duyệt từng phần tử bằng $date và định dạng lại thành M d
        $dates = collect($period)->take(90)->map(function ($date) {
            /** @var \Carbon\Carbon $date */
            return $date->format('M d');
        });

        // ✅ CHỈ tính tổng Story Points từ User Stories (parent_id = null)

        // Lý do: Subtasks không có points riêng, points nằm ở task cha (US)
        $totalStoryPoints = $sprint->tasks()
            ->whereNull('parent_id') // Chỉ lấy User Stories
            ->sum('storyPoints');

        // Tính đường burndown lý tưởng
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
        // Công thức: Mỗi ngày đốt cháy (totalStoryPoints / số ngày) SP
        $idealPointsPerDay = $totalStoryPoints / ($dates->count() > 1 ? $dates->count() - 1 : 1);
        $idealData = $dates->map(function ($date, $index) use ($totalStoryPoints, $idealPointsPerDay) {
            return round(max(0, $totalStoryPoints - ($idealPointsPerDay * $index)));
        });//công thức này sẽ duyệt qua từng này và sử dụng tổng storypoints và điểm lý tưởng mỗi ngày đạt được để tính ra số điểm lý tưởng của ngày đó
        //sau đó sẽ làm tròn bằng hàm round

        // ✅ Lấy User Stories (task cha) và subtasks để suy luận "done" an toàn
        // Lý do: Trong thực tế có thể PO chưa flip US sang "done" dù tất cả subtasks đã done
        // → Burndown nên burn tại thời điểm tất cả subtasks hoàn thành (hoặc khi US được set done)
        $allUserStories = $sprint->tasks()
            ->whereNull('parent_id')
            ->with(['subTasks:id,parent_id,status,updated_at'])
            ->orderBy('updated_at')
            ->get();

        // Xác định danh sách US được coi là "done" và mốc thời gian ghi nhận
        // - Nếu US.status = done → dùng updated_at của US
        // - Nếu tất cả subtasks đều done → dùng max(updated_at) của các subtask (thời điểm cuối cùng được hoàn thành)
        $doneUserStories = $allUserStories->map(function ($us) {
            $isExplicitDone = $us->status === 'done';

            // Kiểm tra tất cả subtasks đã done hay chưa
            $subtasks = $us->subTasks ?? collect();
            $hasSubtasks = $subtasks->count() > 0;
            $allSubtasksDone = $hasSubtasks && $subtasks->every(function ($st) {
                return $st->status === 'done';
            });

            if ($isExplicitDone) {
                return [
                    'id' => $us->id,
                    'storyPoints' => $us->storyPoints ?? 0,
                    'done_at' => \Carbon\Carbon::parse($us->updated_at),
                ];
            }

            if ($allSubtasksDone) {
                // Lấy thời điểm hoàn thành là mốc thời gian muộn nhất của các subtasks
                $latestDoneAt = $subtasks->max(function ($st) {
                    return \Carbon\Carbon::parse($st->updated_at);
                });
                return [
                    'id' => $us->id,
                    'storyPoints' => $us->storyPoints ?? 0,
                    'done_at' => \Carbon\Carbon::parse($latestDoneAt),
                ];
            }

            return null; // Chưa done
        })->filter();

        // Group theo ngày và tính tổng Story Points đã "đốt cháy" mỗi ngày
        $pointsBurnedByDate = [];

        // ✅ Parse sprint dates để so sánh
        $sprintStart = \Carbon\Carbon::parse($sprint->start_date)->startOfDay();
        $sprintEnd = \Carbon\Carbon::parse($sprint->end_date)->endOfDay();

        foreach ($doneUserStories as $userStory) {
            $updatedAt = $userStory['done_at'] ?? null;
            if (!$updatedAt) continue;

            // ✅ Nếu task done TRƯỚC sprint start → Gán vào ngày đầu sprint
            if ($updatedAt->lt($sprintStart)) {
                $dateKey = $sprintStart->format('M d');
            }
            // ✅ Nếu task done SAU sprint end → Gán vào ngày cuối sprint
            elseif ($updatedAt->gt($sprintEnd)) {
                $dateKey = $sprintEnd->format('M d');
            }
            // ✅ Nếu task done TRONG sprint → Lấy đúng ngày
            else {
                $dateKey = $updatedAt->format('M d');
            }

            if (!isset($pointsBurnedByDate[$dateKey])) {
                $pointsBurnedByDate[$dateKey] = 0;
            }
            // Cộng dồn Story Points của US vào ngày nó được chuyển sang "Done"
            $pointsBurnedByDate[$dateKey] += $userStory['storyPoints'] ?? 0;
        }

        // Tính đường burndown thực tế (Remaining Story Points)
        // Bắt đầu từ $totalStoryPoints, giảm dần khi có US done
        $remainingPoints = $totalStoryPoints;
        $actualData = $dates->map(function ($date) use (&$remainingPoints, $pointsBurnedByDate) {
            // Nếu có US done trong ngày này, trừ đi Story Points
            if (isset($pointsBurnedByDate[$date])) {
                $remainingPoints -= $pointsBurnedByDate[$date];
            }
            return round(max(0, $remainingPoints), 2);
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
     *
     * ✅ LOGIC: Chỉ tính Story Points từ User Stories done (parent_id = null)
     */
    private function getVelocityChartData(Teams $team)
    {
        // Lấy 5 sprints gần đây nhất đã hoàn thành
        $completedSprints = $team->sprints()
            ->where('is_active', false)
            ->where('status', 'completed')
            ->orderBy('end_date')
            ->take(5)
            ->get()
            ->reverse(); // dùng reverse() để đảo ngược mảng, vì lấy ra 5 sprint gần nhất nhưng muốn hiển thị từ cũ đến mới

        if ($completedSprints->isEmpty()) {
            return [];
        }

        //lấy ra chỉ một thuộc tính cột name lưu vào biến $label
        $label = $completedSprints->pluck('name');

        // ✅ CHỈ tính Story Points từ User Stories done (parent_id = null)
        // Lý do: Subtasks không có points riêng
        $data = $completedSprints->map(function ($sprint) {
            return $sprint->tasks()
                ->whereNull('parent_id') // Chỉ lấy User Stories
                ->where('status', 'done')
                ->sum('storyPoints');
        });

        return [
            'labels' => $label,
            'data' => $data,
        ];
    }
    /**
     * Lấy dữ liệu về hiệu suất của từng thành viên trong sprint hiện tại.
     *
     * ✅ LOGIC MỚI: Group theo User Story, hiển thị metrics cho từng member
     * - Mỗi US có nhiều members
     * - Mỗi member có: Total Subtasks | Subtasks Completed | Completion Rate
     * - Thêm dòng NONE cho members không được giao subtask
     *
     * Cấu trúc trả về:
     * [
     *   'user_stories' => [...],
     *   'members_without_subtasks' => [...]
     * ]
     */
    private function getTeamPerformance($sprint)
    {
        if (!$sprint) {
            return [];
        }

        // ✅ Lấy tất cả User Stories trong sprint với subtasks đã eager load
        $userStories = $sprint->tasks()
            ->whereNull('parent_id')
            ->with(['subTasks.assignee'])
            ->orderBy('order_index')
            ->get();

        $result = [];

        foreach ($userStories as $us) {
            // Group subtasks theo assigned_to
            $subtasksByMember = $us->subTasks
                ->whereNotNull('assigned_to')
                ->groupBy('assigned_to');

            $membersData = [];

            foreach ($subtasksByMember as $memberId => $subtasks) {
                $member = $subtasks->first()->assignee;

                $totalSubtasks = $subtasks->count();
                $completedSubtasks = $subtasks->where('status', 'done')->count();
                $completionRate = ($totalSubtasks > 0)
                    ? round(($completedSubtasks / $totalSubtasks) * 100)
                    : 0;

                $membersData[] = [
                    'name' => $member ? $member->name : 'Unassigned',
                    'total_subtasks' => $totalSubtasks,
                    'completed_subtasks' => $completedSubtasks,
                    'completion_rate' => $completionRate,
                ];
            }

            // Chỉ thêm US nếu có ít nhất 1 member được giao subtask
            if (count($membersData) > 0) {
                $result[] = [
                    'user_story' => $us->title . ' (' . ($us->storyPoints ?? 0) . ' pts)',
                    'members' => $membersData,
                ];
            }
        }

        // ✅ Tìm các members không có subtask nào trong sprint
        $teamMembers = $sprint->team->users;
        $membersWithSubtasks = $sprint->tasks()
            ->whereNotNull('parent_id')
            ->whereNotNull('assigned_to')
            ->pluck('assigned_to')
            ->unique();

        $membersWithoutSubtasks = $teamMembers
            ->filter(function ($member) use ($membersWithSubtasks) {
                return !$membersWithSubtasks->contains($member->id);
            })
            ->map(function ($member) {
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                ];
            })
            ->values();

        return [
            'user_stories' => $result,
            'members_without_subtasks' => $membersWithoutSubtasks,
        ];
    }
}
