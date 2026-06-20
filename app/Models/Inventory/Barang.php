<?php

namespace App\Models\Inventory;

use App\Models\BasicCodeDetail;
use App\Models\Purchase\PurchaseOrderDetail;
use App\Models\Setting\Company;
use App\Models\StockMutation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    protected $table = 'data_barang';

    protected $guarded = [];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function kategoriID()
    {
        return $this->belongsTo(BasicCodeDetail::class, 'kategori_id');
    }

    public function warehouseID()
    {
        return $this->belongsTo(Warehouse::class, 'gudang_id');
    }

    public function brandID()
    {
        return $this->belongsTo(BasicCodeDetail::class, 'brand_id');
    }

    public function typeID()
    {
        return $this->belongsTo(BasicCodeDetail::class, 'tipe_persediaan_id');
    }

    public function unitID()
    {
        return $this->belongsTo(BasicCodeDetail::class, 'unit_id');
    }

    public function conversions()
    {
        return $this->hasMany(DataBarangConversion::class, 'data_barang_id');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'data_barang_id');
    }

    public function stockHistories()
    {
        return $this->hasMany(DataBarangStok::class, 'data_barang_id');
    }

    public function poDetails()
    {
        // Parameter: NamaModelDetail, foreign_key_di_tabel_detail
        return $this->hasMany(PurchaseOrderDetail::class, 'product_id');
    }

    public function mutations()
    {
        return $this->hasMany(StockMutation::class, 'data_barang_id');
    }

    public function getPriceHistory()
    {
        return $this->poDetails()
            ->select('unit_price') // Ambil kolom harga dari tabel detail
            ->distinct()           // Berfungsi agar harga yang sama tidak duplikat
            ->latest('created_at') // Urutkan berdasarkan transaksi terbaru di tabel detail
            ->limit(5)             // Ambil 5 harga unik terakhir saja
            ->pluck('unit_price'); // Mengubah hasil query langsung menjadi array [50000, 48000, ...]
    }

    public function getCurrentStockAttribute()
    {
        $cutOffDate = Company::value('cut_off_date');

        return $this->mutations()
            ->when($cutOffDate, function ($q) use ($cutOffDate) {
                $q->whereDate('date_stock', '>=', $cutOffDate);
            })
            ->selectRaw("
                COALESCE(
                    SUM(
                        CASE
                            WHEN type = 'in'
                            THEN total_base_qty
                            ELSE -total_base_qty
                        END
                    ),
                0
                ) as total
            ")
            ->value('total') ?? 0;
    }

    public function stockBalances()
    {
        return $this->hasMany(StockBalance::class, 'product_id');
    }
}
