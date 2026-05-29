<?php

declare(strict_types=1);

namespace Mitunierp\PluginManager\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $name
 * @property string|null $author
 * @property string|null $summary
 * @property string|null $description
 * @property string|null $icon
 * @property string|null $latest_version
 * @property string|null $license
 * @property bool $is_core
 * @property bool $is_active
 * @property bool $is_installed
 * @property int $sort
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
#[Fillable([
    'name',
    'author',
    'summary',
    'description',
    'icon',
    'latest_version',
    'license',
    'is_core',
    'is_active',
    'is_installed',
    'sort',
])]
final class Plugin extends Model
{
    protected $table = 'plugins';

    /** @return BelongsToMany<Plugin, $this> */
    public function dependencies(): BelongsToMany
    {
        return $this->belongsToMany(
            Plugin::class,
            'plugin_dependencies',
            'plugin_id',
            'dependency_id',
        );
    }

    /** @return BelongsToMany<Plugin, $this> */
    public function dependents(): BelongsToMany
    {
        return $this->belongsToMany(
            Plugin::class,
            'plugin_dependencies',
            'dependency_id',
            'plugin_id',
        );
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_core' => 'boolean',
            'is_active' => 'boolean',
            'is_installed' => 'boolean',
            'sort' => 'integer',
        ];
    }
}
