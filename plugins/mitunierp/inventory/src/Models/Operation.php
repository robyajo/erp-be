<?php

declare(strict_types=1);

namespace Mitunierp\Inventory\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mitunierp\Inventory\Enums\OperationState;

/**
 * @property int $id
 * @property string $name
 * @property string|null $reference
 * @property int $operation_type_id
 * @property int $source_location_id
 * @property int $destination_location_id
 * @property int|null $user_id
 * @property string $state
 * @property \Illuminate\Support\Carbon|null $scheduled_at
 * @property \Illuminate\Support\Carbon|null $validated_at
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
#[Fillable([
    'name',
    'reference',
    'operation_type_id',
    'source_location_id',
    'destination_location_id',
    'user_id',
    'state',
    'scheduled_at',
    'validated_at',
    'notes',
])]
final class Operation extends Model
{
    protected $table = 'inventory_operations';

    /** @return BelongsTo<OperationType, $this> */
    public function operationType(): BelongsTo
    {
        return $this->belongsTo(OperationType::class, 'operation_type_id');
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

    /** @return HasMany<Move, $this> */
    public function moves(): HasMany
    {
        return $this->hasMany(Move::class, 'operation_id');
    }

    public function isEditable(): bool
    {
        return in_array($this->state, [OperationState::Draft->value, OperationState::Confirmed->value], true);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'state' => 'string',
            'scheduled_at' => 'datetime',
            'validated_at' => 'datetime',
        ];
    }
}
