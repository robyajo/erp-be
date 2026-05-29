<?php

declare(strict_types=1);

namespace Mitunierp\Contacts\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'color'])]
final class Tag extends Model
{
    protected $table = 'contacts_tags';

    /** @return BelongsToMany<Partner, $this> */
    public function partners(): BelongsToMany
    {
        return $this->belongsToMany(Partner::class, 'contacts_partner_tag', 'tag_id', 'partner_id');
    }
}
