<?php

declare(strict_types=1);

namespace Mitunierp\Inventory;

use Mitunierp\PluginManager\Package;
use Mitunierp\PluginManager\PackageServiceProvider;

final class InventoryServiceProvider extends PackageServiceProvider
{
    public function configureCustomPackage(Package $package): void
    {
        $package
            ->name('inventory')
            ->icon('package')
            ->description('Manage products, warehouses, and stock operations with full state-machine workflow')
            ->latestVersion('1.0.0')
            ->license('MIT')
            ->hasMigrations([
                '2026_01_01_000001_create_inventory_categories_table',
                '2026_01_01_000002_create_inventory_products_table',
                '2026_01_01_000003_create_inventory_warehouses_table',
                '2026_01_01_000004_create_inventory_locations_table',
                '2026_01_01_000005_create_inventory_operation_types_table',
                '2026_01_01_000006_create_inventory_operations_table',
                '2026_01_01_000007_create_inventory_moves_table',
                '2026_01_01_000008_create_inventory_move_lines_table',
                '2026_01_01_000009_create_inventory_product_quantities_table',
            ])
            ->runsMigrations()
            ->hasRoutes(['api'])
            ->hasUninstallCommand()
            ->hasInstallCommand(function (\Spatie\LaravelPackageTools\Commands\InstallCommand $cmd) use ($package): void {
                $cmd->startWith(function (\Spatie\LaravelPackageTools\Commands\InstallCommand $command) use ($package): void {
                    $relativePath = $this->getMigrationPath();

                    foreach ($package->dependencies as $dep) {
                        $command->call("{$dep}:install");
                    }

                    $command->call('migrate', ['--path' => $relativePath]);
                    $command->call('db:seed', ['--class' => 'Mitunierp\\Inventory\\Database\\Seeders\\InventorySeeder']);
                    $package->updateOrCreate();
                });
            });
    }
}
