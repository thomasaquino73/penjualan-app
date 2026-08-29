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
        Schema::create("sales_quotation_{$this->year}", function (Blueprint $table) {
            $table->id();
            $table->string('sales_quotation_code')->unique();
            $table->date('sales_quotation_date');
            $table->unsignedBigInteger('payment_term_id')->nullable();
            $table->unsignedBigInteger('salesman_id')->nullable();
            $table->string('address')->nullable();
            $table->string('description')->nullable();
            $table->string('taxpayer_data')->nullable();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('customer_contact_id')->nullable();
            $table->boolean('kena_pajak')->default(1)->comment('kena pajak atau tidak')->nullable();
            $table->boolean('total_termasuk_pajak')->default(1)->comment('harga total termasuk pajak')->nullable();
            $table->enum('status', [
                'draft',        // Data baru dibuat
                'processing',   // Disetujui, siap diproses
                'partial',      // Baru dibuatkan SQ sebagian
                'closed',       // Selesai (Semua qty sudah dibuatkan SQ habis)
                'done',         // Dibatalkan/Selesai alur lainnya
            ])->default('draft');
            $table->decimal('sub_total', 18, 2)->default(0)->nullable();
            $table->decimal('disc_percent', 5, 2)->default(0)->nullable();
            $table->decimal('disc_nominal', 18, 2)->default(0)->nullable();
            $table->decimal('grand_total', 18, 2)->default(0)->nullable();
            $table->unsignedBigInteger('tax_id')->nullable();
            $table->decimal('tax_percent', 5, 2)->default(0)->nullable();
            $table->decimal('tax_amount', 15, 2)->default(0)->nullable();
            $table->decimal('biaya_lain', 15, 2)->default(0)->nullable();
            $table->tinyInteger('active')->default(1)->comment('0=delete, 1=active, 2=not active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::create("sales_quotation_detail_{$this->year}", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_quotation_id');
            $table->integer('urutan')->default(0);
            $table->unsignedBigInteger('product_id');
            $table->decimal('qty', 18, 4);
            $table->bigInteger('unit_id');
            $table->decimal('unit_price', 15, 2);
            $table->string('discount_percent')->nullable();
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('amount', 15, 2);
            $table->decimal('sq_qty', 18, 4)->default(0)->comment('Qty yang sudah sukses di-SQ-kan');
            $table->decimal('outstanding_qty', 18, 4)->default(0)
                ->comment('Sisa qty yang belum di-SQ-kan: qty - sq_qty');
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
        Schema::dropIfExists("sales_quotation_detail_{$this->year}");
        Schema::dropIfExists("sales_quotation_{$this->year}");
    }
};
