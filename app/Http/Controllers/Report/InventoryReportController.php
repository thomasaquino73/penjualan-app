<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Barang;
use App\Models\Inventory\DataBarangConversion;
use App\Models\Setting\Company;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InventoryReportController extends Controller
{
    public function index()
    {
        $x = [
            'title' => 'Inventory Report List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Inventory Report', 'url' => ''],
            ],
            'barangs' => Barang::where('status', 2)->get(),
        ];

        return view('report.inventory-report.inventory-report-index', $x);
    }

    public function printMutation(Request $request)
    {
        $request->validate([
            'barang_id' => 'required|exists:data_barang,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $detail = Barang::with([
            'variants',
            'stockHistories.warehouseID',
            'stockHistories.unitID',
            'mutations.unitID',
        ])->findOrFail($request->barang_id);

        $startDate = Carbon::parse($request->start_date)->format('Y-m-d');
        $endDate = Carbon::parse($request->end_date)->format('Y-m-d');

        $cutOffDate = Company::value('cut_off_date');

        /*
        |--------------------------------------------------------------------------
        | OPENING BALANCE
        |--------------------------------------------------------------------------
        */
        $openingBalance = 0;

        $beforeMutations = $detail->mutations()
            ->when($cutOffDate, function ($q) use ($cutOffDate) {
                $q->whereDate('date_stock', '>=', $cutOffDate);
            })
            ->whereDate('date_stock', '<', $startDate)   // sebelum periode
            ->orderBy('date_stock')
            ->orderBy('id')
            ->get();

        $saldo = 0;

        foreach ($beforeMutations as $mutation) {

            if ($mutation->type == 'in') {
                $saldo += $mutation->total_base_qty;
            } else {
                $saldo -= $mutation->total_base_qty;
            }
        }

        $openingBalance = $saldo;

        /*
        |--------------------------------------------------------------------------
        | MUTASI SESUAI PERIODE
        |--------------------------------------------------------------------------
        */
        $mutations = $detail->mutations()
            ->with('unitID')
            ->when($cutOffDate, function ($q) use ($cutOffDate) {
                $q->whereDate('date_stock', '>=', $cutOffDate);
            })
            ->whereBetween('date_stock', [
                $startDate,
                $endDate,
            ])
            ->orderBy('date_stock')
            ->orderBy('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | RUNNING BALANCE
        |--------------------------------------------------------------------------
        */
        $saldo = $openingBalance;

        foreach ($mutations as $mutation) {

            if ($mutation->type == 'in') {
                $saldo += $mutation->total_base_qty;
                $mutation->qty_in = $mutation->qty_transaksi;
                $mutation->qty_out = null;
            } else {
                $saldo -= $mutation->total_base_qty;
                $mutation->qty_in = null;
                $mutation->qty_out = $mutation->qty_transaksi;
            }

            $mutation->saldo_akhir = $saldo;
        }

        $currentStock = $saldo;

        /*
        |--------------------------------------------------------------------------
        | UNIT CONVERSION
        |--------------------------------------------------------------------------
        */
        $unitConversion = DataBarangConversion::where(
            'data_barang_id',
            $detail->id
        )->where('qty', '>', 0)->get();

        $pdf = Pdf::loadView('pdf.report.mutasi_barang_pdf', [
            'title' => 'Laporan Mutasi Barang',
            'detail' => $detail,
            'mutations' => $mutations,
            'unitConversion' => $unitConversion,
            'openingBalance' => $openingBalance,
            'currentStock' => $currentStock,
            'cutOffDate' => $cutOffDate,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);

        return $pdf->stream('mutasi-barang.pdf');
    }
}
