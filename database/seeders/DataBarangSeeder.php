<?php

namespace Database\Seeders;

use App\Models\Inventory\Barang;
use App\Models\Inventory\DataBarangStok;
use Illuminate\Database\Seeder;

class DataBarangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $i = 1;
        $j = 1;
        Barang::insert([
            [
                'id' => $j++,
                'id_barang' => 'P-0001',
                'nama_barang' => 'Kran CLS-02 1/2" ONDA',
                'unit_id' => 4,
                'kategori_id' => 1,
                'product_type' => 'supply',
            ],
            [
                'id' => $j++,
                'id_barang' => 'P-0002',
                'nama_barang' => 'Ball Valve 1/4" Kuningan Onda',
                'unit_id' => 4,
                'kategori_id' => 1,
                'product_type' => 'supply',
            ],
        ]);
        DataBarangStok::insert([
            [
                'id' =>$i++,
                'data_barang_id' => 1,
                'date_stock' => '2026-01-01',
                'quantity' => 100,
                'stok_unit_id' => 4,
                'warehouse_id' => 1,
            ],
            [
                'id' =>$i++,
                'data_barang_id' => 2,
                'date_stock' => '2026-01-01',
                'quantity' => 100,
                'stok_unit_id' => 4,
                'warehouse_id' => 1,
            ],
        ]);
    }
}
