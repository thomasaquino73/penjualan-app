<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleHasPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Reset cache Spatie
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Daftar semua permission yang dibutuhkan
        $permissions = [
            'user-browse',
            'user-create',
            'user-edit',
            'user-delete',
            'role-browse',
            'role-create',
            'role-edit',
            'role-delete',

        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 2. Buat role Admin
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);

        // 3. Assign semua permission ke Admin
        $adminRole->syncPermissions(Permission::all());

        // 4. Assign Admin ke user pertama
        $firstUser = User::first();
        if ($firstUser) {
            $firstUser->assignRole('Admin');
        }
    }
}
