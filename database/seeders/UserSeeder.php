<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // buat role dulu
        // Role::firstOrCreate(['name' => 'Super Admin']);

        $usersData = [
            [
                'id' => 1,
                'fullname' => 'Thomas Aquino',
                'nickname' => 'Thomas',
                'username' => 'thomas',
                'email' => 'thomas.aquino73@gmail.com',
                'address' => 'Jl. Raya Bogor No. 123',
                'phone' => '081299097474',
                'password' => Hash::make('1'),
                'created_by' => 1,
                'role' => 'Super Admin',
                'role_group_id' => 1,
            ],
              [
                'id' => 2,
                'fullname' => 'Rinto Prabowo',
                'nickname' => 'Bowo',
                'username' => 'bowo',
                'email' => 'bowo@gmail.com',
                'address' => 'Jl. Raya Bogor No. 124',
                'phone' => '081299097474',
                'password' => Hash::make('1'),
                'created_by' => 1,
                'role' => 'Admin',
                'role_group_id' => 2,
            ],
        ];

        foreach ($usersData as $u) {
            $user = User::firstOrCreate(
                ['id' => $u['id']],
                [
                    'fullname' => $u['fullname'],
                    'nickname' => $u['nickname'],
                    'username' => $u['username'],
                    'email' => $u['email'],
                    'address' => $u['address'],
                    'phone' => $u['phone'],
                    'role_group_id' => $u['role_group_id'],
                    'password' => $u['password'],
                    'email_verified_at' => now(),
                    'created_by' => $u['created_by'],
                ]
            );

            $user->assignRole($u['role']);
        }
    }
}
