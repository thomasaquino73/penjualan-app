<?php

namespace App\Models\Master_Data;

use App\Models\BasicCodeDetail;
use App\Models\Transaction\PurchaseOrderDetail;
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

    public function getPriceHistory()
    {
        return $this->poDetails()
            ->select('unit_price') // Ambil kolom harga dari tabel detail
            ->distinct()           // Berfungsi agar harga yang sama tidak duplikat
            ->latest('created_at') // Urutkan berdasarkan transaksi terbaru di tabel detail
            ->limit(5)             // Ambil 5 harga unik terakhir saja
            ->pluck('unit_price'); // Mengubah hasil query langsung menjadi array [50000, 48000, ...]
    }
}
