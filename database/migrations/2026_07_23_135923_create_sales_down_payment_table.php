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
        Schema::create("sales_down_payments_{$this->year}", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->string('sales_downpayment_code')->unique();
            $table->date('sales_downpayment_date');
            $table->unsignedBigInteger('sales_order_id');
            $table->unsignedBigInteger('payment_term_id');
            $table->string('address');
            $table->string('po_number');

            // Nilai
            $table->decimal('sales_order_amount', 15, 4);
            $table->decimal('down_payment_percent', 8, 2)->default(0);
            $table->decimal('down_payment_amount', 15, 4);
            // Sudah dibayar
            $table->decimal('paid_amount', 15, 4)->default(0);
            // Sisa DP
            $table->decimal('remaining_amount', 15, 4)->default(0);
            // Tanggal jatuh tempo
            $table->date('due_date')->nullable();
            // Keterangan
            $table->text('description')->nullable();
            // Status
            $table->enum('status', [
                'unpaid',
                'paid',
                'cancelled',
                'closed',
            ])->default('unpaid');

            $table->tinyInteger('active')
                ->default(1)
                ->comment('0=deleted,1=active,2=inactive');

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
        Schema::dropIfExists("sales_down_payment_{$this->year}");
    }
};
