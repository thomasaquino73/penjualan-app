<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BasicCodeDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $i = 1;
        DB::table('basic_code_detail')->insert([
            [
                'id' => $i++,
                'master_id' => '1',
                'detail' => 'Material Utama',
                'description' => '',
            ],
            [
                'id' => $i++,
                'master_id' => '1',
                'detail' => 'Material Pendamping',
                'description' => '',
            ],
            [
                'id' => $i++,
                'master_id' => '2',
                'detail' => 'PCS',
                'description' => '',
            ],
            [
                'id' => $i++,
                'master_id' => '2',
                'detail' => 'Unit',
                'description' => '',
            ],
            [
                'id' => $i++,
                'master_id' => '2',
                'detail' => 'Pack',
                'description' => '',
            ],
            [
                'id' => $i++,
                'master_id' => '2',
                'detail' => 'KG',
                'description' => '',
            ],
            [
                'id' => $i++,
                'master_id' => '3',
                'detail' => 'Umum',
                'description' => '',
            ],
            [
                'id' => $i++,
                'master_id' => '4',
                'detail' => 'Set Manual',
                'description' => 'Set syarat pembayaran manual',
            ],
            [
                'id' => $i++,
                'master_id' => '4',
                'detail' => 'NET 15',
                'description' => 'Jatuh tempo 15 hari',
            ],
            [
                'id' => $i++,
                'master_id' => '4',
                'detail' => 'NET 30',
                'description' => 'Jatuh tempo 30 hari',
            ],
            [
                'id' => $i++,
                'master_id' => '4',
                'detail' => 'NET 45',
                'description' => 'Jatuh tempo 45 hari',
            ],
            [
                'id' => $i++,
                'master_id' => '5',
                'detail' => 'Bank Central Asia',
                'description' => '014',
            ],
            [
                'id' => $i++,
                'master_id' => '5',
                'detail' => 'Bank Mandiri',
                'description' => '008',
            ],
            [
                'id' => $i++,
                'master_id' => '7',
                'detail' => 'Destination',
                'description' => '',
            ],
            [
                'id' => $i++,
                'master_id' => '7',
                'detail' => 'Shipping Point',
                'description' => '008',
            ],

        ]);

        DB::table('warehouse')->insert([
            [
                'id_gudang' => 'WH-001',
                'nama_gudang' => 'Gudang Utama',
                'alamat' => 'Jl. Merdeka No. 1',
            ],
            [
                'id_gudang' => 'WH-002',
                'nama_gudang' => 'Gudang Cadangan',
                'alamat' => 'Jl. Kemerdekaan No. 2',
            ],
        ]);
    }
}
