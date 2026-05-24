<?php

namespace App\Http\Controllers\Pengaturan\Company;

use App\Http\Controllers\Controller;
use App\Models\Pengaturan\SyaratPembayaran;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class SyaratPembayaranController extends Controller
{
    public function index(Request $r){

    if ($r->ajax()) {
            $query = SyaratPembayaran::orderBy('created_at', 'desc')->get();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('created_at', function ($row) {
                    return $row->created_at
                        ? (($row->creator->fullname ?? 'Unknown')).
                        ' <br><small class="text-muted"> '.$row->created_at->diffForHumans().'</small>'
                        : 'N/A';
                })
                ->addColumn('updated_at', function ($row) {
                    if ($row->updated_at) {
                        $updaterName = $row->updater->fullname ?? 'Unknown';
                        $timeAgo = $updaterName !== 'Unknown' ? $row->updated_at->diffForHumans() : 'N/A';

                        return $updaterName.
                            ' <br><small class="text-muted">'.$timeAgo.'</small>';
                    }

                    return 'N/A';
                })
                ->rawColumns(['created_at', 'updated_at'])
                ->make(true);
        }
        $x = [
            'title' => 'Payment Term List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Payment Term', 'url' => ''],
            ],
        ];

        return view('pengaturan.syarat_pembayaran.syarat_pembayaran_index', $x);
    }
}
