<?php

namespace App\Models\Sales;

use App\Models\Setting\SyaratPembayaran;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesDownPayment extends Model
{
    use HasFactory;

    protected $table = 'sales_down_payments';

    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $year = date('Y');
        $this->table = "sales_down_payments_{$year}";
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function customerID()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }
    public function paymentTermID()
{
    return $this->belongsTo(
        SyaratPembayaran::class,
        'payment_term_id'
    );
}
}
