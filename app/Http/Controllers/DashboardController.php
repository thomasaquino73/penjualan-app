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
        $x = [
            'totalUsers' => $stats['totalUsers'],
            'totalActive' => $stats['totalActive'],
            'totalVerified' => $stats['totalVerified'],
            'totalLogin' => $stats['totalLogin'],
            'minStock' => $minStock,
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
}
