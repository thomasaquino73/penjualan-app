<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Sales\ArApHistory;
use App\Models\Sales\Customer;
use App\Models\Setting\Company;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SalesReportController extends Controller
{
    public function index()
    {
        $x = [
            'title' => 'Sales Report List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Sales Report', 'url' => ''],
            ],
            'customers' => Customer::where('status', 1)->get(),
        ];

        return view('report.sales-report.sales-report-index', $x);
    }

    public function printPiutang(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $company = Company::first();
        $startDate = Carbon::parse($request->start_date)->format('Y-m-d');
        $endDate = Carbon::parse($request->end_date)->format('Y-m-d');

        $query = ArApHistory::with('customer')
            ->where('type', 'payable')
            ->whereBetween('transaction_date', [
                $startDate,
                $endDate,
            ])
            ->orderBy('party_id')
            ->orderBy('transaction_date')
            ->orderBy('id');

        if ($request->filled('customer_id')) {
            $query->where('party_id', $request->customer_id);
        }

        $histories = $query->get();

        $grouped = $histories->groupBy('party_id');

        $pdf = Pdf::loadView('pdf.report.piutang_customer_pdf', [
            'company' => $company,
            'groups' => $grouped,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return $pdf->stream('Laporan Piutang Customer.pdf');
    }
}
