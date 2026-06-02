<?php

namespace Database\Seeders;

use App\Models\Purchase\Supplier;
use App\Models\Setting\Company;
use App\Models\Setting\Currency;
use App\Models\Setting\SyaratPembayaran;
use Illuminate\Database\Seeder;

class CompanyInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Company::create([
            'nama_perusahaan' => 'PT Almex Bintang Timur',
            'alamat' => 'Green Lake City Ruko Food City RKFC-005 Petir Cipondoh',
            'kodepos' => '16424',
            'nomor_telepon' => '081382397429',
            'negara' => 'Indonesia',
            'website' => 'https://www.almexbintangtimur.com',
            'email' => 'info@almexbintangtimur.com',
            'logo' => 'image/logo/69fd6d6ab719c1778216298.png',
            'favicon' => 'image/logo/69fd6d6ab719c1778216298.png',
        ]);

        Currency::insert([
            [
                'code' => 'IDR',
                'name' => 'Rupiah',
                'symbol' => 'Rp',
                'country' => 'Indonesia',
            ],
            [
                'code' => 'USD',
                'name' => 'US Dollar',
                'symbol' => 'USD',
                'country' => 'United States',
            ],
            [
                'code' => 'SGD',
                'name' => 'Singapore Dollar',
                'symbol' => 'SGD',
                'country' => 'Singapore',
            ],
        ]);

        Supplier::create([
            'id_supplier' => 'S-00001',
            'nama_supplier' => 'PT Almex Bintang Timur',
            'kategori_supplier_id' => 1,
            'email' => 'info@almexbintangtimur.com',
            'phone_1' => '081382397429',
            'phone_2' => '081382397429',
            'no_whatsapp' => '081382397429',
            'faximili' => null,
            'website' => 'https://www.almexbintangtimur.com',
            'alamat_pembayaran' => 'Green Lake City Ruko Food City RKFC-005 Petir Cipondoh',
            'kota' => 'Tangerang',
            'kodepos' => '16424',
            'provinsi' => 'Banten',
            'negara' => 'Indonesia',
            'tipe_pemasok_id' => null,
            'syarat_pembelian' => 'Net 30',
            'default_diskon' => null,
            'default_deskripsi' => null,
            'status' => 1,
            'created_by' => 1,
            'updated_by' => null,
        ]);

        SyaratPembayaran::insert([
            [
                'nama' => 'COD',
                'total_diskon' => 0,
                'total_hari' => 0,
                'masa_jatuh_tempo' => 0,
                'keterangan' => '',
                'edited' => 1,
                'created_by' => 1,
                'created_at' => now(),
            ],
            [
                'nama' => 'Cicilan',
                'total_diskon' => 0,
                'total_hari' => 0,
                'masa_jatuh_tempo' => 0,
                'keterangan' => '',
                'edited' => 1,
                'created_by' => 1,
                'created_at' => now(),
            ],
            [
                'nama' => 'Set Manual',
                'total_diskon' => 0,
                'total_hari' => 0,
                'masa_jatuh_tempo' => 0,
                'keterangan' => '',
                'edited' => 1,
                'created_by' => 1,
                'created_at' => now(),
            ],
            [
                'nama' => 'Net 15',
                'total_diskon' => 0,
                'total_hari' => 0,
                'masa_jatuh_tempo' => 15,
                'keterangan' => '',
                'edited' => 1,
                'created_by' => 1,
                'created_at' => now(),
            ],  [
                'nama' => 'Net 30',
                'total_diskon' => 0,
                'total_hari' => 0,
                'masa_jatuh_tempo' => 30,
                'keterangan' => '',
                'edited' => 1,
                'created_by' => 1,
                'created_at' => now(),
            ],  [
                'nama' => 'Net 45',
                'total_diskon' => 0,
                'total_hari' => 0,
                'masa_jatuh_tempo' => 45,
                'keterangan' => '',
                'edited' => 1,
                'created_by' => 1,
                'created_at' => now(),
            ], [
                'nama' => 'Net 60',
                'total_diskon' => 0,
                'total_hari' => 0,
                'masa_jatuh_tempo' => 60,
                'keterangan' => '',
                'edited' => 1,
                'created_by' => 1,
                'created_at' => now(),
            ], [
                'nama' => 'Net 7',
                'total_diskon' => 0,
                'total_hari' => 0,
                'masa_jatuh_tempo' => 7,
                'keterangan' => '',
                'edited' => 1,
                'created_by' => 1,
                'created_at' => now(),
            ]]);

    }
}
