<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Barang;
use App\Models\Inventory\Warehouse;
use App\Models\Sales\Customer;
use App\Models\Sales\StoreSales;
use App\Models\Setting\Company;
use App\Models\Setting\Shipping;
use App\Models\Setting\SyaratPembayaran;
use App\Models\Setting\Tax;
use Illuminate\Http\Request;

class KasirController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $routeName = $request->route()->getName();

            $permissionMap = [
                'penjualan-toko.index' => 'penjualan_toko-browse',
                'penjualan-toko.show' => 'penjualan_toko-read',
                'penjualan-toko.create' => 'penjualan_toko-create',
                'penjualan-toko.store' => 'penjualan_toko-create',
                'penjualan-toko.edit' => 'penjualan_toko-edit',
                'penjualan-toko.update' => 'penjualan_toko-edit',
                'penjualan-toko.destroy' => 'penjualan_toko-delete',
                'penjualan-toko.trash' => 'penjualan_toko-trash',
                'penjualan-toko.restore' => 'penjualan_toko-restore',
            ];

            if (isset($permissionMap[$routeName])) {
                if (! $request->user()->can($permissionMap[$routeName])) {
                    abort(403, 'Unauthorized action');
                }
            }

            return $next($request);
        });
    }

    public function index()
    {
        $x = [
            'title' => 'Store Sales List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Store Sales', 'url' => ''],
            ],
        ];

        return view('sales.kasir.kasir_index', $x);
    }

    public function bulanRomawi($bulan)
    {
        $romawi = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];

        return $romawi[$bulan] ?? 'I';
    }

    private function generateNumberId()
    {
        $tahun = date('Y');
        $bulan = date('n');
        $bulanRomawi = $this->bulanRomawi($bulan);

        // Prefix yang akan dicari
        $prefix = "TRX/{$tahun}/{$bulanRomawi}/";

        // Ambil nomor terakhir pada bulan & tahun yang sama
        $last = StoreSales::where('store_sales_code', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();

        if ($last) {
            // Ambil 4 digit terakhir
            $lastNumber = (int) substr($last->store_sales_code, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            // Jika belum ada pada bulan ini mulai dari 0001
            $nextNumber = 1;
        }

        return $prefix.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function create()
    {
        $company = Company::with('defaultCurrency')->first();
        $taxes = Tax::where('is_active', true)
            ->whereIn('usage', ['purchase', 'both'])
            ->get();

        return view('sales.kasir.kasir_create', [
            'title' => 'Add Product',
            'idNumber' => $this->generateNumberId(),
            'mataUangDefault' => $company->defaultCurrency,
            'payment' => SyaratPembayaran::where('status', '<>', 0)->get(),
            'shipping' => Shipping::where('status', 1)->get(),
            'customer' => Customer::where('status', '<>', 0)->get(),
            'product' => Barang::where('status', '<>', 0)->get(),
            'warehouse' => Warehouse::where('status', '<>', 0)->get(),
            'taxes' => $taxes,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
