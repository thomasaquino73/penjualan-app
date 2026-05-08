<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BasicCodeMaster extends Model
{
    use HasFactory;

    protected $connection = '';

    protected $table = 'basic_code_master';

    protected $id = 'id';

    public $incrementing = true;

    protected $keytype = 'int';

    protected $guarded = [];

    public function CodeDetail(): HasMany
    {
        return $this->hasMany(BasicCodeDetail::class, 'master_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
