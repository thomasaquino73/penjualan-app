<?php

namespace App\Observers;

use App\Models\Inventory\Barang;
use App\Models\Inventory\DataBarangConversion;
use App\Models\Inventory\StockBalance;
use App\Models\StockMutation;

class StockMutationObserver
{
    public function saved(StockMutation $stockMutation): void
    {
        $this->recalculateStock($stockMutation->data_barang_id);
    }

    public function deleted(StockMutation $stockMutation): void
    {
        $this->recalculateStock($stockMutation->data_barang_id);
    }

    private function recalculateStock(int $barangId): void
    {
        StockBalance::where('product_id', $barangId)->delete();

        $mutations = StockMutation::where('data_barang_id', $barangId)
            ->get();

        $warehouseStocks = [];
        $totalStock = 0;

        foreach ($mutations as $mutation) {

            $qtyBase = $mutation->total_base_qty;

            /**
             * Fallback jika total_base_qty kosong
             */
            if (empty($qtyBase) || $qtyBase == 0) {

                $conversion = DataBarangConversion::where(
                    'data_barang_id',
                    $barangId
                )
                    ->where(
                        'from_unit_id',
                        $mutation->unit_id
                    )
                    ->first();

                $rate = $conversion->qty ?? 1;

                $qtyBase = $mutation->qty_transaksi * $rate;
            }

            if ($mutation->type == 'out') {
                $qtyBase *= -1;
            }

            if (! isset($warehouseStocks[$mutation->warehouse_id])) {
                $warehouseStocks[$mutation->warehouse_id] = 0;
            }

            $warehouseStocks[$mutation->warehouse_id] += $qtyBase;

            $totalStock += $qtyBase;
        }

        foreach ($warehouseStocks as $warehouseId => $qty) {

            if ($qty <= 0) {
                continue;
            }

            StockBalance::create([
                'product_id' => $barangId,
                'warehouse_id' => $warehouseId,
                'qty' => $qty,
            ]);
        }

        $barang = Barang::find($barangId);

        if ($barang) {

            $barang->update([
                'quantity' => $totalStock,
                'is_low_stock' => $totalStock <= ($barang->primary_minimum_stock ?? 0),
            ]);
        }
    }
}
