<?php

namespace App\Services;

use App\Models\Setting\Company;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Menghitung stok real berdasarkan mutasi.
     */
    public function realStock($productId, $warehouseId, $unitId)
    {
        $company = Company::first();

        $cutoffDate = $company?->cut_off_date ?? '1900-01-01';

        return DB::table('stock_mutations')
            ->where('data_barang_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->where('unit_id', $unitId)
            ->whereDate('date_stock', '>=', $cutoffDate)
            ->selectRaw("
                COALESCE(
                    SUM(
                        CASE
                            WHEN type='in'
                            THEN total_base_qty
                            ELSE -total_base_qty
                        END
                    ),
                0) AS stock
            ")
            ->value('stock');
    }
}
