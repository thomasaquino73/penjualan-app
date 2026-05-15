<?php

namespace App\Models\Master_Data;

use App\Models\BasicCodeDetail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataBarangConversion extends Model
{
    use HasFactory;

    protected $table = 'data_barang_conversions';

    protected $guarded = [];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'data_barang_id');
    }

    public function fromUnitID()
    {
        return $this->belongsTo(BasicCodeDetail::class, 'from_unit_id');
    }

    public function toUnitID()
    {
        return $this->belongsTo(BasicCodeDetail::class, 'to_unit_id');
    }
}
