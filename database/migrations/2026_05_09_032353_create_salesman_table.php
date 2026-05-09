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
        Schema::create('salesman', function (Blueprint $table) {
            $table->id();
            $table->string('id_salesman')->unique();
            $table->string('nama');
            $table->string('alamat');
            $table->string('kodepos')->nullable();
            $table->string('negara')->nullable();
            $table->string('telepon')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('email')->nullable();
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
        Schema::dropIfExists('salesman');
    }
};
