<?php

namespace App\View\Components;

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
                'label' => 'MASTER DATA',
                'roles' => ['Super Admin'],
            ],
              [
                'type' => 'single',
                'name' => 'Item Data',
                'route' => 'customer.index',
                'icon' => 'ti ti-box',
                'pattern' => 'customer.*',
                'active' => true,
                'roles' => ['Super Admin'],
                'permissions' => ['customer-browse'],
            ],
              [
                'type' => 'single',
                'name' => 'Customers',
                'route' => 'customer.index',
                'icon' => 'ti ti-users-group',
                'pattern' => 'customer.*',
                'active' => true,
                'roles' => ['Super Admin'],
                'roles' => ['Super Admin', 'Data Entri'],
                'permissions' => ['customer-browse'],
            ],
            [
                'type' => 'section',
                'label' => 'TRANSACTIONS',
                'roles' => ['Super Admin'],
            ],
            [
                'type' => 'section',
                'label' => 'SETTING',
                'roles' => ['Super Admin'],
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
                'roles' => ['SuperAdmin'],
                'permissions' => ['user-browse'],
            ],
               [
                'type' => 'single',
                'name' => 'Application System',
                'route' => 'pengaturan.sistem',
                'icon' => 'ti-database',
                'pattern' => 'pengaturan.*',
                'active' => true,
                'roles' => ['SuperAdmin'],
                'permissions' => ['user-browse'],
            ],

        ];
    }

    public function render()
    {
        return view('components.sidebar');
    }
}
