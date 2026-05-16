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
        Schema::create('purchase_requisition', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->date('date');
            $table->text('description')->nullable();
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
        Schema::create('purchase_requisition_detail', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('purchase_requisition_id');
            $table->bigInteger('product_id');
            $table->bigInteger('qty');
            $table->bigInteger('unit_id');
            $table->bigInteger('unit_price');
            $table->bigInteger('discount');
            $table->bigInteger('tax');
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
        Schema::dropIfExists('purchase_requisition');
        Schema::dropIfExists('purchase_requisition_id');
    }
};
