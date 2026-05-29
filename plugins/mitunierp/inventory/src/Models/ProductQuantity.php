<?php

declare(strict_types=1);

namespace Mitunierp\Inventory\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $product_id
 * @property int $location_id
 * @property int $quantity
 * @property int $reserved_qty
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
#[Fillable([
    'product_id',
    'location_id',
    'quantity',
    'reserved_qty',
])]
final class ProductQuantity extends Model
{
    protected $table = 'inventory_product_quantities';

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

    public function availableQty(): int
    {
        return $this->quantity - $this->reserved_qty;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'reserved_qty' => 'integer',
        ];
    }
}
