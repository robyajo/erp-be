<?php

declare(strict_types=1);

namespace Mitunierp\PluginManager;

final class PluginManagerServiceProvider extends PackageServiceProvider
{
    public function configureCustomPackage(Package $package): void
    {
        $package
            ->name('plugin-manager')
            ->isCore()
            ->icon('puzzle')
            ->description('Core plugin management system')
            ->latestVersion('1.0.0')
            ->license('MIT')
            ->hasMigrations([
                '2026_01_01_000001_create_plugins_table',
                '2026_06_29_000001_add_columns_to_plugins_table',
            ])
            ->runsMigrations()
            ->hasInstallCommand(function (\Spatie\LaravelPackageTools\Commands\InstallCommand $cmd) use ($package): void {
                $cmd->startWith(function (\Spatie\LaravelPackageTools\Commands\InstallCommand $command) use ($package): void {
                    $fullPath = $package->basePath('/../database/migrations');
                    $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $fullPath);
                    $relativePath = str_replace('\\', '/', $relativePath);
                    $command->call('migrate', ['--path' => $relativePath]);
                    $command->call('db:seed', ['--class' => 'Mitunierp\\PluginManager\\Database\\Seeders\\PluginManagerSeeder']);
                    $package->updateOrCreate();
                });
            });
    }
}
