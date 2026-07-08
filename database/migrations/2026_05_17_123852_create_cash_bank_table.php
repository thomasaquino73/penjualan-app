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
        Schema::create('cash_banks', function (Blueprint $table) {
            $table->id();

            $table->string('code')->unique();
            $table->string('name');

            $table->enum('type', ['cash', 'bank']);

            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_name')->nullable();

            $table->decimal('opening_balance', 18, 2)->default(0);
            $table->decimal('current_balance', 18, 2)->default(0);

            // $table->foreignId('coa_id')->nullable()->constrained('chart_of_accounts');

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cash_bank_transactions', function (Blueprint $table) {
            $table->id();

            $table->string('transaction_no')->unique();

            $table->date('transaction_date');

            $table->foreignId('cash_bank_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('transaction_type', [
                'receipt',      // uang masuk
                'payment',      // uang keluar
                'transfer',      // transfer bank
            ]);

            $table->enum('reference_type', [
                'sales_invoice',
                'purchase_invoice',
                'expense',
                'income',
                'transfer',
                'others',
            ])->default('others');

            $table->unsignedBigInteger('reference_id')->nullable();

            $table->decimal('amount', 18, 2);

            $table->text('description')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->enum('status', [
                'draft',
                'posted',
                'void',
            ])->default('draft');

            $table->timestamp('posted_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['reference_type', 'reference_id']);
        });

        Schema::create('cash_bank_transaction_details', function (Blueprint $table) {

            $table->id();

            $table->foreignId('cash_bank_transaction_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('reference_type', [
                'sales_invoice',
                'purchase_invoice',
            ]);

            $table->unsignedBigInteger('reference_id');

            $table->decimal('invoice_amount', 18, 2);

            $table->decimal('paid_amount', 18, 2);

            $table->decimal('remaining_amount', 18, 2);

            $table->timestamps();

            $table->index([
                'reference_type',
                'reference_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_banks');
        Schema::dropIfExists('cash_bank_transactions');
        Schema::dropIfExists('cash_bank_transaction_details');
    }
};
