<?php

declare(strict_types=1);

namespace Mitunierp\PluginManager\Database\Seeders;

use Illuminate\Database\Seeder;
use Mitunierp\PluginManager\Models\Plugin;

final class PluginManagerSeeder extends Seeder
{
    public function run(): void
    {
        Plugin::query()->firstOrCreate(
            ['name' => 'plugin-manager'],
            [
                'author' => 'Mitunierp',
                'summary' => 'Core plugin management system for the ERP',
                'version' => '1.0.0',
                'is_core' => true,
                'is_installed' => true,
                'is_active' => true,
                'sort' => 0,
            ]
        );
    }
}
