<?php

namespace App\Http\Controllers\Web\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Mail\Admin\AdminOtpMail;
use App\Models\User;
use App\Models\UserSecurityToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    /*
    |----------------------------------------------------------------------
    | PAGE VIEWS
    |----------------------------------------------------------------------
    */

    public function index()
    {
        return view('pages.auth.forgot_password');
    }

    public function showVerifyOtp()
    {
        if (! session()->has('otp_email')) {
            return redirect()->route('show.forgot-password');
        }

        return view('pages.auth.verify_otp');
    }

    public function showSetNewPassword()
    {
        if (! session()->has('otp_verified_email')) {
            return redirect()->route('show.forgot-password');
        }

        return view('pages.auth.set_new_password');
    }

    /*
    |----------------------------------------------------------------------
    | STEP 1 — Send OTP
    |----------------------------------------------------------------------
    */

    public function sendOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Email address is required.',
            'email.email'    => 'Please enter a valid email address.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Verify active admin exists
        $user = User::where('email', $request->email)
            ->where('status', 'active')
            ->first();

        if (! $user || ! $user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'errors'  => ['email' => ['No active admin account found with this email.']],
            ], 404);
        }

        // Throttle: block re-send within 60 seconds
        $recentExists = UserSecurityToken::recentlySent(
            $request->email,
            UserSecurityToken::TYPE_PASSWORD_RESET,
            60
        )->exists();

        if ($recentExists) {
            return response()->json([
                'success' => false,
                'errors'  => ['email' => ['Please wait 60 seconds before requesting a new OTP.']],
            ], 429);
        }

        // Invalidate all previous password_reset tokens for this email
        UserSecurityToken::invalidatePrevious(
            $request->email,
            UserSecurityToken::TYPE_PASSWORD_RESET
        );

        // Generate + hash OTP
        $plainOtp = (string) random_int(100000, 999999);

        UserSecurityToken::create([
            'user_id'    => $user->id,
            'identifier' => $request->email,
            'token_hash' => Hash::make($plainOtp),
            'type'       => UserSecurityToken::TYPE_PASSWORD_RESET,
            'expires_at' => now()->addMinutes(10),
            'used_at'    => null,
        ]);

        Mail::to($user->email)->send(new AdminOtpMail($plainOtp));

        session(['otp_email' => $request->email]);

        return response()->json([
            'success'  => true,
            'message'  => 'OTP sent to your email. Please check your inbox.',
            'redirect' => route('show.otp.verification'),
        ]);
    }

    /*
    |----------------------------------------------------------------------
    | STEP 2 — Verify OTP
    |----------------------------------------------------------------------
    */

    public function verifyOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'otp' => ['required', 'digits:6'],
        ], [
            'otp.required' => 'OTP is required.',
            'otp.digits'   => 'OTP must be exactly 6 digits.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $email = session('otp_email');

        if (! $email) {
            return response()->json([
                'success'  => false,
                'message'  => 'Session expired. Please restart the password reset process.',
                'redirect' => route('show.forgot-password'),
            ], 403);
        }

        // Find latest valid (non-expired, non-used) token record
        $tokenRecord = UserSecurityToken::valid(
            $email,
            UserSecurityToken::TYPE_PASSWORD_RESET
        )->latest()->first();

        if (! $tokenRecord) {
            return response()->json([
                'success' => false,
                'errors'  => ['otp' => ['OTP has expired or is invalid. Please request a new one.']],
            ], 422);
        }

        if (! Hash::check($request->otp, $tokenRecord->token_hash)) {
            return response()->json([
                'success' => false,
                'errors'  => ['otp' => ['Incorrect OTP. Please try again.']],
            ], 422);
        }

        // Mark as used (consumed)
        $tokenRecord->markUsed();

        session()->forget('otp_email');
        session([
            'otp_verified_email' => $email,
            'otp_verified_at'    => now()->timestamp,
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'OTP verified successfully.',
            'redirect' => route('show.set.new.password'),
        ]);
    }

    /*
    |----------------------------------------------------------------------
    | STEP 3 — Set New Password
    |----------------------------------------------------------------------
    */

    public function setNewPassword(Request $request): JsonResponse
    {
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
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $email      = session('otp_verified_email');
        $verifiedAt = session('otp_verified_at');

        if (! $email || ! $verifiedAt) {
            return response()->json([
                'success'  => false,
                'message'  => 'Session expired. Please restart the password reset process.',
                'redirect' => route('show.forgot-password'),
            ], 403);
        }

        // 15-minute window after OTP verification
        if (now()->timestamp - $verifiedAt > 900) {
            session()->forget(['otp_verified_email', 'otp_verified_at']);

            return response()->json([
                'success'  => false,
                'message'  => 'Your session has timed out. Please start over.',
                'redirect' => route('show.forgot-password'),
            ], 403);
        }

        $user = User::where('email', $email)->where('status', 'active')->first();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Account not found.'], 404);
        }

        $user->update(['password' => Hash::make($request->password)]);

        // Cleanup
        UserSecurityToken::invalidatePrevious($email, UserSecurityToken::TYPE_PASSWORD_RESET);
        session()->forget(['otp_verified_email', 'otp_verified_at']);

        return response()->json([
            'success'  => true,
            'message'  => 'Password reset successfully. You can now log in.',
            'redirect' => route('show.admin.login'),
        ]);
    }
}
