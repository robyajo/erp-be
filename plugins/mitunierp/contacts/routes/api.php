<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Mitunierp\Contacts\Http\Controllers\API\V1\IndustryController;
use Mitunierp\Contacts\Http\Controllers\API\V1\PartnerController;
use Mitunierp\Contacts\Http\Controllers\API\V1\TagController;
use Mitunierp\Contacts\Http\Controllers\API\V1\TitleController;

Route::prefix('api/v1/contacts')->middleware(['auth:sanctum', 'throttle:authenticated'])->group(function (): void {
    Route::apiResource('partners', PartnerController::class);
    Route::apiResource('industries', IndustryController::class)->except(['show']);
    Route::apiResource('tags', TagController::class)->except(['show']);
    Route::apiResource('titles', TitleController::class)->except(['show']);
});
