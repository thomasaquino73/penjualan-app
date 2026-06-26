<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   protected string $year;

    public function __construct()
    {
        $this->year = date('Y'); // tahun berjalan
    }

    public function up(): void
    {
        Schema::create("store_sales_{$this->year}", function (Blueprint $table) {
            $table->id();
            $table->string('store_sales_code')->unique();
            $table->date('store_sales_date');
            $table->unsignedBigInteger('customer_id');
            $table->decimal('sub_total', 18, 2)->default(0)->nullable();
            $table->decimal('disc_nominal', 18, 2)->default(0)->nullable();
            $table->unsignedBigInteger('tax_id')->nullable();
            $table->decimal('tax_percent', 5, 2)->default(0)->nullable();
            $table->decimal('tax_amount', 15, 2)->default(0)->nullable();
            $table->decimal('grand_total', 18, 2)->default(0)->nullable();
            $table->decimal('amount_receive', 18, 2)->default(0)->nullable();
            $table->decimal('change', 18, 2)->default(0)->nullable();
            $table->string('payment_method');
            $table->string('shipping_method');
            $table->string('notes');
            $table->enum('status', [
                'draft',              
                'pending',            
                'paid',            
            ])->default('draft');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::create("store_sales_detail_{$this->year}", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_sales_id');
            $table->bigInteger('product_id');
            $table->decimal('qty', 18, 4);
            $table->bigInteger('unit_id');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('amount', 15, 2);
            $table->timestamps();
            $table->foreign('store_sales_id')->references('id')->on("store_sales_{$this->year}")->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("store_sales_detail_{$this->year}");
        Schema::dropIfExists("store_sales_{$this->year}");
    }
};
