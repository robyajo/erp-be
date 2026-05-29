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

    public function menus(): JsonResponse
    {
        $menus = [
            'inventory' => [
                'headerNav' => [
                    ['label' => 'Operations', 'href' => '/inventory/operations'],
                    ['label' => 'Products', 'href' => '/inventory/products'],
                    ['label' => 'Configurations', 'href' => '/inventory/warehouses'],
                    ['label' => 'Settings', 'href' => '/settings'],
                ],
                'sidebar' => [
                    'operations' => [
                        ['label' => 'Transfers', 'items' => [
                            ['title' => 'Receipts', 'url' => '/inventory/operations', 'icon' => 'inbox'],
                            ['title' => 'Deliveries', 'url' => '/inventory/operations', 'icon' => 'truck'],
                        ]],
                        ['label' => 'Adjustments', 'items' => [
                            ['title' => 'Quantities', 'url' => '/inventory/operations', 'icon' => 'clipboard-list'],
                            ['title' => 'Scraps', 'url' => '/inventory/operations', 'icon' => 'trash-2'],
                        ]],
                    ],
                    'products' => [
                        ['label' => 'Products', 'items' => [
                            ['title' => 'Products', 'url' => '/inventory/products', 'icon' => 'package'],
                            ['title' => 'Categories', 'url' => '/inventory/categories', 'icon' => 'tags'],
                        ]],
                    ],
                    'configurations' => [
                        ['label' => 'Warehouse Management', 'items' => [
                            ['title' => 'Warehouses', 'url' => '/inventory/warehouses', 'icon' => 'warehouse'],
                            ['title' => 'Operation Types', 'url' => '/inventory/operations', 'icon' => 'settings'],
                        ]],
                        ['label' => 'Products', 'items' => [
                            ['title' => 'Categories', 'url' => '/inventory/categories', 'icon' => 'tags'],
                            ['title' => 'Attributes', 'url' => '/inventory/products', 'icon' => 'package'],
                        ]],
                    ],
                ],
            ],
            'blog' => [
                'headerNav' => [
                    ['label' => 'Posts', 'href' => '/blog/posts'],
                ],
                'sidebar' => [
                    'posts' => [
                        ['label' => 'Content', 'items' => [
                            ['title' => 'Posts', 'url' => '/blog/posts', 'icon' => 'file-text'],
                            ['title' => 'Categories', 'url' => '/blog/categories', 'icon' => 'tags'],
                        ]],
                    ],
                ],
            ],
            'contacts' => [
                'headerNav' => [
                    ['label' => 'All Contacts', 'href' => '/contacts'],
                    ['label' => 'Industries', 'href' => '/contacts/industries'],
                ],
                'sidebar' => [
                    'contacts' => [
                        ['label' => 'Contacts', 'items' => [
                            ['title' => 'All Contacts', 'url' => '/contacts', 'icon' => 'users'],
                            ['title' => 'Industries', 'url' => '/contacts/industries', 'icon' => 'building-2'],
                        ]],
                    ],
                ],
            ],
            'settings' => [
                'headerNav' => [
                    ['label' => 'Settings', 'href' => '/settings'],
                    ['label' => 'Roles', 'href' => '/settings/roles'],
                    ['label' => 'Companies', 'href' => '/settings/companies'],
                    ['label' => 'Teams', 'href' => '/settings/teams'],
                    ['label' => 'Users', 'href' => '/settings/users'],
                    ['label' => 'Custom Fields', 'href' => '/settings/custom-fields'],

                ],
                'sidebar' => [
                    'roles' => [
                        ['label' => 'Access Control', 'items' => [
                            ['title' => 'Roles', 'url' => '/settings/roles', 'icon' => 'shield'],
                            ['title' => 'Permissions', 'url' => '/settings/roles', 'icon' => 'lock'],
                        ]],
                    ],
                    'companies' => [
                        ['label' => 'Organization', 'items' => [
                            ['title' => 'Companies', 'url' => '/settings/companies', 'icon' => 'building-2'],
                            ['title' => 'Teams', 'url' => '/settings/teams', 'icon' => 'users'],
                        ]],
                    ],
                    'users' => [
                        ['label' => 'User Management', 'items' => [
                            ['title' => 'Users', 'url' => '/settings/users', 'icon' => 'user-circle'],
                            ['title' => 'Invitations', 'url' => '/settings/users', 'icon' => 'mail'],
                        ]],
                    ],
                    'custom-fields' => [
                        ['label' => 'Custom Fields', 'items' => [
                            ['title' => 'Fields', 'url' => '/settings/custom-fields', 'icon' => 'database'],
                        ]],
                    ],
                    'settings' => [
                        ['label' => 'General', 'items' => [
                            ['title' => 'Settings', 'url' => '/settings', 'icon' => 'settings'],
                        ]],
                    ],
                ],
            ],
        ];

        return $this->success($menus);
    }

    public function index(): JsonResponse
    {
        $plugins = Plugin::query()->orderBy('sort')->get();

        return $this->success($plugins);
    }

    public function available(Request $request): JsonResponse
    {
        $userPlugins = DB::table('user_plugin')
            ->where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->pluck('plugin_name')
            ->all();

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
                'installed' => in_array($name, $userPlugins, true),
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

        $userId = $request->user()->id;
        $already = DB::table('user_plugin')
            ->where('user_id', $userId)
            ->where('plugin_name', $name)
            ->where('is_active', true)
            ->exists();

        if ($already) {
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
                    'icon' => self::AVAILABLE[$name]['icon'],
                    'latest_version' => '1.0.0',
                    'license' => 'MIT',
                    'is_installed' => true,
                    'is_active' => true,
                ]
            );

            DB::table('user_plugin')->updateOrInsert(
                ['user_id' => $userId, 'plugin_name' => $name],
                ['is_active' => true, 'installed_at' => now()],
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

        $userId = $request->user()->id;
        $existing = DB::table('user_plugin')
            ->where('user_id', $userId)
            ->where('plugin_name', $name)
            ->where('is_active', true)
            ->exists();

        if (!$existing) {
            return $this->error("Plugin '{$name}' is not installed.", 400);
        }

        DB::table('user_plugin')
            ->where('user_id', $userId)
            ->where('plugin_name', $name)
            ->update(['is_active' => false]);

        return $this->success(['name' => $name], "Plugin '{$name}' uninstalled successfully.");
    }
}
