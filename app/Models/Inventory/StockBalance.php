<?php

namespace App\Models\Inventory;

use App\Models\Inventory\Warehouse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockBalance extends Model
{
     use HasFactory;

    protected $table = 'stock_balance';

    protected $guarded = [];

        public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
}
