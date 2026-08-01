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
        Schema::create('ar_ap_histories', function (Blueprint $table) {

            $table->id();

            // customer / supplier
            $table->enum('type', [
                'receivable', // piutang customer
                'payable',     // hutang supplier
            ]);

            $table->unsignedBigInteger('party_id');
            // customer_id atau supplier_id

            // sumber transaksi
            $table->string('transaction_type');

            /*
                invoice
                payment
                down_payment
                return
                adjustment
            */

            $table->string('reference_type');
            /*
                sales_invoice
                purchase_invoice
                customer_payment
                supplier_payment
            */

            $table->unsignedBigInteger('reference_id');

            $table->date('transaction_date');

            // nomor dokumen
            $table->string('document_no');

            // nilai transaksi
            $table->decimal(
                'debit',
                18,
                2
            )->default(0);

            $table->decimal(
                'credit',
                18,
                2
            )->default(0);

            /*
                Piutang:

                Invoice
                debit +100jt

                Payment
                credit +50jt


                Hutang:

                Purchase Invoice
                credit +100jt

                Payment
                debit +50jt

            */

            $table->text('description')
                ->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ar_ap_histories');
    }
};
