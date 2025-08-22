<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TasksController;
use App\Http\Controllers\SprintsController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- Authentication Routes ---
// Các route này dành cho khách (chưa đăng nhập).
Route::middleware('guest')->group(function () {
    // Route để HIỂN THỊ trang đăng nhập
    // Trỏ đến hàm showLoginForm() trong AuthController
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login.auth');

    // Route để XỬ LÝ dữ liệu khi người dùng nhấn nút đăng nhập
    // Trỏ đến hàm login() trong AuthController
    Route::post('login', [AuthController::class, 'login'])->name('login.post');

    // Route để HIỂN THỊ trang đăng ký
    // Trỏ đến hàm showRegistrationForm() trong AuthController
    Route::get('signup', [AuthController::class, 'showRegistrationForm'])->name('signup.auth');

    // Route để XỬ LÝ dữ liệu khi người dùng nhấn nút đăng ký
    // Trỏ đến hàm register() trong AuthController
    Route::post('signup', [AuthController::class, 'register'])->name('signup.post');
});


// --- Authenticated Routes ---
// Tất cả các route trong group này yêu cầu người dùng phải ĐÃ ĐĂNG NHẬP.
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [TasksController::class, 'index'])->name('dashboard');

    // Task Routes
    Route::get('/tasksboard', [TasksController::class, 'taskBoard'])->name('tasksboard');
    Route::post('/tasks', [TasksController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/{task}/edit', [TasksController::class, 'edit'])->name('tasks.edit');
    Route::patch('/tasks/{task}', [TasksController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TasksController::class, 'destroy'])->name('tasks.destroy');
    Route::patch('/tasks/{task}/status', [TasksController::class, 'updateStatus'])->name('tasks.updateStatus');

    // Sprint Routes
    Route::get('/sprint/planning', [SprintsController::class, 'create'])->name('sprint.create');
    Route::post('/sprint', [SprintsController::class, 'store'])->name('sprint.store');
    Route::post('/sprint/cancel', [SprintsController::class, 'cancel'])->name('sprint.cancel');

    // --- Team Management Routes ---
    Route::get('/team-management', [TeamController::class, 'index'])->name('team.management');
    Route::post('/team/add-member', [TeamController::class, 'addMember'])->name('team.addMember');
    Route::delete('/team/{team}/remove-member/{user}', [TeamController::class, 'removeMember'])->name('team.removeMember');
    Route::put('/team/{team}/update-role/{member}', [TeamController::class, 'updateMemberRole'])->name('team.updateRole');

    // Other Routes
    Route::view('/reports', 'pages.reports')->name('reports');
    Route::view('/settings', 'pages.settings')->name('settings');

    // Logout Route
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

//middleware dành cho AI
Route::middleware('auth')->group(function () {
    // ... các route cũ của bạn

    // Route mới cho chức năng gợi ý của AI
    Route::post('/tasks/suggest', [TasksController::class, 'suggestAllWithAI'])->name('tasks.suggest');
});


// Route mặc định
Route::get('/', function () {
    return redirect('/dashboard');
});
Route::get('/fix-my-account', function() {
    // 1. Kiểm tra xem bạn đã đăng nhập chưa
    if (!Auth::check()) {
        return 'Vui lòng đăng nhập bằng tài khoản bạn muốn sửa trước, sau đó truy cập lại trang này.';
    }

    $user = Auth::user();
    $team = \App\Models\Teams::first(); // Lấy team đầu tiên có trong database

    if (!$team) {
        return 'Không tìm thấy team nào. Vui lòng chạy lệnh "php artisan migrate:fresh --seed" trước.';
    }

    // 2. Gán quyền Product Owner của team này cho tài khoản của bạn
    // syncWithoutDetaching sẽ thêm mới nếu chưa có, hoặc không làm gì nếu đã có
    $team->users()->syncWithoutDetaching([
        $user->id => ['roleInTeam' => 'product_owner']
    ]);

    // 3. Cập nhật luôn vai trò chung của bạn cho chắc chắn
    $user->role = 'product_owner';
    $user->save();

    return 'Thành công! Tài khoản "' . $user->name . '" của bạn đã được gán quyền Product Owner cho team "' . $team->name . '". Hãy quay lại trang Team Management để kiểm tra.';
});
