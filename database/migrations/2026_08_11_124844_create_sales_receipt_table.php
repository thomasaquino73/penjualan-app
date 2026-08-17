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
        Schema::create("sales_receipt_{$this->year}", function (Blueprint $table) {
            $table->id();
            $table->string('proforma_invoice_code')->unique();
            $table->date('proforma_invoice_date');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('cash_bank_id');
            $table->tinyInteger('active')->default(1)->comment('0=delete, 1=active, 2=not active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::create("sales_receipt_detail_{$this->year}", function (Blueprint $table) {
            $table->id();

            $table->foreignId('sales_receipt_id')
                ->constrained("sales_receipt_{$this->year}")
                ->cascadeOnDelete();

            $table->unsignedBigInteger('sales_invoice_id');

            $table->decimal('invoice_amount', 18, 2)->default(0);
            $table->decimal('paid_amount', 18, 2)->default(0);
            $table->decimal('discount_amount', 18, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("sales_receipt_{$this->year}");
        Schema::dropIfExists("sales_receipt_detail_{$this->year}");
    }
};
