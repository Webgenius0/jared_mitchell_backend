<?php

namespace App\Http\Controllers\Web\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Mail\Admin\AdminOtpMail;
use App\Models\User;
use App\Models\UserSecurityToken;
use App\Traits\AdminApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    use AdminApiResponse;

    /*
    |----------------------------------------------------------------------
    | PAGE VIEWS
    |----------------------------------------------------------------------
    */

    public function index(): View|RedirectResponse
    {
        // Already logged in — no need to reset
        if (auth('admin')->check()) {
            return redirect()->route('show.admin.dashboard');
        }

        return view('web.auth.forgot_password');
    }

    public function showVerifyOtp(): View|RedirectResponse
    {
        if (! session()->has('otp_email')) {
            return redirect()->route('show.forgot-password')
                ->with('error', 'Please enter your email first.');
        }

        return view('web.auth.verify_otp');
    }

    public function showSetNewPassword(): View|RedirectResponse
    {
        if (! session()->has('otp_verified_email')) {
            return redirect()->route('show.forgot-password')
                ->with('error', 'Please complete OTP verification first.');
        }

        return view('web.auth.set_new_password');
    }

    /*
    |----------------------------------------------------------------------
    | STEP 1 — Send OTP
    |----------------------------------------------------------------------
    */

    public function sendOtp(Request $request): JsonResponse
    {
        // ── 1. Validate ────────────────────────────────────────────────────
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Email address is required.',
            'email.email'    => 'Please enter a valid email address.',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        // ── 2. Check active admin exists ───────────────────────────────────
        $user = User::where('email', $request->email)
            ->where('status', 'active')
            ->first();

        if (! $user || ! $user->hasRole('admin')) {
            return $this->error('No active admin account found with this email.', [
                'email' => ['No active admin account found with this email.'],
            ], 404);
        }

        // ── 3. Throttle: max 3 OTP requests per 5 minutes ─────────────────
        $throttleKey = 'otp-send:' . $request->email;

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return $this->error(
                "Too many OTP requests. Please wait {$seconds} seconds before trying again.",
                ['email' => ["Too many OTP requests. Please wait {$seconds} seconds."]],
                429
            );
        }

        // ── 4. Block re-send within 60 seconds ────────────────────────────
        $recentExists = UserSecurityToken::recentlySent(
            $request->email,
            UserSecurityToken::TYPE_PASSWORD_RESET,
            60
        )->exists();

        if ($recentExists) {
            return $this->error(
                'Please wait 60 seconds before requesting a new OTP.',
                ['email' => ['Please wait 60 seconds before requesting a new OTP.']],
                429
            );
        }

        // ── 5. Invalidate previous tokens & create new one ─────────────────
        UserSecurityToken::invalidatePrevious(
            $request->email,
            UserSecurityToken::TYPE_PASSWORD_RESET
        );

        $plainOtp = (string) random_int(100000, 999999);

        UserSecurityToken::create([
            'user_id'    => $user->id,
            'identifier' => $request->email,
            'token_hash' => Hash::make($plainOtp),
            'type'       => UserSecurityToken::TYPE_PASSWORD_RESET,
            'expires_at' => now()->addMinutes(10),
            'used_at'    => null,
        ]);

        // ── 6. Send OTP email ──────────────────────────────────────────────
        Mail::to($user->email)->send(
            new AdminOtpMail($plainOtp, $user->name ?? 'Admin')
        );

        RateLimiter::hit($throttleKey, 300); // 5-minute window

        session(['otp_email' => $request->email]);

        return $this->success(
            'OTP sent to your email. Please check your inbox.',
            [],
            route('show.otp.verification')
        );
    }

    /*
    |----------------------------------------------------------------------
    | STEP 2 — Verify OTP
    |----------------------------------------------------------------------
    */

    public function verifyOtp(Request $request): JsonResponse
    {
        // ── 1. Validate ────────────────────────────────────────────────────
        $validator = Validator::make($request->all(), [
            'otp' => ['required', 'digits:6'],
        ], [
            'otp.required' => 'OTP is required.',
            'otp.digits'   => 'OTP must be exactly 6 digits.',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        // ── 2. Brute-force protection: max 5 attempts per email ────────────
        $email       = session('otp_email');
        $throttleKey = 'otp-verify:' . ($email ?? $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return $this->error(
                "Too many failed attempts. Please wait {$seconds} seconds.",
                ['otp' => ["Too many attempts. Please wait {$seconds} seconds."]],
                429
            );
        }

        // ── 3. Session check ───────────────────────────────────────────────
        if (! $email) {
            return $this->error(
                'Session expired. Please restart the password reset process.',
                [],
                403,
            );
        }

        // ── 4. Find latest valid token ─────────────────────────────────────
        $tokenRecord = UserSecurityToken::valid(
            $email,
            UserSecurityToken::TYPE_PASSWORD_RESET
        )->latest()->first();

        if (! $tokenRecord) {
            return $this->error(
                'OTP has expired or is invalid. Please request a new one.',
                ['otp' => ['OTP has expired or is invalid. Please request a new one.']],
                422
            );
        }

        // ── 5. Check OTP hash ──────────────────────────────────────────────
        if (! Hash::check($request->otp, $tokenRecord->token_hash)) {
            RateLimiter::hit($throttleKey, 300); // count this failed attempt

            return $this->error(
                'Incorrect OTP. Please try again.',
                ['otp' => ['Incorrect OTP. Please try again.']],
                422
            );
        }

        // ── 6. OTP correct — mark used, advance session ────────────────────
        $tokenRecord->markUsed();
        RateLimiter::clear($throttleKey);

        session()->forget('otp_email');
        session([
            'otp_verified_email' => $email,
            'otp_verified_at'    => now()->timestamp,
        ]);

        return $this->success(
            'OTP verified successfully.',
            [],
            route('show.set.new.password')
        );
    }

    /*
    |----------------------------------------------------------------------
    | STEP 3 — Set New Password
    |----------------------------------------------------------------------
    */

    public function setNewPassword(Request $request): JsonResponse
    {
        // ── 1. Validate ────────────────────────────────────────────────────
        $validator = Validator::make($request->all(), [
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/',
            ],
            'password_confirmation' => ['required'],
        ], [
            'password.required'              => 'New password is required.',
            'password.min'                   => 'Password must be at least 8 characters.',
            'password.confirmed'             => 'Passwords do not match.',
            'password.regex'                 => 'Password must include uppercase, lowercase, number, and special character.',
            'password_confirmation.required' => 'Please confirm your password.',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        // ── 2. Session check ───────────────────────────────────────────────
        $email      = session('otp_verified_email');
        $verifiedAt = session('otp_verified_at');

        if (! $email || ! $verifiedAt) {
            return $this->error(
                'Session expired. Please restart the password reset process.',
                [],
                403
            );
        }

        // ── 3. 15-minute window after OTP verification ─────────────────────
        if (now()->timestamp - $verifiedAt > 900) {
            session()->forget(['otp_verified_email', 'otp_verified_at']);

            return $this->error(
                'Your session has timed out. Please start over.',
                [],
                403
            );
        }

        // ── 4. Find user ───────────────────────────────────────────────────
        $user = User::where('email', $email)
            ->where('status', 'active')
            ->first();

        if (! $user) {
            return $this->error('Account not found.', [], 404);
        }

        // ── 5. Prevent reusing the same password ───────────────────────────
        if (Hash::check($request->password, $user->password)) {
            return $this->error(
                'New password cannot be the same as your current password.',
                ['password' => ['New password cannot be the same as your current password.']],
                422
            );
        }

        // ── 6. Update password & cleanup ───────────────────────────────────
        $user->update(['password' => Hash::make($request->password)]);

        UserSecurityToken::invalidatePrevious($email, UserSecurityToken::TYPE_PASSWORD_RESET);
        session()->forget(['otp_verified_email', 'otp_verified_at']);

        return $this->success(
            'Password reset successfully. You can now log in.',
            [],
            route('show.admin.login')
        );
    }
}
