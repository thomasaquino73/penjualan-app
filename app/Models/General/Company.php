<?php

namespace App\Models\General;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Company extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'company';

    protected $guarded = [];

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'mata_uang_id');
    }
}
