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
        Schema::create('document_transaction_histories', function (Blueprint $table) {
            $table->id();
            $table->string('module'); // sales,purchase,finance
            // Header asal
            $table->string('from_type')->nullable();
            $table->unsignedBigInteger('from_id')->nullable();
            $table->unsignedBigInteger('from_detail_id')->nullable();
            // Header tujuan
            $table->string('to_type');
            $table->unsignedBigInteger('to_id');
            // Detail tujuan
            $table->unsignedBigInteger('to_detail_id')->nullable();
            $table->enum('transaction_type', [
                'sales_order',
                'invoice',
                'payment',
                'receipt',
                'return',
                'adjustment',
                'credit_note',
                'debit_note',
                'write_off',
            ]);
            $table->decimal('qty', 18, 4)->default(0);
            $table->decimal('unit_price', 18, 2)->default(0);
            $table->decimal('discount', 18, 2)->default(0);
            $table->decimal('amount', 18, 2)->default(0);
            $table->date('transaction_date');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['from_type', 'from_id']);
            $table->index(['to_type', 'to_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_transaction_histories');
    }
};
