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
                'unit_id' => 3,
                'product_type' => 'supply', // Sesuai dengan enum ('supply', 'non_supply')
                'quantity' => 100, // Opsional: Contoh tambahan data sesuai migration
                'price' => 25000,   // Opsional: Contoh tambahan data sesuai migration
                'created_by' => 1,
            ],
            [
                'id' => 2,
                'id_barang' => 'P-0002',
                'nama_barang' => 'Semen Padang',
                'kategori_id' => 1,
                'gudang_id' => 1,
                'tipe_persediaan_id' => 9,
                'unit_id' => 4,
                'product_type' => 'supply',
                'quantity' => 50,
                'price' => 65000,
                'created_by' => 1,
            ]
        ];

        // 2. Definisikan Data Konversi Satuan (Gunakan relasi id_barang sebagai key pencocokan)
        $conversions = [
            'P-0001' => [
                'from_unit_id' => 3, // Misal: Box
                'to_unit_id' => 1,   // Misal: Pcs
                'qty' => 12,         // 1 Box = 12 Pcs
            ],
            'P-0002' => [
                'from_unit_id' => 4, // Misal: Sak
                'to_unit_id' => 2,   // Misal: Kg
                'qty' => 40,         // 1 Sak = 40 Kg
            ]
        ];

        // 3. Proses Looping Input Data ke Database
        foreach ($products as $p) {
            
            // Insert atau ambil data barang jika sudah ada
            $barang = Barang::firstOrCreate(
                ['id_barang' => $p['id_barang']], // Unik berdasarkan id_barang agar aman
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

            // Cek apakah produk ini memiliki data konversi di array $conversions
            if (isset($conversions[$barang->id_barang])) {
                $convData = $conversions[$barang->id_barang];

                // Insert data konversi sesuai skema table data_barang_conversions
                DataBarangConversion::firstOrCreate(
                    [
                        'data_barang_id' => $barang->id, // Mengambil ID otomatis dari hasil insert/find diatas
                        'from_unit_id' => $convData['from_unit_id'],
                        'to_unit_id' => $convData['to_unit_id'],
                    ],
                    [
                        'qty' => $convData['qty']
                    ]
                );
            }
        }

        $this->command->info('Products and Conversions seeded successfully.');
    }
}