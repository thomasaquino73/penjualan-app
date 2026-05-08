<?php

namespace Database\Seeders;

use App\Models\Permissions;
use App\Models\Roles;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $modules = ['role', 'user', 'permission','customer'];

        $actions = ['browse', 'create', 'read', 'edit', 'delete','trash','restore'];

        $role = Roles::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web',
        ]);

        foreach ($modules as $module) {
            foreach ($actions as $action) {

                $permission = Permissions::firstOrCreate([
                    'name' => $module.'-'.$action,
                    'module' => $module,
                    'guard_name' => 'web',
                ]);

                // 🔥 INI YANG PENTING
                $role->givePermissionTo($permission);
            }
        }

        $this->command->info('Permissions + Role assigned successfully.');
    }
}
