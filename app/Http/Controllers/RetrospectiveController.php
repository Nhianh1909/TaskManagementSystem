<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller quản lý trang Retrospective Meeting (Buổi họp hồi cố)
 * 
 * Chức năng chính:
 * - Hiển thị 3 cột: Went Well (Tốt), To Improve (Cần cải thiện), Action Items (Hành động)
 * - Cho phép thành viên thêm/sửa/xóa các item feedback
 * - Scrum Master có thể thêm Action Items vào Product Backlog
 * - Kết thúc buổi họp và lưu kết quả
 */
class RetrospectiveController extends Controller
{
    /**
     * Hiển thị trang Retrospective Meeting
     * 
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        // Lấy thông tin user đang đăng nhập
        $user = Auth::user();
        
        // Lấy team đầu tiên mà user tham gia
        $team = $user->teams()->first();

        // Kiểm tra xem user có thuộc team nào không
        if (!$team) {
            return redirect()->route('dashboard')->with('error', 'Bạn phải thuộc một team để xem trang retrospective.');
        }

        // Lấy vai trò của user trong team (scrum_master, product_owner, developer)
        $userRoleInTeam = $team->users()->find($user->id)?->pivot->roleInTeam;

        // ===== DỮ LIỆU MẪU CHO 3 CỘT =====
        // TODO: Thay thế bằng dữ liệu thật từ database khi tích hợp model Retrospective
        
        // Cột 1: Những điều làm tốt (Went Well 👍)
        $likedItems = [
            ['id' => 1, 'content' => 'Team collaboration has significantly improved this sprint.', 'creator' => 'Alice', 'votes' => 5],
            ['id' => 2, 'content' => 'Successfully completed sprint goals ahead of schedule.', 'creator' => 'David', 'votes' => 3],
        ];

        // Cột 2: Những điều cần cải thiện (To Improve ⚙️)
        $toImproveItems = [
            ['id' => 3, 'content' => 'Communication between Dev and QA needs improvement, leading to delays.', 'creator' => 'Bob', 'votes' => 4],
            ['id' => 4, 'content' => 'Insufficient unit test coverage in critical modules.', 'creator' => 'David', 'votes' => 2],
        ];

        // Cột 3: Các hành động cải tiến (Action Items 🚀)
        $actionItems = [
            ['id' => 5, 'content' => 'Implement daily 15-minute sync-up meetings between Dev QA teams.', 'creator' => 'Alice', 'votes' => 0],
            ['id' => 6, 'content' => 'Allocate 2 hours per sprint for increasing Dev QA coverage.', 'creator' => 'Scrum Master', 'votes' => 0],
        ];

        // Trả về view với dữ liệu
        return view('pages.retrospective', compact(
            'likedItems',
            'toImproveItems',
            'actionItems',
            'userRoleInTeam',
            'team'
        ));
    }

    /**
     * Lưu một item retrospective mới
     * 
     * @param Request $request - Chứa 'content' (nội dung) và 'type' (loại: liked/improve/action)
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeItem(Request $request)
    {
        // Validate dữ liệu đầu vào
        $validated = $request->validate([
            'content' => 'required|string|max:500',  // Nội dung bắt buộc, tối đa 500 ký tự
            'type' => 'required|in:liked,improve,action',  // Loại phải là: liked, improve hoặc action
        ]);

        // TODO: Lưu vào database khi đã tích hợp model Retrospective
        // VD: RetrospectiveItem::create([...])
        
        return response()->json([
            'message' => 'Thêm item thành công!',
            'item' => [
                'id' => rand(100, 999),  // ID tạm, thay bằng ID từ DB sau
                'content' => $validated['content'],
                'creator' => Auth::user()->name,
                'votes' => 0,
            ]
        ], 201);  // 201 = Created
    }

    /**
     * Cập nhật một item đã có
     * 
     * @param Request $request
     * @param int $id - ID của item cần update
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateItem(Request $request, $id)
    {
        // Validate nội dung mới
        $validated = $request->validate([
            'content' => 'required|string|max:500',
        ]);

        // TODO: Tìm item trong DB và update
        // VD: $item = RetrospectiveItem::findOrFail($id);
        //     $item->update($validated);
        
        return response()->json([
            'message' => 'Cập nhật item thành công!',
        ]);
    }

    /**
     * Xóa một item
     * 
     * @param int $id - ID của item cần xóa
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteItem($id)
    {
        // TODO: Xóa item khỏi database
        // VD: RetrospectiveItem::findOrFail($id)->delete();
        
        return response()->json([
            'message' => 'Xóa item thành công!',
        ]);
    }

    /**
     * Thêm Action Item vào Product Backlog
     * CHỈ SCRUM MASTER MỚI ĐƯỢC DÙNG CHỨC NĂNG NÀY
     * 
     * @param Request $request
     * @param int $id - ID của action item
     * @return \Illuminate\Http\JsonResponse
     */
    public function addToBacklog(Request $request, $id)
    {
        $user = Auth::user();
        $team = $user->teams()->first();
        $userRoleInTeam = $team ? $team->users()->find($user->id)?->pivot->roleInTeam : null;

        // Kiểm tra quyền: chỉ Scrum Master mới được phép
        if ($userRoleInTeam !== 'scrum_master') {
            return response()->json([
                'message' => 'Chỉ Scrum Master mới có thể thêm item vào backlog.'
            ], 403);  // 403 = Forbidden
        }

        // TODO: Tạo task mới trong Product Backlog từ action item này
        // VD: Tasks::create([
        //     'title' => $item->content,
        //     'epic_id' => null,
        //     'sprint_id' => null,
        //     ...
        // ]);
        
        return response()->json([
            'message' => 'Đã thêm Action Item vào Product Backlog thành công!',
        ]);
    }

    /**
     * Kết thúc buổi Retrospective Meeting
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function endMeeting()
    {
        // TODO: Đánh dấu buổi họp là đã hoàn thành, lưu kết quả cuối cùng vào DB
        // VD: $retrospective->update(['status' => 'completed', 'ended_at' => now()]);
        
        return redirect()->route('dashboard')->with('success', 'Buổi retrospective đã kết thúc thành công!');
    }
}
