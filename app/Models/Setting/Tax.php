<?php

namespace App\Models\Setting;

use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    protected $fillable = [
        'tax_name',
        'tax_type',
        'percentage'
    ];

    public function calculate($amount)
    {
        return ($amount * $this->percentage) / 100;
    }
}
