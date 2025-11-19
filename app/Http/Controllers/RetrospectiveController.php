<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sprints;
use App\Models\Retrospective;
use App\Models\RetrospectiveItem;
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
    // /**
    //  * Hiển thị trang Retrospective Meeting
    //  *
    //  * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    //  */
    public function index(Request $request)
    {
        $user = Auth::user();
        $team = $user->teams()->first();

        // Kiểm tra nếu người dùng không thuộc team nào
        if (!$team) {
            return redirect()->route('dashboard')->with('error', 'Bạn cần tham gia một team để truy cập Retrospective Meeting.');
        }
        // Lấy vai trò
        $userRoleInTeam = $team->users()->find($user->id)?->pivot->roleInTeam;
        // Lấy tất cả sprint đã hoàn thành để làm dropdown
        $allSprints = $team->sprints()->where('status', 'completed')->orderBy('end_date','desc')->get();

        //xác định xem sprint nào được chọn
        $activeSprint =null;
        if($request->query('sprint_id')){
            //nếu user lọc theo sprint
            $activeSprint = Sprints::where('id', $request->query('sprint_id'))
                ->where('team_id', $team->id)
                ->where('status', 'completed')
                ->first();
        }else{
            //nếu user không lọc theo sprint, lấy sprint gần nhất
            $activeSprint = $allSprints->first();
        }
        // Khởi tạo mảng rỗng
        // $likedItems = collect();
        // $toImproveItems = collect();
        // $actionItems = collect();
        $retro = null;

        // Chỉ khi tìm thấy sprint thì mới lấy data
        if ($activeSprint) {
            // Tìm hoặc tạo buổi họp
            $retro = Retrospective::firstOrCreate(
                ['sprint_id' => $activeSprint->id],
                ['team_id' => $team->id, 'is_locked' => false]
            );

            // Đặt tên biến khớp với file UI bạn cung cấp
            // $likedItems = $retro->items()->where('type', 'good')->with('user')->get();
            // $toImproveItems = $retro->items()->where('type', 'bad')->with('user')->get();
            // $actionItems = $retro->items()->where('type', 'action')->with('user')->get();
        }


        // Trả về view với đầy đủ dữ liệu
        return view('pages.retrospective', compact(
            'retro',
            // 'likedItems',
            // 'toImproveItems',
            // 'actionItems',
            'allSprints',
            'activeSprint',
            'userRoleInTeam',
        ));
    }
    public function getItems(Retrospective $retro)
    {
        $user = Auth::user();
        $items = $retro->items()->with('user')
                        ->orderBy('created_at', 'asc') // Sắp xếp cũ -> mới
                        ->get();

        // Trả về dạng JSON
        return response()->json($items);
    }

    // /**
    //  * Lưu một item retrospective mới
    //  *
    //  * @param Request $request - Chứa 'contenst' (nội dung) và 'type' (loại: liked/improve/action)
    //  * @return \Illuminate\Http\JsonResponse
    //  */
    public function storeItem(Request $request, Retrospective $retro)
    {
        $user = Auth::user();

        // 1. Kiểm tra bảo mật: Cuộc họp đã bị khóa chưa?
        if ($retro->is_locked) {
            return redirect()->back()->with('error', 'Buổi họp này đã bị khóa.');
        }
        // 2. Kiểm tra bảo mật: User có thuộc team này không?
        if ($user->team()->id !== $retro->team_id) {
            return redirect()->back()->with('error', 'Bạn không có quyền thêm item.');
        }
        // 3. Validate dữ liệu (Đảm bảo 'type' là 1 trong 3 giá trị)
        $validated = $request->validate([
            'content' => 'required|string|max:2000',
            'type' => ['required', \Illuminate\Validation\Rule::in(['good', 'bad', 'action'])],
        ]);
        // 4. Tạo item mới
        RetrospectiveItem::create([
            'retrospective_id' => $retro->id,
            'user_id' => $user->id,
            'content' => $validated['content'],
            'type' => $validated['type'],
        ]);
        // Trả về JSON
        return redirect()->back()->with('success', 'Thêm item thành công!');
    }


    public function updateItem(Request $request, RetrospectiveItem $item)
    {
        $user = Auth::user();
        $retro = $item->retrospective;
        // 1. Kiểm tra bảo mật: Cuộc họp đã khóa chưa?
        if ($retro->is_locked) {
            return response()->json(['message' => 'Buổi họp này đã bị khóa.'], 403);
        }
        // 2. Kiểm tra quyền: Chỉ người tạo item mới được sửa (PO/SM cũng không nên sửa lời người khác)
        if ($item->user_id !== $user->id) {
            return response()->json(['message' => 'Bạn chỉ có thể sửa item của chính mình.'], 403);
        }
        // 3. Validate dữ liệu
        $validated = $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        // 4. Cập nhật
        $item->update([
            'content' => $validated['content']
        ]);

        // 4. Quay lại trang cũ với thông báo thành công (Flash Message)
        return redirect()->back()->with('success', 'Cập nhật item thành công!');
    }


    public function destroyItem(RetrospectiveItem $item)
    {
        $user = Auth::user();
        $team = $user->team();
        $userRoleInTeam = $team->users()->find($user->id)?->pivot->roleInTeam;

        // Lấy buổi họp (retro) từ item
        $retro = $item->retrospective;

        // 1. Kiểm tra bảo mật: Cuộc họp đã bị khóa chưa?
        if ($retro->is_locked) {
            return redirect()->back()->with('error', 'Buổi họp này đã bị khóa.');
        }

        // 2. Kiểm tra quyền: Chỉ chủ item, PO, hoặc SM mới được xóa
        $isOwner = ($item->user_id === $user->id);
        $isAdmin = in_array($userRoleInTeam, ['product_owner', 'scrum_master']);

        if (!$isOwner && !$isAdmin) {
            return redirect()->back()->with('error', 'Bạn không có quyền xóa item này.');
        }

        // 3. Xóa item
        $item->delete();

        // 4. Quay lại trang cũ
        return response()->json([
            'message' => 'Xóa item thành công!',
        ]);
    }
    // === HÀM 1: KẾT THÚC CUỘC HỌP (KHÓA) ===
    public function lockRetrospective(Request $request, Retrospective $retro)
    {
        // 1. Kiểm tra quyền Admin (PO hoặc SM)
        if (!$this->checkAdminPermission()) {
            return redirect()->back()->with('error', 'Bạn không có quyền kết thúc cuộc họp.');
        }

        // 2. Cập nhật trạng thái
        $retro->update(['is_locked' => true]);

        return redirect()->back()->with('success', 'Cuộc họp đã kết thúc và được khóa lại.');
    }

    // === HÀM 2: MỞ LẠI CUỘC HỌP (MỞ KHÓA) ===
    public function unlockRetrospective(Request $request, Retrospective $retro)
    {
        // 1. Kiểm tra quyền Admin
        if (!$this->checkAdminPermission()) {
            return redirect()->back()->with('error', 'Bạn không có quyền mở lại cuộc họp.');
        }

        // 2. Cập nhật trạng thái
        $retro->update(['is_locked' => false]);

        return redirect()->back()->with('success', 'Cuộc họp đã được mở lại.');
    }

    // === HÀM PHỤ: CHECK QUYỀN ADMIN (PO/SM) ===
    private function checkAdminPermission()
    {
        $user = Auth::user();
        $team = $user->team();
        if (!$team) return false;

        $role = $team->users()->find($user->id)?->pivot->roleInTeam;
        return in_array($role, ['product_owner', 'scrum_master']);
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
