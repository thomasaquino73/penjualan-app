<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class PengaturanSistem extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'pengaturan_sistem';

    protected $guarded = [];
}
