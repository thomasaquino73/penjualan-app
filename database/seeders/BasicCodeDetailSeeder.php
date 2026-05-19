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
                'detail' => 'COD',
                'description' => '',
            ],
            [
                'id' => $i++,
                'master_id' => '4',
                'detail' => 'NET 30',
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
