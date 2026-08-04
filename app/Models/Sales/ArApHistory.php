<?php

namespace App\Models\Sales;

use App\Models\Purchase\Supplier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArApHistory extends Model
{
    use HasFactory;

    protected $table = 'ar_ap_histories';

    protected $guarded = [];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'party_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'party_id');
    }
}
