<?php

declare(strict_types=1);

namespace Mitunierp\Inventory\Http\Controllers\API\V1;

use Mitunierp\Inventory\Models\Warehouse;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

final class WarehouseController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $warehouses = QueryBuilder::for(Warehouse::class)
            ->allowedFilters(['name', 'code', 'city', 'is_active'])
            ->allowedSorts(['name', 'code', 'city', 'created_at'])
            ->defaultSort('name')
            ->paginate($request->input('per_page', 15));

        return $this->success($warehouses);
    }

    public function show(Warehouse $warehouse): JsonResponse
    {
        return $this->success($warehouse);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:inventory_warehouses,code'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $warehouse = Warehouse::query()->create($validated);

        return $this->created($warehouse, 'Warehouse created successfully.');
    }

    public function update(Request $request, Warehouse $warehouse): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['string', 'max:255'],
            'code' => ['string', 'max:50', 'unique:inventory_warehouses,code,' . $warehouse->id],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $warehouse->update($validated);

        return $this->success($warehouse, 'Warehouse updated successfully.');
    }

    public function destroy(Warehouse $warehouse): JsonResponse
    {
        if ($warehouse->stockMovements()->exists()) {
            return $this->error('Cannot delete warehouse with existing stock movements.', 409);
        }

        $warehouse->delete();

        return $this->noContent();
    }
}
