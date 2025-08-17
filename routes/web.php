<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\dashboardController;
use App\Http\Controllers\TasksController;
use App\Http\Controllers\SprintsController;

Route::get('/', function () {
   return redirect()->route('home');
});

// Các trang cần đăng nhập
Route::middleware('auth')->group(function() {
    Route::view('/homepage', 'pages.homepage')->name('home');
    Route::get('/dashboard', [TasksController::class, 'index'])->name('dashboard');

    //các route CRUD task trong taskboard (ĐÃ SỬA LỖI)
    Route::get('/tasksboard', [TasksController::class, 'taskBoard'])->name('tasksboard');//route hiển thị cho taskboard
    Route::post('/tasks', [TasksController::class, 'store'])->name('tasks.store');//route add task
    Route::get('/tasks/{task}/edit', [TasksController::class, 'edit'])->name('tasks.edit');//route get id task
    Route::patch('/tasks/{task}', [TasksController::class, 'update'])->name('tasks.update');//roue update task
    Route::delete('/tasks/{task}', [TasksController::class, 'destroy'])->name('tasks.destroy');
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    Route::get('/sprint/planning', [SprintsController::class, 'create'])->name('sprint.create');
    // THÊM ROUTE NÀY
    Route::post('/sprint', [SprintsController::class, 'store'])->name('sprint.store');
    Route::post('/sprint/cancel', [SprintsController::class, 'cancel'])->name('sprint.cancel');
    // Route::view('/team', 'pages.teamManagement')->name('team');
    Route::get('/team', [TeamController::class, 'index'])->name('team');
    Route::view('/reports', 'pages.reports')->name('reports');
    Route::view('/settings', 'pages.settings')->name('settings');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/team/addTeam', [TeamController::class, 'add'])->name('addTeam'); // Thêm thành viên vào team
    Route::delete('/team/{id}', [TeamController::class, 'destroy'])->name('destroy'); // Xóa thành viên khỏi team
});

// Các trang public
Route::view('login', "auth.loginPage")->name('login.auth');
Route::post("/login", [AuthController::class, 'login'])->name('login.post');
Route::view('/signup', "auth.signupPage")->name('signup.auth');
Route::post("/signup", [AuthController::class, 'register'])->name('signup.post');

Route::get('teams', [TeamController::class, 'index']);

