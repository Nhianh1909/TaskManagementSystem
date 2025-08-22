<?php

namespace App\Http\Controllers;

use App\Models\Teams;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
    /**
     * Hiển thị trang quản lý team.
     * Chỉ người dùng có vai trò quản lý (PO, SM) mới thấy được team của họ.
     */
    public function index()
    {
        $user = Auth::user();

        // Lấy các team mà người dùng này có vai trò là PO hoặc SM
        $teams = $user->teams()
                      ->whereIn('roleInTeam', ['product_owner', 'scrum_master'])
                      ->with('users') // Tải sẵn danh sách thành viên của mỗi team
                      ->get();

        // --- BẮT ĐẦU SỬA LOGIC Ở ĐÂY ---

        // Lấy ID của tất cả các user đã thuộc về các team mà người này quản lý
        $memberIds = $teams->pluck('users.*.id')->flatten()->unique();

        // Lấy tất cả user CHƯA CÓ trong danh sách thành viên ở trên
        $allUsers = User::whereNotIn('id', $memberIds)
                        ->orderBy('name')
                        ->get();

        // --- KẾT THÚC SỬA LOGIC ---

        return view('pages.teamManagement', compact('teams', 'allUsers'));
    }

    /**
     * Thêm một thành viên mới vào team.
     */
    public function addMember(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'team_id' => 'required|exists:teams,id',
            'roleInTeam' => ['required', Rule::in(['product_owner', 'scrum_master', 'developer'])],
        ]);

        $team = Teams::findOrFail($validated['team_id']);
        $userToAdd = User::findOrFail($validated['user_id']);

        // Dùng Gate để kiểm tra người dùng hiện tại có quyền quản lý team này không
        $this->authorize('manage-team-members', $team);
        if (in_array($validated['roleInTeam'], ['product_owner', 'scrum_master'])) {
            $roleExists = $team->users()->where('roleInTeam', $validated['roleInTeam'])->exists();
            if ($roleExists) {
                return back()->with('error', "This team already has a {$validated['roleInTeam']}.");
            }
        }

        // Kiểm tra xem người dùng đã ở trong team chưa
        if ($team->users()->where('user_id', $userToAdd->id)->exists()) {
            return back()->with('error', 'User is already in this team.');
        }

        // Thêm thành viên vào team
        $team->users()->attach($userToAdd->id, ['roleInTeam' => $validated['roleInTeam']]);

        return back()->with('success', 'Member added successfully!');
    }

    /**
     * Xóa một thành viên khỏi team.
     */
    public function removeMember(Teams $team, User $user)
    {
        // Dùng Gate để kiểm tra quyền
        $this->authorize('manage-team-members', $team);

        // Không cho phép người quản lý tự xóa chính mình
        if (Auth::id() === $user->id) {
            return back()->with('error', 'You cannot remove yourself from the team.');
        }

        $team->users()->detach($user->id);

        return back()->with('success', 'Member removed successfully.');
    }

    /**
     * Cập nhật vai trò của một thành viên trong team.
     */
    /**
     * Cập nhật vai trò của một thành viên trong team, với logic bảo vệ vai trò Product Owner.
     */
    public function updateMemberRole(Request $request, Teams $team, User $member)
    {
        // 1. Kiểm tra quyền quản lý chung
        $this->authorize('manage-team-members', $team);

        // 2. Validate dữ liệu đầu vào
        $validated = $request->validate([
            'roleInTeam' => ['required', Rule::in(['product_owner', 'scrum_master', 'developer'])],
        ]);

        $newRole = $validated['roleInTeam'];
        $currentRole = $team->users()->find($member->id)->pivot->roleInTeam;
        // Ngăn chặn việc thay đổi vai trò của Scrum Master cuối cùng
        if ($currentRole === 'scrum_master' && $newRole !== 'scrum_master') {
            // Đếm xem trong team có bao nhiêu Scrum Master
            $scrumMasterCount = $team->users()->where('roleInTeam', 'scrum_master')->count();

            // Nếu chỉ có 1 SM (chính là người đang bị đổi vai trò), thì chặn lại
            if ($scrumMasterCount <= 1) {
                return back()->with('error', 'Cannot change role. This is the last Scrum Master in the team. Please assign the SM role to another member first.');
            }
        }
        // 3. LOGIC MỚI: Ngăn chặn việc "hạ cấp" trực tiếp Product Owner
        if ($currentRole === 'product_owner' && $newRole !== 'product_owner') {
            return back()->with('error', 'You must transfer the Product Owner role to another user before changing this member\'s role.');
        }

        // 4. LOGIC MỚI: Xử lý việc "chuyển giao" vai trò Product Owner
        if ($newRole === 'product_owner' && $currentRole !== 'product_owner') {
            // Tìm PO cũ trong team
            $oldPO = $team->users()->where('roleInTeam', 'product_owner')->first();
            if ($oldPO) {
                // Tự động hạ cấp PO cũ thành developer
                $team->users()->updateExistingPivot($oldPO->id, ['roleInTeam' => 'developer']);
            }
        }

        // (Bạn có thể áp dụng logic tương tự cho Scrum Master nếu muốn)
        if ($newRole === 'scrum_master' && $currentRole !== 'scrum_master') {
            $oldSM = $team->users()->where('roleInTeam', 'scrum_master')->first();
            if ($oldSM) {
                 $team->users()->updateExistingPivot($oldSM->id, ['roleInTeam' => 'developer']);
            }
        }


        // 5. Tiến hành cập nhật vai trò cho thành viên được chọn
        $team->users()->updateExistingPivot($member->id, ['roleInTeam' => $newRole]);

        return back()->with('success', 'Member role updated successfully.');
    }
}
