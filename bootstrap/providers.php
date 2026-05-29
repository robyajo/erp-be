<?php

declare(strict_types=1);

use Mitunierp\Blog\BlogServiceProvider;
use Mitunierp\Contacts\ContactsServiceProvider;
use Mitunierp\Inventory\InventoryServiceProvider;
use Mitunierp\PluginManager\PluginManagerServiceProvider;
use Spatie\Permission\PermissionServiceProvider;

return [
    App\Providers\AppServiceProvider::class,
    PermissionServiceProvider::class,
    PluginManagerServiceProvider::class,
    InventoryServiceProvider::class,
    BlogServiceProvider::class,
    ContactsServiceProvider::class,
];
