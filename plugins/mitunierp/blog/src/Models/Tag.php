<?php

declare(strict_types=1);

namespace Mitunierp\Blog\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'color', 'sort'])]
final class Tag extends Model
{
    protected $table = 'blog_tags';

    /** @return BelongsToMany<Post, $this> */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'blog_post_tag', 'tag_id', 'post_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['sort' => 'integer'];
    }
}
