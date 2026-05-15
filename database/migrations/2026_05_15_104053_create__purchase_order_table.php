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
       Schema::create('purchase_order', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('customer_id');
            $table->bigInteger('code');
            $table->date('date');
            $table->date('expected_date');
            $table->bigInteger('sales_id')->nullable();
            $table->bigInteger('term_id')->nullable();
            $table->bigInteger('vehicle_id')->nullable();
            $table->enum('status',['processing','deliver'])->default('processing')->comment('status penawaran');
            $table->tinyInteger('active')->default(1)->comment('0=delete, 1=active, 2=not active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::create('purchase_order_detail', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('purchase_order_id');
            $table->bigInteger('product_id');
            $table->bigInteger('qty');
            $table->bigInteger('unit_id');
            $table->bigInteger('unit_price');
            $table->bigInteger('discount');
            $table->bigInteger('tax');
            $table->tinyInteger('active')->default(1)->comment('0=delete, 1=active, 2=not active');
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
        Schema::dropIfExists('purchase_order');
    }
};
