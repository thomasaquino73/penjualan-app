<?php

namespace App\Models\Sales;

use App\Models\Setting\BankList;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreSales extends Model
{
    use HasFactory;

    protected $table = 'store_sales';

    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $year = date('Y');
        $this->table = "store_sales_{$year}";
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function customerID()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    public function bankID()
    {
        return $this->belongsTo(BankList::class, 'bank_list_id');
    }
   

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function details()
    {
        return $this->hasMany(StoreSalesDetail::class, 'store_sales_id');
    }
}
