<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Teams;
use App\Models\Sprints;
use App\Models\Tasks;
use App\Models\TasksComments;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- 1. TẠO TEAM CHÍNH ---
        $team = Teams::create([
            'name' => 'ScrumSpark Team',
            'description' => 'The main development team for the ScrumSpark project.',
        ]);

        // --- 2. TẠO NGƯỜI DÙNG ---
        // Tạo 10 user với vai trò mặc định là 'developer'
        $users = User::factory()->count(10)->create([
            'role' => 'developer',
        ]);

        // Lấy ngẫu nhiên 2 user để làm PO và SM
        $pickedUsers = $users->shuffle()->take(2);
        $productOwner = $pickedUsers->first();
        $scrumMaster = $pickedUsers->last();

        // Cập nhật lại vai trò chính trong bảng `users`
        $productOwner->update(['role' => 'product_owner']);
        $scrumMaster->update(['role' => 'scrum_master']);

        // --- 3. GẮN TẤT CẢ USER VÀO TEAM VỚI VAI TRÒ TƯƠNG ỨNG ---
        // Gắn Product Owner
        $team->users()->attach($productOwner->id, ['roleInTeam' => 'product_owner']);

        // Gắn Scrum Master
        $team->users()->attach($scrumMaster->id, ['roleInTeam' => 'scrum_master']);

        // Gắn tất cả các user còn lại làm Developer
        $developerIds = User::where('role', 'developer')->pluck('id');
        foreach ($developerIds as $devId) {
            $team->users()->attach($devId, ['roleInTeam' => 'developer']);
        }

        // --- 4. TẠO SPRINTS, TASKS, VÀ COMMENTS ---
        // Lấy danh sách ID của tất cả thành viên trong team
        $teamMemberIds = $team->users()->pluck('users.id');

        // Tạo 2 Sprints cho team
        Sprints::factory()->count(2)->create(['team_id' => $team->id])
            ->each(function (Sprints $sprint) use ($teamMemberIds) {
                // Tạo 5 Tasks cho mỗi Sprint
                Tasks::factory()->count(5)->create([
                    'sprint_id'   => $sprint->id,
                    'created_by'  => $teamMemberIds->random(),
                    'assigned_to' => $teamMemberIds->random(),
                ])->each(function (Tasks $task) use ($teamMemberIds) {
                    // Tạo 2 Comments cho mỗi Task
                    TasksComments::factory()->count(2)->create([
                        'task_id' => $task->id,
                        'user_id' => $teamMemberIds->random(),
                    ]);
                });
            });

        // --- 5. IN THÔNG TIN TEST RA MÀN HÌNH ---
        $this->command->info('Database seeded successfully!');
        $this->command->info('A single team named "ScrumSpark Team" has been created with all users.');
        $this->command->table(['Email', 'Password', 'Role in Team'], [
            [$productOwner->email, 'password', 'Product Owner'],
            [$scrumMaster->email, 'password', 'Scrum Master'],
            [User::where('role', 'developer')->first()->email, 'password', 'Developer (Example)'],
        ]);
    }
}
