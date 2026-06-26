<?php

namespace App\Models\Sales;

use App\Models\BasicCodeDetail;
use App\Models\Inventory\Barang;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreSalesDetail extends Model
{
    use HasFactory;

    protected $table = 'store_sales_detail';

    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $year = date('Y');
        $this->table = "store_sales_detail_{$year}";
    }
    public function produkID()
    {
        return $this->belongsTo(Barang::class, 'product_id', 'id');
    }

    public function unitID()
    {
        // Sesuaikan nama class Unit dengan model master unit Anda
        return $this->belongsTo(BasicCodeDetail::class, 'unit_id', 'id');
    }
}
