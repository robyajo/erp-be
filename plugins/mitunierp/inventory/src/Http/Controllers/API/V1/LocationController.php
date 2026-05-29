<?php

declare(strict_types=1);

namespace Mitunierp\Inventory\Http\Controllers\API\V1;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mitunierp\Inventory\Models\Location;
use Spatie\QueryBuilder\QueryBuilder;

final class LocationController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $locations = QueryBuilder::for(Location::class)
            ->allowedFilters('name', 'warehouse_id', 'is_active')
            ->allowedSorts('name', 'created_at')
            ->defaultSort('name')
            ->paginate($request->input('per_page', 15));

        return $this->success($locations);
    }

    public function show(Location $location): JsonResponse
    {
        $location->load(['warehouse', 'parent', 'children']);

        return $this->success($location);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'barcode' => ['nullable', 'string', 'max:100', 'unique:inventory_locations,barcode'],
            'warehouse_id' => ['required', 'exists:inventory_warehouses,id'],
            'parent_id' => ['nullable', 'exists:inventory_locations,id'],
            'is_active' => ['boolean'],
        ]);

        if (isset($validated['parent_id'])) {
            $parent = Location::query()->findOrFail($validated['parent_id']);
            $validated['parent_path'] = ($parent->parent_path ? $parent->parent_path . '/' : '') . $parent->id;
        }

        $location = Location::query()->create($validated);

        return $this->created($location, 'Location created successfully.');
    }

    public function update(Request $request, Location $location): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['string', 'max:255'],
            'barcode' => ['nullable', 'string', 'max:100', 'unique:inventory_locations,barcode,' . $location->id],
            'warehouse_id' => ['exists:inventory_warehouses,id'],
            'parent_id' => ['nullable', 'exists:inventory_locations,id'],
            'is_active' => ['boolean'],
        ]);

        if (isset($validated['parent_id'])) {
            $parent = Location::query()->findOrFail($validated['parent_id']);
            $validated['parent_path'] = ($parent->parent_path ? $parent->parent_path . '/' : '') . $parent->id;
        }

        $location->update($validated);

        return $this->success($location, 'Location updated successfully.');
    }

    public function destroy(Location $location): JsonResponse
    {
        if ($location->children()->exists()) {
            return $this->error('Cannot delete location with child locations.', 409);
        }

        $location->delete();

        return $this->noContent();
    }
}
