<?php

declare(strict_types=1);

namespace Mitunierp\PluginManager;

use Mitunierp\PluginManager\Models\Plugin;
use Spatie\LaravelPackageTools\Package as BasePackage;

final class Package extends BasePackage
{
    public static array $plugins = [];

    public bool $isCore = false;

    public bool $runsMigrations = false;

    public bool $runsSeeders = false;

    public array $dependencies = [];

    public array $seederClasses = [];

    public ?string $icon = null;

    public ?string $description = null;

    public ?string $latestVersion = null;

    public ?string $license = null;

    public bool $hasUninstallCommand = false;

    public function isCore(bool $v = true): static
    {
        $this->isCore = $v;

        return $this;
    }

    public function hasDependency(string $dep): static
    {
        $this->dependencies[] = $dep;

        return $this;
    }

    public function runsMigrations(bool $v = true): static
    {
        $this->runsMigrations = $v;

        return $this;
    }

    public function runsSeeders(bool $v = true): static
    {
        $this->runsSeeders = $v;

        return $this;
    }

    public function hasSeeder(string $seederClass): static
    {
        $this->seederClasses[] = $seederClass;

        return $this;
    }

    public function icon(string $name): static
    {
        $this->icon = $name;

        return $this;
    }

    public function description(string $text): static
    {
        $this->description = $text;

        return $this;
    }

    public function latestVersion(string $version): static
    {
        $this->latestVersion = $version;

        return $this;
    }

    public function license(string $license): static
    {
        $this->license = $license;

        return $this;
    }

    public function hasUninstallCommand(): static
    {
        $this->hasUninstallCommand = true;

        return $this;
    }

    public static function isPluginInstalled(string $name): bool
    {
        try {
            if (static::$plugins === []) {
                static::$plugins = Plugin::query()->get()->keyBy('name')->all();
            }

            return isset(static::$plugins[$name]) && static::$plugins[$name]->is_installed;
        } catch (\Exception) {
            return false;
        }
    }

    public static function getPackagePlugin(string $name): ?Plugin
    {
        try {
            if (static::$plugins === []) {
                static::$plugins = Plugin::query()->get()->keyBy('name')->all();
            }

            return static::$plugins[$name] ?? null;
        } catch (\Exception) {
            return null;
        }
    }

    public function updateOrCreate(): Plugin
    {
        return Plugin::query()->updateOrCreate(
            ['name' => $this->shortName()],
            [
                'author' => 'Mitunierp',
                'summary' => $this->description ?? $this->packageData['description'] ?? null,
                'description' => $this->description ?? null,
                'icon' => $this->icon,
                'latest_version' => $this->latestVersion ?? '1.0.0',
                'license' => $this->license ?? 'MIT',
                'is_core' => $this->isCore,
                'is_installed' => true,
                'is_active' => true,
            ]
        );
    }

    public function delete(): void
    {
        Plugin::query()->where('name', $this->shortName())->delete();
        static::$plugins = [];
    }
}
