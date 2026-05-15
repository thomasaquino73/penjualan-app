<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Master_Data\Customer;
use App\Models\Master_Data\Salesman;
use App\Models\Transaction\PurchaseRequisition;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class PurchaseRequisitionController extends Controller
{
     public function index(Request $r)
    {
        if ($r->ajax()) {
            $query = PurchaseRequisition::where('active', '<>', 0)->get();

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
            'title' => 'Purchase Requisition List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Purchase Requisition', 'url' => ''],
            ],
        ];

        return view('transaction.purchase_requisition.purchase_requisition_index', $x);
    }

    private function generateNumberId()
    {
        $last = PurchaseRequisition::whereNotNull('code')
            ->orderBy('id', 'desc')
            ->first();

        if (! $last) {
            return 'PR-0001';
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
            'title' => 'Purchase Requisition New',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Purchase Requisition', 'url' => ''],
            ],
            'customer'=>Customer::where('status','<>',0)->get(),
            'idNumber' => $this->generateNumberId(),

        ];

        return view('transaction.purchase_requisition.purchase_requisition_create', $x);
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

     public function trash(Request $r)
    {
        if ($r->ajax()) {
            $query = PurchaseRequisition::where('active',  0)->get();

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
            'title' => 'Deleted Purchase Requisition ',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Purchase Requisition', 'url' => ''],
            ],
        ];

        return view('transaction.purchase_requisition.purchase_requisition_trash', $x);
    }
}
