<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentTransactionHistory extends Model
{
    protected $table = 'document_transaction_histories';

    protected $guarded = [];
     protected $casts = [
        'metadata' => 'array',
    ];
}
