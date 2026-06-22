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
        Schema::create("delivery_order_{$this->year}", function (Blueprint $table) {
            $table->id();
            $table->string('delivery_order_code')->unique();
            $table->date('delivery_order_date');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('shipping_id')->nullable();
            $table->string('no_document')->nullable();
            $table->string('address')->nullable();
            $table->string('description')->nullable();
            $table->unsignedBigInteger('customer_contact_id')->nullable();
            $table->string('fob_id')->nullable();
            $table->enum('status', [
                'draft',
                'confirmed',
                'partial',
                'delivered',
                'cancelled'
            ])->default('draft');
            $table->tinyInteger('active')->default(1)->comment('0=delete, 1=active, 2=not active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

         Schema::create("delivery_order_detail_{$this->year}", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('delivery_order_id');
            $table->unsignedBigInteger('sales_order_detail_id');
            $table->unsignedBigInteger('data_barang_id');
            $table->unsignedBigInteger('unit_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->decimal('qty', 15, 4);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("delivery_order_detail_{$this->year}");
        Schema::dropIfExists("delivery_order_{$this->year}");
    }
};
