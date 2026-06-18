<?php

namespace App\Models\Inventory;

use App\Models\BasicCodeDetail;
use App\Models\Inventory\Barang;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemTransferDetail extends Model
{
      use HasFactory;

    protected $table = 'item_transfer_detail';

    protected $guarded = [];
    
      public function produkID()
    {
        return $this->belongsTo(Barang::class, 'data_barang_id', 'id');
    }

    public function unitID()
    {
        // Sesuaikan nama class Unit dengan model master unit Anda
        return $this->belongsTo(BasicCodeDetail::class, 'unit_id', 'id');
    }
}
