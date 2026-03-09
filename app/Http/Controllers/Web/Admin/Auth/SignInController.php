<?php

namespace App\Http\Controllers\Web\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\AdminApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class SignInController extends Controller
{
    use AdminApiResponse;

    /*
    |--------------------------------------------------------------------------
    | Show Login Page
    |--------------------------------------------------------------------------
    */

    public function index(): View|RedirectResponse
    {
        if ($this->isAdminAuthenticated()) {
            return redirect()->route('show.admin.dashboard');
        }

        return view('web.auth.login');
    }

    /*
    |--------------------------------------------------------------------------
    | Handle Login  (Axios → JSON)
    |--------------------------------------------------------------------------
    */

    public function login(Request $request): JsonResponse
    {
        // ── 1. Validate ────────────────────────────────────────────────────
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
            return $this->validationError($validator);
        }

        // ── 2. User exists? ────────────────────────────────────────────────
        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return $this->error('No account found with this email address.', [
                'email' => ['No account found with this email address.'],
            ], 401);
        }

        // ── 3. Account active? ─────────────────────────────────────────────
        if ($user->status !== 'active') {
            return $this->error('Your account has been deactivated. Contact support.', [
                'email' => ['Your account has been deactivated. Contact support.'],
            ], 403);
        }

        // ── 4. Admin role? (Spatie) ────────────────────────────────────────
        if (! $user->hasAnyRole(['admin', 'super-admin'])) {
            return $this->error('You are not authorized to access the admin panel.', [
                'email' => ['You are not authorized to access the admin panel.'],
            ], 403);
        }

        // ── 5. Attempt session login ───────────────────────────────────────
        $credentials = $request->only('email', 'password');
        $remember    = (bool) $request->input('remember', false);

        if (! auth('admin')->attempt($credentials, $remember)) {
            return $this->error('Incorrect password. Please try again.', [
                'password' => ['Incorrect password. Please try again.'],
            ], 401);
        }

        // ── 6. Regenerate session (prevents fixation) ─────────────────────
        $request->session()->regenerate();

        return $this->success(
            'Login successful. Redirecting…',
            [],
            route('show.admin.dashboard')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    public function logout(Request $request): JsonResponse
    {
        auth('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $this->success(
            'Logged out successfully.',
            [],
            route('show.admin.login')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Private Helpers
    |--------------------------------------------------------------------------
    */

    private function isAdminAuthenticated(): bool
    {
        if (! auth('admin')->check()) {
            return false;
        }

        $user = auth('admin')->user();

        return $user
            && $user->status === 'active'
            && $user->hasRole('admin');
    }
}
