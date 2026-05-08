<?php

namespace Database\Seeders;

use App\Models\PengaturanSistem;
use Illuminate\Database\Seeder;

class SistemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PengaturanSistem::create([
            'nama_aplikasi' => 'SPKSM APP',
            'nama_sistem' => 'Laravel 12',
            'nama_instansi' => 'Paroki St. Monika Serpong',
            'favicon' => 'image/favicon/favicon.png',
            'logo' => 'image/logo/69ae7671a81171773041265.png',

        ]);
    }
}
