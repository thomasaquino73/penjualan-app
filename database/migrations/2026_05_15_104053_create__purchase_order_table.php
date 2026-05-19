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
            $table->date('expected_date')->nullable();
            $table->string('fob_id')->nullable();
            $table->integer('term')->nullable();
            $table->string('description')->nullable();
            $table->bigInteger('vehicle_id')->nullable();
            $table->bigInteger('sub_total')->nullable();
            $table->integer('disc_percent')->nullable();
            $table->bigInteger('disc_nominal')->nullable();
            $table->bigInteger('grand_total')->nullable();
            $table->bigInteger('tax')->nullable();
            $table->enum('status', [
                'draft',        // Data baru dibuat, masih bisa diedit oleh staff
                'pending',      // Menunggu persetujuan (approval) dari Manager/Direktur
                'processing',   // Disetujui, sedang dipersiapkan / dipacking di gudang
                'deliver',      // Barang sedang dalam perjalanan (dikirim)
                'received',     // Barang sudah sampai dan diterima oleh pemesan
                'completed',    // Selesai (Semua dokumen & pembayaran sudah klop)
                'rejected',     // Ditolak saat pengajuan approval
                'cancelled',     // Dibatalkan oleh user
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
