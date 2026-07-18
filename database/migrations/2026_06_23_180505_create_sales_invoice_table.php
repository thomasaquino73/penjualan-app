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
    Schema::create("sales_invoice_{$this->year}", function (Blueprint $table) {
        $table->id();
        $table->string('sales_invoice_code')->unique();
        $table->date('sales_invoice_date');

        $table->unsignedBigInteger('sales_order_id')->nullable();
        $table->unsignedBigInteger('payment_term_id')->nullable();
        $table->unsignedBigInteger('salesman_id')->nullable();
        $table->unsignedBigInteger('customer_id');
        $table->unsignedBigInteger('customer_contact_id')->nullable();
        $table->unsignedBigInteger('tax_id')->nullable();

        // ... kolom lainnya

        $table->timestamps();

        // =========================
        // INDEX
        // =========================
        $table->index('sales_invoice_date');
        $table->index('customer_id');
        $table->index('sales_order_id');
        $table->index('salesman_id');
        $table->index('payment_term_id');
        $table->index('tax_id');
        $table->index('status');
        $table->index('active');

        // Composite Index
        $table->index(['customer_id', 'status']);
        $table->index(['sales_invoice_date', 'status']);
        $table->index(['active', 'status']);
    });

    Schema::create("sales_invoice_detail_{$this->year}", function (Blueprint $table) {
        $table->id();

        $table->unsignedBigInteger('sales_invoice_id');
        $table->unsignedBigInteger('sales_order_detail_id')->nullable();
        $table->unsignedBigInteger('product_id');
        $table->unsignedBigInteger('unit_id');
        $table->unsignedBigInteger('warehouse_id');

        // ... kolom lainnya

        $table->timestamps();

        // =========================
        // INDEX
        // =========================
        $table->index('sales_invoice_id');
        $table->index('sales_order_detail_id');
        $table->index('product_id');
        $table->index('unit_id');
        $table->index('warehouse_id');

        // Composite Index
        $table->index(['sales_invoice_id', 'product_id']);
        $table->index(['product_id', 'warehouse_id']);
        $table->index(['sales_order_detail_id', 'status']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("sales_invoice_{$this->year}");
        Schema::dropIfExists("sales_invoice_detail_{$this->year}");
    }
};
