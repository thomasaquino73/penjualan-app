<?php

namespace App\Http\Controllers\Archive;

use App\Http\Controllers\Controller;
use App\Models\Sales\SalesQuotation;
use App\Models\Setting\Company;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Yajra\DataTables\Facades\DataTables;

class PenjualanArsipController extends Controller
{
    // sales quotation
    public function indexSalesQuotation()
    {

        $x = [
            'title' => 'Arsip Sales Quotation',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Arsip Sales Quotation', 'url' => ''],
            ],
        ];

        return view('archive.penjualan.sales-quotation', $x);
    }

    public function tabelSalesQuotation(Request $request)
    {
        if (! $request->filled('year')) {
            return DataTables::of([])->make(true);
        }

        $year = $request->year;
        $table = "sales_quotation_{$year}";

        if (! Schema::hasTable($table)) {
            return DataTables::of([])->make(true);
        }

        $query = DB::table($table.' as pr')
            ->leftJoin('customer as c', 'c.id', '=', 'pr.customer_id')
            ->leftJoin('users as creator', 'creator.id', '=', 'pr.created_by')
            ->leftJoin('users as updater', 'updater.id', '=', 'pr.updated_by')
            ->select([
                'pr.*',
                'c.nama_customer as customer_name',
                'creator.fullname as creator_name',
                'updater.fullname as updater_name',
            ])
            ->orderBy('pr.sales_quotation_code', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('created_at', function ($row) {
                if (! $row->created_at) {
                    return 'N/A';
                }

                return ($row->creator_name ?? 'Unknown')
                    .'<br><small class="text-muted">'
                    .Carbon::parse($row->created_at)->diffForHumans()
                    .'</small>';
            })
            ->addColumn('updated_at', function ($row) {
                if (! $row->updated_at) {
                    return 'N/A';
                }

                return ($row->updater_name ?? 'Unknown')
                    .'<br><small class="text-muted">'
                    .Carbon::parse($row->updated_at)->diffForHumans()
                    .'</small>';
            })
            ->addColumn('sales_quotation_date', function ($row) {
                return $row->sales_quotation_date ? Carbon::parse($row->sales_quotation_date)->format('d M Y') : 'N/A';
            })
            ->addColumn('total', function ($row) {
                return format_uang(convert_currency($row->grand_total, $row->currency_id ?? 1));
            })
            ->addColumn('customer', function ($row) {
                return $row->customer_name ?? '-';
            })
            ->addColumn('status', function ($row) {
                switch ($row->status) {
                    case 'draft':
                        $badge = 'bg-label-secondary';
                        $text = 'Draft';
                        break;

                    case 'processing':
                        $badge = 'bg-label-info';
                        $text = 'Processing';
                        break;

                        // ─── TAMBAHAN BADGE UNTUK STATUS PARTIAL ──────────────────
                    case 'partial':
                        $badge = 'bg-warning text-dark';
                        $text = 'Partial PO';
                        break;

                    case 'closed':
                        $badge = 'bg-dark';
                        $text = 'Closed';
                        break;

                    case 'cancelled':
                        $badge = 'bg-danger';
                        $text = 'Cancelled';
                        break;

                    default:
                        $badge = 'bg-label-secondary';
                        $text = ucfirst($row->status);
                        break;
                }

                return '<span class="badge '.$badge.' text-uppercase">'.$text.'</span>';
            })
            ->addColumn('action', function ($row) use ($year) {
                return '
                        <a href="'.route('archive.sales-quotation.print', [
                    'year' => $year,
                    'id' => $row->id,
                ]).'" target="_blank" class="btn btn-sm btn-danger">
                            <i class="fa fa-print"></i>
                        </a>
                    ';
            })
            ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'sales_quotation_date', 'total', 'customer'])
            ->make(true);
    }

    public function printSalesQuotation($year, $id)
    {
        $table = "sales_quotation_{$year}";

        if (! Schema::hasTable($table)) {
            abort(404, 'Tabel tidak ditemukan.');
        }

        $salesQuotation = new SalesQuotation;
        $salesQuotation->setTable($table);

        $model = $salesQuotation->newQuery()
            ->with([
                'details.produkID',
                'details.unitID',
                'creator',
                'updater',
            ])
            ->findOrFail($id);

        $company = Company::first();

        $logoBase64 = null;

        if ($company && $company->logo) {
            $path = public_path($company->logo);

            if (file_exists($path)) {
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($path);

                $logoBase64 = 'data:image/'.$type.';base64,'.base64_encode($data);
            }
        }

        $pdf = Pdf::loadView(
            'pdf.sales_quotation_pdf',
            compact(
                'model',
                'company',
                'logoBase64',
            )
        )->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'chroot' => [public_path()],
        ]);

        $fileName = str_replace('/', '-', $model->code).'.pdf';

        return $pdf->stream($fileName);
    }

    // sales order
    public function indexSalesOrder()
    {

        $x = [
            'title' => 'Arsip Sales Order',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Arsip Sales Order', 'url' => ''],
            ],
        ];

        return view('archive.penjualan.sales-order', $x);
    }

    public function tabelSalesOrder(Request $request)
    {
        if (! $request->filled('year')) {
            return DataTables::of([])->make(true);
        }

        $year = $request->year;
        $table = "sales_order_{$year}";

        if (! Schema::hasTable($table)) {
            return DataTables::of([])->make(true);
        }

            $query = DB::table($table.' as pr')
            ->leftJoin('customer as c', 'c.id', '=', 'pr.customer_id')
            ->leftJoin('users as creator', 'creator.id', '=', 'pr.created_by')
            ->leftJoin('users as updater', 'updater.id', '=', 'pr.updated_by')
            ->select([
                'pr.*',
                'c.nama_customer as customer_name',
                'creator.fullname as creator_name',
                'updater.fullname as updater_name',
            ])
            ->orderBy('pr.sales_order_code', 'desc');
        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('created_at', function ($row) {
                if (! $row->created_at) {
                    return 'N/A';
                }

                return ($row->creator_name ?? 'Unknown')
                    .'<br><small class="text-muted">'
                    .Carbon::parse($row->created_at)->diffForHumans()
                    .'</small>';
            })
            ->addColumn('updated_at', function ($row) {
                if (! $row->updated_at) {
                    return 'N/A';
                }

                return ($row->updater_name ?? 'Unknown')
                    .'<br><small class="text-muted">'
                    .Carbon::parse($row->updated_at)->diffForHumans()
                    .'</small>';
            })
            ->addColumn('date', function ($row) {
                return $row->sales_order_date ? Carbon::parse($row->sales_order_date)->format('d M Y') : 'N/A';
            })
            ->addColumn('tanggal_pengiriman', function ($row) {
                return $row->tanggal_pengiriman ? Carbon::parse($row->tanggal_pengiriman)->format('d M Y') : 'N/A';
            })
            ->addColumn('status', function ($row) {
                switch ($row->status) {
                    case 'draft':
                        $badge = 'bg-label-secondary';
                        $text = 'Draft';
                        break;

                    case 'processing':
                        $badge = 'bg-label-info';
                        $text = 'Processing';
                        break;

                        // ─── TAMBAHAN BADGE UNTUK STATUS PARTIAL ──────────────────
                    case 'partial':
                        $badge = 'bg-warning text-dark';
                        $text = 'Partial PO';
                        break;

                    case 'closed':
                        $badge = 'bg-dark';
                        $text = 'Closed';
                        break;

                    case 'cancelled':
                        $badge = 'bg-danger';
                        $text = 'Cancelled';
                        break;

                    default:
                        $badge = 'bg-label-secondary';
                        $text = ucfirst($row->status);
                        break;
                }

                return '<span class="badge '.$badge.' text-uppercase">'.$text.'</span>';
            })
            ->addColumn('amount', function ($row) {
                return format_uang(convert_currency($row->grand_total, $row->currency_id ?? 1));
            })
             ->addColumn('customer', function ($row) {
                return $row->customer_name ?? '-';
            })
            ->addColumn('action', function ($row) use ($year) {
                return '
                        <a href="'.route('archive.purchase-requisition.print', [
                    'year' => $year,
                    'id' => $row->id,
                ]).'" target="_blank" class="btn btn-sm btn-danger">
                            <i class="fa fa-print"></i>
                        </a>
                    ';
            })
            ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'date','customer','tanggal_pengiriman','amount'])
            ->make(true);
    }
}
