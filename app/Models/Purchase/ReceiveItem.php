<?php

namespace App\Models\Purchase;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceiveItem extends Model
{
    use HasFactory;

    protected $table = 'receive_item';

    protected $guarded = [];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $year = date('Y');
        $this->table = "receive_item_{$year}";
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function supplierId()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id', 'id');
    }

    public function details()
    {
        return $this->hasMany(ReceiveItemDetail::class, 'receive_item_id');
    }
}
