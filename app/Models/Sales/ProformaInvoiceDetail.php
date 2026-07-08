<?php

namespace App\Models\Sales;

use App\Models\BasicCodeDetail;
use App\Models\Inventory\Barang;
use App\Models\Inventory\Warehouse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProformaInvoiceDetail extends Model
{
    use HasFactory;

    protected $table = 'proforma_invoice_detail';

    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $year = date('Y');
        $this->table = "proforma_invoice_detail_{$year}";
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

    public function warehouseID()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id');
    }

    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class, 'sales_invoice_id');
    }

    public function salesQuotationDetail()
    {
        return $this->belongsTo(SalesQuotationDetail::class, 'sales_quotation_detail_id', 'id');
    }
}
