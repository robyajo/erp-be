<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Requests\Api\V1\ResendVerificationRequest;
use App\Http\Requests\Api\V1\ResetPasswordRequest;
use App\Http\Requests\Api\V1\VerifyEmailRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AvatarService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class AuthController extends ApiController
{
    private function makeUsername(string $name, string $fallback = 'user'): string
    {
        $base = Str::slug($name, '');
        if (empty($base)) {
            $base = $fallback;
        }
        $username = $base;
        $count = 1;
        while (User::where('username', $username)->exists()) {
            $username = $base . $count++;
        }
        return $username;
    }
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::query()->create([
            'name' => $request->name,
            'username' => $this->makeUsername($request->name),
            'provider_name' => 'credentials',
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->sendEmailVerificationNotification();

        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->created([
            'user' => new UserResource($user),
            'token' => $token,
        ], 'User registered successfully. Please check your email to verify your account.');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->unauthorized('Invalid credentials');
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->success([
            'user' => new UserResource($user),
            'token' => $token,
        ], 'Login successful');
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        /** @var PersonalAccessToken|null $token */
        $token = $user->currentAccessToken();

        if ($token !== null) {
            $token->delete();
        }

        return $this->success(message: 'Logged out successfully');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success(new UserResource($request->user()));
    }

    public function verifyEmail(VerifyEmailRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return $this->success(message: 'Email already verified');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return $this->success(message: 'Email verified successfully');
    }

    public function resendVerificationEmail(ResendVerificationRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->email)->first();

        if (! $user) {
            return $this->notFound('User not found');
        }

        if ($user->hasVerifiedEmail()) {
            return $this->error('Email already verified', 400);
        }

        $user->sendEmailVerificationNotification();

        return $this->success(message: 'Verification email sent successfully');
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return $this->success(message: 'Password reset link sent to your email');
        }

        return $this->error('Unable to send reset link', 500);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();

                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return $this->success(message: 'Password reset successfully');
        }

        return $this->error(
            match ($status) {
                Password::INVALID_TOKEN => 'Invalid or expired reset token',
                Password::INVALID_USER => 'User not found',
                default => 'Unable to reset password',
            },
            400
        );
    }
    public function refresh(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return $this->success($this->formatUserData($user), 'Token refreshed successfully');
    }

    public function socialRedirect(string $provider): RedirectResponse
    {
        /** @var \Laravel\Socialite\Two\AbstractProvider $driver */
        $driver = Socialite::driver($provider);

        return $driver->stateless()->redirect();
    }

    public function socialCallback(string $provider, AvatarService $avatar): RedirectResponse
    {
        try {
            /** @var \Laravel\Socialite\Two\AbstractProvider $driver */
            $driver = Socialite::driver($provider);
            /** @var \Laravel\Socialite\Two\User $socialUser */
            $socialUser = $driver->stateless()->user();
        } catch (\Exception $e) {
            $url = config('app.frontend_url');
            $frontendUrl = is_string($url) ? $url : 'http://localhost:3000';
            return redirect($frontendUrl . '/signin?error=Invalid credentials');
        }

        $user = User::where('email', $socialUser->getEmail())->first();

        if (!$user) {
            $data = [
                'name' => $socialUser->getName() ?? $socialUser->getNickname(),
                'email' => $socialUser->getEmail(),
                'username' => $this->makeUsername(
                    $socialUser->getName() ?? $socialUser->getNickname() ?? '',
                    explode('@', (string) $socialUser->getEmail())[0]
                ),
                'provider_id' => $socialUser->getId(),
                'provider_name' => $provider,
                'email_verified_at' => now(),
            ];

            $avatar = $socialUser->getAvatar();
            if ($avatar) {
                $data['avatar'] = $avatar;
            }

            $user = User::create($data);
            $user->assignRole('User');
        } else {
            $updateData = [
                'name' => $socialUser->getName() ?? $socialUser->getNickname(),
                'provider_id' => $socialUser->getId(),
                'provider_name' => $provider,
            ];

            $avatar = $socialUser->getAvatar();
            if ($avatar) {
                $updateData['avatar'] = $avatar;
            }

            $user->update($updateData);
        }

        $url = config('app.frontend_url', 'http://localhost:3000');
        $frontendUrl = is_string($url) ? $url : 'http://localhost:3000';

        $accessToken = $user->createToken('credential-login')->plainTextToken;
        $refreshToken = $user->createToken('credential-refresh')->plainTextToken;

        return redirect($frontendUrl . '/oauth/callback?accessToken=' . $accessToken . '&refreshToken=' . $refreshToken);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatUserData(User $user): array
    {
        $accessToken = $user->createToken('credential-login')->plainTextToken;
        $refreshToken = $user->createToken('credential-refresh')->plainTextToken;

        return [
            'user' => new UserResource($user),
            'roles' => $user->roles->pluck('name')->implode(','),
            'permissions' => $user->permissions->pluck('name')->toArray(),
            'tokens' => [
                'accessToken' => $accessToken,
                'refreshToken' => $refreshToken,
            ],
        ];
    }
}
