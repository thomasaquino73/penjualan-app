<?php

namespace App\Http\Controllers\Purchase\Supplier;

use App\Http\Controllers\Controller;
use App\Http\Requests\KategoriSupplierRequest;
use App\Models\BasicCodeDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\DataTables;

class KategoriSupplierController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $routeName = $request->route()->getName();

            $permissionMap = [
                'kategori-supplier.index' => 'kategori_supplier-browse',
                'kategori-supplier.show' => 'kategori_supplier-read',
                'kategori-supplier.create' => 'kategori_supplier-create',
                'kategori-supplier.store' => 'kategori_supplier-create',
                'kategori-supplier.edit' => 'kategori_supplier-edit',
                'kategori-supplier.update' => 'kategori_supplier-edit',
                'kategori-supplier.destroy' => 'kategori_supplier-delete',
                'kategori-supplier.trash' => 'kategori_supplier-trash',
                'kategori-supplier.restore' => 'kategori_supplier-restore',
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
        $data = BasicCodeDetail::where('master_id', 8)->get();

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
                    if (auth()->user()->can('kategori_supplier-edit')) {

                        $btn .= '<a class="dropdown-item editPost" href="javascript:void(0)"
                            data-id="'.$row->id.'"> <i class="far fa-edit"></i> Edit</a>';
                    }
                    if (auth()->user()->can('kategori_supplier-delete')) {

                        $btn .= '<a class="dropdown-item" href="javascript:void(0)" id="delete"
                                data-id="'.$row->id.'"
                                data-name="'.$row->detail.'"
                                ><i class="ti ti-trash"></i> Delete</a>';
                    }

                    return $btn;
                })

                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'cekbok'])
                ->make(true);
        }
        $x = [
            'title' => 'Categories',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Categories', 'url' => ''],
            ],
        ];

        return view('purchase.supplier.kategori_supplier_index', $x);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function store(KategoriSupplierRequest $request)
    {
        try {
            $id = $request->input('id');
            $data = $request->all();
            $data['master_id'] = 8;

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
            $detail = BasicCodeDetail::findOrFail($id);

            // Check if this code detail is being used in the 'supplier' table
            if ($detail->supplier_category()->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot delete this data because it is currently assigned to products.',
                ], 422);
            }

            $detail->delete();

            return response()->json([
                'action' => 'delete',
                'status' => 'success',
                'message' => 'Data deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred: '.$e->getMessage(),
            ], 500);
        }
    }

    public function deleteMultiple(Request $request)
    {
        $ids = $request->ids;

        if (! $ids || count($ids) == 0) {
            return response()->json(['success' => false]);
        }

        BasicCodeDetail::whereIn('id', $ids)->delete();

        return response()->json(['success' => true]);
    }
}
