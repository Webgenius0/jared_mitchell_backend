<?php

namespace App\Http\Controllers\Web\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class SignInController extends Controller
{
    /*
    |----------------------------------------------------------------------
    | Show Login Page
    |----------------------------------------------------------------------
    */

    public function index()
    {
        // If already authenticated, redirect to dashboard
        if ($this->isAdminAuthenticated()) {
            return redirect()->route('show.admin.dashboard');
        }

        return view('pages.auth.login');
    }

    /*
    |----------------------------------------------------------------------
    | Handle Login — returns JSON (consumed by Axios)
    |----------------------------------------------------------------------
    */

    public function login(Request $request): JsonResponse
    {
        // 1. Validate input
        $validator = Validator::make($request->all(), [
            'email'    => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'email.required'    => 'Email address is required.',
            'email.email'       => 'Please enter a valid email address.',
            'password.required' => 'Password is required.',
            'password.min'      => 'Password must be at least 6 characters.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        // 2. Check user exists
        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'errors'  => ['email' => ['No account found with this email address.']],
            ], 401);
        }

        // 3. Check account status
        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'errors'  => ['email' => ['Your account has been deactivated. Contact support.']],
            ], 403);
        }

        // 4. Check admin role (spatie)
        if (! $user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'errors'  => ['email' => ['You are not authorized to access the admin panel.']],
            ], 403);
        }

        // 5. Attempt JWT authentication
        $credentials = $request->only('email', 'password');

        if (! $token = auth('admin')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'errors'  => ['password' => ['Incorrect password. Please try again.']],
            ], 401);
        }

        // 6. Return response with HttpOnly cookie
        return $this->respondWithToken($token, 'Login successful. Redirecting…');
    }

    /*
    |----------------------------------------------------------------------
    | Logout
    |----------------------------------------------------------------------
    */

    public function logout(Request $request): JsonResponse
    {
        try {
            auth('admin')->logout();
        } catch (\Exception) {
            // token already invalid — fine
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Logged out successfully.',
            'redirect' => route('show.admin.login'),
        ])->withoutCookie('admin_token');
    }

    /*
    |----------------------------------------------------------------------
    | Private Helpers
    |----------------------------------------------------------------------
    */

    /**
     * Build JSON response and attach JWT as an HttpOnly cookie.
     */
    private function respondWithToken(string $token, string $message): JsonResponse
    {
        $ttlMinutes = config('jwt.ttl', 60); // default 60 min

        return response()->json([
            'success'  => true,
            'message'  => $message,
            'redirect' => route('show.admin.dashboard'),
        ])->cookie(
            'admin_token',  // name
            $token,         // value
            $ttlMinutes,    // minutes
            '/',            // path
            null,           // domain
            true,           // secure (HTTPS only — set false in local dev if needed)
            true,           // httpOnly — JS cannot read this cookie
            false,          // raw
            'Strict'        // sameSite
        );
    }

    /**
     * Check if a valid admin JWT cookie already exists.
     */
    private function isAdminAuthenticated(): bool
    {
        $token = request()->cookie('admin_token');

        if (! $token) {
            return false;
        }

        try {
            $user = JWTAuth::setToken($token)->authenticate();

            return $user && $user->hasRole('admin') && $user->status === 'active';
        } catch (\Exception) {
            return false;
        }
    }
}
