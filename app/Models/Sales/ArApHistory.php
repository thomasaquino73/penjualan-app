<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArApHistory extends Model
{
   use HasFactory;

    protected $table = 'ar_ap_histories';

    protected $guarded = [];
}
