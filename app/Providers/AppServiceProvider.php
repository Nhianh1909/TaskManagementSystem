<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate; // <-- THÊM DÒNG NÀY
use App\Models\User;                  // <-- THÊM DÒNG NÀY
use App\Models\Teams;                 // <-- THÊM DÒNG NÀY

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
         * Định nghĩa Gate: 'manage-team-members'
         *
         * Chỉ cho phép `product_owner` hoặc `scrum_master` của một team
         * có quyền thực hiện các hành động (thêm, sửa, xóa) trên team đó.
         */
        Gate::define('manage-team-members', function (User $user, Teams $team) {
            // Lấy vai trò của người dùng trong team đang xét
            $memberInfo = $team->users()->where('user_id', $user->id)->first();

            if (!$memberInfo) {
                return false; // Không phải thành viên thì không có quyền
            }

            $roleInTeam = $memberInfo->pivot->roleInTeam;

            // Trả về true nếu là PO hoặc SM
            return in_array($roleInTeam, ['product_owner', 'scrum_master']);
        });
    }
}
