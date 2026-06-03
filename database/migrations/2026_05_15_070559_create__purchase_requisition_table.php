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
        Schema::create("purchase_requisition_{$this->year}", function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->date('date');
            $table->text('description')->nullable();
            $table->enum('status', [
                'draft',        // Data baru dibuat
                'processing',   // Disetujui, siap diproses
                'partial',      // <--- TAMBAHKAN INI: Baru dibuatkan PO sebagian
                'closed',       // Selesai (Semua qty sudah dibuatkan PO habis)
                'done',         // Dibatalkan/Selesai alur lainnya
            ])->default('draft');
            $table->tinyInteger('active')->default(1)->comment('0=delete, 1=active, 2=not active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::create("purchase_requisition_detail_{$this->year}", function (Blueprint $table) {
            $table->id();
            $table->bigInteger('purchase_requisition_id');
            $table->bigInteger('product_id');
            $table->bigInteger('qty');
            $table->decimal('po_qty', 18, 4)->default(0)->comment('Qty yang sudah sukses di-PO-kan');
            $table->decimal('outstanding_qty', 18, 4)->default(0)
                ->comment('Sisa qty yang belum di-PO-kan: qty - po_qty');
            $table->bigInteger('unit_id');
            $table->date('required_date')->nullable();
            $table->string('notes')->nullable();
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
        Schema::dropIfExists("purchase_requisition_detail_{$this->year}");
        Schema::dropIfExists("purchase_requisition_{$this->year}");
    }
};
