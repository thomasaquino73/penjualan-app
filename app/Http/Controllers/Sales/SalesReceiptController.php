<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;

class SalesReceiptController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $routeName = $request->route()->getName();

            $permissionMap = [
                'sales-receipt.index' => 'sales_receipt-browse',
                'sales-receipt.show' => 'sales_receipt-read',
                'sales-receipt.create' => 'sales_receipt-create',
                'sales-receipt.store' => 'sales_receipt-create',
                'sales-receipt.edit' => 'sales_receipt-edit',
                'sales-receipt.update' => 'sales_receipt-edit',
                'sales-receipt.destroy' => 'sales_receipt-delete',
                'sales-receipt.trash' => 'sales_receipt-trash',
                'sales-receipt.restore' => 'sales_receipt-restore',
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
            'title' => 'Sales Receipt List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Sales Receipt', 'url' => ''],
            ],

        ];

        return view('sales.salesReceipt.sales_receipt_index', $x);
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

        $prefix = "SR/{$tahun}/{$bulanRomawi}/";

        $last = SalesQuotation::where('sales_quotation_code', 'like', $prefix.'%')
            ->orderByRaw("
            CAST(
                REGEXP_REPLACE(
                    SUBSTRING_INDEX(sales_quotation_code,'/',-1),
                    '[^0-9]',
                    ''
                ) AS UNSIGNED
            ) DESC
        ")
            ->first();

        if ($last) {
            preg_match('/(\d+)/', substr($last->sales_quotation_code, strrpos($last->sales_quotation_code, '/') + 1), $match);
            $lastNumber = isset($match[1]) ? (int) $match[1] : 0;
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
    public function create()
    {
        $x = [
            'title' => 'Sales Receipt List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Sales Receipt', 'url' => ''],
            ],

        ];

        return view('sales.salesReceipt.sales_receipt_index', $x);
    }


}
