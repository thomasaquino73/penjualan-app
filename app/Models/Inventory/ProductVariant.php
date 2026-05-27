<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $table = 'data_barang_variants';

    protected $guarded = [];

    protected $casts = [
        'specifications' => 'array',
    ];

    // Relasi balik ke Data Barang
    public function dataBarang()
    {
        return $this->belongsTo(Barang::class, 'data_barang_id');
    }
}
