<?php

declare(strict_types=1);

namespace Mitunierp\Inventory\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mitunierp\Inventory\Enums\MoveState;

/**
 * @property int $id
 * @property int $operation_id
 * @property int $product_id
 * @property int $source_location_id
 * @property int $destination_location_id
 * @property int $requested_qty
 * @property int $reserved_qty
 * @property int $done_qty
 * @property string $state
 * @property string|null $reference
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
#[Fillable([
    'operation_id',
    'product_id',
    'source_location_id',
    'destination_location_id',
    'requested_qty',
    'reserved_qty',
    'done_qty',
    'state',
    'reference',
])]
final class Move extends Model
{
    protected $table = 'inventory_moves';

    /** @return BelongsTo<Operation, $this> */
    public function operation(): BelongsTo
    {
        return $this->belongsTo(Operation::class, 'operation_id');
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /** @return BelongsTo<Location, $this> */
    public function sourceLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'source_location_id');
    }

    /** @return BelongsTo<Location, $this> */
    public function destinationLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'destination_location_id');
    }

    /** @return HasMany<MoveLine, $this> */
    public function moveLines(): HasMany
    {
        return $this->hasMany(MoveLine::class, 'move_id');
    }

    public function qtyDone(): int
    {
        return (int) $this->moveLines()->sum('qty_done');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'requested_qty' => 'integer',
            'reserved_qty' => 'integer',
            'done_qty' => 'integer',
            'state' => 'string',
        ];
    }
}
