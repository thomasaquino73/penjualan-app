<?php

namespace Database\Seeders;

use App\Models\LoginBackground;
use App\Models\PengaturanSistem;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SistemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PengaturanSistem::create([
            'nama_aplikasi' => 'ALMEX APP',
            'nama_sistem' => 'Laravel 12',
            'nama_instansi' => 'PT. Almex Bintang Timur',
            'favicon' => 'image/favicon/favicon.png',
            'logo' => 'image/logo/69fd6d6ab719c1778216298.png',

        ]);

        LoginBackground::create([
            'gambar' => 'login1.jpg',
            'alias' => '69fd6d6ab719c1778216298.png',
            'status' => 1,
            'created_by' => 1,
            'created_at' => Carbon::now(),

        ]);
    }
}
