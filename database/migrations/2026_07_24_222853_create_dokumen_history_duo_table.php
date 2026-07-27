<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Jika Anda menggunakan pemisahan per tahun seperti tabel lainnya,
        // Anda bisa menyesuaikan namanya menjadi "document_transaction_histories_{$this->year}"
        Schema::create('document_transaction_histories_duo', function (Blueprint $table) {
            $table->id();
            $table->string('module')->default('sales'); // sales, purchase, finance

            // Sumber Dokumen (Bisa dari Sales Order / Sales Invoice / dll)
            $table->string('from_type'); // Contoh: App\Models\SalesOrder
            $table->unsignedBigInteger('from_id');

            // Tujuan Dokumen / Transaksi (Bisa dari Sales Down Payment / Sales Invoice / Payment)
            $table->string('to_type'); // Contoh: App\Models\SalesDownPayment atau App\Models\SalesInvoice
            $table->unsignedBigInteger('to_id');

            // Jenis Transaksi
            $table->enum('transaction_type', [
                'sales_order',
                'down_payment',  // Untuk mencatat DP 1, DP 2, dst.
                'sales_invoice', // Untuk mencatat tagihan faktur
                'payment',       // Untuk pelunasan / pembayaran kas/bank
                'return',
                'adjustment',
                'credit_note',
                'debit_note',
                'write_off',
            ]);

            // Label / Nama Pembayaran (Contoh: "DP 1", "DP 2", "Pelunasan")
            $table->string('payment_name')->nullable();

            // Persentase jika berbasis persen (Contoh: 50.00 untuk 50%)
            $table->decimal('payment_percent', 5, 2)->nullable();

            // Nominal uang yang terlibat dalam transaksi ini
            $table->decimal('amount', 18, 2)->default(0);

            // Tanggal transaksi
            $table->date('transaction_date');

            // Catatan tambahan (JSON)
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Index untuk performa query pencarian relasi
            $table->index(['from_type', 'from_id']);
            $table->index(['to_type', 'to_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_transaction_histories_duo');
    }
};
