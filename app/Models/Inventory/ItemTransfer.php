<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemTransfer extends Model
{
      use HasFactory;

    protected $table = 'item_transfer';

    protected $guarded = [];
}
