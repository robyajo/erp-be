<?php

declare(strict_types=1);

namespace Mitunierp\Inventory\Services;

use Illuminate\Support\Facades\DB;
use Mitunierp\Inventory\Enums\MoveState;
use Mitunierp\Inventory\Enums\OperationState;
use Mitunierp\Inventory\Models\Location;
use Mitunierp\Inventory\Models\Move;
use Mitunierp\Inventory\Models\MoveLine;
use Mitunierp\Inventory\Models\Operation;
use Mitunierp\Inventory\Models\Product;
use Mitunierp\Inventory\Models\ProductQuantity;

final class InventoryManager
{
    public function confirmOperation(Operation $operation): Operation
    {
        DB::transaction(function () use ($operation): void {
            $operation->update(['state' => OperationState::Confirmed->value]);

            foreach ($operation->moves as $move) {
                $move->update(['state' => MoveState::Confirmed->value]);
            }

            $this->assignTransfers($operation);
        });

        return $operation->fresh();
    }

    public function assignTransfers(Operation $operation): Operation
    {
        DB::transaction(function () use ($operation): void {
            $allAssigned = true;

            foreach ($operation->moves as $move) {
                $productQty = ProductQuantity::query()
                    ->where('product_id', $move->product_id)
                    ->where('location_id', $move->source_location_id)
                    ->first();

                $available = $productQty ? $productQty->availableQty() : 0;
                $toReserve = min($move->requested_qty, $available);

                if ($toReserve > 0) {
                    $move->update([
                        'reserved_qty' => $toReserve,
                        'state' => MoveState::Assigned->value,
                    ]);

                    if ($productQty !== null) {
                        $productQty->increment('reserved_qty', $toReserve);
                    }
                }

                if ($toReserve < $move->requested_qty) {
                    $allAssigned = false;
                }
            }

            $operation->update([
                'state' => $allAssigned
                    ? OperationState::Assigned->value
                    : OperationState::Confirmed->value,
            ]);
        });

        return $operation->fresh();
    }

    public function validateOperation(Operation $operation): Operation
    {
        DB::transaction(function () use ($operation): void {
            foreach ($operation->moves as $move) {
                $this->validateMove($move);
            }

            $operation->update([
                'state' => OperationState::Done->value,
                'validated_at' => now(),
            ]);
        });

        return $operation->fresh();
    }

    public function validateMove(Move $move): Move
    {
        DB::transaction(function () use ($move): void {
            $qtyDone = $move->qtyDone();

            if ($qtyDone <= 0) {
                $qtyDone = $move->requested_qty;
            }

            $qtyToProcess = min($qtyDone, $move->requested_qty);

            $this->updateSourceQuantity($move, $qtyToProcess);
            $this->updateDestinationQuantity($move, $qtyToProcess);

            $move->update([
                'done_qty' => $qtyToProcess,
                'reserved_qty' => 0,
                'state' => MoveState::Done->value,
            ]);

            if ($qtyToProcess < $move->requested_qty) {
                $this->createBackOrder($move, $move->requested_qty - $qtyToProcess);
            }
        });

        return $move->fresh();
    }

    public function cancelOperation(Operation $operation): Operation
    {
        DB::transaction(function () use ($operation): void {
            foreach ($operation->moves as $move) {
                $this->cancelMove($move);
            }

            $operation->update(['state' => OperationState::Canceled->value]);
        });

        return $operation->fresh();
    }

    public function cancelMove(Move $move): void
    {
        if ($move->reserved_qty > 0) {
            ProductQuantity::query()
                ->where('product_id', $move->product_id)
                ->where('location_id', $move->source_location_id)
                ->decrement('reserved_qty', $move->reserved_qty);
        }

        $move->update([
            'reserved_qty' => 0,
            'state' => MoveState::Canceled->value,
        ]);
    }

    public function returnOperation(Operation $operation): Operation
    {
        return DB::transaction(function () use ($operation): Operation {
            $returnOp = Operation::query()->create([
                'name' => 'Return: ' . $operation->name,
                'reference' => 'RET/' . $operation->reference,
                'operation_type_id' => $operation->operation_type_id,
                'source_location_id' => $operation->destination_location_id,
                'destination_location_id' => $operation->source_location_id,
                'state' => OperationState::Draft->value,
            ]);

            foreach ($operation->moves as $move) {
                if ($move->done_qty > 0) {
                    $returnMove = Move::query()->create([
                        'operation_id' => $returnOp->id,
                        'product_id' => $move->product_id,
                        'source_location_id' => $move->destination_location_id,
                        'destination_location_id' => $move->source_location_id,
                        'requested_qty' => $move->done_qty,
                        'state' => MoveState::Draft->value,
                    ]);

                    MoveLine::query()->create([
                        'move_id' => $returnMove->id,
                        'product_id' => $move->product_id,
                        'location_id' => $move->destination_location_id,
                        'qty_done' => $move->done_qty,
                    ]);
                }
            }

            return $returnOp;
        });
    }

    private function updateSourceQuantity(Move $move, int $qty): void
    {
        $sourceQty = ProductQuantity::query()
            ->where('product_id', $move->product_id)
            ->where('location_id', $move->source_location_id)
            ->first();

        if ($sourceQty !== null) {
            $newQty = max(0, $sourceQty->quantity - $qty);
            $newReserved = max(0, $sourceQty->reserved_qty - min($qty, $sourceQty->reserved_qty));
            $sourceQty->update([
                'quantity' => $newQty,
                'reserved_qty' => $newReserved,
            ]);
        }
    }

    private function updateDestinationQuantity(Move $move, int $qty): void
    {
        $destQty = ProductQuantity::query()
            ->firstOrCreate(
                [
                    'product_id' => $move->product_id,
                    'location_id' => $move->destination_location_id,
                ],
                ['quantity' => 0, 'reserved_qty' => 0],
            );

        $destQty->increment('quantity', $qty);
    }

    private function createBackOrder(Move $originalMove, int $remainingQty): void
    {
        $backOrderOp = Operation::query()->create([
            'name' => 'Back Order: ' . ($originalMove->operation->name ?? ''),
            'reference' => $originalMove->operation->reference ?? null,
            'operation_type_id' => $originalMove->operation->operation_type_id,
            'source_location_id' => $originalMove->source_location_id,
            'destination_location_id' => $originalMove->destination_location_id,
            'state' => OperationState::Draft->value,
        ]);

        Move::query()->create([
            'operation_id' => $backOrderOp->id,
            'product_id' => $originalMove->product_id,
            'source_location_id' => $originalMove->source_location_id,
            'destination_location_id' => $originalMove->destination_location_id,
            'requested_qty' => $remainingQty,
            'state' => MoveState::Draft->value,
        ]);
    }
}
