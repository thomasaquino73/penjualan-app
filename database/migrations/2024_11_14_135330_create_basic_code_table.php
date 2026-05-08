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
        Schema::create('basic_code_master', function (Blueprint $table) {
            $table->id();
            $table->string('detail', 50)->nullable();
            $table->string('description', 50)->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::create('basic_code_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('master_id')->nullable();
            $table->string('detail', 50)->nullable();
            $table->string('description', 50)->nullable();
            $table->uuid('created_by', 100)->nullable();
            $table->uuid('updated_by', 100)->nullable();
            $table->timestamps();

            $table->foreign('master_id')->references('id')->on('basic_code_master');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('basic_code_master');
        Schema::dropIfExists('basic_code_detail');
    }
};
