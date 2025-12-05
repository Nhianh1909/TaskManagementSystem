<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Tasks;
use App\Models\Sprints;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SprintsController extends Controller
{   //hàm này để hiển thị trang lập kế hoạch sprint
    public function create()
    {
        // Dùng Gate để kiểm tra quyền truy cập. Nếu ko có quyền sẽ tự động trả về 403 và hàm create sẽ đóng
        $this->authorize('plan-sprints');

        $user = Auth::user();//tạo biến user lưu thông tin người dùng đã đăng nhập
        $team = $user->team()->first(); // với user hiện tại, lấy ra team mà user đó đang tham gia

        if (!$team) {
            return redirect()->route('dashboard')->with('error', 'Bạn chưa thuộc team nào.');
        }
        //với team đã lấy được, ta tìm sprint đang hoạt động của team đó
        $activeSprint = $team->sprints()->where('is_active', true)->first();

        // Khi chưa có sprint đang chạy: liệt kê các Future Sprints (status=planning, is_active=false)
        $futureSprints = collect();
        if (!$activeSprint) {
            // ✅ Sắp xếp theo created_at ASC (sprint cũ nhất → mới nhất)
            // Lý do: Sprints phải chạy tuần tự, sprint tạo trước phải start trước
            $futureSprints = $team->sprints()
                ->where('status', 'planning')
                ->where('is_active', false)
                ->with(['tasks' => function($q) { $q->orderBy('priority'); }])
                ->orderBy('created_at', 'asc') // ✅ Đổi từ DESC sang ASC
                ->get();
        }

        return view('pages.sprintPlanning', compact('activeSprint', 'futureSprints'));
    }
    //hàm này để xử lý sprint mới được tạo
    public function store(Request $request)
    {
        // Dùng Gate để kiểm tra xem có quyền tạo sprint hay ko?
        $this->authorize('plan-sprints');

        // Dùng helper request() để tránh cảnh báo phân tích tĩnh
        $validated = request()->validate([
            'name' => 'required|string|max:255',
            'goal' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:tasks,id',//kiểm tra từng id có tồn tại trong bảng tasks hay không
        ]);
        //bắt đầu transaction để đảm bảo tính toàn vẹn dữ liệu
        DB::beginTransaction();
        try {
            //tạo biến team để lưu team của user hiện tại
            $team = Auth::user()->teams()->first();
            if (!$team) {
                throw new \Exception('User is not assigned to any team.');
            }

            // Hủy các sprint cũ đang hoạt động trong db để tạo sprint mới
            $team->sprints()->where('is_active', true)->update(['is_active' => false, 'status' => 'completed', // Hoặc 'closed' để báo hiệu kết thúc
            'end_date' => now()]);

            // Tạo sprint mới
            $sprint = $team->sprints()->create([
                'name' => $validated['name'],
                'goal' => $validated['goal'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'is_active' => true,
            ]);

            // Cập nhật các task được chọn vào sprint mới
            Tasks::whereIn('id', $validated['task_ids'])->update([
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
    //hàm này để hủy sprint đang hoạt động
    public function cancel()
    {
        // Dùng Gate để kiểm tra quyền hủy sprint
        $this->authorize('plan-sprints');
        //lấy ra team của user hiện tại
        $team = Auth::user()->teams()->first();
        //tìm sprint đang hoạt động của team đó và gán vào biến activeSprint
        $activeSprint = $team ? $team->sprints()->where('is_active', true)->first() : null;

        if (!$activeSprint) {
            return back()->with('error', 'Không có Sprint nào đang hoạt động để hủy.');
        }
        //tiếp theo bắt đầu transaction để đảm bảo tính toàn vẹn dữ liệu
        DB::beginTransaction();
        try {
            // ✅ FIX: Dùng status_id thay vì status (enum cũ)
            // Tìm cột "To Do" để gán các task chưa xong
            $toDoStatus = TaskStatus::where('team_id', $team->id)
                ->where('name', 'To Do')
                ->first();
            
            // Đưa các task chưa "done" (is_done=false) trở lại Product Backlog
            $activeSprint->tasks()
                ->with('status')
                ->whereHas('status', function ($q) {
                    $q->where('is_done', false);
                })
                ->update([
                    'sprint_id' => null,
                    'status_id' => $toDoStatus ? $toDoStatus->id : null,
                    'assigned_to' => null,
                    'completed_at' => null
                ]);
            
            // ✅ FIX: XÓA các task ĐÃ xong (is_done=true)
            $activeSprint->tasks()
                ->with('status')
                ->whereHas('status', function ($q) {
                    $q->where('is_done', true);
                })
                ->delete();

            // Hủy kích hoạt sprint
            $activeSprint->is_active = false;
            $activeSprint->status = 'completed';
            $activeSprint->save();

            DB::commit();
            return redirect()->route('sprint.create')->with('success', 'Sprint đã được hủy thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error cancelling sprint: ' . $e->getMessage());
            return back()->with('error', 'Đã có lỗi xảy ra khi hủy Sprint.');
        }
    }

    // Bắt đầu một Future Sprint đã được lên kế hoạch (chọn từ Product Backlog)
    public function start()
    {
        $this->authorize('plan-sprints');

        $team = Auth::user()->teams()->first();
        if (!$team) {
            return back()->with('error', 'Bạn chưa thuộc team nào.');
        }

    // Lấy sprint id từ route param và tải model
    $sprintId = request()->route('sprint');
    $sprintModel = Sprints::findOrFail($sprintId);

        // Đảm bảo sprint thuộc về team của user
        if ($sprintModel->team_id !== $team->id) {
            return back()->with('error', 'Bạn không có quyền bắt đầu sprint này.');
        }

        DB::beginTransaction();
        try {
            // Chỉ cho 1 sprint hoạt động cùng lúc: hủy kích hoạt sprint khác nếu có
            $team->sprints()->where('is_active', true)->update(['is_active' => false]);

            // Kích hoạt sprint này
            $sprintModel->is_active = true;
            $sprintModel->status = 'inProgress';
            if (empty($sprintModel->start_date)) {
                $sprintModel->start_date = now();
            }
            // Set end_date nếu chưa có (mặc định 2 tuần = 14 ngày)
            if (empty($sprintModel->end_date)) {
                $sprintModel->end_date = now()->addDays(14);
            }
            $sprintModel->save();

            DB::commit();
            return redirect()->route('sprint.create')->with('success', 'Sprint đã được bắt đầu.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error starting sprint: ' . $e->getMessage());
            return back()->with('error', 'Đã có lỗi xảy ra khi bắt đầu Sprint.');
        }
    }
}
