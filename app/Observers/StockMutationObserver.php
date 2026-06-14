<?php

namespace App\Observers;

use App\Models\Inventory\Barang;
use App\Models\StockMutation;

class StockMutationObserver
{
    public function saved(StockMutation $stockMutation): void
    {
        $barang = Barang::find($stockMutation->data_barang_id);

        if (! $barang) {
            return;
        }

        $currentStock = StockMutation::where(
            'data_barang_id',
            $barang->id
        )
            ->selectRaw("
            COALESCE(
                SUM(
                    CASE
                        WHEN type = 'in'
                        THEN total_base_qty
                        ELSE -total_base_qty
                    END
                ),0
            ) as total
        ")
            ->value('total');

        $barang->update([
            'quantity' => $currentStock,
            'is_low_stock' => $currentStock <= ($barang->primary_minimum_stock ?? 0),
        ]);
    }
}
