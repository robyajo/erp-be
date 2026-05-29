<?php

declare(strict_types=1);

namespace Mitunierp\PluginManager;

use Spatie\LaravelPackageTools\Package as BasePackage;
use Spatie\LaravelPackageTools\PackageServiceProvider as BasePackageServiceProvider;

abstract class PackageServiceProvider extends BasePackageServiceProvider
{
    abstract public function configureCustomPackage(Package $package): void;

    public function configurePackage(BasePackage $package): void
    {
        $this->configureCustomPackage($package);
    }

    public function newPackage(): Package
    {
        return new Package;
    }

    public function boot(): void
    {
        $this->bootingPackage();

        $this
            ->bootPackageAssets()
            ->bootPackageBladeComponents()
            ->bootPackageCommands()
            ->bootPackageConfigs()
            ->bootPackageInertia()
            ->bootPackageTranslations()
            ->bootPackageViews()
            ->bootPackageViewComposers()
            ->bootPackageViewSharedData();

        $this->bootPackageConsoleCommands();

        if ($this->package->isCore || Package::isPluginInstalled($this->package->shortName())) {
            $this
                ->bootPackageMigrations()
                ->bootPackageRoutes()
                ->bootPackageServiceProviders();
        }

        $this->packageBooted();
    }

    protected function getMigrationPath(): string
    {
        $fullPath = $this->package->basePath('/../database/migrations');

        return str_replace('\\', '/', str_replace(base_path() . DIRECTORY_SEPARATOR, '', $fullPath));
    }
}
