<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Tasks;
use App\Models\Sprints;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SprintsController extends Controller
{   //hàm này để hiển thị trang lập kế hoạch sprint
    public function create()
    {
        // Dùng Gate để kiểm tra quyền truy cập. Nếu ko có quyền sẽ tự động trả về 403 và hàm create sẽ đóng
        $this->authorize('plan-sprints');

        $user = Auth::user();//tạo biến user lưu thông tin người dùng đã đăng nhập
        $team = $user->teams()->first(); // với user hiện tại, lấy ra team mà user đó đang tham gia

        if (!$team) {
            return redirect()->route('dashboard')->with('error', 'Bạn chưa thuộc team nào.');
        }
        //với team đã lấy được, ta tìm sprint đang hoạt động của team đó
        $activeSprint = $team->sprints()->where('is_active', true)->first();
        //tao biến backlogTasks để lưu các task chưa thuộc sprint nào
        $backlogTasks = collect();
        if (!$activeSprint) {
            // Lấy các task chưa thuộc bất kỳ sprint nào sắp xếp theo độ ưu tiên
            $backlogTasks = Tasks::whereNull('sprint_id')->orderBy('priority')->get();
        }

        return view('pages.sprintPlanning', compact('backlogTasks', 'activeSprint'));
    }
    //hàm này để xử lý sprint mới được tạo
    public function store(Request $request)
    {
        // Dùng Gate để kiểm tra xem có quyền tạo sprint hay ko?
        $this->authorize('plan-sprints');

        $validated = $request->validate([
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
            // Đưa các task chưa "Done" trở lại Product Backlog nếu như user đang đăng nhập là PO hoặc SM và sprint của team
            $activeSprint->tasks()
                ->where('status', '!=', 'done')
                ->update(['sprint_id' => null, 'status' => 'toDo']);

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
}
