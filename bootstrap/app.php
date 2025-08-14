<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\Authenticate;
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Đăng ký alias
        $middleware->alias([
            'auth' =>Authenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Xử lý ngoại lệ chung
        $exceptions->reportable(function (Throwable $e) {
            // Ghi log hoặc xử lý lỗi tùy ý
            // Ví dụ: Log::error($e->getMessage());
        });

        // Nếu muốn xử lý hiển thị lỗi custom
        // $exceptions->renderable(function (Throwable $e, $request) {
        //     return response()->view('errors.custom', [], 500);
        // });
    })
    ->create();

