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
        /*
        |--------------------------------------------------------------------------
        | MASTER CASH & BANK
        |--------------------------------------------------------------------------
        */
        Schema::create('cash_banks', function (Blueprint $table) {

            $table->id();

            $table->string('code')->unique();
            $table->string('name');

            $table->enum('type', [
                'cash',
                'bank',
            ]);

            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_name')->nullable();
            $table->string('branch_name')->nullable();
            $table->string('currency', 10)->default('IDR');

            $table->decimal('opening_balance', 18, 2)->default(0);
            $table->decimal('current_balance', 18, 2)->default(0);

            $table->boolean('is_active')->default(true);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        /*
        |--------------------------------------------------------------------------
        | CASH BANK TRANSACTION HEADER
        |--------------------------------------------------------------------------
        */

        Schema::create('cash_bank_transactions', function (Blueprint $table) {

            $table->id();

            $table->string('transaction_no')->unique();

            $table->date('transaction_date');

            $table->unsignedBigInteger('cash_bank_id');

            $table->enum('transaction_type', [
                'receipt',     // uang masuk
                'payment',     // uang keluar
                'transfer',     // transfer antar rekening
            ]);

            /*
            |--------------------------------------------------------------------------
            | Referensi Dokumen
            |--------------------------------------------------------------------------
            |
            | Karena sistem memakai tabel per tahun maka disimpan:
            |
            | reference_table = sales_invoice_2026
            | reference_id    = 15
            |
            */

            $table->string('reference_table')->nullable();

            $table->unsignedBigInteger('reference_id')->nullable();

            $table->enum('reference_type', [
                'sales_order',
                'sales_invoice',
                'purchase_order',
                'purchase_invoice',
                'expense',
                'income',
                'transfer',
                'journal',
                'others',
            ])->default('others');

            $table->string('reference_number')->nullable();

            $table->decimal('amount', 18, 2);

            $table->text('description')->nullable();

            $table->enum('status', [
                'draft',
                'posted',
                'void',
            ])->default('draft');

            $table->timestamp('posted_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();

            $table->softDeletes();

            $table->foreign('cash_bank_id')
                ->references('id')
                ->on('cash_banks')
                ->cascadeOnDelete();

            $table->index([
                'reference_table',
                'reference_id',
            ]);

            $table->index([
                'reference_type',
            ]);

            $table->index([
                'transaction_date',
            ]);

        });

        /*
        |--------------------------------------------------------------------------
        | CASH BANK TRANSACTION DETAIL
        |--------------------------------------------------------------------------
        */

        Schema::create('cash_bank_transaction_details', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('cash_bank_transaction_id');

            /*
            |--------------------------------------------------------------------------
            | Bisa membayar banyak Invoice
            |--------------------------------------------------------------------------
            */

            $table->string('reference_table');

            $table->unsignedBigInteger('reference_id');

            $table->enum('reference_type', [
                'sales_invoice',
                'purchase_invoice',
            ]);

            $table->string('reference_number')->nullable();

            $table->decimal('invoice_amount', 18, 2);

            $table->decimal('paid_amount', 18, 2);

            $table->decimal('remaining_amount', 18, 2);

            $table->text('description')->nullable();

            $table->timestamps();

            $table->foreign('cash_bank_transaction_id')
                ->references('id')
                ->on('cash_bank_transactions')
                ->cascadeOnDelete();

            $table->index([
                'reference_table',
                'reference_id',
            ]);

        });

        /*
        |--------------------------------------------------------------------------
        | TRANSFER ANTAR BANK
        |--------------------------------------------------------------------------
        */

        Schema::create('cash_bank_transfers', function (Blueprint $table) {

            $table->id();

            $table->string('transfer_no')->unique();

            $table->date('transfer_date');

            $table->unsignedBigInteger('from_cash_bank_id');

            $table->unsignedBigInteger('to_cash_bank_id');

            $table->decimal('amount', 18, 2);

            $table->text('description')->nullable();

            $table->enum('status', [
                'draft',
                'posted',
                'void',
            ])->default('draft');

            $table->timestamp('posted_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();

            $table->softDeletes();

            $table->foreign('from_cash_bank_id')
                ->references('id')
                ->on('cash_banks');

            $table->foreign('to_cash_bank_id')
                ->references('id')
                ->on('cash_banks');

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
