<?php

namespace App\Models\Sales;

use App\Models\BasicCodeDetail;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customer';

    protected $guarded = [];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function kategoriCustomer()
    {
        return $this->belongsTo(BasicCodeDetail::class, 'kategori_customer_id');
    }
}
