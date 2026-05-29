<?php

declare(strict_types=1);

namespace Mitunierp\Contacts\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'description', 'is_active'])]
final class Industry extends Model
{
    protected $table = 'contacts_industries';

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
