<?php

use App\Http\Middleware\AdminAuthCheckMiddleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        channels: __DIR__ . '/../routes/channels.php',
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
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'stripe/*',
            'webhooks/aws-ivs',
        ]);

        // No named 'login' route exists in this app. Point the guest redirect at a
        // URL so unauthenticated requests fail cleanly (401 JSON via the API
        // exception renderer) instead of throwing RouteNotFoundException.
        $middleware->redirectGuestsTo(fn () => '/login');
    })




    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }
        });

        $exceptions->render(function (UnauthorizedException $e, Request $request) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have the required authorization to perform this action.',
                ], 403);
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if (!$request->wantsJson() && !$request->is('api/*')) {
                return null;
            }

            $message = 'Resource not found.';
            $previous = $e->getPrevious();

            if ($previous instanceof ModelNotFoundException) {
                $model = class_basename($previous->getModel());
                $ids = implode(', ', $previous->getIds());
                $message = "{$model} not found" . ($ids !== '' ? " ({$ids})" : '.');
            }

            return response()->json([
                'success' => false,
                'message' => $message,
                'data' => null,
                'errors' => null,
                'code' => 404,
            ], 404);
        });
    })
    ->create();
