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
                'detail' => 'Bahan Baku',
                'description' => '',
            ],
            [
                'id' => $i++,
                'master_id' => '1',
                'detail' => 'Bahan Baku Pembantu',
                'description' => '',
            ],
            [
                'id' => $i++,
                'master_id' => '1',
                'detail' => 'Barang Setengah Jadi',
                'description' => '',
            ],
            [
                'id' => $i++,
                'master_id' => '1',
                'detail' => 'Barang Jadi',
                'description' => '',
            ],
            [
                'id' => $i++,
                'master_id' => '1',
                'detail' => 'Barang Lain-lain',
                'description' => '',
            ],

            [
                'id' => $i++,
                'master_id' => '2',
                'detail' => 'Material Utama',
                'description' => '',
            ],
            [
                'id' => $i++,
                'master_id' => '2',
                'detail' => 'Material Pendamping',
                'description' => '',
            ],

            [
                'id' => $i++,
                'master_id' => '3',
                'detail' => 'PCS',
                'description' => '',
            ],
            [
                'id' => $i++,
                'master_id' => '3',
                'detail' => 'Unit',
                'description' => '',
            ],
            [
                'id' => $i++,
                'master_id' => '3',
                'detail' => 'Pack',
                'description' => '',
            ],

            [
                'id' => $i++,
                'master_id' => '3',
                'detail' => 'KG',
                'description' => '',
            ],

        ]);
    }
}
