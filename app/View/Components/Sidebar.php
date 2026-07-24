<?php

namespace App\View\Components;

use App\Helpers\MenuBadge;
use Illuminate\View\Component;

class Sidebar extends Component
{
    public $links;

    public function __construct()
    {
        $this->links = [
            [
                'type' => 'single',
                'name' => 'Dashboard',
                'route' => 'dashboard',
                'icon' => 'ti-home',
                'active' => true,
                'roles' => ['Super Admin'],

            ],
            [
                'type' => 'section',
                'label' => 'PEMBELIAN',
                'roles' => ['Super Admin'],
            ],
            [
                'type' => 'dropdown',
                'name' => 'Supplier',
                'icon' => 'ti ti-user',
                'roles' => ['Super Admin'],
                'permissions' => ['supplier-browse', 'kategori_supplier-browse'],
                'children' => [

                    [
                        'name' => 'Supplier List',
                        'route' => 'supplier.index',
                        'pattern' => 'supplier.*',
                        'roles' => ['Super Admin'],
                        'permissions' => ['supplier-browse'],
                    ],

                    [
                        'name' => 'Supplier Category',
                        'route' => 'kategori-supplier.index',
                        'pattern' => 'kategori-supplier.*',
                        'roles' => ['Super Admin'],
                        'permissions' => ['kategori_supplier-browse'],
                    ],
                ],
            ],
            [
                'type' => 'dropdown',
                'name' => 'TRANSAKSI',
                'icon' => 'ti ti-shopping-cart',
                'roles' => ['Super Admin'],
                'permissions' => [
                    'permintaan_pembelian-browse',
                    'purchase_order-browse',
                    'receive_item-browse',
                    'purchase_invoice-browse',
                ],
                'children' => [
                    [
                        'name' => 'Purchase Requisition',
                        'route' => 'permintaan-pembelian.index',
                        'pattern' => 'permintaan-pembelian.*',
                        'roles' => ['Super Admin'],
                        'permissions' => ['permintaan_pembelian-browse'],
                        'badge' => fn () => MenuBadge::count('purchase_requisition', 'date'),
                        'badge_color' => 'danger',
                    ],
                    [
                        'name' => 'Purchase Order',
                        'route' => 'purchase-order.index',
                        'pattern' => 'purchase-order.*',
                        'roles' => ['Super Admin'],
                        'permissions' => ['purchase_order-browse'],
                        'badge' => fn () => MenuBadge::count('purchase_order', 'datePO'),
                        'badge_color' => 'danger',
                    ],

                    [
                        'name' => 'Receive Item',
                        'route' => 'receive-item.index',
                        'pattern' => 'receive-item.*',
                        'roles' => ['Super Admin'],
                        'permissions' => ['receive_item-browse'],

                    ],

                    // [
                    //     'name' => 'Purchase Payment',
                    //     'route' => 'customer.index',
                    //     'pattern' => 'customer.*',
                    //     'roles' => ['Super Admin'],
                    //     'permissions' => ['barang-browse'],
                    // ],
                    // [
                    //     'name' => 'Purchase Return',
                    //     'route' => 'customer.index',
                    //     'pattern' => 'customer.*',
                    //     'roles' => ['Super Admin'],
                    //     'permissions' => ['barang-browse'],
                    // ],
                ],
            ],
            // [
            //     'type' => 'dropdown',
            //     'name' => 'PEMBAYARAN',
            //     'icon' => 'ti ti-file-invoice',
            //     'roles' => ['Super Admin'],
            //     'permissions' => [
            //         'purchase_invoice-browse',
            //     ],
            //     'children' => [
            //         [
            //             'name' => 'Purchase Invoice',
            //             'route' => 'purchase-invoice.index',
            //             'pattern' => 'purchase-invoice.*',
            //             'roles' => ['Super Admin'],
            //             'permissions' => ['purchase_invoice-browse'],
            //         ],
            //         [
            //             'name' => 'Purchase Payment',
            //             'route' => 'purchase-invoice.index',
            //             'pattern' => 'purchase-invoice.*',
            //             'roles' => ['Super Admin'],
            //             'permissions' => ['purchase_invoice-browse'],
            //         ],

            //     ],
            // ],
            [
                'type' => 'section',
                'label' => 'PENJUALAN',
                'roles' => ['Super Admin'],
            ],
            [
                'type' => 'dropdown',
                'name' => 'Konsumen',
                'icon' => 'ti ti-users-group',
                'roles' => ['Super Admin'],
                'permissions' => ['customer-browse', 'kategori_customer-browse'],
                'children' => [

                    [
                        'name' => 'Daftar Konsumen',
                        'route' => 'customer.index',
                        'pattern' => 'customer.*',
                        'roles' => ['Super Admin'],
                        'permissions' => ['customer-browse'],
                    ],

                    [
                        'name' => 'Kategori Konsumen',
                        'route' => 'kategori-customer.index',
                        'pattern' => 'kategori-customer.*',
                        'roles' => ['Super Admin'],
                        'permissions' => ['kategori_customer-browse'],
                    ],
                ],
            ],
            [
                'type' => 'dropdown',
                'name' => 'TRANSAKSI',
                'icon' => 'ti ti-moneybag',
                'roles' => ['Super Admin'],
                'permissions' => ['sales_quotation-browse', 'sales_order-browse'],
                'children' => [
                    [
                        'name' => 'Penawaran Harga',
                        'route' => 'sales-quotation.index',
                        'pattern' => 'sales-quotation.*',
                        'roles' => ['Super Admin'],
                        'permissions' => ['sales_quotation-browse'],
                        'badge' => fn () => MenuBadge::count('sales_quotation', 'sales_quotation_date'),
                        'badge_color' => 'danger',
                    ],
                    [
                        'name' => 'Penjualan',
                        'route' => 'sales-order.index',
                        'pattern' => 'sales-order.*',
                        'roles' => ['Super Admin'],
                        'permissions' => ['sales_order-browse'],
                        'badge' => fn () => MenuBadge::count('sales_order', 'sales_order_date'),
                        'badge_color' => 'danger',

                    ],

                    // [
                    //     'name' => 'Sales Return',
                    //     'route' => 'customer.index',
                    //     'pattern' => 'customer.*',
                    //     'roles' => ['Super Admin'],
                    //     'permissions' => ['barang-browse'],
                    // ],
                ],
            ],
            [
                'type' => 'dropdown',
                'name' => 'INVOICE',
                'icon' => 'ti ti-file-invoice',
                'roles' => ['Super Admin'],
                'permissions' => [
                    'permintaan_pembelian-browse',
                    'purchase_order-browse',
                    'receive_item-browse',
                    'purchase_invoice-browse',
                ],
                'children' => [
                    [
                        'name' => 'Proforma Invoice',
                        'route' => 'proforma-invoice.index',
                        'pattern' => 'proforma-invoice.*',
                        'roles' => ['Super Admin'],
                        'permissions' => ['proforma_invoice-browse'],
                        'badge' => fn () => MenuBadge::count('proforma_invoice', 'proforma_invoice_date'),
                        'badge_color' => 'danger',
                    ],
                    [
                        'name' => 'Sales Down Payment',
                        'route' => 'sales-down-payment.index',
                        'pattern' => 'sales-down-payment.*',
                        'roles' => ['Super Admin'],
                        'permissions' => ['sales_down_payment-browse'],

                    ],
                    [
                        'name' => 'Sales Invoice',
                        'route' => 'sales-invoice.index',
                        'pattern' => 'sales-invoice.*',
                        'roles' => ['Super Admin'],
                        'permissions' => ['sales_invoice-browse'],
                        'badge' => fn () => MenuBadge::count('sales_invoice', 'sales_invoice_date'),
                        'badge_color' => 'danger',
                    ],

                ],
            ],
            [
                'type' => 'single',
                'name' => 'Delivery Order',
                'route' => 'delivery-order.index',
                'icon' => 'ti ti-truck-delivery',
                'pattern' => 'delivery-order.*',
                'active' => true,
                'roles' => ['Super Admin'],
                'permissions' => ['delivery_order-browse'],
            ],
            [
                'type' => 'single',
                'name' => 'Store Sales',
                'route' => 'penjualan-toko.index',
                'icon' => 'ti ti-calculator',
                'pattern' => 'penjualan-toko.*',
                'active' => true,
                'roles' => ['Super Admin'],
                'permissions' => ['penjualan_toko-browse'],
            ],
            [
                'type' => 'section',
                'label' => 'INVENTORY',
                'roles' => ['Super Admin'],
            ],
            [
                'type' => 'dropdown',
                'name' => 'Barang',
                'icon' => 'ti ti-trolley',
                'roles' => ['Super Admin'],
                'permissions' => ['barang-browse', 'kategori_barang-browse', 'satuan_barang-browse'],
                'children' => [

                    [
                        'name' => 'Daftar Barang',
                        'route' => 'data-barang.index',
                        'pattern' => 'data-barang.*',
                        'roles' => ['Super Admin'],
                        'permissions' => ['barang-browse'],
                    ],

                    [
                        'name' => 'Kategori Barang',
                        'route' => 'kategori-barang.index',
                        'pattern' => 'kategori-barang.*',
                        'roles' => ['Super Admin'],
                        'permissions' => ['kategori_barang-browse'],
                    ],
                    [
                        'name' => 'Satuan Barang',
                        'route' => 'satuan-barang.index',
                        'pattern' => 'satuan-barang.*',
                        'roles' => ['Super Admin'],
                        'permissions' => ['satuan_barang-browse'],
                    ],
                    // [
                    //     'name' => 'Brands',
                    //     'route' => 'brand.index',
                    //     'pattern' => 'brand.*',
                    //     'roles' => ['Super Admin'],
                    //     'permissions' => ['brand-browse'],
                    // ],

                ],
            ],
            [
                'type' => 'single',
                'name' => 'Gudang',
                'route' => 'warehouse.index',
                'icon' => 'ti ti-building-warehouse',
                'pattern' => 'warehouse.*',
                'active' => true,
                'roles' => ['Super Admin'],
                'permissions' => ['warehouse-browse'],
            ],
            [
                'type' => 'single',
                'name' => 'Transfer Barang',
                'route' => 'item-transfer.index',
                'icon' => 'ti ti-forklift',
                'pattern' => 'item-transfer.*',
                'active' => true,
                'roles' => ['Super Admin'],
                'permissions' => ['item_transfer-browse'],
            ],
            [
                'type' => 'section',
                'label' => 'LAPORAN',
                'roles' => ['Super Admin'],
            ],
            [
                'type' => 'section',
                'label' => 'ARSIP',
                'roles' => ['Super Admin'],
                'permissions' => [
                    'archive_purchase_requisition-browse',
                    'archive_purchase_order-browse',
                    'archive_sales_quotation-browse',
                    'archive_sales_order-browse',
                ],
            ],
            [
                'type' => 'dropdown',
                'name' => 'PEMBELIAN',
                'icon' => 'ti ti-file-invoice',
                'roles' => ['Super Admin'],
                'permissions' => [
                    'archive_purchase_requisition-browse',
                    'archive_purchase_order-browse',
                ],
                'children' => [
                    [
                        'name' => 'Arsip Permintaan Pembelian',
                        'route' => 'archive.purchase-requisition',
                        'pattern' => 'archive.purchase-requisition*',
                        'roles' => ['Super Admin'],
                        'permissions' => ['archive_purchase_requisition-browse'],
                    ],
                    [
                        'name' => 'Arsip Pembelian',
                        'route' => 'archive.purchase-order',
                        'pattern' => 'archive.purchase-order*',
                        'roles' => ['Super Admin'],
                        'permissions' => ['archive_purchase_order-browse'],
                    ],
                    // [
                    //     'name' => 'Arsip Purchase Invoice',
                    //     'route' => 'sales-invoice.index',
                    //     'pattern' => 'sales-invoice.*',
                    //     'roles' => ['Super Admin'],
                    //     'permissions' => ['archive_purchase_invoice-browse'],
                    // ],

                ],
            ],
            [
                'type' => 'dropdown',
                'name' => 'PENJUALAN',
                'icon' => 'ti ti-file-invoice',
                'roles' => ['Super Admin'],
                'permissions' => [
                    'archive_sales_quotation-browse',
                    'archive_sales_order-browse',
                ],
                'children' => [
                    [
                        'name' => 'Arsip Penawaran Harga',
                        'route' => 'archive.sales-quotation',
                        'pattern' => 'archive.sales-quotation*',
                        'roles' => ['Super Admin'],
                        'permissions' => ['archive_sales_quotation-browse'],
                    ],
                    [
                        'name' => 'Arsip Penjualan',
                        'route' => 'archive.sales-order',
                        'pattern' => 'archive.sales-order*',
                        'roles' => ['Super Admin'],
                        'permissions' => ['archive_sales_order-browse'],
                    ],
                    // [
                    //     'name' => 'Arsip Proforma Invoice',
                    //     'route' => 'sales-invoice.index',
                    //     'pattern' => 'sales-invoice.*',
                    //     'roles' => ['Super Admin'],
                    //     'permissions' => ['sales_invoice-browse'],
                    // ],
                    // [
                    //     'name' => 'Arsip Sales Invoice',
                    //     'route' => 'sales-invoice.index',
                    //     'pattern' => 'sales-invoice.*',
                    //     'roles' => ['Super Admin'],
                    //     'permissions' => ['sales_invoice-browse'],
                    // ],

                ],
            ],
            [
                'type' => 'section',
                'label' => 'PENGATURAN',
                'roles' => ['Super Admin'],
            ],
            [
                'type' => 'dropdown',
                'name' => 'Preference',
                'icon' => 'ti ti-settings',
                'roles' => ['Super Admin'],
                'permissions' => ['fob-browse', 'shipping-browse'],
                'children' => [

                    [
                        'name' => 'Company Information',
                        'route' => 'company.info',
                        'pattern' => 'company.*',
                        'roles' => ['Super Admin'],
                        'permissions' => ['company-browse'],
                    ],
                    [
                        'name' => 'Tax',
                        'route' => 'customer.index',
                        'pattern' => 'customer.*',
                        'roles' => ['Super Admin'],
                        'permissions' => ['customer-browse'],
                    ],

                    [
                        'name' => 'Payment Term',
                        'route' => 'syarat-pembayaran.index',
                        'pattern' => 'syarat-pembayaran.*',
                        'roles' => ['Super Admin'],
                        'permissions' => ['syarat_pembayaran-browse'],
                    ],
                    [
                        'name' => 'Shipping',
                        'route' => 'shipping.index',
                        'pattern' => 'shipping.*',
                        'roles' => ['Super Admin'],
                        'permissions' => ['shipping-browse'],
                    ],
                    [
                        'name' => 'FOB',
                        'route' => 'fob.index',
                        'pattern' => 'fob.*',
                        'roles' => ['Super Admin'],
                        'permissions' => ['fob-browse'],
                    ],
                    [
                        'name' => 'General Setting',
                        'route' => 'general-setting.index',
                        'pattern' => 'general-setting.*',
                        'roles' => ['Super Admin'],
                        'permissions' => ['general-browse'],
                    ],
                ],
            ],
            [
                'type' => 'dropdown',
                'name' => 'Manage Access',
                'icon' => 'ti-shield-cog',
                'roles' => ['Super Admin'],
                'permissions' => ['role-browse', 'permission-browse'],
                'children' => [

                    [
                        'name' => 'Roles',
                        'route' => 'roles.index',
                        'pattern' => 'roles.*',
                        'roles' => ['Super Admin'],
                        'permissions' => ['role-browse'],
                    ],

                    [
                        'name' => 'Permissions',
                        'route' => 'permissions.index',
                        'pattern' => 'permissions.*',
                        'roles' => ['Super Admin'],
                        'permissions' => ['permission-browse'],
                    ],

                ],
            ],
            [
                'type' => 'single',
                'name' => 'Users',
                'route' => 'user.index',
                'icon' => 'ti-user-cog',
                'pattern' => 'user.*',
                'active' => true,
                'roles' => ['Super Admin'],
                'permissions' => ['user-browse'],
            ],
            [
                'type' => 'single',
                'name' => 'Application System',
                'route' => 'pengaturan.sistem',
                'icon' => 'ti-database',
                'pattern' => 'pengaturan.*',
                'active' => true,
                'roles' => ['Super Admin'],
                'permissions' => [''],
            ],

        ];
    }

    public function render()
    {
        return view('components.sidebar');
    }
}
