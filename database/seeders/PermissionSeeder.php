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
            'role' => ['alias' => 'Role', 'group' => 'Access Management'],
            'user' => ['alias' => 'User', 'group' => 'Access Management'],
            'permission' => ['alias' => 'Permission', 'group' => 'Access Management'],

            'customer' => ['alias' => 'Customer', 'group' => 'Master Data'],
            'supplier' => ['alias' => 'Supplier', 'group' => 'Master Data'],
            'salesman' => ['alias' => 'Salesman', 'group' => 'Master Data'],

            'kendaraan' => ['alias' => 'Vehicle', 'group' => 'Master Data'],
            'warehouse' => ['alias' => 'Warehouse', 'group' => 'Master Data'],

            'barang' => ['alias' => 'Product', 'group' => 'Master Data'],
            'kategori_barang' => ['alias' => 'Category', 'group' => 'Master Data'],
            'satuan_barang' => ['alias' => 'Unit', 'group' => 'Master Data'],

            'mata_uang' => ['alias' => 'Currency', 'group' => 'General'],
            'company' => ['alias' => 'Company', 'group' => 'General'],
            'general' => ['alias' => 'General', 'group' => 'General'],

            'penawaran_pembelian' => ['alias' => 'Purchase Requisition', 'group' => 'Transaction'],
            'purchase_order' => ['alias' => 'Purchase Order', 'group' => 'Transaction'],
        ];

        $actions = ['browse', 'create', 'read', 'edit', 'delete', 'trash', 'restore'];

        $role = Roles::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web',
        ]);

        foreach ($modules as $module => $config) {
            foreach ($actions as $action) {

                $permission = Permissions::firstOrCreate([
                    'name' => $module.'-'.$action,
                    'module' => $module,
                    'alias' => $config['alias'],
                    'group_name' => $config['group'], // 🔥 ini penting
                    'guard_name' => 'web',
                ]);

                $role->givePermissionTo($permission);
            }
        }

        $this->command->info('Permissions + Role assigned successfully.');
    }
}
