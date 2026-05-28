<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
|
| Routes for API version 1.
|
*/

// Public routes with auth rate limiter (5/min - brute force protection)
Route::middleware('throttle:auth')->group(function (): void {
    Route::post('register', [AuthController::class, 'register'])->name('api.v1.register');
    Route::post('login', [AuthController::class, 'login'])->name('api.v1.login');
});

// Protected routes with authenticated rate limiter (120/min)
Route::middleware(['auth:sanctum', 'throttle:authenticated'])->group(function (): void {
    Route::post('logout', [AuthController::class, 'logout'])->name('api.v1.logout');
    Route::get('me', [AuthController::class, 'me'])->name('api.v1.me');

    // Email verification
    Route::post('email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware('signed')
        ->name('verification.verify');
    Route::post('email/resend', [AuthController::class, 'resendVerificationEmail'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

// Password reset routes (public with rate limiting)
Route::middleware('throttle:6,1')->group(function (): void {
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])
        ->name('password.email');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])
        ->name('password.reset');
});

// Redirect password reset link from email to frontend
Route::get('reset-password', function (\Illuminate\Http\Request $request) {
    $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
    $query = http_build_query($request->only('token', 'email'));
    return redirect("{$frontendUrl}/reset-password?{$query}");
})->name('password.reset.form');

// Email verification link from email (GET) — redirects to frontend with result
Route::get('email/verify/{id}/{hash}', function (string $id, string $hash, \Illuminate\Http\Request $request) {
    $frontendUrl = config('app.frontend_url', 'http://localhost:3000');

    $user = \App\Models\User::find($id);
    if (!$user) {
        return redirect("{$frontendUrl}/verify-email?verified=invalid");
    }

    if ($user->hasVerifiedEmail()) {
        return redirect("{$frontendUrl}/verify-email?verified=true");
    }

    if ($user->markEmailAsVerified()) {
        event(new \Illuminate\Auth\Events\Verified($user));
    }

    return redirect("{$frontendUrl}/verify-email?verified=true");
})->middleware('signed')->name('verification.verify.get');

// Refresh token (requires auth)
Route::middleware('auth:sanctum')->post('refresh', [AuthController::class, 'refresh'])->name('api.v1.refresh');


/*
    | Authentication
    */
Route::prefix('auth')->group(function () {
    // Social login (public)
    Route::get('/{provider}/redirect', [AuthController::class, 'socialRedirect'])->name('api.v1.social.redirect');
    Route::get('/{provider}/callback', [AuthController::class, 'socialCallback'])->name('api.v1.social.callback');
});
