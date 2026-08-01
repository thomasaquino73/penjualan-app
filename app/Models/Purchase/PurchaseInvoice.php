<?php

namespace App\Models\Purchase;

use App\Models\BasicCodeDetail;
use App\Models\Inventory\Barang;
use App\Models\Setting\SyaratPembayaran;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseInvoice extends Model
{
    use HasFactory;

    protected $table = 'purchase_invoice';

    protected $guarded = [];

    protected $casts = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $year = date('Y');
        $this->table = "purchase_invoice_{$year}";
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function details()
    {
        return $this->hasMany(PurchaseInvoiceDetail::class, 'purchase_invoice_id');
    }

    public function PurchaseInvoiceDetail()
    {
        return $this->hasMany(PurchaseInvoiceDetail::class, 'purchase_invoice_id');
    }

    public function produkID()
    {
        return $this->belongsTo(Barang::class, 'product_id');
    }

    public function unitID()
    {
        // Sesuaikan nama class Unit dengan model master unit Anda
        return $this->belongsTo(BasicCodeDetail::class, 'unit_id', 'id');
    }

    public function paymentTermID()
    {
        return $this->belongsTo(SyaratPembayaran::class, 'payment_term');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id', 'id');
    }
}
