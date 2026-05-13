<?php

namespace App\Models\Master_Data;

use App\Models\BasicCodeDetail;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    protected $table = 'data_barang';

    protected $guarded = [];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function kategoriID()
    {
        return $this->belongsTo(BasicCodeDetail::class, 'kategori_id');
    }

    public function warehouseID()
    {
        return $this->belongsTo(Warehouse::class, 'gudang_id');
    }

    public function typeID()
    {
        return $this->belongsTo(BasicCodeDetail::class, 'tipe_persediaan_id');
    }

    public function unitID()
    {
        return $this->belongsTo(BasicCodeDetail::class, 'unit_id');
    }
    public function conversions()
    {
        return $this->hasMany(DataBarangConversion::class, 'data_barang_id');
    }
}
