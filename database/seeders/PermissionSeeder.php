<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $permissions = [
            // manage role
            'get-role',
            'create-role',
            'show-role',
            'edit-role',
            'delete-role',
            // manage permission
            'get-permission',
            'create-permission',
            'show-permission',
            'edit-permission',
            'delete-permission',
            // manage user
            'get-user',
            'create-user',
            'show-user',
            'edit-user',
            'delete-user',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $this->command->info("PermissionSeeder: Berhasil membuat " . count(Permission::all()) . " permission.");
    }
}