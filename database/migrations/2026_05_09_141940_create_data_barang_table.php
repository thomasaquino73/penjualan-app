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
            $table->integer('unit_1')->nullable();
            $table->integer('unit_2')->nullable();
            $table->integer('quantity1')->nullable();
            $table->integer('quantity2')->nullable();
            $table->tinyInteger('status')->default(1)->comment('0=delete, 1=active, 2=not active');
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
        Schema::dropIfExists('data_barang');
    }
};
