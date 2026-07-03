<?php

namespace App\Models\Sales;

use App\Models\Setting\Shipping;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class DeliveryOrder extends Model
{
    protected $table = 'delivery_order';

    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $year = date('Y');
        $this->table = "delivery_order_{$year}";
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function customerID()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function shippingID()
    {
        return $this->belongsTo(Shipping::class, 'shipping_id');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function details()
    {
        return $this->hasMany(DeliveryOrderDetail::class, 'delivery_order_id');
    }
}
