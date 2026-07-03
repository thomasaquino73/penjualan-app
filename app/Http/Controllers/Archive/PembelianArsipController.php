<?php

namespace App\Http\Controllers\Archive;

use App\Http\Controllers\Controller;
use App\Models\Purchase\PurchaseRequisition;
use App\Models\Setting\Company;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Yajra\DataTables\Facades\DataTables;

class PembelianArsipController extends Controller
{
    public function indexPurchaseRequisition()
    {

        $x = [
            'title' => 'Arsip Permintaan Pembelian',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Arsip Permintaan Pembelian', 'url' => ''],
            ],
        ];

        return view('archive.pembelian.purchase-requisition', $x);
    }

    public function tabelPurchaseRequisition(Request $request)
    {
        if (! $request->filled('year')) {
            return DataTables::of([])->make(true);
        }

        $year = $request->year;
        $table = "purchase_requisition_{$year}";

        if (! Schema::hasTable($table)) {
            return DataTables::of([])->make(true);
        }

        $query = DB::table($table.' as pr')
            ->leftJoin('users as creator', 'creator.id', '=', 'pr.created_by')
            ->leftJoin('users as updater', 'updater.id', '=', 'pr.updated_by')
            ->select([
                'pr.*',
                'creator.fullname as creator_name',
                'updater.fullname as updater_name',
            ])
            ->orderBy('pr.code', 'desc');

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
                return $row->date ? Carbon::parse($row->date)->format('d M Y') : 'N/A';
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
                        <a href="'.route('archive.purchase-requisition.print', [
                    'year' => $year,
                    'id' => $row->id,
                ]).'" target="_blank" class="btn btn-sm btn-danger">
                            <i class="fa fa-print"></i>
                        </a>
                    ';
            })
            ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'date'])
            ->make(true);
    }

    public function printPurchaseRequisition($year, $id)
    {
        $table = "purchase_requisition_{$year}";

        if (! Schema::hasTable($table)) {
            abort(404, 'Tabel tidak ditemukan.');
        }

        $purchaseRequisition = new PurchaseRequisition;
        $purchaseRequisition->setTable($table);

        $detail = $purchaseRequisition->newQuery()
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
            'pdf.purchase_requisition_pdf',
            compact(
                'detail',
                'company',
                'logoBase64',
            )
        )->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'chroot' => [public_path()],
        ]);

        $fileName = str_replace('/', '-', $detail->code).'.pdf';

        return $pdf->download($fileName);
    }
}
