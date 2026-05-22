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
            $table->string('barcode')->unique()->nullable();
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
            $table->integer('primary_supplier_id')->nullable();
            $table->integer('primary_unit_id')->nullable();
            $table->integer('primary_price')->nullable();
            $table->integer('primary_minimum_order')->nullable();
            $table->integer('primary_minimum_stock')->nullable();
            $table->tinyInteger('status')->default(1)->comment('0=delete, 1=active, 2=not active')->nullable();
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
        Schema::create('data_barang_stok', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('data_barang_id');
            $table->date('date')->nullable();
            $table->integer('quantity')->nullable();
            $table->integer('price')->nullable();
            $table->unsignedBigInteger('stok_unit_id')->nullable();
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->timestamps();
            $table->index('stok_unit_id');
            $table->index('warehouse_id');
        });
        Schema::create('data_barang_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_barang_id');
            $table->string('variant_name')->nullable();
            $table->json('specifications')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_barang_variants');
        Schema::dropIfExists('data_barang_conversions');
        Schema::dropIfExists('data_barang');
    }
};
