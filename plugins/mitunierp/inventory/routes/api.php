<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Mitunierp\Inventory\Http\Controllers\API\V1\CategoryController;
use Mitunierp\Inventory\Http\Controllers\API\V1\LocationController;
use Mitunierp\Inventory\Http\Controllers\API\V1\OperationController;
use Mitunierp\Inventory\Http\Controllers\API\V1\OperationTypeController;
use Mitunierp\Inventory\Http\Controllers\API\V1\ProductController;
use Mitunierp\Inventory\Http\Controllers\API\V1\StockMovementController;
use Mitunierp\Inventory\Http\Controllers\API\V1\WarehouseController;

Route::prefix('api/v1/inventory')->middleware(['auth:sanctum', 'throttle:authenticated'])->group(function (): void {
    // Core resources
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('warehouses', WarehouseController::class);

    // Locations (hierarchical warehouse locations)
    Route::apiResource('locations', LocationController::class);

    // Operation Types
    Route::apiResource('operation-types', OperationTypeController::class);

    // Operations with workflow actions
    Route::apiResource('operations', OperationController::class)->only(['index', 'show', 'store']);
    Route::post('operations/{operation}/confirm', [OperationController::class, 'confirm'])->name('api.v1.inventory.operations.confirm');
    Route::post('operations/{operation}/assign', [OperationController::class, 'assign'])->name('api.v1.inventory.operations.assign');
    Route::post('operations/{operation}/validate', [OperationController::class, 'validate'])->name('api.v1.inventory.operations.validate');
    Route::post('operations/{operation}/cancel', [OperationController::class, 'cancel'])->name('api.v1.inventory.operations.cancel');
    Route::post('operations/{operation}/return', [OperationController::class, 'return'])->name('api.v1.inventory.operations.return');

    // Legacy stock movements (for backward compatibility)
    Route::apiResource('stock-movements', StockMovementController::class)->only(['index', 'show']);
    Route::post('stock/in', [StockMovementController::class, 'stockIn'])->name('api.v1.inventory.stock.in');
    Route::post('stock/out', [StockMovementController::class, 'stockOut'])->name('api.v1.inventory.stock.out');
    Route::post('stock/adjust', [StockMovementController::class, 'adjust'])->name('api.v1.inventory.stock.adjust');
    Route::post('stock/transfer', [StockMovementController::class, 'transfer'])->name('api.v1.inventory.stock.transfer');
});
