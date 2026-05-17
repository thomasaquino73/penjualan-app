<?php

namespace App\Models\General;

use App\Models\General\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $table = 'currency';

    protected $guarded = [];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function companies()
    {
        return $this->hasMany(Company::class, 'mata_uang_id');
    }
 
    public function cashBanks()
    {
        return $this->hasMany(CashBank::class, 'currency_id');
    }
}
