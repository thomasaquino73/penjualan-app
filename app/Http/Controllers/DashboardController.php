<?php

namespace App\Http\Controllers;

use App\Models\Inventory\Barang;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Tahun yang dipilih (default = tahun sekarang)
        $currentYear = date('Y');
        $year = $request->get('year', $currentYear);
        $lastYear = $currentYear - 1;

        // Statistik penjualan berdasarkan tahun yang dipilih
        $salesChart = $this->getSalesStatisticsByYear($year);
        $totalSales = $this->getTotalSalesByYear($year);

        // Statistik user
        $data = User::query();
        $stats = $this->getUserStatistics($data);

        // Minimum stock
        $minStock = $this->getMinimumStock();
        $getMostBuyers = $this->getMostBuyers();

        // Produk transaksi terbanyak
        $transaksiTerbanyak = $this->getTransaksiTerbanyak();

        // Total transaksi bulan ini
        $TotalTransactions = $this->getTotalTransactions();

        // Brand populer bulan ini
        $popularBrands = $this->getPopularBrandThisMonth();
        $brandLabels = $popularBrands->pluck('brand_name');
        $brandValues = $popularBrands->pluck('total_qty');

        if ($brandLabels->isEmpty()) {
            $brandLabels = collect(['No Data']);
            $brandValues = collect([0]);
        }

        return view('dashboard', [
            // User
            'totalUsers' => $stats['totalUsers'],
            'totalActive' => $stats['totalActive'],
            'totalVerified' => $stats['totalVerified'],
            'totalLogin' => $stats['totalLogin'],

            // Stock
            'minStock' => $minStock,
            'mostBuyers' => $getMostBuyers,

            // Transaksi
            'transaksiTerbanyak' => $transaksiTerbanyak,
            'TotalTransactions' => $TotalTransactions,

            // Brand
            'popularBrands' => $popularBrands,
            'brandLabels' => $brandLabels,
            'brandValues' => $brandValues,

            // Sales Chart
            'salesStatistics' => $salesChart,
            'salesLabels' => $salesChart['labels'],
            'salesValues' => $salesChart['values'],

            // Total Sales
            'totalSales' => $totalSales,

            // Tahun
            'selectedYear' => $year,
            'currentYear' => $currentYear,
            'lastYear' => $lastYear,
            'totalSalesThisYear' => $this->getTotalSalesByYear($currentYear),
            'totalSalesLastYear' => $this->getTotalSalesByYear($lastYear),
        ]);
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
            // ->whereMonth('stock_mutations.date_stock', $currentMonth)
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
            ->whereIn('po.status', ['processing', 'completed'])
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
            ->whereIn('status', ['processing', 'completed', 'closed', 'fully_delivered'])
            ->whereMonth('sales_order_date', now()->month)
            ->whereYear('sales_order_date', now()->year)
            ->where('active', 1)
            ->count();
    }

    private function getPopularBrandThisMonth()
    {
        $year = now()->year;

        $detailTable = "sales_invoice_detail_{$year}";
        $headerTable = "sales_invoice_{$year}";

        return DB::table("$detailTable as sid")
            ->join("$headerTable as si", 'sid.sales_invoice_id', '=', 'si.id')
            ->join('data_barang as db', 'sid.product_id', '=', 'db.id')

            // JOIN BRAND dari basic_code_detail (master_id = 11)
            ->leftJoin('basic_code_detail as brand', function ($join) {
                $join->on('db.brand_id', '=', 'brand.id')
                    ->where('brand.master_id', '=', 11);
            })

            ->whereMonth('si.sales_invoice_date', now()->month)
            ->whereYear('si.sales_invoice_date', now()->year)

            ->select(
                'brand.id as brand_id',
                DB::raw('COALESCE(brand.detail, "No Brand") as brand_name'),
                DB::raw('SUM(sid.qty) as total_qty')
            )

            ->groupBy('brand.id', 'brand.detail')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();
    }

    private function getSalesStatisticsThisYear()
    {
        $year = date('Y');
        $table = "sales_order_{$year}";

        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $values = array_fill(0, 12, 0);

        if (! DB::getSchemaBuilder()->hasTable($table)) {
            return compact('labels', 'values');
        }

        $rows = DB::table($table)
            ->selectRaw('MONTH(sales_order_date) as month, SUM(grand_total) as total')
            ->where('active', 1)
            ->whereIn('status', ['processing', 'completed', 'closed', 'fully_delivered'])
            ->groupBy(DB::raw('MONTH(sales_order_date)'))
            ->get();

        foreach ($rows as $row) {
            $values[$row->month - 1] = (float) $row->total;
        }

        return compact('labels', 'values');
    }

    private function getTotalSalesThisYear()
    {
        $year = date('Y');
        $table = "sales_order_{$year}";

        if (! DB::getSchemaBuilder()->hasTable($table)) {
            return 0;
        }

        return (float) DB::table($table)
            ->where('active', 1)
            ->whereIn('status', [
                'approved',
                'processing',
                'partial',
                'completed',
                'fully_delivered',
            ])
            ->sum('grand_total');
    }

    private function getTotalSalesByYear($year)
    {
        $table = "sales_order_{$year}";

        if (! DB::getSchemaBuilder()->hasTable($table)) {
            return 0;
        }

        return (float) DB::table($table)
            ->where('active', 1)
            ->whereIn('status', [
                'approved',
                'processing',
                'partial',
                'completed',
                'fully_delivered',
            ])
            ->sum('grand_total');
    }

    private function getSalesStatisticsByYear($year)
    {
        $table = "sales_order_{$year}";

        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $values = array_fill(0, 12, 0);

        if (! DB::getSchemaBuilder()->hasTable($table)) {
            return compact('labels', 'values');
        }

        $rows = DB::table($table)
            ->selectRaw('MONTH(sales_order_date) as month, SUM(grand_total) as total')
            ->where('active', 1)
            ->whereIn('status', ['approved', 'processing', 'partial', 'completed', 'fully_delivered'])
            ->groupBy(DB::raw('MONTH(sales_order_date)'))
            ->get();

        foreach ($rows as $row) {
            $values[$row->month - 1] = (float) $row->total;
        }

        return compact('labels', 'values');
    }

    public function salesStatistics(Request $request)
    {
        $year = $request->year ?? date('Y');

        $salesChart = $this->getSalesStatisticsByYear($year);
        $totalSales = $this->getTotalSalesByYear($year);

        return response()->json([
            'labels' => $salesChart['labels'],
            'values' => $salesChart['values'],
            'totalSales' => number_format($totalSales, 0, ',', '.'),
            'year' => $year,
        ]);
    }

    private function getMostBuyers()
    {
        $year = date('Y');
        $table = "sales_order_{$year}";

        return DB::table($table)
            ->join('customer', "{$table}.customer_id", '=', 'customer.id')
            ->select(
                'customer.nama_customer as customer_name',
                DB::raw("COUNT({$table}.id) as total_invoice"),
                DB::raw("SUM({$table}.grand_total) as total_amount")
            )
            ->whereNotNull("{$table}.customer_id")
            ->whereMonth("{$table}.sales_order_date", now()->month)
            ->whereYear("{$table}.sales_order_date", now()->year)
            ->groupBy('customer.id', 'customer.nama_customer')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get();
    }
}
