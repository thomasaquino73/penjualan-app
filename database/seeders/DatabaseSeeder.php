<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            BasicCodeMasterSeeder::class,
            BasicCodeDetailSeeder::class,
            SistemSeeder::class,
            RoleSeeder::class,
            PermissionSeeder::class,
            RoleHasPermissionsSeeder::class,
            UserSeeder::class,
        ]);
    }
}
