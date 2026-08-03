<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Purchase\Supplier;
use App\Models\Sales\ArApHistory;
use App\Models\Setting\Company;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PurchaseReportController extends Controller
{
    public function index()
    {
        $x = [
            'title' => 'Purchase Report List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Purchase Report', 'url' => ''],
            ],
            'suppliers' => Supplier::where('status', 1)->get(),
        ];

        return view('report.purchase-report.purchase-report-index', $x);
    }

    public function printHutang(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $company = Company::first();
        $startDate = Carbon::parse($request->start_date)->format('Y-m-d');
        $endDate = Carbon::parse($request->end_date)->format('Y-m-d');

        $query = ArApHistory::with('supplier')
            ->where('type', 'payable')
            ->whereBetween('transaction_date', [
                $startDate,
                $endDate,
            ])
            ->orderBy('party_id')
            ->orderBy('transaction_date')
            ->orderBy('id');

        if ($request->filled('supplier_id')) {
            $query->where('party_id', $request->supplier_id);
        }

        $histories = $query->get();

        $grouped = $histories->groupBy('party_id');

        $pdf = Pdf::loadView('pdf.report.hutang_supplier_pdf', [
            'company' => $company,
            'groups' => $grouped,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return $pdf->stream('Laporan Hutang Supplier.pdf');
    }
}
