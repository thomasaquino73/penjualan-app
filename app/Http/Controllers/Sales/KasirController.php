<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\Customer;
use App\Models\Setting\Company;
use App\Models\Setting\Shipping;
use App\Models\Setting\SyaratPembayaran;
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
        $x=[];
        return view('sales.kasir.kasir_index',$x);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
   {
         $company = Company::with('defaultCurrency')->first();

        return view('sales.kasir.kasir_create', [
            'title' => 'Add Product',
            
            'mataUangDefault' => $company->defaultCurrency,
            'payment' => SyaratPembayaran::where('status', '<>', 0)->get(),
            'shipping' => Shipping::where('status', 1)->get(),
            'customer' => Customer::where('status', '<>', 0)->get(),
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
