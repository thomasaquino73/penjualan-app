<?php

namespace App\Models\Master_Data;

use App\Models\BasicCodeDetail;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataBarangStok extends Model
{
    use HasFactory;

    protected $table = 'data_barang_stok';

    protected $guarded = [];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function unitID()
    {
        return $this->belongsTo(BasicCodeDetail::class, 'stok_unit_id');
    }

    public function warehouseID()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
}
