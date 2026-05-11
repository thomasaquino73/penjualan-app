<?php

namespace App\Models\Master_Data;

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
}
