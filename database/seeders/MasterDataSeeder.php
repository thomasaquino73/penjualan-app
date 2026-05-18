<?php

namespace Database\Seeders;

use App\Models\Master_Data\Customer;
use App\Models\Master_Data\Kendaraan;
use App\Models\Master_Data\Salesman;
use App\Models\Master_Data\Supplier;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        Customer::create([
            'id_pelanggan' => 'C-0001',
            'nama' => 'Thomas',
            'alamat' => 'Tangerang',
            'alamat_pajak' => 'Tangerang',
            'kodepos' => '15321',
            'negara' => 'Indonesia',
            'telepon' => '081299097474',
            'personal_kontak' => 'Thomas',
            'email' => 'thomas.aquino73@gmail.com',
            'website' => 'www.thomasaquino.my.id',
            'created_by' => 1,

        ]);

        Supplier::create([
            'id_supplier' => 'C-0001',
            'nama' => 'Thomas Supplier',
            'alamat' => 'Tangerang',
            'alamat_pajak' => 'Tangerang',
            'kodepos' => '15321',
            'negara' => 'Indonesia',
            'telepon' => '081299097474',
            'personal_kontak' => 'Thomas',
            'email' => 'thomas.aquino73@gmail.com',
            'website' => 'www.thomasaquino.my.id',
            'created_by' => 1,
        ]);

        Salesman::create([
            'id_salesman' => 'C-0001',
            'nama' => 'Thomas',
            'alamat' => 'Tangerang',
            'kodepos' => '15321',
            'negara' => 'Indonesia',
            'telepon' => '081299097474',
            'jabatan' => 'Sales',
            'email' => 'thomas.aquino73@gmail.com',
            'created_by' => 1,
        ]);

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
