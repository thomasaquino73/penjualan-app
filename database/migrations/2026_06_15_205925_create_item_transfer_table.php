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
        Schema::create('item_transfer', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_code')->unique();

            $table->unsignedBigInteger('from_warehouse_id');
            $table->unsignedBigInteger('to_warehouse_id');
            $table->date('transfer_date');
            $table->string('description')->nullable();
            $table->enum('status', [
                'draft',
                'pending',
                'approved',
                'completed',
                'rejected',
            ])->default('draft');
            $table->tinyInteger('active')->default(1)->comment('0=delete, 1=active, 2=not active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('pic_by')->nullable();
            $table->datetime('pic_at')->nullable();
            $table->timestamps();
        });

        Schema::create('item_transfer_detail', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('item_transfer_id');
            $table->unsignedBigInteger('data_barang_id');
            $table->unsignedBigInteger('unit_id');
            $table->decimal('qty', 15, 4);
            $table->timestamps();
        });

        Schema::create('stock_balance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->decimal('qty', 15, 4)->default(0);
            $table->timestamps();
            $table->unique([
                'product_id',
                'warehouse_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_balance');
        Schema::dropIfExists('item_transfer_detail');
        Schema::dropIfExists('item_transfer');
    }
};
