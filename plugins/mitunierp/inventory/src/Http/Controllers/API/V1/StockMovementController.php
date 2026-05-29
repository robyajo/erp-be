<?php

declare(strict_types=1);

namespace Mitunierp\Inventory\Http\Controllers\API\V1;

use Mitunierp\Inventory\Models\Product;
use Mitunierp\Inventory\Models\StockMovement;
use Mitunierp\Inventory\Models\Warehouse;
use Mitunierp\Inventory\Services\StockService;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

final class StockMovementController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $movements = QueryBuilder::for(StockMovement::class)
            ->allowedIncludes(['product', 'warehouse', 'user'])
            ->allowedFilters([
                AllowedFilter::exact('product_id'),
                AllowedFilter::exact('warehouse_id'),
                AllowedFilter::exact('type'),
                AllowedFilter::exact('user_id'),
            ])
            ->allowedSorts(['created_at', 'type'])
            ->defaultSort('-created_at')
            ->paginate($request->input('per_page', 15));

        return $this->success($movements);
    }

    public function show(StockMovement $stockMovement): JsonResponse
    {
        $stockMovement->load(['product', 'warehouse', 'user']);

        return $this->success($stockMovement);
    }

    public function stockIn(Request $request, StockService $stockService): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:inventory_products,id'],
            'warehouse_id' => ['required', 'exists:inventory_warehouses,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
            'reference_type' => ['nullable', 'string', 'max:100'],
            'reference_id' => ['nullable', 'integer'],
        ]);

        $product = Product::query()->findOrFail($validated['product_id']);
        $warehouse = Warehouse::query()->findOrFail($validated['warehouse_id']);

        $movement = $stockService->addStock(
            $product,
            $warehouse,
            $validated['quantity'],
            $validated['notes'] ?? null,
            $request->user()?->id,
            $validated['reference_type'] ?? null,
            $validated['reference_id'] ?? null,
        );

        return $this->created($movement, 'Stock added successfully.');
    }

    public function stockOut(Request $request, StockService $stockService): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:inventory_products,id'],
            'warehouse_id' => ['required', 'exists:inventory_warehouses,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
            'reference_type' => ['nullable', 'string', 'max:100'],
            'reference_id' => ['nullable', 'integer'],
        ]);

        $product = Product::query()->findOrFail($validated['product_id']);
        $warehouse = Warehouse::query()->findOrFail($validated['warehouse_id']);

        try {
            $movement = $stockService->removeStock(
                $product,
                $warehouse,
                $validated['quantity'],
                $validated['notes'] ?? null,
                $request->user()?->id,
                $validated['reference_type'] ?? null,
                $validated['reference_id'] ?? null,
            );
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 409);
        }

        return $this->created($movement, 'Stock removed successfully.');
    }

    public function adjust(Request $request, StockService $stockService): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:inventory_products,id'],
            'warehouse_id' => ['required', 'exists:inventory_warehouses,id'],
            'quantity' => ['required', 'integer'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $product = Product::query()->findOrFail($validated['product_id']);
        $warehouse = Warehouse::query()->findOrFail($validated['warehouse_id']);

        $movement = $stockService->adjustStock(
            $product,
            $warehouse,
            $validated['quantity'],
            $validated['notes'] ?? null,
            $request->user()?->id,
        );

        return $this->created($movement, 'Stock adjusted successfully.');
    }

    public function transfer(Request $request, StockService $stockService): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:inventory_products,id'],
            'from_warehouse_id' => ['required', 'exists:inventory_warehouses,id'],
            'to_warehouse_id' => ['required', 'exists:inventory_warehouses,id', 'different:from_warehouse_id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $product = Product::query()->findOrFail($validated['product_id']);
        $fromWarehouse = Warehouse::query()->findOrFail($validated['from_warehouse_id']);
        $toWarehouse = Warehouse::query()->findOrFail($validated['to_warehouse_id']);

        try {
            $result = $stockService->transferStock(
                $product,
                $fromWarehouse,
                $toWarehouse,
                $validated['quantity'],
                $validated['notes'] ?? null,
                $request->user()?->id,
            );
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 409);
        }

        return $this->created($result, 'Stock transferred successfully.');
    }
}
