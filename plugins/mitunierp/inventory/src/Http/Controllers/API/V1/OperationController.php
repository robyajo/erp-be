<?php

declare(strict_types=1);

namespace Mitunierp\Inventory\Http\Controllers\API\V1;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mitunierp\Inventory\Models\Operation;
use Mitunierp\Inventory\Services\InventoryManager;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

final class OperationController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $operations = QueryBuilder::for(Operation::class)
            ->allowedIncludes('operationType', 'moves', 'sourceLocation', 'destinationLocation')
            ->allowedFilters(
                AllowedFilter::exact('operation_type_id'),
                AllowedFilter::exact('state'),
                AllowedFilter::partial('name'),
                AllowedFilter::partial('reference'),
            )
            ->allowedSorts('name', 'state', 'created_at', 'scheduled_at')
            ->defaultSort('-created_at')
            ->paginate($request->input('per_page', 15));

        return $this->success($operations);
    }

    public function show(Operation $operation): JsonResponse
    {
        $operation->load(['operationType', 'moves.moveLines', 'sourceLocation', 'destinationLocation']);

        return $this->success($operation);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255', 'unique:inventory_operations,reference'],
            'operation_type_id' => ['required', 'exists:inventory_operation_types,id'],
            'source_location_id' => ['required', 'exists:inventory_locations,id'],
            'destination_location_id' => ['required', 'exists:inventory_locations,id'],
            'scheduled_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'moves' => ['nullable', 'array'],
            'moves.*.product_id' => ['required_with:moves', 'exists:inventory_products,id'],
            'moves.*.quantity' => ['required_with:moves', 'integer', 'min:1'],
        ]);

        $operation = Operation::query()->create([
            'name' => $validated['name'],
            'reference' => $validated['reference'] ?? null,
            'operation_type_id' => $validated['operation_type_id'],
            'source_location_id' => $validated['source_location_id'],
            'destination_location_id' => $validated['destination_location_id'],
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'state' => 'draft',
            'user_id' => $request->user()?->id,
        ]);

        foreach ($validated['moves'] ?? [] as $moveData) {
            $operation->moves()->create([
                'product_id' => $moveData['product_id'],
                'source_location_id' => $validated['source_location_id'],
                'destination_location_id' => $validated['destination_location_id'],
                'requested_qty' => $moveData['quantity'],
                'state' => 'draft',
            ]);
        }

        return $this->created($operation->load('moves'), 'Operation created successfully.');
    }

    public function confirm(Operation $operation, InventoryManager $manager): JsonResponse
    {
        if ($operation->state !== 'draft') {
            return $this->error('Only draft operations can be confirmed.', 400);
        }

        $operation = $manager->confirmOperation($operation);

        return $this->success($operation, 'Operation confirmed successfully.');
    }

    public function validate(Operation $operation, InventoryManager $manager): JsonResponse
    {
        if (!in_array($operation->state, ['confirmed', 'assigned'], true)) {
            return $this->error('Operation must be confirmed or assigned before validation.', 400);
        }

        $operation = $manager->validateOperation($operation);

        return $this->success($operation, 'Operation validated successfully.');
    }

    public function cancel(Operation $operation, InventoryManager $manager): JsonResponse
    {
        if (in_array($operation->state, ['done', 'canceled'], true)) {
            return $this->error('Operation cannot be canceled in its current state.', 400);
        }

        $operation = $manager->cancelOperation($operation);

        return $this->success($operation, 'Operation canceled successfully.');
    }

    public function return(Operation $operation, InventoryManager $manager): JsonResponse
    {
        if ($operation->state !== 'done') {
            return $this->error('Only completed operations can be returned.', 400);
        }

        $returnOp = $manager->returnOperation($operation);

        return $this->success($returnOp->load('moves'), 'Return operation created successfully.');
    }

    public function assign(Operation $operation, InventoryManager $manager): JsonResponse
    {
        if ($operation->state !== 'confirmed') {
            return $this->error('Operation must be confirmed before assigning.', 400);
        }

        $operation = $manager->assignTransfers($operation);

        return $this->success($operation, 'Operation assigned successfully.');
    }
}
