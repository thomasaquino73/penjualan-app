<?php

namespace Database\Seeders;

use App\Models\General\Company;
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
            'mata_uang_id' => 7,
            'website' => 'https://www.almexbintangtimur.com',
            'email' => 'info@almexbintangtimur.com',
            'logo' => 'image/logo/69fd6d6ab719c1778216298.png',
            'favicon' => 'image/logo/69fd6d6ab719c1778216298.png',
        ]);

        
    }
}
