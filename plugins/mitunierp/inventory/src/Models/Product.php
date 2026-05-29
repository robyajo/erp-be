<?php

declare(strict_types=1);

namespace Mitunierp\Inventory\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int|null $category_id
 * @property string $name
 * @property string $slug
 * @property string $sku
 * @property string|null $description
 * @property float $price
 * @property float|null $cost
 * @property string $unit
 * @property string|null $barcode
 * @property string|null $image
 * @property int $min_stock
 * @property int|null $max_stock
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
#[Fillable([
    'category_id',
    'name',
    'slug',
    'sku',
    'description',
    'price',
    'cost',
    'unit',
    'barcode',
    'image',
    'min_stock',
    'max_stock',
    'is_active',
])]
#[Hidden([])]
final class Product extends Model
{
    protected $table = 'inventory_products';

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /** @return HasMany<StockMovement, $this> */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'product_id');
    }

    public function currentStock(?int $warehouseId = null): int
    {
        $query = StockMovement::query()->where('product_id', $this->id);

        if ($warehouseId !== null) {
            $query->where('warehouse_id', $warehouseId);
        }

        $in = (clone $query)->where('type', 'in')->sum('quantity');
        $out = (clone $query)->where('type', 'out')->sum('quantity');
        $adjustment = (clone $query)->where('type', 'adjustment')->sum('quantity');

        return (int) ($in - $out + $adjustment);
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product): void {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'cost' => 'decimal:2',
            'min_stock' => 'integer',
            'max_stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
