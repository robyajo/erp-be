<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Mitunierp\Blog\Http\Controllers\API\V1\CategoryController;
use Mitunierp\Blog\Http\Controllers\API\V1\PostController;
use Mitunierp\Blog\Http\Controllers\API\V1\TagController;

Route::prefix('api/v1/blog')->middleware(['auth:sanctum', 'throttle:authenticated'])->group(function (): void {
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('posts', PostController::class);
    Route::apiResource('tags', TagController::class);
});
