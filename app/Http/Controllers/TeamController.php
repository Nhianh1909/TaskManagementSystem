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

        // Lấy tất cả user để hiển thị trong modal "Add Member"
        $allUsers = User::orderBy('name')->get();

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
    public function updateMemberRole(Request $request, Teams $team, User $member)
    {
        // Dùng Gate để kiểm tra quyền
        $this->authorize('manage-team-members', $team);

        $validated = $request->validate([
            'roleInTeam' => ['required', Rule::in(['product_owner', 'scrum_master', 'developer'])],
        ]);

        // Không cho phép tước quyền của chính mình nếu là người quản lý duy nhất
        if (Auth::id() === $member->id) {
             $managersCount = $team->users()->whereIn('roleInTeam', ['product_owner', 'scrum_master'])->count();
             if ($managersCount <= 1) {
                 return back()->with('error', 'Cannot change your own role as you are the only manager.');
             }
        }

        $team->users()->updateExistingPivot($member->id, [
            'roleInTeam' => $validated['roleInTeam']
        ]);

        return back()->with('success', 'Member role updated successfully.');
    }
}
