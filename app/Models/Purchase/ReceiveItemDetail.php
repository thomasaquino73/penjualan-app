<?php

namespace App\Models\Purchase;

use App\Models\BasicCodeDetail;
use App\Models\Inventory\Barang;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceiveItemDetail extends Model
{
     use HasFactory;

    protected $table = 'receive_item_detail';

    protected $guarded = [];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $year = date('Y');
        $this->table = "receive_item_detail_{$year}";
    }
        public function produkID()
    {
        return $this->belongsTo(Barang::class, 'product_id', 'id');
    }

    public function unitID()
    {
        // Sesuaikan nama class Unit dengan model master unit Anda
        return $this->belongsTo(BasicCodeDetail::class, 'unit_id', 'id');
    }
    public function receiveItem()
    {
        return $this->belongsTo(ReceiveItem::class, 'receive_item_id');
    }

}
