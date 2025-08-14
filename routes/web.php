<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\dashboardController;
use App\Http\Controllers\TasksController;

Route::get('/', function () {
   return redirect()->route('home');
});

// Các trang cần đăng nhập
Route::middleware('auth')->group(function() {
    Route::view('/homepage', 'pages.homepage')->name('home');
    Route::get('/dashboard', [TasksController::class, 'index'])->name('dashboard');
    Route::view('/tasksboard', 'pages.taskBoard')->name('tasksboard');
    Route::view('/sprint', 'pages.sprintPlanning')->name('sprint');
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

