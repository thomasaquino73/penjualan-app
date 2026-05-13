<?php

namespace Database\Seeders;

use App\Models\Permissions;
use App\Models\Roles;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            'role' => 'role',
            'user' => 'user',
            'permission' => 'permission',
            'customer' => 'customer',
            'supplier' => 'supplier',
            'salesman' => 'salesman',
            'kendaraan' => 'vehicle',
            'warehouse' => 'warehouse',
            'barang' => 'product',
            'kategori_barang' => 'category',
            'satuan_barang' => 'unit',
            'application_system' => 'application system',
            'login_background' => 'login background',
            'mata_uang' => 'currency',
            'company' => 'company',
            'general' => 'general',
        ];

        $actions = ['browse', 'create', 'read', 'edit', 'delete', 'trash', 'restore'];

        $role = Roles::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web',
        ]);

        foreach ($modules as $module => $alias) {
            foreach ($actions as $action) {

                $permission = Permissions::firstOrCreate([
                    'name' => $module.'-'.$action,
                    'module' => $module,
                    'alias' => $alias,
                    'guard_name' => 'web',
                ]);

                $role->givePermissionTo($permission);
            }
        }

        $this->command->info('Permissions + Role assigned successfully.');
    }
}
