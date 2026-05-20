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
        Schema::create('customer', function (Blueprint $table) {
            $table->id();
            $table->string('id_customer')->unique();
            $table->string('nama_customer');
            $table->string('notel_bisnis')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('no_whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->string('faximili')->nullable();
            $table->string('website')->nullable();
            $table->string('alamat_tagihan')->nullable();
            $table->string('kota_tagihan')->nullable();
            $table->string('kodepos_tagihan')->nullable();
            $table->string('provinsi_tagihan')->nullable();
            $table->string('negara_tagihan')->nullable();
            $table->tinyInteger('status')->default(1)->comment('0=delete, 1=active, 2=not active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::create('customer_kontak', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
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
            $table->timestamps();
        });
        Schema::create('customer_pengiriman', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->boolean('default_pengiriman')->default(1)->comment('ppn atau non ppn')->nullable();
            $table->string('nama_penerima')->nullable();
            $table->string('handphone_penerima')->nullable();
            $table->string('alamat_pengiriman')->nullable();
            $table->string('kota_pengiriman')->nullable();
            $table->string('kodepos_pengiriman')->nullable();
            $table->string('provinsi_pengiriman')->nullable();
            $table->string('negara_pengiriman')->nullable();
            $table->timestamps();
        });

        Schema::create('customer_pajak', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->boolean('default_pajak')->default(1)->comment('ppn atau non ppn')->nullable();
            $table->string('tipe_id_pajak')->nullable();
            $table->string('nomor_wajib_pajak')->nullable();
            $table->string('nama_wajib_pajak')->nullable();
            $table->string('id_tku')->nullable();
            $table->boolean('check_address')->default(1)->comment('ppn atau non ppn')->nullable();
            $table->string('alamat_pajak')->nullable();
            $table->string('kota_pajak')->nullable();
            $table->string('kodepos_pajak')->nullable();
            $table->string('provinsi_pajak')->nullable();
            $table->string('negara_pajak')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer');
    }
};
