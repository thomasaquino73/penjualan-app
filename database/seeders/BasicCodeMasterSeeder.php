<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BasicCodeMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('basic_code_master')->insert([[
            'id' => '1',
            'detail' => 'Kategori',
            'description' => 'Daftar kategori barang',
        ],
            [
                'id' => '2',
                'detail' => 'Satuan',
                'description' => 'Daftar satuan barang',
            ],
            [
                'id' => '3',
                'detail' => 'Tipe Supplier',
                'description' => 'Daftar tipe supplier',
            ],
            [
                'id' => '4',
                'detail' => 'Syarat Pembayaran',
                'description' => 'Daftar syarat pembayaran',
            ],
            [
                'id' => '5',
                'detail' => 'Data Bank',
                'description' => 'Daftar data bank',
            ],

        ]);

    }
}
