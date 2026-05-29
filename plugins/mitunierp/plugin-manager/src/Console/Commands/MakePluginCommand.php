<?php

declare(strict_types=1);

namespace Mitunierp\PluginManager\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final class MakePluginCommand extends Command
{
    protected $signature = 'make:plugin {name : The plugin name (e.g., blog)}';
    protected $description = 'Create a new plugin structure';

    public function handle(): int
    {
        $name = strtolower(Str::snake($this->argument('name')));
        $studly = Str::studly($name);
        $basePath = base_path("plugins/mitunierp/{$name}");

        if (is_dir($basePath)) {
            $this->error("Plugin '{$name}' already exists at plugins/mitunierp/{$name}");

            return self::FAILURE;
        }

        // Create directory structure
        $dirs = [
            'src/Models',
            'src/Http/Controllers/API/V1',
            'database/migrations',
            'routes',
        ];

        foreach ($dirs as $dir) {
            File::ensureDirectoryExists("{$basePath}/{$dir}");
        }

        // composer.json
        $this->putFile(
            "{$basePath}/composer.json",
            <<<JSON
            {
                "name": "mitunierp/{$name}",
                "description": "{$studly} plugin",
                "type": "library",
                "require": {
                    "php": "^8.3",
                    "spatie/laravel-package-tools": "*"
                },
                "autoload": {
                    "psr-4": {
                        "Mitunierp\\\{{$studly}}\\": "src/"
                    }
                },
                "extra": {
                    "laravel": {
                        "providers": [
                            "Mitunierp\\\{{$studly}}\\{$studly}ServiceProvider"
                        ]
                    }
                }
            }
            JSON,
        );

        // ServiceProvider
        $this->putFile(
            "{$basePath}/src/{$studly}ServiceProvider.php",
            <<<PHP
            <?php

            declare(strict_types=1);

            namespace Mitunierp\\{$studly};

            use Mitunierp\PluginManager\Package;
            use Mitunierp\PluginManager\PackageServiceProvider;

            final class {$studly}ServiceProvider extends PackageServiceProvider
            {
                public function configureCustomPackage(Package \$package): void
                {
                    \$package
                        ->name('{$name}')
                        ->icon('puzzle')
                        ->description('{$studly} plugin')
                        ->latestVersion('1.0.0')
                        ->license('MIT')
                        ->hasMigrations([])
                        ->runsMigrations()
                        ->hasRoutes(['api'])
                        ->hasInstallCommand(function (\Spatie\LaravelPackageTools\Commands\InstallCommand \$cmd) use (\$package): void {
                            \$cmd->startWith(function (\Spatie\LaravelPackageTools\Commands\InstallCommand \$command) use (\$package): void {
                                \$relativePath = \$this->getMigrationPath();
                                \$command->call('migrate', ['--path' => \$relativePath]);
                                \$package->updateOrCreate();
                            });
                        });
                }
            }
            PHP,
        );

        // Routes
        $this->putFile(
            "{$basePath}/routes/api.php",
            <<<PHP
            <?php

            declare(strict_types=1);

            use Illuminate\Support\Facades\Route;

            Route::prefix('api/v1/{$name}')->middleware(['auth:sanctum', 'throttle:authenticated'])->group(function (): void {
                //
            });
            PHP,
        );

        $this->info("Plugin '{$name}' created successfully at plugins/mitunierp/{$name}");
        $this->warn("Next steps:");
        $this->line("  1. Register in bootstrap/providers.php:");
        $this->line("     use Mitunierp\\{$studly}\\{$studly}ServiceProvider;");
        $this->line("     {$studly}ServiceProvider::class,");
        $this->line("  2. Add to PluginController::AVAILABLE array");
        $this->line("  3. Add to PluginController::menus() method");
        $this->line("  4. Run: composer dump-autoload");
        $this->line("  5. Install: php artisan {$name}:install");

        return self::SUCCESS;
    }

    private function putFile(string $path, string $content): void
    {
        file_put_contents($path, $content);
        $this->line("Created: " . str_replace(base_path() . '/', '', $path));
    }
}
