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

            $table->unsignedBigInteger('sales_order_id');
            $table->unsignedBigInteger('customer_id');

            $table->string('address')->nullable();
            $table->string('description')->nullable();

            $table->decimal('sub_total', 18, 2)->default(0);
            $table->decimal('disc_percent', 5, 2)->default(0);
            $table->decimal('disc_nominal', 18, 2)->default(0);
            $table->decimal('grand_total', 18, 2)->default(0);

            $table->enum('status', [
                'draft',
                'posted',
                'paid',
                'cancelled',
            ])->default('draft');

            $table->tinyInteger('active')->default(1)->comment('0=delete, 1=active, 2=not active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('pic_by')->nullable();
            $table->datetime('pic_at')->nullable();

            $table->timestamps();
        });

        Schema::create("sales_invoice_detail_{$this->year}", function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('sales_invoice_id');
            $table->unsignedBigInteger('sales_order_detail_id')->nullable();

            $table->unsignedBigInteger('product_id');
            $table->decimal('qty', 18, 4);
            $table->unsignedBigInteger('unit_id');

            $table->decimal('unit_price', 15, 2);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('amount', 15, 2);

            $table->unsignedBigInteger('warehouse_id');

            $table->tinyInteger('active')->default(1);

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
        Schema::dropIfExists("sales_invoice_{$this->year}");
        Schema::dropIfExists("sales_invoice_detail_{$this->year}");
    }
};
