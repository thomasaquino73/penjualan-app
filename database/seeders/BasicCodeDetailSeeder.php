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
                'detail' => 'IDR',
                'description' => 'Indonesia Rupiah',
            ],
            [
                'id' => $i++,
                'master_id' => '3',
                'detail' => 'USD',
                'description' => 'US Dollar',
            ],
            [
                'id' => $i++,
                'master_id' => '4',
                'detail' => 'Bahan Baku',
                'description' => '',
            ],
            [
                'id' => $i++,
                'master_id' => '4',
                'detail' => 'Bahan Baku Pembantu',
                'description' => '',
            ],
            [
                'id' => $i++,
                'master_id' => '4',
                'detail' => 'Barang Setengah Jadi',
                'description' => '',
            ],
            [
                'id' => $i++,
                'master_id' => '4',
                'detail' => 'Barang Jadi',
                'description' => '',
            ],
            [
                'id' => $i++,
                'master_id' => '4',
                'detail' => 'Barang Lain-lain',
                'description' => '',
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
