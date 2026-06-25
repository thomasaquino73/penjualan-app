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
        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->string('tax_name'); // contoh: PPN 11%
            $table->string('tax_type'); // contoh: PPN, PPh
            $table->decimal('percentage', 5, 2); // contoh: 11.00
            $table->string('description')->nullable();
            $table->boolean('is_default')->default(false); // pajak default
            $table->enum('calculation_type', ['percent', 'fixed']);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->enum('usage', ['sale', 'purchase', 'both'])->default('both');
            $table->timestamps();
            $table->softDeletes(); // lebih proper daripada flag delete
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxes');
    }
};
