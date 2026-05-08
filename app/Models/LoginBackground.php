<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginBackground extends Model
{
    protected $table = 'login_background';

    protected $primaryKey = 'id';

    protected $guarded = [];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
