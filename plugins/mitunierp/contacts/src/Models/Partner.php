<?php

declare(strict_types=1);

namespace Mitunierp\Contacts\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'account_type', 'name', 'email', 'phone', 'mobile', 'job_title',
    'website', 'tax_id', 'reference', 'street1', 'street2', 'city', 'zip',
    'title_id', 'industry_id', 'parent_id', 'company_id',
    'is_active', 'notes',
])]
final class Partner extends Model
{
    protected $table = 'contacts_partners';

    /** @return BelongsTo<Title, $this> */
    public function title(): BelongsTo
    {
        return $this->belongsTo(Title::class, 'title_id');
    }

    /** @return BelongsTo<Industry, $this> */
    public function industry(): BelongsTo
    {
        return $this->belongsTo(Industry::class, 'industry_id');
    }

    /** @return BelongsTo<Partner, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** @return BelongsTo<Partner, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(self::class, 'company_id');
    }

    /** @return HasMany<Partner, $this> */
    public function contacts(): HasMany
    {
        return $this->hasMany(self::class, 'company_id');
    }

    /** @return BelongsToMany<Tag, $this> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'contacts_partner_tag', 'partner_id', 'tag_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
