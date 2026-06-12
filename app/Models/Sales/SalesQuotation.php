<?php

namespace App\Models\Sales;

use App\Models\Setting\SyaratPembayaran;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesQuotation extends Model
{
    use HasFactory;

    protected $table = 'sales_quotation';

    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $year = date('Y');
        $this->table = "sales_quotation_{$year}";
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

    public function paymentTermID()
    {
        return $this->belongsTo(SyaratPembayaran::class, 'payment_term_id');
    }
     public function details()
    {
        return $this->hasMany(SalesQuotationDetail::class, 'sales_quotation_id');
    }
}
