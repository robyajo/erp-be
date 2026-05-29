<?php

declare(strict_types=1);

namespace Mitunierp\Contacts;

use Mitunierp\PluginManager\Package;
use Mitunierp\PluginManager\PackageServiceProvider;

final class ContactsServiceProvider extends PackageServiceProvider
{
    public function configureCustomPackage(Package $package): void
    {
        $package
            ->name('contacts')
            ->icon('users')
            ->description('Manage partners, customers, companies, and contact information')
            ->latestVersion('1.0.0')
            ->license('MIT')
            ->hasMigrations([
                '2026_06_01_000001_create_contacts_industries_table',
                '2026_06_01_000002_create_contacts_titles_table',
                '2026_06_01_000003_create_contacts_partners_table',
                '2026_06_01_000004_create_contacts_tags_table',
                '2026_06_01_000005_create_contacts_partner_tag_table',
            ])
            ->runsMigrations()
            ->hasRoutes(['api'])
            ->hasUninstallCommand()
            ->hasInstallCommand(function (\Spatie\LaravelPackageTools\Commands\InstallCommand $cmd) use ($package): void {
                $cmd->startWith(function (\Spatie\LaravelPackageTools\Commands\InstallCommand $command) use ($package): void {
                    $relativePath = $this->getMigrationPath();
                    $command->call('migrate', ['--path' => $relativePath]);
                    $package->updateOrCreate();
                });
            });
    }
}
