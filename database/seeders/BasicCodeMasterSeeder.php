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
        DB::table('basic_code_master')->insert([

            [
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
                'detail' => 'Mata Uang',
                'description' => 'Daftar mata uang',
            ],
            [
                'id' => '4',
                'detail' => 'Tipe Persediaan',
                'description' => 'Tipe persediaan untuk barang',
            ],
            [
                'id' => '5',
                'detail' => 'Term',
                'description' => 'Jenis Pembayaran',
            ],

        ]);

    }
}
