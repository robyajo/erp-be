<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $rAdmin = User::create([
            'name' => 'Roby Karti S',
            'username' => 'admin',
            'email' => 'roby@mituni.id',
            'gender' => 'male',
            'password' => Hash::make('Password123'),
            'email_verified_at' => now(),
        ]);
        $rAdmin->assignRole('Super Admin');


        $eAdmin = User::create([
            'name' => 'Paja Admin',
            'username' => 'pajaadmin',
            'email' => 'pajaadmin@mituni.id',
            'password' => Hash::make('Password123'),
            'email_verified_at' => now(),
        ]);
        $eAdmin->assignRole('Admin');

        $sAdmin = User::create([
            'name' => 'Siti Aminah',
            'username' => 'sitiaminah',
            'email' => 'siti@mituni.id',
            'password' => Hash::make('Password123'),
            'email_verified_at' => now(),
        ]);
        $sAdmin->assignRole('User');

        $wAdmin = User::create([

            'name' => 'Willy Aja',
            'username' => 'willyaja',
            'email' => 'willy@mituni.id',
            'password' => Hash::make('Password123'),
            'email_verified_at' => now(),
        ]);
        $wAdmin->assignRole('User');

        Role::query()->update(['guard_name' => 'web']);
        Permission::query()->update(['guard_name' => 'web']);

        $this->command->info('UserSeeder: Berhasil membuat ' . count(User::all()) . ' user.');
    }
}
