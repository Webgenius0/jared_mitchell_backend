<?php

namespace App\Http\Controllers\Api\Auth\V2;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Profile;
use App\Models\User;
use App\Models\UserSecurityToken;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    use ApiResponse;

    /**
     * Register a new user.
     * Sends a verification link (token via query string) to the user's email.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:100',
            'email'    => 'required|string|email|max:150',
            'password' => 'required|string|min:6|confirmed',
            'role'     => 'required|in:5,6,7,8',
        ]);

        if ($validator->fails()) {
            return $this->validationError(
                $validator->errors()->toArray(),
                'Validation failed',
                422
            );
        }

        DB::beginTransaction();

        try {
            $existingUser = User::where('email', $request->email)->first();

            // Email already exists
            if ($existingUser) {

                // Already verified → block
                if ($existingUser->email_verified_at) {
                    return $this->error(
                        'Email already registered.',
                        'Email already registered. Please login.',
                        409
                    );
                }

                // Not verified → invalidate old tokens and resend link
                UserSecurityToken::where('user_id', $existingUser->id)
                    ->where('type', 'email_verification')
                    ->whereNull('used_at')
                    ->update(['used_at' => now()]);

                $verificationLink = $this->generateVerificationLink($existingUser);

                // Mail::to($existingUser->email)
                //     ->send(new EmailVerificationMail($existingUser, $verificationLink));

                DB::commit();

                return $this->success(
                    'Email already registered but not verified. A new verification link has been sent.',
                    [
                        'user'                  => new UserResource($existingUser),
                        'verification_required' => true,
                        // REMOVE in production — only exposed for dev/testing
                        '_dev_link'             => $verificationLink,
                    ]
                );
            }

            // Create new user
            $user = User::create([
                'email'    => strtolower($request->email),
                'password' => Hash::make($request->password),
                'status'   => 'inactive',
            ]);

            $slug     = Helper::generateSlug($request->name);
            $username = Helper::generateUsername($request->name);

            Profile::create([
                'user_id'  => $user->id,
                'name'     => $request->name,
                'username' => $username,
                'slug'     => $slug,
            ]);

            DB::table('model_has_roles')->insert([
                'role_id'    => $request->role,
                'model_type' => User::class,
                'model_id'   => $user->id,
            ]);

            $verificationLink = $this->generateVerificationLink($user);

            // Mail::to($user->email)
            //     ->send(new EmailVerificationMail($user, $verificationLink));

            DB::commit();

            return $this->success(
                'Registered successfully. Please check your email to verify your account.',
                [
                    'user'                  => new UserResource($user),
                    'verification_required' => true,
                    // REMOVE in production — only exposed for dev/testing
                    '_dev_link'             => $verificationLink,
                ],
                201
            );
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Registration failed: ' . $e->getMessage());

            return $this->error('User registration failed', $e->getMessage(), 500);
        }
    }

    /**
     * Verify email via the link clicked from the inbox.
     * GET /v2/verify-email?token=xxxx&email=user@example.com
     */
    public function verifyEmail(Request $request)
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

            // Already verified
            if ($user->email_verified_at) {
                return $this->error('Email is already verified.', null, 409);
            }

            $tokenRecord = UserSecurityToken::where('user_id', $user->id)
                ->where('type', 'email_verification')
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->latest()
                ->first();

            if (!$tokenRecord || !Hash::check($request->token, $tokenRecord->token_hash)) {

                // Check if it's expired (no used_at but expires_at has passed)
                $expiredToken = UserSecurityToken::where('user_id', $user->id)
                    ->where('type', 'email_verification')
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

            // Mark token as used
            $tokenRecord->update(['used_at' => now()]);

            // Verify user
            $user->update([
                'email_verified_at' => now(),
                'status'            => 'active',
            ]);

            // try {
            //     Mail::to($user->email)->send(new WelcomeMail($user));
            // } catch (Exception $mailEx) {
            //     Log::error('Welcome email failed: ' . $mailEx->getMessage());
            // }

            // Issue JWT
            $jwt = auth('api')->login($user);
            return redirect(
                $frontendUrl . '/auth/verified?token=' . $jwt . '&token_type=bearer'
            );
        } catch (Exception $e) {
            Log::error('Email verification failed: ' . $e->getMessage());
            return redirect($frontendUrl . '/auth/verify-error');
        }
    }

    /**
     * Resend the verification link to the user's email.
     *
     * POST /v2/resend-verification
     */
    public function resendVerification(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        try {
            $user = User::where('email', $request->email)->first();

            if ($user->email_verified_at) {
                return $this->error('Email is already verified.', null, 409);
            }

            // Rate-limit: only allow resend every 2 minutes
            $recentToken = UserSecurityToken::where('user_id', $user->id)
                ->where('type', 'email_verification')
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->latest()
                ->first();

            if ($recentToken) {
                $resendAvailableAt = $recentToken->created_at->addSeconds(120);

                if (now()->lessThan($resendAvailableAt)) {
                    $waitSeconds = (int) now()->diffInSeconds($resendAvailableAt);
                    return $this->error(
                        "Please wait {$waitSeconds} seconds before requesting a new link.",
                        null,
                        429
                    );
                }

                // Cooldown passed → invalidate old token
                $recentToken->update(['used_at' => now()]);
            }

            $verificationLink = $this->generateVerificationLink($user);

            // Mail::to($user->email)
            //     ->send(new EmailVerificationMail($user, $verificationLink));

            return $this->success(
                'A new verification link has been sent to your email.',
                [
                    // ⚠️  REMOVE in production — only exposed for dev/testing
                    '_dev_link' => $verificationLink,
                ]
            );
        } catch (Exception $e) {
            Log::error('Resend verification failed: ' . $e->getMessage());
            return $this->error('Failed to resend verification link.', null, 500);
        }
    }

    // -------------------------------------------------------------------------
    //  Private Helpers
    // -------------------------------------------------------------------------
    /**
     * Generate a signed verification link and persist the hashed token.
     */
    private function generateVerificationLink(User $user): string
    {
        $plainToken = Str::random(64);

        UserSecurityToken::create([
            'user_id'    => $user->id,
            'identifier' => $user->email,
            'token_hash' => Hash::make($plainToken),
            'type'       => 'email_verification',
            'expires_at' => now()->addHour(),
        ]);

        // Build the link: your frontend / API route handles this GET
        return config('app.url') . '/api/v2/verify-email'
            . '?token=' . urlencode($plainToken)
            . '&email=' . urlencode($user->email);
    }
}
