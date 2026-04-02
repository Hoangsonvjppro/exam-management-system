<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        
        // Append middleware vào global web stack
        $middleware->append(\App\Http\Middleware\EnsureUserIsActive::class);

        // Alias middleware để dùng trong routes
        $middleware->alias([
            'active' => \App\Http\Middleware\EnsureUserIsActive::class,
            'ensure_student_code' => \App\Http\Middleware\EnsureStudentCodeIsSet::class,
            'redirect_by_user_state' => \App\Http\Middleware\RedirectByUserState::class,
            'lecturer_role' => \App\Http\Middleware\EnsureLecturerRole::class,
            'student_role' => \App\Http\Middleware\EnsureStudentRole::class,
            'must_change_password_handled' => \App\Http\Middleware\EnsureMustChangePasswordHandled::class,
            'role'   => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
