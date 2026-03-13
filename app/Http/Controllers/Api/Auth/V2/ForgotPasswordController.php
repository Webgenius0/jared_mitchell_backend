<?php

namespace App\Http\Controllers\Api\Auth\V2;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSecurityToken;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    use ApiResponse;

    /**
     * Send a password-reset link to the user's email.
     *
     * POST /v2/forgot-password
     *
     * The link contains a plain token as a query-string parameter.
     * Clicking the link calls verifyResetToken (GET) which returns
     * a short-lived reset_token the client uses to POST /v2/reset-password.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        try {
            $user = User::where('email', $request->email)->first();

            // Check for an existing, unexpired, unused token
            $existingToken = UserSecurityToken::where('user_id', $user->id)
                ->where('type', 'password_reset')
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->latest()
                ->first();

            if ($existingToken) {
                // Rate-limit: wait 2.5 minutes between requests
                $resendAvailableAt = $existingToken->created_at->addSeconds(150);

                if (now()->lessThan($resendAvailableAt)) {
                    $waitSeconds = now()->diffInSeconds($resendAvailableAt);
                    return $this->error(
                        "A reset link was already sent. Please wait {$waitSeconds} seconds before trying again.",
                        null,
                        429
                    );
                }

                // Cooldown passed → invalidate old token
                $existingToken->update(['used_at' => now()]);
            }

            $resetLink = $this->generateResetLink($user);

            // Mail::to($user->email)
            //     ->queue(new PasswordResetMail($user, $resetLink));

            return $this->success(
                'A password reset link has been sent to your email.',
                [
                    'email'      => $user->email,
                    'expires_in' => '1 hour',
                    // REMOVE in production — only exposed for dev/testing
                    '_dev_link'  => $resetLink,
                ]
            );
        } catch (Exception $e) {
            Log::error('Password reset link failed: ' . $e->getMessage());
            return $this->error('Failed to send reset link.', null, 500);
        }
    }

    /**
     * Validate the reset link clicked from the inbox.
     * Returns a short-lived reset_token for the final password reset step.
     *
     * GET /v2/verify-reset-token?token=xxxx&email=user@example.com
     */
    public function verifyResetToken(Request $request)
    {
        $frontendUrl = config('app.frontend_url');

        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
                'token' => 'required|string',
            ]);

            if ($validator->fails()) {
                return redirect($frontendUrl . '/auth/verify-invalid');
            }

            $user = User::where('email', $request->email)->first();

            // Find a valid link-token (type = password_reset, not yet consumed)
            $tokenRecord = UserSecurityToken::where('user_id', $user->id)
                ->where('type', 'password_reset')
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->latest()
                ->first();

            if (!$tokenRecord || !Hash::check($request->token, $tokenRecord->token_hash)) {

                // Check if it's expired (no used_at but expires_at has passed)
                $expiredToken = UserSecurityToken::where('user_id', $user->id)
                    ->where('type', 'password_reset')
                    ->whereNull('used_at')
                    ->where('expires_at', '<=', now())
                    ->latest()
                    ->first();

                if ($expiredToken) {
                    // Expired page — resend button will be here
                    return redirect(
                        $frontendUrl . '/auth/verify-expired?email=' . urlencode($user->email)
                    );
                }

                return redirect($frontendUrl . '/auth/verify-invalid');
            }

            // Mark the link-token as used
            $tokenRecord->update(['used_at' => now()]);

            // Issue a short-lived reset_token the client sends with the new password
            $plainResetToken = Str::random(64);

            UserSecurityToken::create([
                'user_id'    => $user->id,
                'identifier' => $user->email,
                'token_hash' => Hash::make($plainResetToken),
                'type'       => 'password_reset',
                'expires_at' => now()->addMinutes(15), // short window after link click
            ]);

            return redirect(
                $frontendUrl . '/auth/set-new-password?token=' . $plainResetToken
            );
        } catch (Exception $e) {
            Log::error('Reset token verification failed: ' . $e->getMessage());
            return redirect($frontendUrl . '/auth/verify-error');
        }
    }

    /**
     * Set a new password using the reset_token obtained after link verification.
     *
     * POST /v2/reset-password
     * Body: { email, token, password, password_confirmation }
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'    => 'required|email|exists:users,email',
            'token'    => 'required|string',
            'password' => 'required|min:6|confirmed',
        ]);

        try {
            $user = User::where('email', $request->email)->first();

            $tokenRecord = UserSecurityToken::where('user_id', $user->id)
                ->where('type', 'password_reset')
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->latest()
                ->first();

            if (!$tokenRecord || !Hash::check($request->token, $tokenRecord->token_hash)) {
                return $this->error(
                    'Invalid or expired reset token. Please restart the password reset process.',
                    null,
                    419
                );
            }

            // Update password
            $user->update([
                'password' => Hash::make($request->password),
            ]);

            // Invalidate the reset token
            $tokenRecord->update(['used_at' => now()]);

            // Optionally: invalidate all other active reset tokens for this user
            UserSecurityToken::where('user_id', $user->id)
                ->where('type', 'password_reset')
                ->whereNull('used_at')
                ->update(['used_at' => now()]);

            // try {
            //     Mail::to($user->email)->send(new PasswordChangedMail($user));
            // } catch (Exception $mailEx) {
            //     Log::error('Password changed mail failed: ' . $mailEx->getMessage());
            // }

            return $this->success('Password has been reset successfully. You can now log in.');
        } catch (Exception $e) {
            Log::error('Password reset failed: ' . $e->getMessage());
            return $this->error('Password reset failed.', null, 500);
        }
    }

    // -------------------------------------------------------------------------
    //  Private Helpers
    // -------------------------------------------------------------------------

    /**
     * Generate a signed reset link and persist the hashed token.
     */
    private function generateResetLink(User $user): string
    {
        $plainToken = Str::random(64);

        UserSecurityToken::create([
            'user_id'    => $user->id,
            'identifier' => $user->email,
            'token_hash' => Hash::make($plainToken),
            'type'       => 'password_reset',
            'expires_at' => now()->addHour(),
        ]);

        // Build the link: your frontend handles this GET route
        return config('app.url') . '/api/v2/verify-reset-token'
            . '?token=' . urlencode($plainToken)
            . '&email=' . urlencode($user->email);
    }
}
