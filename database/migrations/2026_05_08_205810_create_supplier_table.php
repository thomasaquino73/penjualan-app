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
        Schema::create('supplier', function (Blueprint $table) {
            $table->id();
            $table->string('id_supplier')->unique();
            $table->string('nama_supplier');
            $table->string('notel_bisnis')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('no_whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->string('faximili')->nullable();
            $table->string('website')->nullable();
            $table->string('alamat_pembayaran')->nullable();
            $table->string('kota')->nullable();
            $table->string('kodepos')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('negara')->nullable();
            $table->string('tipe_pemasok_id')->nullable();
            $table->string('syarat_pembelian')->nullable(); // syarat pembelian
            $table->string('default_diskon')->nullable();
            $table->string('default_deskripsi')->nullable();
            $table->tinyInteger('status')->default(1)->comment('0=delete, 1=active, 2=not active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::create('supplier_kontak', function (Blueprint $table) {
            $table->unsignedBigInteger('supplier_id')->unique();
            $table->string('sapaan')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('posisi_jabatan')->nullable();
            $table->string('email_kontak')->nullable();
            $table->string('handphone_kontak')->nullable();
            $table->string('notel_bisnis_kontak')->nullable();
            $table->string('faximili_kontak')->nullable();
            $table->string('no_whatsapp_kontak')->nullable();
            $table->string('website_kontak')->nullable();
            $table->string('catatan')->nullable();
        });
        Schema::create('supplier_pembelian', function (Blueprint $table) {
            $table->unsignedBigInteger('supplier_id')->unique();
            $table->integer('payment_term')->nullable();
            $table->string('discount')->nullable();
            $table->string('default_deskripsi')->nullable();
        });
        Schema::create('supplier_rekening', function (Blueprint $table) {
            $table->unsignedBigInteger('supplier_id')->unique();
            $table->string('nama_bank')->nullable();
            $table->string('nomor_rekening')->nullable();
            $table->string('nama_rekening')->nullable();
        });
        Schema::create('supplier_pajak', function (Blueprint $table) {
            $table->unsignedBigInteger('supplier_id')->unique();
            $table->boolean('default_pajak')->default(1)->comment('ppn atau non ppn');
            $table->string('tipe_id_pajak')->nullable();
            $table->string('nomor_wajib_pajak')->nullable();
            $table->string('nama_wajib_pajak')->nullable();
            $table->string('id_tku')->nullable();
            $table->string('alamat_pajak')->nullable();
            $table->string('kota_pajak')->nullable();
            $table->string('kodepos_pajak')->nullable();
            $table->string('provinsi_pajak')->nullable();
            $table->string('negara_pajak')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier');
    }
};
