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
        Schema::create("sales_order_{$this->year}", function (Blueprint $table) {
            $table->id();
            $table->string('sales_order_code')->unique();
            $table->date('sales_order_date');
            $table->unsignedBigInteger('payment_term_id')->nullable();
            $table->unsignedBigInteger('salesman_id')->nullable();
            $table->string('address')->nullable();
            $table->string('description')->nullable();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('customer_contact_id')->nullable();
            $table->boolean('kena_pajak')->default(1)->comment('kena pajak atau tidak')->nullable();
            $table->boolean('total_termasuk_pajak')->default(1)->comment('harga total termasuk pajak')->nullable();
            $table->enum('status', [
                'draft',               // Baru dibuat
                'pending',             // Menunggu approval
                'approved',            // Sudah approve
                'rejected',            // Ditolak
                'sent',                // Sudah dikirim ke supplier
                'partial',  // Barang diterima sebagian
                'completed',           // Semua barang diterima
                'closed',           // Dibatalkan
            ])->default('draft');
            $table->decimal('sub_total', 18, 2)->default(0)->nullable();
            $table->decimal('disc_percent', 5, 2)->default(0)->nullable();
            $table->decimal('disc_nominal', 18, 2)->default(0)->nullable();
            $table->decimal('grand_total', 18, 2)->default(0)->nullable();
            $table->string('po_number')->nullable();
            $table->date('tanggal_pengiriman')->nullable();
            $table->unsignedBigInteger('jenis_pengiriman')->nullable();
            $table->string('fob_id')->nullable();
            $table->tinyInteger('active')->default(1)->comment('0=delete, 1=active, 2=not active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('pic_by')->nullable();
            $table->datetime('pic_at')->nullable();
            $table->timestamps();
        });
        Schema::create("sales_order_detail_{$this->year}", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_order_id');
            $table->unsignedBigInteger('sales_quotation_detail_id')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->decimal('qty', 18, 4);
            $table->bigInteger('unit_id');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('amount', 15, 2);
            $table->unsignedBigInteger('warehouse_id');
            $table->decimal('so_qty', 18, 4)->default(0)->comment('Qty yang sudah sukses di-SO-kan');
            $table->decimal('outstanding_qty', 18, 4)->default(0)
                ->comment('Sisa qty yang belum di-SQ-kan: qty - sq_qty');
            $table->enum('status', [
                'open',
                'partial',
                'completed',
                'cancelled',
            ])->default('open');
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
        Schema::dropIfExists("sales_order_{$this->year}");
        Schema::dropIfExists("sales_order_detail_{$this->year}");
    }
};
