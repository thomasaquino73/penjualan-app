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
        Schema::create('data_barang', function (Blueprint $table) {
            $table->id();
            $table->string('id_barang')->unique();
            $table->string('photo_filename')->nullable();
            $table->string('nama_barang');
            $table->unsignedBigInteger('kategori_id');
            $table->unsignedBigInteger('gudang_id');
            $table->unsignedBigInteger('tipe_persediaan_id')->nullable();
            $table->unsignedBigInteger('unit_id');
            $table->enum('product_type', ['supply', 'non_supply'])->default('supply');
            $table->string('keterangan')->nullable();
            $table->bigInteger('quantity')->nullable();
            $table->bigInteger('price')->nullable();
            $table->bigInteger('hasil_akhir')->nullable();
            $table->date('date')->nullable();
            $table->tinyInteger('status')->default(1)->comment('0=delete, 1=active, 2=not active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::create('data_barang_conversions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('data_barang_id');
            $table->unsignedBigInteger('from_unit_id')->nullable();
            $table->unsignedBigInteger('to_unit_id')->nullable();
            $table->integer('qty')->nullable();
            $table->timestamps();
            $table->index('data_barang_id');
            $table->index('from_unit_id');
            $table->index('to_unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_barang_conversions');
        Schema::dropIfExists('data_barang');
    }
};
