<?php

namespace App\Models\Purchase;

use App\Models\Purchase\PurchaseOrderDetail;
use App\Models\Setting\Shipping;
use App\Models\Setting\SyaratPembayaran;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $table = 'purchase_order';

    protected $guarded = [];

    protected $casts = [
        'expected_date' => 'datetime',
        'date' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $year = date('Y');
        $this->table = "purchase_order_{$year}";
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
        return $this->hasMany(PurchaseOrderDetail::class, 'purchase_order_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'pic_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'pic_by');
    }

    public function paymentTerm()
    {
        return $this->belongsTo(SyaratPembayaran::class, 'payment_term');
    }

    public function ship()
    {
        return $this->belongsTo(Shipping::class, 'vehicle_id');
    }
    public function purchaseRequisition()
    {
        // Sesuaikan 'purchase_requisition_id' dengan nama kolom foreign key yang ada di tabel PO kamu
        return $this->belongsTo(PurchaseRequisition::class, 'purchase_requisition_id', 'id');
    }
}
