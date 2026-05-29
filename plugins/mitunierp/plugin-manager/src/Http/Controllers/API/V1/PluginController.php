<?php

declare(strict_types=1);

namespace Mitunierp\PluginManager\Http\Controllers\API\V1;

use Mitunierp\PluginManager\Models\Plugin;
use Mitunierp\PluginManager\Package;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

final class PluginController extends ApiController
{
    private const AVAILABLE = [
        'inventory' => [
            'label' => 'Inventory',
            'description' => 'Manage products, warehouses, stock operations with state-machine workflow',
            'icon' => 'package',
        ],
        'blog' => [
            'label' => 'Blog',
            'description' => 'Create and manage blog posts, categories, and tags',
            'icon' => 'file-text',
        ],
        'contacts' => [
            'label' => 'Contacts',
            'description' => 'Manage partners, customers, companies, and contact information',
            'icon' => 'users',
        ],
    ];

    public function index(): JsonResponse
    {
        $plugins = Plugin::query()->orderBy('sort')->get();

        return $this->success($plugins);
    }

    public function available(): JsonResponse
    {
        $installed = Plugin::query()->get()->keyBy('name');

        $result = [];
        foreach (self::AVAILABLE as $name => $meta) {
            $result[] = [
                'name' => $name,
                'label' => $meta['label'],
                'description' => $meta['description'],
                'icon' => $meta['icon'],
                'latest_version' => '1.0.0',
                'license' => 'MIT',
                'author' => 'Mitunierp',
                'installed' => isset($installed[$name]) && $installed[$name]->is_installed,
            ];
        }

        return $this->success($result);
    }

    private function getPluginMigrations(string $name): array
    {
        $paths = [
            'inventory' => ['plugins/mitunierp/inventory/database/migrations'],
            'blog' => ['plugins/mitunierp/blog/database/migrations'],
            'contacts' => ['plugins/mitunierp/contacts/database/migrations'],
        ];

        return $paths[$name] ?? [];
    }

    private function getPluginSeeders(string $name): array
    {
        $seeders = [
            'inventory' => ['Mitunierp\\Inventory\\Database\\Seeders\\InventorySeeder'],
            'blog' => [],
            'contacts' => [],
        ];

        return $seeders[$name] ?? [];
    }

    public function install(Request $request): JsonResponse
    {
        $name = strtolower((string) $request->input('name'));

        if (empty($name)) {
            return $this->validationError(['name' => ['Plugin name is required.']]);
        }

        if (!isset(self::AVAILABLE[$name])) {
            return $this->error("Plugin '{$name}' is not available.", 404);
        }

        if (Package::isPluginInstalled($name)) {
            return $this->error("Plugin '{$name}' is already installed.", 400);
        }

        try {
            DB::beginTransaction();

            $migrationPaths = $this->getPluginMigrations($name);

            if (!empty($migrationPaths)) {
                foreach ($migrationPaths as $path) {
                    Artisan::call('migrate', ['--path' => $path, '--force' => true]);
                }
            }

            $seeders = $this->getPluginSeeders($name);
            foreach ($seeders as $seeder) {
                Artisan::call('db:seed', ['--class' => $seeder, '--force' => true]);
            }

            Plugin::query()->updateOrCreate(
                ['name' => $name],
                [
                    'author' => 'Mitunierp',
                    'summary' => self::AVAILABLE[$name]['description'],
                    'description' => self::AVAILABLE[$name]['description'],
                    'icon' => self::AVAILABLE[$name]['icon'],
                    'latest_version' => '1.0.0',
                    'license' => 'MIT',
                    'is_core' => false,
                    'is_installed' => true,
                    'is_active' => true,
                ]
            );

            DB::commit();

            Package::$plugins = [];

            return $this->success(['name' => $name], "Plugin '{$name}' installed successfully.");
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->error("Failed to install '{$name}': {$e->getMessage()}", 500);
        }
    }

    public function uninstall(Request $request): JsonResponse
    {
        $name = strtolower((string) $request->input('name'));

        if (empty($name)) {
            return $this->validationError(['name' => ['Plugin name is required.']]);
        }

        if (!Package::isPluginInstalled($name)) {
            return $this->error("Plugin '{$name}' is not installed.", 400);
        }

        $plugin = Package::getPackagePlugin($name);

        if ($plugin !== null) {
            $dependents = $plugin->dependents()->where('is_installed', true)->get();
            if ($dependents->isNotEmpty()) {
                $names = $dependents->pluck('name')->implode(', ');

                return $this->error("Cannot uninstall '{$name}'. Dependent plugins must be uninstalled first: {$names}", 409);
            }
        }

        Plugin::query()->where('name', $name)->update([
            'is_installed' => false,
            'is_active' => false,
        ]);

        return $this->success(['name' => $name], "Plugin '{$name}' uninstalled successfully.");
    }
}
