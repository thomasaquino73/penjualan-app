<?php

namespace App\Models\Sales;

use App\Models\BasicCodeDetail;
use App\Models\Inventory\Barang;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesQuotationDetail extends Model
{
    use HasFactory;

    protected $table = 'sales_quotation_detail';

    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $year = date('Y');
        $this->table = "sales_quotation_detail_{$year}";
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
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

    public function salesOrderDetails()
    {
        return $this->hasMany(SalesOrderDetail::class, 'sales_quotation_detail_id');
    }

    public function quotation()
    {
        return $this->belongsTo(
            SalesQuotation::class,
            'sales_quotation_id',
            'id'
        );
    }
}
