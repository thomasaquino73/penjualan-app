<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemTransferDetail extends Model
{
      use HasFactory;

    protected $table = 'item_transfer_detail';

    protected $guarded = [];
}
