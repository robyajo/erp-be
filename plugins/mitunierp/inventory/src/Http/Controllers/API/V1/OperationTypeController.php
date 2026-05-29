<?php

declare(strict_types=1);

namespace Mitunierp\Inventory\Http\Controllers\API\V1;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mitunierp\Inventory\Models\OperationType;
use Spatie\QueryBuilder\QueryBuilder;

final class OperationTypeController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $operationTypes = QueryBuilder::for(OperationType::class)
            ->allowedFilters(['name', 'code', 'type', 'is_active'])
            ->allowedSorts(['name', 'type', 'created_at'])
            ->defaultSort('name')
            ->paginate($request->input('per_page', 15));

        return $this->success($operationTypes);
    }

    public function show(OperationType $operationType): JsonResponse
    {
        $operationType->load(['sourceLocation', 'destinationLocation']);

        return $this->success($operationType);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:inventory_operation_types,code'],
            'type' => ['required', 'string', 'in:receipt,delivery,internal_transfer'],
            'source_location_id' => ['nullable', 'exists:inventory_locations,id'],
            'destination_location_id' => ['nullable', 'exists:inventory_locations,id'],
            'is_active' => ['boolean'],
        ]);

        $operationType = OperationType::query()->create($validated);

        return $this->created($operationType, 'Operation type created successfully.');
    }

    public function update(Request $request, OperationType $operationType): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['string', 'max:255'],
            'code' => ['string', 'max:50', 'unique:inventory_operation_types,code,' . $operationType->id],
            'type' => ['string', 'in:receipt,delivery,internal_transfer'],
            'source_location_id' => ['nullable', 'exists:inventory_locations,id'],
            'destination_location_id' => ['nullable', 'exists:inventory_locations,id'],
            'is_active' => ['boolean'],
        ]);

        $operationType->update($validated);

        return $this->success($operationType, 'Operation type updated successfully.');
    }

    public function destroy(OperationType $operationType): JsonResponse
    {
        if ($operationType->operations()->exists()) {
            return $this->error('Cannot delete operation type with existing operations.', 409);
        }

        $operationType->delete();

        return $this->noContent();
    }
}
