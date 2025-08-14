<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Teams;
use App\Models\Sprints;
use App\Models\Tasks;
use App\Models\TasksComments;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {


        // 1) Tạo 10 users, mặc định role = developer
        $users = User::factory()->count(10)->create([
            'role' => 'developer',
        ]);

        // 2) Lấy ngẫu nhiên 4 user khác nhau cho 4 role đặc biệt
        $picked = $users->shuffle()->take(4)->values();
        [$admin, $po, $sm, $leadDevUser] = [$picked[0], $picked[1], $picked[2], $picked[3]];//dùng shuffle randome sau đó pick 4 user. Sắp xếp lại
        //thứ tự các role theo mảng từ 1 đến 4. Sau đó gán lại các role cho user

        $admin->update(['role' => 'admin']);
        $po->update(['role' => 'product_owner']);
        $sm->update(['role' => 'scrum_master']);
        $leadDevUser->update(['role' => 'leadDeveloper']);//update lại role cho user leadDeveloper

        // Đảm bảo tất cả user còn lại là developer (phòng trường hợp factory có set khác)
        User::whereNotIn('id', [$admin->id, $po->id, $sm->id, $leadDevUser->id])
            ->update(['role' => 'developer']);//cập nhật lại tất cả user còn lại là developer trừ 4 user đã được gán role đặc biệt

        // 3) Tạo 3 teams: product_owner, scrum_master, leadDeveloper
        $poTeam = Teams::create([
            'name' => 'product_owner',
            'description' => 'Team for product owner',
        ]);

        $smTeam = Teams::create([
            'name' => 'scrum_master',
            'description' => 'Team for scrum master',
        ]);

        $ldTeam = Teams::create([
            'name' => 'leadDeveloper',
            'description' => 'Team for lead developer and developers',
        ]);

        // 4) Gắn user vào team qua pivot team_members (roleInTeam)
        // mỗi team 1 user duy nhất, riêng leadDeveloper-team có lead + toàn bộ developers
        $poTeam->users()->attach($po->id, ['roleInTeam' => 'product_owner']);
        $smTeam->users()->attach($sm->id, ['roleInTeam' => 'scrum_master']);

        // leadDeveloper team: lead + tất cả developers còn lại
        $attach = [
            $leadDevUser->id => ['roleInTeam' => 'leadDeveloper'],
        ];//tạo một cái mảng để chứa user leadDeveloper trước

        $developerIds = User::where('role', 'developer')->pluck('id');//lấy tất cả id của user có role là developer
        foreach ($developerIds as $devId) {
            $attach[$devId] = ['roleInTeam' => 'developer'];
        }// gán tất cả developer vào mảng attach với roleInTeam là developer
        $ldTeam->users()->attach($attach);//sau khi thêm vào mảng attach thì gắn vào team, attach là hàm của laravel many to many relationship

        // 5) Mỗi team có 2 sprints, mỗi sprint 5 tasks, mỗi task 2 comments
        $allTeams = collect([$poTeam, $smTeam, $ldTeam]);

        $allTeams->each(function (Teams $team) {
            // Lấy danh sách user id trong team này để random cho tasks/comments
            $teamUserIds = $team->users()->pluck('users.id');

            // 2 sprint mỗi team
            $sprints = Sprints::factory()->count(2)->create([
                'team_id' => $team->id,
            ]);

            foreach ($sprints as $sprint) {
                // 5 tasks mỗi sprint
                $tasks = Tasks::factory()->count(5)->create([
                    'sprint_id'   => $sprint->id,
                    'created_by'  => $teamUserIds->random(),
                    'assigned_to' => $teamUserIds->random(),
                ]);

                // 2 comments mỗi task
                foreach ($tasks as $task) {
                    TasksComments::factory()->count(2)->create([
                        'task_id' => $task->id,
                        'user_id' => $teamUserIds->random(),
                    ]);
                }
            }
        });
    }
}
