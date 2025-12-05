<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TaskStatus;
use App\Models\User;
use App\Models\Teams;
use App\Models\Sprints;
use App\Models\Tasks;
use App\Models\TasksComments;
use App\Models\Epics;
use App\Models\Retrospective;
use App\Models\RetrospectiveItem;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB; // Import DB để lấy status id
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- 1. TẠO TEAM CHÍNH ---
        $team = Teams::create([
            'name' => 'ScrumSpark Team',
            'description' => 'The main development team for the ScrumSpark project.',
        ]);

        // =================================================================
        // BƯỚC 2: TẠO STATUS CHO TEAM
        // =================================================================
        $this->command->info('Creating Task Statuses for team...');
        
        $toDo = TaskStatus::create([
            'name' => 'To Do',
            'order_index' => 1,
            'is_done' => false,
            'color_class' => 'border-blue-400',
            'team_id' => $team->id,
        ]);

        $inProgress = TaskStatus::create([
            'name' => 'In Progress',
            'order_index' => 2,
            'is_done' => false,
            'color_class' => 'border-yellow-400',
            'team_id' => $team->id,
        ]);

        $done = TaskStatus::create([
            'name' => 'Done',
            'order_index' => 3,
            'is_done' => true,
            'color_class' => 'border-green-400',
            'team_id' => $team->id,
        ]);

        $toDoId = $toDo->id;
        $inProgressId = $inProgress->id;
        $doneId = $done->id;

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
        $teamMemberIds = $team->users()->pluck('users.id'); //lấy ra tất cả các user có trong team
        // --- 5. TẠO EPIC ---
        $this->command->info('Creating Epics for the team...');//dòng này để in thông báo khi chạy seeder
        //tạo ra 2 epic cho team vừa tạo
        $epic1 = Epics::factory()->create([
            'team_id' => $team->id,
            'title' => 'Epic 1: User Authentication',
            'description' => 'Implement user registration, login, and password reset functionalities.',
        ]);
        $epic2 = Epics::factory()->create([
            'team_id' => $team->id,
            'title' => 'Epic 2: Task Management',
            'description' => 'Develop features for creating, updating, and tracking tasks within sprints.',
        ]);
        // --- 6. TẠO SPRINTS, TASKS, VÀ COMMENTS ---
        //Tạo 1 sprint "completed" để test report
        $sprint0 = Sprints::factory()->create([
            'team_id' => $team->id,
            'name' => 'Sprint 0 - Khởi động',
            'status' => 'completed', // Đã hoàn thành
            'start_date' => now()->subWeeks(2),
            'end_date' => now()->subWeek(),
        ]);
        //Tạo 1 sprint "in progresss" cho team làm việc
        $sprint1 = Sprints::factory()->create([
            'team_id' => $team->id,
            'name' => 'Sprint 1 - Ra mắt MVP',
            'status' => 'inProgress', // Đang chạy
            'start_date' => now(),
            'end_date' => now()->addWeeks(2),
        ]);
        // --- 7. TẠO USERSTORY & SUB-TASK (LOGIC QUAN TRỌNG NHẤT) ---
        $this->command->info('Creating User Stories and Sub-tasks...');
        // --- Tạo 1 User Story "DONE" cho Sprint 0 (để test Report) ---
        $us0 = Tasks::factory()->create([
            'title' => 'US-001: Cài đặt project Laravel',
            'created_by' => $productOwner->id,
            'sprint_id' => $sprint0->id,
            'epic_id' => $epic1->id,
            'parent_id' => null, // Là User Story (cha)
            'status_id' => $doneId, // ✨ Dùng ID
        ]);
        // --- Tạo 2 User Story cho Sprint 1 ---
        $us1 = Tasks::factory()->create([
            'title' => 'US-101: Là người dùng, tôi muốn Đăng nhập',
            'created_by' => $productOwner->id,
            'sprint_id' => $sprint1->id,
            'epic_id' => $epic1->id,
            'parent_id' => null, // Là User Story (cha)
            'status_id' => $inProgressId, // ✨ Dùng ID
        ]);
        // Tạo 3 Sub-task cho US-101
        Tasks::factory()->subtask()->count(3)->create([
            'parent_id' => $us1->id,
            'sprint_id' => $sprint1->id,
            'created_by' => $scrumMaster->id,
            'assigned_to' => $teamMemberIds->random(),
            'status_id' => fake()->randomElement([$toDoId, $inProgressId, $doneId]), // ✨ Random ID
        ]);
        $us2 = Tasks::factory()->create([
            'title' => 'US-102: Là người dùng, tôi muốn Đăng ký',
            'created_by' => $productOwner->id,
            'sprint_id' => $sprint1->id,
            'epic_id' => $epic1->id,
            'parent_id' => null,
            'status_id' => $toDoId, // ✨ Dùng ID
        ]);

        // Tạo 2 Sub-task cho US-102
        Tasks::factory()->subtask()->count(2)->create([
            'parent_id' => $us2->id,
            'sprint_id' => $sprint1->id,
            'created_by' => $scrumMaster->id,
            'assigned_to' => $teamMemberIds->random(),
            'status_id' => $toDoId, // ✨ Dùng ID
        ]);
        // --- Tạo 1 User Story cho Product Backlog (chưa vào Sprint) ---
        $us3 = Tasks::factory()->create([
            'title' => 'US-201: Là PO, tôi muốn xem Báo cáo',
            'created_by' => $productOwner->id,
            'sprint_id' => null, // Nằm ở Backlog
            'epic_id' => $epic2->id,
            'parent_id' => null,
            'status_id' => $toDoId, // ✨ Dùng ID
        ]);
        // --- 7. TẠO COMMENTS CHO 1 SUB-TASK NGẪU NHIÊN ---
        $ramdomSubtask = Tasks::whereNotNull('parent_id')->first();
        if($ramdomSubtask){
            TasksComments::factory()->count(3)->create([
                'task_id' => $ramdomSubtask->id,
                'user_id' => $teamMemberIds->random(),
            ]);
        }
        // --- MỤC 8: TẠO DATA CHO RETROSPECTIVE (ĐOẠN CODE MỚI) ---
        $this->command->info('Creating Retrospective data...');
        // (Chúng ta sẽ dùng $sprint0 và $scrumMaster, $productOwner đã tạo ở trên)
        if($sprint0){
            // 1. Tạo buổi họp (cha) cho Sprint 0
            $retroSession = Retrospective::create([
                'sprint_id' => $sprint0->id,
                'team_id' => $sprint0->team_id,
            ]);
            // 2. Tạo 3 'tags' (con) cho buổi họp đó
            RetrospectiveItem::create([
                'retrospective_id' => $retroSession->id,
                'user_id' => $scrumMaster->id,
                'content'=> 'Điểm tốt: Đã hoàn thành Sprint 0 đúng hạn.',
                'type' => 'good',
            ]);

            RetrospectiveItem::create([
                'retrospective_id' => $retroSession->id,
                'user_id' => $productOwner->id,
                'content' => 'Điểm cần cải thiện: Task "Đăng ký" (US-102) ước tính chưa chuẩn.',
                'type' => 'bad',
            ]);

            RetrospectiveItem::create([
                'retrospective_id' => $retroSession->id,
                'user_id' => $scrumMaster->id,
                'content' => 'Hành động: Team sẽ review lại cách ước tính Story Point.',
                'type' => 'action',
            ]);
        }

        // // --- 4. TẠO SPRINTS, TASKS, VÀ COMMENTS ---
        // // Lấy danh sách ID của tất cả thành viên trong team
        // $teamMemberIds = $team->users()->pluck('users.id');

        // // Tạo 2 Sprints cho team
        // Sprints::factory()->count(2)->create(['team_id' => $team->id])
        //     ->each(function (Sprints $sprint) use ($teamMemberIds) {
        //         // Tạo 5 Tasks cho mỗi Sprint
        //         Tasks::factory()->count(5)->create([
        //             'sprint_id'   => $sprint->id,
        //             'created_by'  => $teamMemberIds->random(),
        //             'assigned_to' => $teamMemberIds->random(),
        //         ])->each(function (Tasks $task) use ($teamMemberIds) {
        //             // Tạo 2 Comments cho mỗi Task
        //             TasksComments::factory()->count(2)->create([
        //                 'task_id' => $task->id,
        //                 'user_id' => $teamMemberIds->random(),
        //             ]);
        //         });
        //     });

        // --- 5. IN THÔNG TIN TEST RA MÀN HÌNH ---
        $this->command->info('Database seeded successfully!');
        $this->command->info('A single team named "ScrumSpark Team" has been created with all users.');
        $this->command->table(['Email', 'Password', 'Role in Team'], [
            [$productOwner->email, 'password', 'Product Owner'],
            [$scrumMaster->email, 'password', 'Scrum Master'],
            [User::where('role', 'developer')->first()->email, 'password', 'Developer (Example)'],
        ]);
        // --- 5. IN THÔNG TIN TEST RA MÀN HÌNH ---
        $this->command->info('Database seeded successfully!');
    }
}
