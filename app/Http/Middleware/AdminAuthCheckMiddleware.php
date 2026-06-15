<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthCheckMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Not authenticated
        if (! auth('admin')->check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success'  => false,
                    'message'  => 'Your session has expired. Please log in again.',
                    'redirect' => route('show.admin.login'),
                ], 401);
            }

            return redirect()
                ->route('show.admin.login')
                ->with('error', 'Please log in to continue.');
        }

        $user = auth('admin')->user();

        // Account deactivated
        if ($user->status !== 'active') {
            auth('admin')->logout();
            $request->session()->invalidate();

            if ($request->expectsJson()) {
                return response()->json([
                    'success'  => false,
                    'message'  => 'Your account has been deactivated.',
                    'redirect' => route('show.admin.login'),
                ], 403);
            }

            return redirect()
                ->route('show.admin.login')
                ->with('error', 'Your account has been deactivated.');
        }

        // Not admin role
        if (! $user->hasAnyRole(['admin', 'super-admin'])) {
            auth('admin')->logout();
            $request->session()->invalidate();

            if ($request->expectsJson()) {
                return response()->json([
                    'success'  => false,
                    'message'  => 'Access denied.',
                    'redirect' => route('show.admin.login'),
                ], 403);
            }

            return redirect()
                ->route('show.admin.login')
                ->with('error', 'Access denied.');
        }

        // Authenticated — disable browser caching on all protected pages
        $response = $next($request);

        return $response->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'        => 'no-cache',
            'Expires'       => 'Fri, 01 Jan 1990 00:00:00 GMT',
        ]);
    }
}
