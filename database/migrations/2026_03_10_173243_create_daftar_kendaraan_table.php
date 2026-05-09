<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('daftar_kendaraan', function (Blueprint $table) {
            $table->id();
            $table->string('foto')->nullable();
            $table->string('merk');
            $table->string('tipe');
            $table->string('plat_nomor')->unique();
            $table->string('warna');
            $table->string('pemilik');
            $table->tinyInteger('status')->default(1)->comment('0=delete, 1=active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daftar_kendaraan');
    }
};
