<?php

namespace App\Services;

use App\Models\Inventory\Barang;
use App\Models\Inventory\DataBarangConversion;
use App\Models\Setting\Company;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Menghitung stok real berdasarkan mutasi.
     */
    public function realStock($productId, $warehouseId, $unitId, $cutoffDate = null)
    {
        $today = now()->format('Y-m-d');

        if (! $cutoffDate) {
            $cutoffDate = Company::value('cut_off_date');
        }

        $startDate = $cutoffDate ?? '2000-01-01';

        $barang = Barang::find($productId);

        if (! $barang) {
            return 0;
        }

        // stok dalam BASE UNIT
        $stock = DB::table('stock_mutations')
            ->where('data_barang_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->whereBetween('date_stock', [$startDate, $today])
            ->selectRaw("
            COALESCE(SUM(
                CASE
                    WHEN type='in' THEN total_base_qty
                    WHEN type='out' THEN -total_base_qty
                    ELSE 0
                END
            ),0) stock
        ")
            ->value('stock');

        // jika pilih satuan dasar
        if ((int) $unitId === (int) $barang->unit_id) {
            return $stock;
        }

        // cari konversi
        $conversion = DataBarangConversion::where('data_barang_id', $productId)
            ->where('from_unit_id', $unitId)
            ->where('to_unit_id', $barang->unit_id)
            ->first();

        if (! $conversion || $conversion->qty <= 0) {
            return 0;
        }

        return round(
            $stock / $conversion->qty,
            2
        );
    }
}
