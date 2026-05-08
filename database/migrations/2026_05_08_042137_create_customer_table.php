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
            $table->string('id_pelanggan')->unique();
            $table->string('nama')->unique();
            $table->string('alamat')->unique();
            $table->string('alamat_pajak')->unique();
            $table->string('kodepos')->unique();
            $table->string('negara')->unique();
            $table->string('telepon')->unique();
            $table->string('personal_kontak')->unique();
            $table->string('email')->unique();
            $table->string('website')->unique();
            $table->enum('status', ['Active', 'Not Active'])->default('Active');
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
        Schema::dropIfExists('customer');
    }
};
