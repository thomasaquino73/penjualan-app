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
        Schema::create('sales_receipt', function (Blueprint $table) {
            $table->id();
                 $table->string('proforma_invoice_code')->unique();
            $table->date('proforma_invoice_date');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('cash_bank_id');
            $table->unsignedBigInteger('cash_bank_id');
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
        Schema::dropIfExists('sales_receipt');
    }
};
