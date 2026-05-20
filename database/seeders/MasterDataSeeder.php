<?php

namespace Database\Seeders;

use App\Models\Master_Data\Kendaraan;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        Kendaraan::create([
            'merk' => 'Daihatsu',
            'tipe' => 'Ayla',
            'plat_nomor' => 'B 1234 CD',
            'warna' => 'Hitam',
            'pemilik' => 'Thomas',
            'status' => 1,
            'created_by' => 1,
        ]);

    }
}
