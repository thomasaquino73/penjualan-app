<?php

namespace App\Http\Controllers\Master_Data;

use App\Http\Controllers\Controller;
use App\Http\Requests\WarehouseRequest;
use App\Models\Master_Data\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;

class WarehouseController extends Controller
{
    public function index(Request $r)
    {
        if ($r->ajax()) {
            $query = Warehouse::where('status', '<>', 0)->get();

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

                    if (auth()->user()->can('warehouse-edit')) {
                        $btn .= '<a class="dropdown-item editPost" href="javascript:void(0)"
                            data-id="'.$row->id.'"> <i class="far fa-edit"></i> Edit</a>';
                    }

                    if (auth()->user()->can('warehouse-delete')) {
                        $btn .= '<a class="dropdown-item" href="javascript:void(0)" id="delete"
                                data-id="'.$row->id.'"
                                data-name="'.$row->nama_gudang.'"
                                ><i class="ti ti-trash"></i> Delete</a>';
                    }

                    return $btn;
                })
                ->rawColumns(['action', 'created_at', 'updated_at', 'status'])
                ->make(true);
        }

        $x = [
            'title' => 'Warehouse List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Warehouse', 'url' => ''],
            ],
        ];

        return view('master_data.warehouse.warehouse_index', $x);
    }

    private function generateWarehouseId()
    {
        $last = Warehouse::whereNotNull('id_gudang')
            ->orderBy('id', 'desc')
            ->first();

        if (! $last) {
            return 'WH-001';
        }

        $lastId = $last->id_gudang;

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

    public function generateId()
    {
        return response()->json([
            'id_gudang' => $this->generateWarehouseId(),
        ]);
    }

    public function store(WarehouseRequest $request)
    {
        try {
            $id = $request->input('id');
            $data = $request->validated();

            if (! empty($id)) {

                // UPDATE
                $data['updated_by'] = Auth::id();

                Warehouse::where('id', $id)->update($data);

                return response()->json([
                    'action' => 'update',
                    'message' => 'Data updated successfully',
                ], 200);

            } else {

                // CREATE
                $data['created_by'] = Auth::id();
                $data['status'] = 1;

                // 🔥 CEK ID GUDANG
                if (empty($data['id_gudang'])) {
                    $data['id_gudang'] = $this->generateWarehouseId();
                } else {

                    // 🔥 VALIDASI: jangan sampai duplicate
                    $exists = Warehouse::where('id_gudang', $data['id_gudang'])->exists();

                    if ($exists) {
                        return response()->json([
                            'error' => 'Warehouse ID already in use',
                        ], 422);
                    }
                }

                Warehouse::create($data);

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

    public function create() {}

    public function show(string $id)
    {
        //
    }

    public function edit(Request $request)
    {

        $where = [
            'id' => $request->id,
        ];
        $data = Warehouse::where($where)->first();

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
            $table = Warehouse::findOrFail($id);
            $table->status = '0';
            $table->updated_by = Auth::user()->id;
            $table->save();
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function trash(Request $r)
    {
        if ($r->ajax()) {
            $query = Warehouse::where('status', 0)->get();

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

                    if (auth()->user()->can('warehouse-restore')) {
                        $btn .= '<a class="dropdown-item restore" href="javascript:void(0)"
                            data-id="'.$row->id.'"> <i class="ti ti-trash-off me-1"></i> Restore</a>';
                    }

                    return $btn;
                })
                ->rawColumns(['action', 'created_at', 'updated_at', 'status'])
                ->make(true);
        }

        $x = [
            'title' => 'Deleted Warehouse List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Deleted Warehouse', 'url' => ''],
            ],
        ];

        return view('master_data.warehouse.warehouse_trash', $x);
    }

    public function restore($id)
    {
        DB::beginTransaction();

        try {
            $album = Warehouse::find($id);
            $album->status = 1;
            $album->updated_by = Auth::id();
            $album->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'redirect' => true,
                'message' => 'Warehouse successfully restored.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => true,
                'redirect' => true,
                'message' => 'Warehouse successfully restored.',
            ]);
        }
    }
}
