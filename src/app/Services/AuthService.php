<?php
namespace App\Services;

use App\Helpers\MediaHelper;
use App\Http\Requests\AuthUserAccountRequest;
use App\Http\Requests\AuthUserProfileRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use Exception;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified as VerifiedEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;

class AuthService
{
    public function authUser(): User
    {
        return $this->findBySlug(Auth::user()->slug);
    }

    public function findBySlug(int | string $slugOrId): User
    {
        return User::with([
            'media',
            'createdBy',

            'activityLogs' => fn($query) => $query->latest()->limit(10),
            'activityLogs.causer',

            'latestActivityLog',
            'latestActivityLog.causer',
        ])->where('id', $slugOrId)
            ->orWhere('slug', $slugOrId)
            ->firstOrFail();
    }

    public function login(LoginRequest $request): array
    {
        try {
            $credentials = $request->only('email', 'password');

            if (! Auth::attempt($credentials, $request->boolean('remember'))) {
                return ['status' => 'error', 'message' => 'Invalid email or password.'];
            }

            session()->regenerate();

            return ['status' => 'success', 'message' => 'Login successful.'];
        } catch (Exception $exception) {
            Log::error('Login error.', ['exception' => $exception, 'request_data' => $request->input()]);
            return ['status' => 'error', 'message' => 'Login failed. Please try again.'];
        }
    }

    public function register(RegisterRequest $request): array
    {
        try {
            $user = User::create([
                'name'           => $request->name,
                'email'          => $request->email,
                'password'       => Hash::make($request->password),
                'created_by_id'  => null,

                'is_super_admin' => false,
                'is_default'     => false,
                'created_at'     => now(),
                'updated_at'     => null,
            ]);

            event(new Registered($user));
            Auth::login($user);

            return ['status' => 'success', 'message' => 'Registration successful.'];
        } catch (Exception $exception) {
            Log::error('Register error.', ['exception' => $exception, 'request_data' => $request->input()]);
            return ['status' => 'error', 'message' => 'Registration failed. Please try again.'];
        }
    }

    public function logout(): array
    {
        try {
            if (Auth::check()) {
                Auth::logout();
                session()->invalidate();
                session()->regenerateToken();
            }

            return ['status' => 'success', 'message' => 'Logout successful.'];
        } catch (Exception $exception) {
            Log::error('Logout error.', ['exception' => $exception, 'request_data' => Auth::user()]);
            return ['status' => 'error', 'message' => 'Logout failed. Please try again.'];
        }
    }

    public function forgotPassword(ForgotPasswordRequest $request): array
    {
        try {
            $status = Password::sendResetLink($request->only('email'));

            return $status === Password::RESET_LINK_SENT
                ? ['status' => 'success', 'message' => 'Password reset link has been sent successfully.']
                : ['status' => 'error', 'message' => 'Unable to send password reset link.'];
        } catch (Exception $exception) {
            Log::error('Forget password error.', ['exception' => $exception, 'request_data' => $request->input()]);
            return ['status' => 'error', 'message' => 'Unable to send password reset link.'];
        }
    }

    public function resetPassword(ResetPasswordRequest $request, string $token, string $email): array
    {
        try {
            $status = Password::reset(
                [
                    'email'                 => $request->email,
                    'password'              => $request->password,
                    'password_confirmation' => $request->password_confirmation,
                    'token'                 => $request->token,
                ],
                function ($user) use ($request) {
                    $user->forceFill(['password' => Hash::make($request->password)])->save();
                    event(new PasswordReset($user));
                }
            );

            return $status === Password::PASSWORD_RESET
                ? ['status' => 'success', 'message' => 'Password reset successful.']
                : ['status' => 'error', 'message' => 'Password reset failed. Please try again.'];
        } catch (Exception $exception) {
            Log::error('Reset password error.', ['exception' => $exception, 'request_data' => $request->input()]);
            return ['status' => 'error', 'message' => 'Password reset failed. Please try again.'];
        }
    }

    public function emailVerification(Request $request, $id, $hash): array
    {
        try {
            $user = $request->user();

            if ($user->markEmailAsVerified()) {
                return ['status' => 'success', 'message' => 'Email verification successful.'];
            }

            if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
                return ['status' => 'error', 'message' => 'Email verification link has expired.'];
            }

            event(new VerifiedEvent($user));

            return ['status' => 'error', 'message' => 'Email verification failed.'];
        } catch (Exception $exception) {
            Log::error('Email verify error.', ['exception' => $exception, 'request_data' => $request->input()]);
            return ['status' => 'error', 'message' => 'Email verification failed.'];
        }
    }

    public function emailVerificationResend(Request $request): array
    {
        try {
            $request->user()->sendEmailVerificationNotification();
            return ['status' => 'success', 'message' => 'Verification email has been sent successfully.'];
        } catch (Exception $exception) {
            Log::error('Email verify resend error.', ['exception' => $exception, 'request_data' => $request->input()]);
            return ['status' => 'error', 'message' => 'Unable to send verification email.'];
        }
    }

    public function profileUpdate(AuthUserProfileRequest $request, User $user): array
    {
        try {

            DB::transaction(function () use ($request, $user) {
                $user->name           = $request->input('name');
                $user->birth_date     = $request->input('birth_date');
                $user->gender         = $request->input('gender');
                $user->religion       = $request->input('religion');
                $user->marital_status = $request->input('marital_status');
                $user->mobile         = $request->input('mobile');
                $user->address        = $request->input('address');
                $user->save();

                self::saveUserProfileImage($request, $user);
            });

            return ['status' => 'success', 'message' => 'Profile updated successfully.'];
        } catch (Exception $exception) {

            Log::error('Auth profile update fail.', ['exception' => $exception, 'request_data' => $request->input()]);
            return ['status' => 'error', 'message' => 'Profile update failed. Please try again.'];
        }
    }

    public function accountUpdate(AuthUserAccountRequest $request, User $user): array
    {
        try {

            $requiresVerification = $request->input('email') !== Auth::user()->email;

            $user = DB::transaction(function () use ($request, $requiresVerification, $user) {

                $user->name              = $request->input('name');
                $user->email             = $request->input('email');
                $user->email_verified_at = $requiresVerification ? null : $user->email_verified_at;

                if ($request->change_password == 1) {
                    $user->password = Hash::make($request->input('password'));
                }

                $user->save();

                return $user;
            });

            if ($requiresVerification) {
                $user->sendEmailVerificationNotification();
            }

            return ['status' => 'success', 'message' => 'Account updated successfully.'];
        } catch (Exception $exception) {

            Log::error('Auth account update fail.', ['exception' => $exception, 'request_data' => $request->input()]);
            return ['status' => 'error', 'message' => 'Account update failed. Please try again.'];
        }
    }

    private static function saveUserProfileImage(AuthUserProfileRequest $request, User $user)
    {
        if (! $request->hasFile('profile_image')) {
            return;
        }

        $existing = $user->profileImage();
        if ($existing) {
            $existing->delete();
        }

        $uploaded = $request->file('profile_image');

        if ($uploaded) {
            $name = MediaHelper::generateMediaName(
                $user->name,
                $uploaded->getClientOriginalExtension(),
                200
            );

            $user->addMedia($uploaded)
                ->usingFileName($name)
                ->withCustomProperties([
                    'alt'     => $user->name ?? null,
                    'caption' => $user->name ?? null,
                    'role'    => MediaHelper::ROLE_PROFILE_IMAGE,
                ])
                ->toMediaCollection($user->media_collection_name);
        }
    }
}
