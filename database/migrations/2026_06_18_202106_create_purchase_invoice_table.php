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
        Schema::create("purchase_invoice_{$this->year}", function (Blueprint $table) {
            $table->id();
            $table->bigInteger('supplier_id');
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            $table->string('code')->unique();
            $table->string('no_faktur')->nullable();
            $table->date('datePO');
            $table->date('tanggal_kirim')->nullable();
            $table->bigInteger('vehicle_id')->nullable();
            $table->bigInteger('payment_term')->nullable();
            $table->bigInteger('bank_id')->nullable();
            $table->string('shipping_address')->nullable();
            $table->string('description')->nullable();
            $table->string('taxpayer_data')->nullable();
            $table->boolean('kena_pajak')->default(1)->comment('kena pajak atau tidak')->nullable();
            $table->boolean('total_termasuk_pajak')->default(1)->comment('harga total termasuk pajak')->nullable();
            $table->string('fob_id')->nullable();
            $table->decimal('sub_total', 18, 2)->default(0)->nullable();
            $table->decimal('disc_percent', 5, 2)->default(0)->nullable();
            $table->decimal('disc_nominal', 18, 2)->default(0)->nullable();
            $table->decimal('grand_total', 18, 2)->default(0)->nullable();
            $table->unsignedBigInteger('tax_id')->nullable();
            $table->decimal('tax_percent', 5, 2)->default(0)->nullable();
            $table->decimal('tax_amount', 15, 2)->default(0)->nullable();
            $table->enum('status', [
                'draft', 'processing', 'unpaid', 'partial', 'paid', 'cancelled', 'closed', 'overdue',
            ])->default('draft');
            $table->tinyInteger('active')->default(1)->comment('0=delete, 1=active, 2=not active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('pic_by')->nullable();
            $table->datetime('pic_at')->nullable();
            $table->timestamps();
        });
        Schema::create("purchase_invoice_detail_{$this->year}", function (Blueprint $table) {
            $table->id();
            $table->bigInteger('purchase_invoice_id');
            $table->unsignedBigInteger('receive_item_detail_id')->nullable();
            $table->integer('urutan')->default(0);
            $table->bigInteger('product_id');
            $table->decimal('qty', 18, 4);
            $table->bigInteger('unit_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->decimal('unit_price', 15, 2);
            $table->string('discount_percent')->nullable();
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('amount', 15, 2);
            $table->decimal('received_qty', 18, 4)->default(0);
            $table->decimal('outstanding_qty', 18, 4)->default(0);
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
        Schema::dropIfExists("purchase_invoice_{$this->year}");
        Schema::dropIfExists("purchase_invoice_detail_{$this->year}");
    }
};
