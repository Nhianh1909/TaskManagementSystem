<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Teams;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index()
    {
        $teams = Teams::with('users')->get(); // Lấy tất cả teams cùng với users liên quan
        // dd($teams->toArray());
        $leaders = $teams->map(function ($team){
            //lấy đại diện thành viên của team
            $leaderRole = match($team->name){
                'product_owner' => 'product_owner',
                'scrum_master' => 'scrum_master',
                'leadDeveloper' => 'leadDeveloper',
                default => null,
            };

            //lọc user theo role tương ứng
            $leader = $team->users->firstWhere('role', $leaderRole);

            return [
            'team_id' => $team->id,
            'team_name' => $team->name,
            'team_description' => $team->description,
            'leader' => $leader ? [
                'id' => $leader->id,
                'name' => $leader->name,
                'email' => $leader->email,
                'role' => $leader->role
            ] : null
            ];
        });
        //dd($leaders); // Hiển thị thông tin các team và leader của chúng
         return view('pages.teamManagement', [
            'teamMembers' => $leaders
        ]);
    }
    public function add(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'role'  => 'required|string',
            'email' => 'required|email',
        ]);

        $teamIds = $request->input('team_id', []); // mảng các team_id

        // 1. Kiểm tra user tồn tại theo email
        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email không tồn tại trong hệ thống']);
        }

        // 2. Kiểm tra user đã thuộc team nào trong danh sách chưa
        $isInTeam = Teams::whereIn('id', $teamIds)
            ->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })
            ->exists();

        // 3. Kiểm tra vai trò đã tồn tại trong bất kỳ team nào chưa
        $roleExists = Teams::whereIn('id', $teamIds)
            ->whereHas('users', function ($q) use ($data) {
                $q->where('role', $data['role']);
            })
            ->exists();

        if ($roleExists) {
            return back()->withErrors(['role' => 'Một trong các team này đã có người giữ vai trò này']);
        }

        // Nếu user đã trong team → cập nhật vai trò
        if ($isInTeam) {
            $user->role = $data['role'];
            $user->save();

            return redirect()->route('team')->with('success', 'Cập nhật vai trò thành công!');
        }

        // 4. Nếu user chưa thuộc team nào trong danh sách → attach vào từng team
        foreach ($teamIds as $id) {
            $team = Teams::findOrFail($id);
            $team->users()->attach($user->id);
        }

        return redirect()->route('team')->with('success', 'Thêm thành viên thành công!');
    }


    public function destroy($id) {
        $member = User::find($id);
        if (!$member) {
            return response()->json(['error' => 'Không tìm thấy thành viên'], 404);
        }
        $member->delete();
        return response()->json(['message' => 'Xóa thành công']);
    }


}
