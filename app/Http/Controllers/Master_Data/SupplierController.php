<?php

namespace App\Http\Controllers\Master_Data;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupplierRequest;
use App\Models\Master_Data\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class SupplierController extends Controller
{
    public function index(Request $r)
    {
        if ($r->ajax()) {
            $query = Supplier::where('status', '<>',0)->get();

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
                    if ($row->status == 1) {
                        return '<span class="badge bg-info">Active</span>';
                    } else {
                        return '<span class="badge bg-danger">Not Active</span>';
                    }
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">
                      <button type="button" class="btn btn-primary dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">
                        Action
                      </button>
                      <ul class="dropdown-menu" style="">';

                    if (auth()->user()->can('supplier-edit')) {
                        $btn .= '<a class="dropdown-item editPost" href="javascript:void(0)"
                            data-id="'.$row->id.'"> <i class="far fa-edit"></i> Edit</a>';
                    }

                    if (auth()->user()->can('supplier-delete')) {
                        $btn .= '<a class="dropdown-item" href="javascript:void(0)" id="delete"
                                data-id="'.$row->id.'"
                                data-name="'.$row->nama.'"
                                ><i class="ti ti-trash"></i> Delete</a>';
                    }

                    return $btn;
                })
                ->rawColumns(['action', 'created_at', 'updated_at', 'status'])
                ->make(true);
        }

        $x = [
            'title' => 'Supplier List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Supplier', 'url' => ''],
            ],
        ];

        return view('master_data.supplier.supplier_index', $x);
    }

 private function generateSupplierId()
{
    $last = Supplier::whereNotNull('id_supplier')
        ->orderBy('id', 'desc')
        ->first();

    if (!$last) {
        return 'SUP-001';
    }

    $lastId = $last->id_supplier;

    // 🔥 ambil angka terakhir
    preg_match('/(\d+)$/', $lastId, $matches);

    if (!$matches) {
        // kalau tidak ada angka → tambahin default
        return $lastId . '01';
    }

    $number = (int) $matches[1];
    $number++;

    // 🔥 ambil prefix tanpa angka
    $prefix = substr($lastId, 0, -strlen($matches[1]));

    // 🔥 padding mengikuti panjang angka sebelumnya
    $length = strlen($matches[1]);

    return $prefix . str_pad($number, $length, '0', STR_PAD_LEFT);
}
   public function generateId()
{
    return response()->json([
        'id_supplier' => $this->generateSupplierId()
    ]);
}
 
    public function store(SupplierRequest $request)
    {
        try {
            $id = $request->input('id');
            $data = $request->validated();

            if (! empty($id)) {

                // UPDATE
                $data['updated_by'] = Auth::id();

                Supplier::where('id', $id)->update($data);

                return response()->json([
                    'action' => 'update',
                    'message' => 'Data updated successfully',
                ], 200);

            } else {

                // CREATE
                $data['created_by'] = Auth::id();
                $data['status'] = 1;

                // 🔥 CEK ID PELANGGAN
                if (empty($data['id_supplier'])) {
                    $data['id_supplier'] = $this->generateSupplierId();
                } else {

                    // 🔥 VALIDASI: jangan sampai duplicate
                    $exists = Supplier::where('id_supplier', $data['id_supplier'])->exists();

                    if ($exists) {
                        return response()->json([
                            'error' => 'ID Pelanggan sudah digunakan',
                        ], 422);
                    }
                }

                Supplier::create($data);

                return response()->json([
                    'action' => 'create',
                    'message' => 'Data created successfully',
                ], 201);
            }

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function create()
    {
       
    }
    public function show(string $id)
    {
        //
    }
    
    public function edit(Request $request)
    {

        $where = [
            'id' => $request->id,
        ];
        $data = Supplier::where($where)->first();

        return response()->json($data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

     public function destroy(Request $request, $id)
    {

        try {
            $table = Supplier::findOrFail($id);
            $table->status = '0';
            $table->updated_by = Auth::user()->id;
            $table->save();
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }
    public function trash(Request $r)
    {
        if ($r->ajax()) {
            $query = Supplier::where('status', 0)->get();

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
                    if ($row->status == 1) {
                        return '<span class="badge bg-info">Active</span>';
                    } else {
                        return '<span class="badge bg-danger">Not Active</span>';
                    }
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">
                      <button type="button" class="btn btn-primary dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">
                        Action
                      </button>
                      <ul class="dropdown-menu" style="">';

                    if (auth()->user()->can('supplier-restore')) {
                        $btn .= '<a class="dropdown-item restore" href="javascript:void(0)"
                            data-id="'.$row->id.'"> <i class="ti ti-trash-off me-1"></i> Restore</a>';
                    }


                    return $btn;
                })
                ->rawColumns(['action', 'created_at', 'updated_at', 'status'])
                ->make(true);
        }

        $x = [
            'title' => 'Deleted Supplier List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Deleted Supplier', 'url' => ''],
            ],
        ];

        return view('master_data.supplier.supplier_trash', $x);
    }

    public function restore($id)
    {
        DB::beginTransaction();

        try {
            $album = Supplier::find($id);
            $album->status = 1;
            $album->updated_by = Auth::id();
            $album->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'redirect' => true,
                'message' => 'Supplier successfully restored.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => true,
                'redirect' => true,
                'message' => 'Supplier successfully restored.',
            ]);
        }
    }
}
