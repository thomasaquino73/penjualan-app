<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order_detail_2026', function (Blueprint $table) {
            // Menambahkan kolom relasi ke PR Detail (bisa null jika isi mandiri)
            $table->unsignedBigInteger('purchase_requisition_detail_id')->nullable()->after('purchase_order_id');

            // Opsional: Tambahkan foreign key index jika nama tabel detail PR kamu adalah 'purchase_requisition_details'
            // $table->foreign('purchase_requisition_detail_id', 'fk_po_detail_pr_detail')->references('id')->on('purchase_requisition_details')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_detail_2026', function (Blueprint $table) {
            // $table->dropForeign('fk_po_detail_pr_detail');
            $table->dropColumn('purchase_requisition_detail_id');
        });
    }
};
