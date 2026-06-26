<?php

namespace Database\Seeders;

use App\Models\Purchase\Supplier;
use App\Models\Sales\Customer;
use App\Models\Setting\Company;
use App\Models\Setting\Currency;
use App\Models\Setting\SyaratPembayaran;
use App\Models\Setting\Tax;
use Illuminate\Database\Seeder;

class CompanyInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Currency::insert([
            [
                'id' => 1,
                'code' => 'IDR',
                'name' => 'Rupiah',
                'symbol' => 'Rp',
                'country' => 'Indonesia',
            ],
            [
                'id' => 2,
                'code' => 'USD',
                'name' => 'US Dollar',
                'symbol' => 'USD',
                'country' => 'United States',
            ],
            [
                'id' => 3,
                'code' => 'SGD',
                'name' => 'Singapore Dollar',
                'symbol' => 'SGD',
                'country' => 'Singapore',
            ],
        ]);
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
            'default_currency_id' => '1',
            'cut_off_date' => '2026-05-01',
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

        Customer::create([
            'id_customer' => 'C-00001',
            'nama_customer' => 'Thomas',
            'kategori_customer_id' => 1,
            'email' => 'info@almexbintangtimur.com',
            'phone_1' => '081382397429',
            'phone_2' => '081382397429',
            'faximili' => null,
            'website' => 'https://www.thomasaquino.my.id',
            'alamat_tagihan' => 'Tangerang',
            'kota_tagihan' => 'Tangerang',
            'kodepos_tagihan' => '16424',
            'provinsi_tagihan' => 'Banten',
            'negara_tagihan' => 'Indonesia',
            'status' => 1,
            'created_by' => 1,
            'updated_by' => null,
        ]);

        Tax::create([
            'tax_name' => 'PPN 11%',
            'tax_type' => 'PPN',
            'percentage' => '11',
            'description' => 'Pajak Pertambahan Nilai',
            'is_default' => 1,
            'calculation_type' => 'percent',
            'is_active' => 1,
            'created_by' => 1,
            'updated_by' => null,
            'created_at' => now(),
            'updated_at' => now(),
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
