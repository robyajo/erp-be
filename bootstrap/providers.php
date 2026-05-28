<?php

declare(strict_types=1);

use Spatie\Permission\PermissionServiceProvider;

return [
    App\Providers\AppServiceProvider::class,
    PermissionServiceProvider::class,
];
