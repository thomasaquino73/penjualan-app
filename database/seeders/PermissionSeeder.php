<?php

namespace Database\Seeders;

use App\Models\Permissions;
use App\Models\Roles;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tentukan default actions untuk modul biasa
        $defaultActions = ['browse', 'create', 'read', 'edit', 'delete', 'trash', 'restore'];

        $modules = [
            'supplier' => ['alias' => 'Supplier', 'group' => 'Purchase'],
            'kategori_supplier' => ['alias' => 'Supplier Category', 'group' => 'Purchase'],
            'permintaan_pembelian' => [
                'alias' => 'Purchase Requisition',
                'group' => 'Purchase',
                'actions' => array_merge($defaultActions, ['approval']), // default + approval
            ],
            'purchase_order' => [
                'alias' => 'Purchase Order',
                'group' => 'Purchase',
                'actions' => array_merge($defaultActions, ['approval']), // default + approval
            ],
            'customer' => ['alias' => 'Customer', 'group' => 'Sales'],
            'kategori_customer' => ['alias' => 'Customer Category', 'group' => 'Sales'],

            'warehouse' => ['alias' => 'Warehouse', 'group' => 'Inventory'],
            'barang' => ['alias' => 'Product', 'group' => 'Inventory'],
            'kategori_barang' => ['alias' => 'Product Category', 'group' => 'Inventory'],
            'satuan_barang' => ['alias' => 'Unit', 'group' => 'Inventory'],

            'role' => ['alias' => 'Role', 'group' => 'Setting'],
            'user' => ['alias' => 'User', 'group' => 'Setting'],
            'permission' => ['alias' => 'Permission', 'group' => 'Setting'],
            'company' => ['alias' => 'Company', 'group' => 'Setting'],
            'general' => ['alias' => 'General', 'group' => 'Setting'],
            'shipping' => ['alias' => 'Shipping', 'group' => 'Setting'],
            'fob' => ['alias' => 'FOB', 'group' => 'Setting'],
            'syarat_pembayaran' => ['alias' => 'Payment Term', 'group' => 'Setting'],

            // 🔥 Modul transaksi ditambah action 'approval' khusus
            
            
        ];

        $role = Roles::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web',
        ]);

        foreach ($modules as $module => $config) {
            // 2. Cek apakah ada custom actions, jika tidak gunakan default
            $actions = $config['actions'] ?? $defaultActions;

            foreach ($actions as $action) {
                $permission = Permissions::firstOrCreate([
                    'name' => $module.'-'.$action,
                    'module' => $module,
                    'alias' => $config['alias'],
                    'group_name' => $config['group'],
                    'guard_name' => 'web',
                ]);

                $role->givePermissionTo($permission);
            }
        }

        $this->command->info('Permissions + Role assigned successfully.');
    }
}
