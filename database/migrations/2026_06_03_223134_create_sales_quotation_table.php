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
        Schema::create('sales_quotation', function (Blueprint $table) {
            $table->id();
            $table->string('sales_quotation_code')->unique();
            $table->date('sales_quotation_date');
            $table->unsignedBigInteger('payment_term_id')->nullable();
            $table->foreign('payment_term_id')->references('id')->on('syarat_pembayaran');
            $table->string('address')->nullable();
            $table->string('description')->nullable();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('customer_contact_id');
            $table->foreign('customer_contact_id')->references('id')->on('customer_kontak');
            $table->foreign('customer_id')->references('id')->on('customer');
            $table->boolean('kena_pajak')->default(1)->comment('kena pajak atau tidak')->nullable();
            $table->boolean('total_termasuk_pajak')->default(1)->comment('harga total termasuk pajak')->nullable();
            $table->enum('status', [
                'draft',               // Baru dibuat
                'pending',             // Menunggu approval
                'approved',            // Sudah approve
                'rejected',            // Ditolak
                'sent',                // Sudah dikirim ke supplier
                'partially_received',  // Barang diterima sebagian
                'completed',           // Semua barang diterima
                'closed',           // Dibatalkan
            ])->default('draft');
            $table->decimal('sub_total', 18, 2)->default(0)->nullable();
            $table->decimal('disc_percent', 5, 2)->default(0)->nullable();
            $table->decimal('disc_nominal', 18, 2)->default(0)->nullable();
            $table->decimal('grand_total', 18, 2)->default(0)->nullable();
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
        Schema::dropIfExists('sales_quotation');
    }
};
