<?php

declare(strict_types=1);

namespace Mitunierp\Contacts\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'short_name'])]
final class Title extends Model
{
    protected $table = 'contacts_titles';
}
