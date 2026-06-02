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
        Schema::create('receive_item', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id');
            $table->date('receive_date');
            $table->string('receipt_number')->unique(); // nomor penerimaan internal
            $table->string('supplier_do_number')->nullable(); // nomor DO supplier
            $table->string('address')->nullable(); // nomor DO supplier
            $table->string('description')->nullable(); // nomor DO supplier
            $table->date('shipping_date')->nullable(); // tanggal pengiriman dari supplier
            $table->unsignedBigInteger('shipping_id')->nullable();
            $table->unsignedBigInteger('fob_id')->nullable();
            $table->enum('status', [
                'draft',
                'completed',
                'cancelled',
            ])->default('draft');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::create('receive_item_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('receive_item_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity');
            $table->decimal('price', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receive_item');
    }
};
