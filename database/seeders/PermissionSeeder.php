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
            'receive_item' => ['alias' => 'Receive Item', 'group' => 'Purchase'],
            'purchase_invoice' => ['alias' => 'Purchase Invoice', 'group' => 'Purchase'],
            'permintaan_pembelian' => ['alias' => 'Purchase Requisition', 'group' => 'Purchase'],
            'purchase_order' => [
                'alias' => 'Purchase Order',
                'group' => 'Purchase',
                'actions' => array_merge($defaultActions, ['approval']),
            ],
            'sales_order' => [
                'alias' => 'Sales Order',
                'group' => 'Sales',
                'actions' => array_merge($defaultActions, ['approval']),
            ],
            'item_transfer' => [
                'alias' => 'Item Transfer',
                'group' => 'Sales',
                'actions' => array_merge($defaultActions, ['approval']),
            ],
            'sales_quotation' => ['alias' => 'Sales Quotation', 'group' => 'Sales'],
            'customer' => ['alias' => 'Customer', 'group' => 'Sales'],
            'kategori_customer' => ['alias' => 'Customer Category', 'group' => 'Sales'],
            'delivery_order' => ['alias' => 'Delivery Order', 'group' => 'Sales'],
            'sales_invoice' => ['alias' => 'Sales Invoice', 'group' => 'Sales'],
            'proforma_invoice' => ['alias' => 'Proforma Invoice', 'group' => 'Sales'],
            'penjualan_toko' => ['alias' => 'Store Sales', 'group' => 'Sales'],

            'warehouse' => ['alias' => 'Warehouse', 'group' => 'Inventory'],
            'barang' => ['alias' => 'Product', 'group' => 'Inventory'],
            'kategori_barang' => ['alias' => 'Product Category', 'group' => 'Inventory'],
            'satuan_barang' => ['alias' => 'Unit', 'group' => 'Inventory'],
            'brand' => ['alias' => 'Brands', 'group' => 'Inventory'],

            'role' => ['alias' => 'Role', 'group' => 'Setting'],
            'user' => ['alias' => 'User', 'group' => 'Setting'],
            'permission' => ['alias' => 'Permission', 'group' => 'Setting'],
            'company' => ['alias' => 'Company', 'group' => 'Setting'],
            'general' => ['alias' => 'General', 'group' => 'Setting'],
            'shipping' => ['alias' => 'Shipping', 'group' => 'Setting'],
            'fob' => ['alias' => 'FOB', 'group' => 'Setting'],
            'syarat_pembayaran' => ['alias' => 'Payment Term', 'group' => 'Setting'],
            'archive_purchase_requisition' => [
                'alias' => 'Archive Purchase Requisition',
                'group' => 'Archive',
                'actions' => ['browse'],
            ],

            'archive_purchase_order' => [
                'alias' => 'Archive Purchase Order',
                'group' => 'Archive',
                'actions' => ['browse'],
            ],

            'archive_sales_quotation' => [
                'alias' => 'Archive Sales Quotation',
                'group' => 'Archive',
                'actions' => ['browse'],
            ],

            'archive_sales_order' => [
                'alias' => 'Archive Sales Order',
                'group' => 'Archive',
                'actions' => ['browse'],
            ],

            'archive_delivery_order' => [
                'alias' => 'Archive Delivery Order',
                'group' => 'Archive',
                'actions' => ['browse'],
            ],

            'archive_receive_item' => [
                'alias' => 'Archive Receive Item',
                'group' => 'Archive',
                'actions' => ['browse'],
            ],

            'archive_purchase_invoice' => [
                'alias' => 'Archive Purchase Invoice',
                'group' => 'Archive',
                'actions' => ['browse'],
            ],

            'archive_sales_invoice' => [
                'alias' => 'Archive Sales Invoice',
                'group' => 'Archive',
                'actions' => ['browse'],
            ],
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
