<?php

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Mail\Api\OtpMail;
use App\Models\Profile;
use App\Models\User;
use App\Models\UserSecurityToken;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class RegisterController extends Controller
{
    use ApiResponse;

    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:150',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required',
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

            // email exists
            if ($existingUser) {

                // already verified
                if ($existingUser->email_verified_at) {
                    return $this->error('Email already registered.', 'Email already registered. please login', 409);
                }

                // resend OTP
                $otp = rand(1000, 9999);

                UserSecurityToken::create([
                    'user_id' => $existingUser->id,
                    'identifier' => $existingUser->email,
                    'token_hash' => Hash::make($otp),
                    'type' => 'email_verification',
                    'expires_at' => now()->addMinutes(60),
                ]);

                if ($request->email) {
                    Mail::to($request->email)->queue(
                        (new OtpMail(
                            otp: $otp,
                            user: $existingUser,
                            mailSubject: 'Verify Your Email Address',
                            headerTitle: 'Email Verification',
                            bodyMessage: 'Use the OTP below to verify your email address.',
                            expiresInMinutes: 60,
                        ))->afterCommit()
                    );
                }

                DB::commit();

                return $this->success(
                    'Email already registered but not verified. OTP resent.',
                    [
                        'user' => new UserResource($existingUser),
                        'otp' => $otp,
                        'verification_required' => true
                    ]
                );
            }

            // Create new user
            $user = User::create([
                'email' => strtolower($request->email),
                'password' => Hash::make($request->password),
                'status' => 'inactive',
            ]);

            $slug = Helper::generateSlug($request->name);
            $username = Helper::generateUsername($request->name);

            Profile::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'username' => $username,
                'slug' => $slug,
            ]);

            DB::table('model_has_roles')->insert([
                'role_id' => $request->role,
                'model_type' => User::class,
                'model_id' => $user->id,
            ]);

            $otp = rand(1000, 9999);

            UserSecurityToken::create([
                'user_id' => $user->id,
                'identifier' => $user->email,
                'token_hash' => Hash::make($otp),
                'type' => 'email_verification',
                'expires_at' => now()->addMinutes(60),
            ]);

            if ($user->email) {
                Mail::to($user->email)->queue(
                    (new OtpMail(
                        otp: $otp,
                        user: $user,
                        mailSubject: 'Verify Your Email Address',
                        headerTitle: 'Email Verification',
                        bodyMessage: 'Use the OTP below to verify your email address.',
                        expiresInMinutes: 60,
                    ))->afterCommit()
                );
            }

            DB::commit();

            return $this->success(
                'User registered successfully. Verify your email.',
                [
                    'user' => new UserResource($user),
                    'otp' => $otp,
                    'verification_required' => true
                ],
                201
            );
        } catch (Exception $e) {

            DB::rollBack();

            return $this->error(null, 'User registration failed', 500);
        }
    }

    /**
     * Verify email OTP
     */
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp'   => 'required|digits:4',
        ]);

        try {
            $user = User::where('email', $request->email)->first();

            // Already verified
            if ($user->email_verified_at) {
                DB::rollBack();

                return $this->error(null, 'Email already verified', 409);
            }

            // Fetch OTP token
            $token = UserSecurityToken::where('user_id', $user->id)
                ->where('type', 'email_verification')
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->latest()
                ->first();

            if (!$token || !Hash::check($request->otp, $token->token_hash)) {
                return $this->error(null, 'Invalid or expired OTP', 422);
            }

            // Mark token used
            $token->used_at = now();
            $token->save();

            // Update user verification
            $user->email_verified_at = now();
            $user->status = 'active';
            $user->save();

            // Send Welcome Email
            // try {
            //     Mail::to($user->email)->send(new WelcomeMail($user));
            // } catch (Exception $mailEx) {
            //     // Log mail error silently
            //     Log::error('Welcome email failed: ' . $mailEx->getMessage());
            // }

            // Generate JWT token
            $tokenJwt = JWTAuth::fromUser($user);
            $expires_in = (int) config('jwt.ttl', 60) * 60;

            return $this->success(
                'Email verified successfully.',
                [
                    'user' => new UserResource($user),
                    'token' => $tokenJwt,
                    'token_type' => 'bearer',
                    'expires_in' => $expires_in
                ]
            );
        } catch (Exception $e) {
            return $this->error(null, 'Verification failed', 500);
        }
    }

    /**
     * Resend OTP
     */
    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        try {
            $user = User::where('email', $request->email)->first();

            if ($user->email_verified_at) {
                return $this->error(null, 'Email already verified', 409);
            }

            $otp = rand(1000, 9999);

            UserSecurityToken::create([
                'user_id' => $user->id,
                'identifier' => $user->email,
                'token_hash' => Hash::make($otp),
                'type' => 'email_verification',
                'expires_at' => now()->addMinutes(60),
            ]);

            Mail::to($user->email)->queue(
                (new OtpMail(
                    otp: $otp,
                    user: $user,
                    mailSubject: 'Verify Your Email Address',
                    headerTitle: 'Email Verification',
                    bodyMessage: 'Use the OTP below to verify your email address.',
                    expiresInMinutes: 60,
                ))->afterCommit()
            );

            return $this->success(
                'A new OTP has been sent to your email.',
                [
                    'otp' => $otp
                ],
                201
            );
        } catch (Exception $e) {
            return $this->error(null, 'OTP resend failed', 500);
        }
    }
}
