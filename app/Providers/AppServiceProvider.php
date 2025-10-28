<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Teams;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /**
         * Định nghĩa Gate: 'manage-team-members'. Gate là một tính năng ủy quyền của Laravel. Giúp
         * kiểm tra role của người dùng trong team. Giúp làm gọn code dễ dàng tái sử dụng.
         *
         * Chỉ cho phép `product_owner` hoặc `scrum_master` của một team
         * có quyền thực hiện các hành động (thêm, sửa, xóa) trên team đó.
         */
        Gate::define('manage-team-members', function (User $user, Teams $team) {
            // Lấy ra chỉ 1 user bên trong mỗi team với role trong bảng pivot
            $memberInfo = $team->users()->where('user_id', $user->id)->first();
            //kiểm tra nếu không tìm thấy thông tin thành viên
            if (!$memberInfo) {
                return false;
            }
            //ta dùng biến $mêmberInfo để trích xuất vai trò (roleInTeam) từ dữ liệu 'pivot' (bảng team_members)
            $roleInTeam = $memberInfo->pivot->roleInTeam;

            // Trả về true nếu là PO hoặc SM
            return in_array($roleInTeam, ['product_owner', 'scrum_master']);
        });
        Gate::define('plan-sprints', function (User $user) {
        // Dùng Gate để define xem user trong team có phải PO hoặc SM không
        return $user->teams()
                    ->whereIn('roleInTeam', ['product_owner', 'scrum_master'])
                    ->exists();
        });
    }
}
