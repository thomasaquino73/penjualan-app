<?php

namespace Database\Seeders;

use App\Models\General\Company;
use App\Models\General\Currency;
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

    }
}
