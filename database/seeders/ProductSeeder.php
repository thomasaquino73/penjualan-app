<?php

namespace Database\Seeders;

use App\Models\Master_Data\Barang;
use App\Models\Master_Data\DataBarangConversion;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Definisikan Data Master Barang
        $products = [
            [
                'id' => 1,
                'id_barang' => 'P-0001',
                'nama_barang' => 'Pralon',
                'kategori_id' => 1,
                'gudang_id' => 1,
                'tipe_persediaan_id' => 9,
                'unit_id' => 3, // Misal unit_id dasar: Pcs
                'product_type' => 'supply',
                'quantity' => 100,
                'price' => 25000,
                'created_by' => 1,
            ],
            [
                'id' => 2,
                'id_barang' => 'P-0002',
                'nama_barang' => 'Semen Padang',
                'kategori_id' => 1,
                'gudang_id' => 1,
                'tipe_persediaan_id' => 9,
                'unit_id' => 4, // Misal unit_id dasar: Kg
                'product_type' => 'supply',
                'quantity' => 50,
                'price' => 65000,
                'created_by' => 1,
            ]
        ];

        // 2. Ubah struktur menjadi array dari list konversi (Agar tidak overwrite)
        $conversions = [
            'P-0001' => [
                [
                    'from_unit_id' => 3, // Box
                    'to_unit_id' => 4,   // Pcs
                    'qty' => 12,         // 1 Box = 12 Pcs
                ],
                [
                    'from_unit_id' => 3, // Pack
                    'to_unit_id' => 5,   // Pcs
                    'qty' => 6,          // 1 Pack = 6 Pcs
                ],
            ],
            'P-0002' => [
                [
                    'from_unit_id' => 4, // Sak
                    'to_unit_id' => 3,   // Kg
                    'qty' => 40,         // 1 Sak = 40 Kg
                ],
                [
                    'from_unit_id' => 4, // Pallet
                    'to_unit_id' => 5,   // Sak
                    'qty' => 50,         // 1 Pallet = 50 Sak
                ]
            ]
        ];

        // 3. Proses Looping Input Data ke Database
        foreach ($products as $p) {
            
            // Insert atau ambil data barang jika sudah ada
            $barang = Barang::firstOrCreate(
                ['id_barang' => $p['id_barang']], 
                [
                    'id' => $p['id'],
                    'nama_barang' => $p['nama_barang'],
                    'kategori_id' => $p['kategori_id'],
                    'gudang_id' => $p['gudang_id'],
                    'tipe_persediaan_id' => $p['tipe_persediaan_id'],
                    'unit_id' => $p['unit_id'],
                    'product_type' => $p['product_type'],
                    'quantity' => $p['quantity'] ?? null,
                    'price' => $p['price'] ?? null,
                    'status' => 1,
                    'created_by' => $p['created_by'],
                ]
            );

            // 4. Lakukan looping jika terdapat daftar konversi untuk barang ini
            if (isset($conversions[$barang->id_barang])) {
                
                foreach ($conversions[$barang->id_barang] as $convData) {
                    
                    // Insert masing-masing list data konversi ke tabel database
                    DataBarangConversion::firstOrCreate(
                        [
                            'data_barang_id' => $barang->id, 
                            'from_unit_id' => $convData['from_unit_id'],
                            'to_unit_id' => $convData['to_unit_id'],
                        ],
                        [
                            'qty' => $convData['qty']
                        ]
                    );
                }
            }
        }

        $this->command->info('Products and Multiple Conversions seeded successfully.');
    }
}