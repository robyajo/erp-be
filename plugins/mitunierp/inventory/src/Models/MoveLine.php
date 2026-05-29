<?php

declare(strict_types=1);

namespace Mitunierp\Inventory\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $move_id
 * @property int|null $product_id
 * @property int $location_id
 * @property int|null $lot_id
 * @property int|null $package_id
 * @property int $qty_done
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
#[Fillable([
    'move_id',
    'product_id',
    'location_id',
    'lot_id',
    'package_id',
    'qty_done',
])]
final class MoveLine extends Model
{
    protected $table = 'inventory_move_lines';

    /** @return BelongsTo<Move, $this> */
    public function move(): BelongsTo
    {
        return $this->belongsTo(Move::class, 'move_id');
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /** @return BelongsTo<Location, $this> */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'qty_done' => 'integer',
        ];
    }
}
