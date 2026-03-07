<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class AdminAuthCheckMiddleware
{
    /**
     * Protect admin routes — only active admins with a valid JWT may pass.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $token = $request->cookie('admin_token');

        // 1. No token at all
        if (! $token) {
            return $this->unauthorized($request);
        }

        try {
            // 2. Parse and authenticate from cookie token
            $user = JWTAuth::setToken($token)->authenticate();

            if (! $user) {
                return $this->unauthorized($request);
            }

            // 3. Role check (Spatie)
            if (! $user->hasRole('admin')) {
                return $this->forbidden($request, 'You do not have admin privileges.');
            }

            // 4. Status check
            if ($user->status !== 'active') {
                return $this->forbidden($request, 'Your admin account has been deactivated.');
            }

            // 5. Bind authenticated user to request so controllers can use auth()->user()
            auth()->setUser($user);

        } catch (TokenExpiredException) {
            return $this->unauthorized($request, 'Session expired. Please log in again.');
        } catch (JWTException) {
            return $this->unauthorized($request, 'Invalid session. Please log in again.');
        }

        return $next($request);
    }

    /*
    |----------------------------------------------------------------------
    | Helpers
    |----------------------------------------------------------------------
    */

    private function unauthorized(Request $request, string $message = 'Unauthenticated.'): mixed
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message], 401);
        }

        return redirect()->route('show.admin.login')
                         ->withErrors(['auth' => $message])
                         ->withoutCookie('admin_token');
    }

    private function forbidden(Request $request, string $message): mixed
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message], 403);
        }

        return redirect()->route('show.admin.login')
                         ->withErrors(['auth' => $message])
                         ->withoutCookie('admin_token');
    }
}
