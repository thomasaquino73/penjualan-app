<?php

namespace App\Http\Controllers;

use App\Models\Inventory\Barang;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $data = User::query();
        $stats = $this->getUserStatistics($data);
        $minStock = $this->getMinimumStock();
        $transaksiTerbanyak = $this->getTransaksiTerbanyak();
        $TotalTransactions = $this->getTotalTransactions();
        $x = [
            'totalUsers' => $stats['totalUsers'],
            'totalActive' => $stats['totalActive'],
            'totalVerified' => $stats['totalVerified'],
            'totalLogin' => $stats['totalLogin'],
            'minStock' => $minStock,
            'transaksiTerbanyak' => $transaksiTerbanyak,
            'TotalTransactions' => $TotalTransactions,
        ];

        return view('dashboard', $x);
    }

    private function getUserStatistics($data)
    {
        $allUsers = $data->get();

        return [
            'totalUsers' => User::where('active', 1)->count(),

            'totalActive' => User::where('status', 'Active')
                ->where('active', 1)
                ->count(),

            'totalVerified' => User::whereNotNull('email_verified_at')
                ->where('active', 1)
                ->count(),

            'totalLogin' => $allUsers->filter(function ($user) {
                return Cache::has('user-is-online-'.$user->id);
            })->count(),
        ];
    }

    private function getMinimumStock()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        return Barang::query()
            ->join('stock_mutations', 'data_barang.id', '=', 'stock_mutations.data_barang_id')
            ->where('data_barang.status', 2)
            ->whereMonth('stock_mutations.date_stock', $currentMonth)
            ->whereYear('stock_mutations.date_stock', $currentYear)
            ->select(
                'data_barang.id',
                'data_barang.nama_barang',
                'data_barang.id_barang',
                'data_barang.primary_minimum_stock',
                DB::raw("
                SUM(
                    CASE
                        WHEN stock_mutations.type = 'in'
                        THEN stock_mutations.total_base_qty
                        ELSE -stock_mutations.total_base_qty
                    END
                ) as current_stock
            ")
            )
            ->groupBy(
                'data_barang.id',
                'data_barang.nama_barang',
                'data_barang.id_barang',
                'data_barang.primary_minimum_stock'
            )
            ->havingRaw('current_stock <= primary_minimum_stock')
            ->orderBy('current_stock')
            ->limit(10)
            ->get();
    }

    private function getTransaksiTerbanyak()
    {
        $year = now()->year;
        $tableDetail = "sales_order_detail_{$year}";
        $tableHeader = "sales_order_{$year}";

        return DB::table("{$tableDetail} as pod")
            ->join("{$tableHeader} as po", 'po.id', '=', 'pod.sales_order_id')
            ->join('data_barang as b', 'b.id', '=', 'pod.product_id')
            ->join('basic_code_detail as c', 'c.id', '=', 'b.unit_id')
            ->where('pod.active', 1)
            ->whereMonth('po.sales_order_date', now()->month)
            ->whereYear('po.sales_order_date', now()->year)
            ->select(
                'b.id as product_id',
                'b.nama_barang',
                'b.photo_filename',
                'c.detail as unit_name',
                DB::raw('SUM(pod.qty) as total_qty'),
                DB::raw('COUNT(DISTINCT po.id) as total_transaksi')
            )
            ->groupBy(
                'b.id',
                'b.nama_barang',
                'b.photo_filename',
                'c.detail'
            )
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();
    }

    private function getTotalTransactions()
    {
        $year = now()->year;
        $table = "sales_order_{$year}";

        return DB::table($table)
            ->whereIn('status', ['approved', 'completed'])
            ->whereMonth('sales_order_date', now()->month)
            ->whereYear('sales_order_date', now()->year)
            ->where('active', 1)
            ->count();
    }
}
