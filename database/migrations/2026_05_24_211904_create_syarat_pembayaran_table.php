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
        Schema::create('syarat_pembayaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->bigInteger('total_hari')->nullable();
            $table->bigInteger('total_diskon')->nullable();
            $table->bigInteger('masa_jatuh_tempo')->nullable();
            $table->string('keterangan')->nullable();
            $table->boolean('edited')->default(1);
            $table->integer('status')->default(1)->comment('1=delete, 1=No, 2=Yes');
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
        Schema::dropIfExists('syarat_pembayaran');
    }
};
