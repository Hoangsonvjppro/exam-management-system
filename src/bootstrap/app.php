<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        then: function () {
            Route::middleware('web')->group(__DIR__.'/../routes/admin.php');
        },
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Append middleware vào global web stack
        $middleware->append(\App\Http\Middleware\EnsureUserIsActive::class);

        // Alias middleware để dùng trong routes
        $middleware->alias([
            'active' => \App\Http\Middleware\EnsureUserIsActive::class,
            'is_admin' => \App\Http\Middleware\IsAdmin::class,
            'ensure_student_code' => \App\Http\Middleware\EnsureStudentCodeIsSet::class,
            'role'   => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
