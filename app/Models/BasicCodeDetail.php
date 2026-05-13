<?php

namespace App\Models;

use App\Models\Master_Data\Barang;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BasicCodeDetail extends Model
{
    use HasFactory;

    protected $table = 'basic_code_detail';

    protected $id = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $guarded = [];

    public function codedetail(): BelongsTo
    {
        return $this->belongsTo(BasicCodeMaster::class, 'master_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function master()
    {
        return $this->belongsTo(BasicCodeMaster::class, 'master_id', 'id');
    }

    public function barang_category()
    {
        return $this->hasMany(Barang::class, 'kategori_id', 'id');
    }

     public function barang_unit()
    {
        return $this->hasMany(Barang::class, 'unit_id', 'id');
    }
}
