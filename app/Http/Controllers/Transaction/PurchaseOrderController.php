<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\BasicCodeDetail;
use App\Models\Master_Data\Customer;
use App\Models\Master_Data\Kendaraan;
use App\Models\Transaction\PurchaseOrder;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class PurchaseOrderController extends Controller
{
    public function index(Request $r)
    {
        if ($r->ajax()) {
            $query = PurchaseOrder::where('active', '<>', 0)->get();

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
                ->addColumn('status', function ($row) {
                        return '<span class="badge bg-info">Processing Queue</span>';
                })
                ->addColumn('customer', function ($row) {
                        return $row->customer->nama;
                })
                ->addColumn('cekbok', function ($row) {
                    return '   <div class="form-check form-check-primary mt-3">
                                <input class="form-check-input checkItem" type="checkbox" value="'.$row->id.'"
                                    >
                            </div>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">
                      <button type="button" class="btn btn-primary dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-menu-2 ti-xs me-1"></i>
                      </button>
                      <ul class="dropdown-menu" style="">';

                    if (auth()->user()->can('customer-edit')) {
                        $btn .= '<a class="dropdown-item editPost" href="javascript:void(0)"
                            data-id="'.$row->id.'"> <i class="far fa-edit"></i> Edit</a>';
                    }

                    if (auth()->user()->can('customer-delete')) {
                        $btn .= '<a class="dropdown-item" href="javascript:void(0)" id="delete"
                                data-id="'.$row->id.'"
                                data-name="'.$row->nama.'"
                                ><i class="ti ti-trash"></i> Delete</a>';
                    }

                    return $btn;
                })
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'cekbok','customer'])
                ->make(true);
        }

        $x = [
            'title' => 'Purchase Order List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Purchase Order', 'url' => ''],
            ],
        ];

        return view('transaction.purchase_order.purchase_order_index', $x);
    }

    private function generateNumberId()
    {
        $last = PurchaseOrder::whereNotNull('code')
            ->orderBy('id', 'desc')
            ->first();

        if (! $last) {
            return 'PO-0001';
        }

        $lastId = $last->id_barang;

        // 🔥 ambil angka terakhir
        preg_match('/(\d+)$/', $lastId, $matches);

        if (! $matches) {
            // kalau tidak ada angka → tambahin default
            return $lastId.'01';
        }

        $number = (int) $matches[1];
        $number++;

        // 🔥 ambil prefix tanpa angka
        $prefix = substr($lastId, 0, -strlen($matches[1]));

        // 🔥 padding mengikuti panjang angka sebelumnya
        $length = strlen($matches[1]);

        return $prefix.str_pad($number, $length, '0', STR_PAD_LEFT);
    }
    public function create()
    {
         $x = [
            'title' => 'Purchase Order New',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Purchase Order', 'url' => ''],
            ],
            'customer'=>Customer::where('status','<>',0)->get(),
            'idNumber' => $this->generateNumberId(),
            'kendaraan'=>Kendaraan::where('status','<>',0)->get(),
            'term'=>BasicCodeDetail::where('master_id',5)->get(),

        ];

        return view('transaction.purchase_order.purchase_order_create', $x);
    }
}
