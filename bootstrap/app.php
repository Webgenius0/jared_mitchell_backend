<?php

use App\Http\Middleware\AdminAuthCheckMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function (): void {

            // ── Guest admin routes (login, forgot-password, etc.)
            Route::middleware('web')->prefix('admin')->group(base_path('routes/admin_auth.php'));

            // ── Protected admin routes (dashboard, etc.)
            Route::middleware(['web', 'admin.auth'])->group(base_path('routes/admin.php'));
            Route::middleware(['web', 'admin.auth'])->group(base_path('routes/settings.php'));

            // ── User Management routes (requires manage users permission or super-admin)
            Route::middleware(['web', 'admin.auth'])->group(base_path('routes/user_management.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.auth' => AdminAuthCheckMiddleware::class,
            'role'       => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
