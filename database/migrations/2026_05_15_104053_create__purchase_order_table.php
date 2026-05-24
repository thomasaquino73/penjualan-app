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
        Schema::create("purchase_order_{$this->year}", function (Blueprint $table) {
            $table->id();
            $table->bigInteger('supplier_id');
            $table->string('code');
            $table->date('date');
            $table->date('tanggal_kirim')->nullable();
            $table->bigInteger('vehicle_id')->nullable();
            $table->bigInteger('payment_term')->nullable();
            $table->string('shipping_address')->nullable();
            $table->string('description')->nullable();
            $table->boolean('kena_pajak')->default(1)->comment('kena pajak atau tidak')->nullable();
            $table->boolean('total_termasuk_pajak')->default(1)->comment('harga total termasuk pajak')->nullable();
            $table->string('fob_id')->nullable();
            $table->bigInteger('sub_total')->nullable();
            $table->integer('disc_percent')->nullable();
            $table->bigInteger('disc_nominal')->nullable();
            $table->bigInteger('grand_total')->nullable();
            $table->enum('status', [
                'draft',                 // Data baru dibuat, masih bisa diedit oleh staff purchasing
                'pending',               // Menunggu persetujuan (approval) dari Manager/Direktur
                'approved',              // Disetujui oleh atasan, siap dikirim ke supplier
                'rejected',              // Ditolak oleh atasan saat pengajuan approval
                'sent',                  // Dokumen PO sudah resmi dikirimkan ke pihak supplier
                'partially_received',    // Barang dari supplier baru datang sebagian di gudang
                'completed',             // Selesai (Semua barang telah diterima dengan lengkap)
                'cancelled',             // Dibatalkan (baik oleh internal maupun supplier)
            ])->default('draft');
            $table->tinyInteger('active')->default(1)->comment('0=delete, 1=active, 2=not active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::create("purchase_order_detail_{$this->year}", function (Blueprint $table) {
            $table->id();
            $table->bigInteger('purchase_order_id');
            $table->bigInteger('product_id');
            $table->bigInteger('qty');
            $table->bigInteger('unit_id');
            $table->bigInteger('unit_price');
            $table->decimal('discount', 10, 2)->default(0);
            $table->bigInteger('amount');
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
        Schema::dropIfExists("purchase_order_{$this->year}");
        Schema::dropIfExists("purchase_order_detail_{$this->year}");
    }
};
