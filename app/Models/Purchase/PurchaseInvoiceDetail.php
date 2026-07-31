<?php

namespace App\Models\Purchase;

use App\Models\BasicCodeDetail;
use App\Models\Inventory\Barang;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseInvoiceDetail extends Model
{
    use HasFactory;

    protected $table = 'purchase_invoice_detail';

    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $year = date('Y');
        $this->table = "purchase_invoice_detail_{$year}";
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

    public function purchaseOrderDetail()
    {
        return $this->belongsTo(PurchaseOrderDetail::class, 'purchase_order_detail_id', 'id');
    }

    public function purchaseInvoice()
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id');
    }
}
