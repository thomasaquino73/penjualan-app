<?php

namespace App\Http\Controllers\Master_Data\Barang;

use App\Http\Controllers\Controller;
use App\Http\Requests\SatuanBarangRequest;
use App\Models\BasicCodeDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\DataTables;

class SatuanBarangController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $routeName = $request->route()->getName();

            $permissionMap = [
                'satuan-barang.index' => 'satuan_barang-browse',
                'satuan-barang.show' => 'satuan_barang-read',
                'satuan-barang.create' => 'satuan_barang-create',
                'satuan-barang.store' => 'satuan_barang-create',
                'satuan-barang.edit' => 'satuan_barang-edit',
                'satuan-barang.update' => 'satuan_barang-edit',
                'satuan-barang.destroy' => 'satuan_barang-delete',
            ];

            if (isset($permissionMap[$routeName])) {
                if (! $request->user()->can($permissionMap[$routeName])) {
                    abort(403, 'Unauthorized action');
                }
            }

            return $next($request);
        });
    }

    public function index(Request $r)
    {
        $data = BasicCodeDetail::where('master_id', 3)->get();

        if ($r->ajax()) {
            return DataTables::of($data)
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
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">
                      <button type="button" class="btn btn-primary dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-menu-2 ti-xs me-1"></i>
                      Action
                      </button>
                      <ul class="dropdown-menu" style="">';
                    if (auth()->user()->can('satuan_barang-edit')) {
                        $btn .= '<a class="dropdown-item editPost" href="javascript:void(0)"
                            data-id="'.$row->id.'"> <i class="far fa-edit"></i> Edit</a>';
                    }
                    if (auth()->user()->can('satuan_barang-delete')) {
                        $btn .= '<a class="dropdown-item" href="javascript:void(0)" id="delete"
                                data-id="'.$row->id.'"
                                data-name="'.$row->detail.'"
                                ><i class="ti ti-trash"></i> Delete</a>';
                    }

                    return $btn;
                })

                ->rawColumns(['action', 'created_at', 'updated_at', 'status'])
                ->make(true);
        }
        $x = [
            'title' => 'Unit',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Unit', 'url' => ''],
            ],
        ];

        return view('master_data.barang.satuan_index', $x);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function store(SatuanBarangRequest $request)
    {
        try {
            $id = $request->input('id');
            $data = $request->all();
            $data['master_id'] = 3;

            if (! empty($id)) {
                $data['updated_at'] = now();
                $data['updated_by'] = Auth::id();

                BasicCodeDetail::updateOrCreate(['id' => $id], $data);

                return response()->json([
                    'action' => 'update',
                    'message' => 'Data updated successfully',
                ], 200);

            } else {
                $data['created_at'] = now();
                $data['created_by'] = Auth::id();

                BasicCodeDetail::create($data);

                return response()->json([
                    'action' => 'create',
                    'message' => 'Data created successfully',
                ], 201);
            }

        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    public function edit(Request $request)
    {

        $where = [
            'id' => $request->id,
        ];
        $data = BasicCodeDetail::where($where)->first();

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
            $table = BasicCodeDetail::findOrFail($id);
            $table->delete();

            return response()->json([
                'action' => 'delete',
                'message' => 'Data deleted successfully',
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }
}
