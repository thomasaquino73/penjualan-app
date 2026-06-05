<?php

namespace App\Models;

use App\Models\BasicCodeDetail;
use App\Models\Inventory\Warehouse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMutation extends Model
{
    use HasFactory;

    protected $table = 'stock_mutations';

    protected $guarded = [];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
     public function warehouseID()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
    public function unitID()
    {
        return $this->belongsTo(BasicCodeDetail::class, 'unit_id');
    }

}
