<?php

declare(strict_types=1);

namespace Mitunierp\Inventory\Services;

use Mitunierp\Inventory\Models\Product;
use Mitunierp\Inventory\Models\StockMovement;
use Mitunierp\Inventory\Models\Warehouse;
use Illuminate\Support\Facades\DB;

final class StockService
{
    public function addStock(
        Product $product,
        Warehouse $warehouse,
        int $quantity,
        ?string $notes = null,
        ?int $userId = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
    ): StockMovement {
        return DB::transaction(function () use ($product, $warehouse, $quantity, $notes, $userId, $referenceType, $referenceId): StockMovement {
            return StockMovement::query()->create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'type' => 'in',
                'quantity' => $quantity,
                'notes' => $notes,
                'user_id' => $userId,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
            ]);
        });
    }

    public function removeStock(
        Product $product,
        Warehouse $warehouse,
        int $quantity,
        ?string $notes = null,
        ?int $userId = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
    ): StockMovement {
        return DB::transaction(function () use ($product, $warehouse, $quantity, $notes, $userId, $referenceType, $referenceId): StockMovement {
            $currentStock = $product->currentStock($warehouse->id);

            if ($currentStock < $quantity) {
                throw new \RuntimeException('Insufficient stock. Available: ' . $currentStock . ', Requested: ' . $quantity);
            }

            return StockMovement::query()->create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'type' => 'out',
                'quantity' => $quantity,
                'notes' => $notes,
                'user_id' => $userId,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
            ]);
        });
    }

    public function adjustStock(
        Product $product,
        Warehouse $warehouse,
        int $quantity,
        ?string $notes = null,
        ?int $userId = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
    ): StockMovement {
        return DB::transaction(function () use ($product, $warehouse, $quantity, $notes, $userId, $referenceType, $referenceId): StockMovement {
            return StockMovement::query()->create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'type' => 'adjustment',
                'quantity' => $quantity,
                'notes' => $notes,
                'user_id' => $userId,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
            ]);
        });
    }

    public function transferStock(
        Product $product,
        Warehouse $fromWarehouse,
        Warehouse $toWarehouse,
        int $quantity,
        ?string $notes = null,
        ?int $userId = null,
    ): array {
        return DB::transaction(function () use ($product, $fromWarehouse, $toWarehouse, $quantity, $notes, $userId): array {
            $out = $this->removeStock($product, $fromWarehouse, $quantity, $notes, $userId, 'transfer', null);
            $in = $this->addStock($product, $toWarehouse, $quantity, $notes, $userId, 'transfer', $out->id);

            $out->update(['reference_id' => $in->id]);

            return ['out' => $out, 'in' => $in];
        });
    }
}
