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
        Schema::create("receive_item_{$this->year}", function (Blueprint $table) {
            $table->id();
            $table->string('receive_item_code')->unique();
            $table->dateTime('receive_item_date');
            $table->unsignedBigInteger('supplier_id');
            $table->string('no_dokumen')->nullable();
            $table->string('address')->nullable();
            $table->string('description')->nullable();
            $table->dateTime('tanggal_kirim')->nullable();
            $table->unsignedBigInteger('shipping_id')->nullable();
            $table->string('fob_id')->nullable();
            $table->decimal('sub_total', 18, 2)->default(0)->nullable();
            $table->decimal('disc_percent', 5, 2)->default(0)->nullable();
            $table->decimal('disc_nominal', 18, 2)->default(0)->nullable();
            $table->decimal('grand_total', 18, 2)->default(0)->nullable();
            $table->enum('status', [
                'processing',
                'partial',
                'closed',
                'done',
            ])->default('processing');
            $table->tinyInteger('active')->default(1)->comment('0=delete, 1=active, 2=not active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::create("receive_item_detail_{$this->year}", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('receive_item_id');
            $table->unsignedBigInteger('purchase_order_detail_id')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->decimal('qty', 18, 4);
            $table->bigInteger('unit_id');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('amount', 15, 2);
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->decimal('ri_qty', 18, 4)->default(0)->comment('Qty yang sudah sukses diproses');
            $table->decimal('outstanding_qty', 18, 4)->default(0)
                ->comment('Sisa qty ');
            $table->tinyInteger('active')->default(1)->comment('0=delete, 1=active, 2=not active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("receive_item_detail_{$this->year}");
        Schema::dropIfExists("receive_item_{$this->year}");
    }
};
