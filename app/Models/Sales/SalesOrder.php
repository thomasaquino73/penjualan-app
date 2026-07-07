<?php

namespace App\Models\Sales;

use App\Models\Setting\SyaratPembayaran;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    use HasFactory;

    protected $table = 'sales_order';

    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $year = date('Y');
        $this->table = "sales_order_{$year}";
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function customerID()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function details()
    {
        return $this->hasMany(SalesOrderDetail::class, 'sales_order_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'pic_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'pic_by');
    }

    public function salesQuotation()
    {
        // Sesuaikan 'sales_quotation_id' dengan nama kolom foreign key yang ada di tabel SO kamu
        return $this->belongsTo(SalesQuotation::class, 'sales_quotation_id', 'id');
    }
       public function paymentTermID()
    {
        return $this->belongsTo(SyaratPembayaran::class, 'payment_term_id');
    }
}
