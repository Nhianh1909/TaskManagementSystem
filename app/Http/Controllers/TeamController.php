<?php
// File: app/Http/Controllers/TeamController.php

namespace App\Http\Controllers;

use App\Models\Teams;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
    /**
     * Hiển thị trang quản lý team.
     */
    public function index()
    {
        // Chỉ Admin mới có quyền xem trang này
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        // Lấy tất cả các team và các thành viên trong team đó
        $teams = Teams::with('users')->get();
        $allUsers = User::orderBy('name')->get(); // Lấy tất cả user để hiển thị trong modal

        return view('pages.teamManagement', compact('teams', 'allUsers'));
    }

    /**
     * Thêm một thành viên mới vào team.
     */
    public function addMember(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'team_id' => 'required|exists:teams,id',
            'roleInTeam' => ['required', Rule::in(['product_owner', 'scrum_master', 'leadDeveloper', 'developer'])],
        ]);

        $team = Teams::find($validated['team_id']);
        $user = User::find($validated['user_id']);

        // Kiểm tra xem vai trò chính đã tồn tại trong team chưa (trừ developer)
        if ($validated['roleInTeam'] !== 'developer') {
            $roleExists = $team->users()->where('roleInTeam', $validated['roleInTeam'])->exists();
            if ($roleExists) {
                return back()->with('error', 'Vai trò ' . $validated['roleInTeam'] . ' đã tồn tại trong team này.');
            }
        }

        // Gắn user vào team
        // syncWithoutDetaching sẽ thêm mới nếu chưa có, không làm gì nếu đã có
        $team->users()->syncWithoutDetaching([
            $user->id => ['roleInTeam' => $validated['roleInTeam']]
        ]);

        // Cập nhật luôn vai trò chính của User trong bảng users
        $user->role = $validated['roleInTeam'];
        $user->save();

        return redirect()->route('team')->with('success', 'Thêm thành viên vào team thành công!');
    }

    /**
     * Xóa một thành viên khỏi team.
     */
    public function removeMember(Request $request, Teams $team, User $user)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        // Gỡ user khỏi team
        $team->users()->detach($user->id);

        // Cân nhắc: Có nên hạ vai trò của user trong bảng `users` về 'developer' không?
        // Ví dụ: $user->update(['role' => 'developer']);

        return redirect()->route('team')->with('success', 'Đã xóa thành viên khỏi team.');
    }
}
