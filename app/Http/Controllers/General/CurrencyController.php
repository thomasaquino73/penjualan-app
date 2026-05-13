<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Models\BasicCodeDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class CurrencyController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $routeName = $request->route()->getName();

            $permissionMap = [
                'mata-uang.index' => 'mata_uang-browse',
                'mata-uang.show' => 'mata_uang-read',
                'mata-uang.create' => 'mata_uang-create',
                'mata-uang.store' => 'mata_uang-create',
                'mata-uang.edit' => 'mata_uang-edit',
                'mata-uang.update' => 'mata_uang-edit',
                'mata-uang.destroy' => 'mata_uang-delete',
                'mata-uang.trash' => 'mata_uang-trash',
                'mata-uang.restore' => 'mata_uang-restore',
            ];

            if (isset($permissionMap[$routeName])) {
                if (! $request->user()->can($permissionMap[$routeName])) {
                    abort(403, 'Unauthorized action');
                }
            }

            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $r)
    {
        if ($r->ajax()) {
            $query = BasicCodeDetail::where('master_id', 3);

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
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">
                      <button type="button" class="btn btn-primary dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-menu-2 ti-xs me-1"></i> 
                      </button>
                      <ul class="dropdown-menu" style="">';
                    if (auth()->user()->can('mata_uang-edit')) {
                        $btn .= '<a class="dropdown-item editPost" href="javascript:void(0)"
                            data-id="'.$row->id.'"> <i class="far fa-edit me-1"></i>Edit</a>';
                    }
                    if (auth()->user()->can('mata_uang-delete')) {
                        $btn .= '<a class="dropdown-item" href="javascript:void(0)" id="delete"
                                data-id="'.$row->id.'"
                                data-name="'.$row->detail.'"
                                ><i class="fa fa-trash me-1"></i> Delete</a>';
                    }

                    return $btn;
                })
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'gambar'])
                ->make(true);
        }

        $x = [
            'title' => 'Currency',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Currency', 'url' => ''],
            ],
        ];

        return view('general.mata_uang.index', $x);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $id = $request->input('id');

        // ✅ RULE VALIDASI
        $rules = [
            'detail' => 'required|unique:basic_code_detail,detail,'.$id.',id,master_id,3',
            'description' => 'nullable',
        ];

        $validator = Validator::make($request->all(), $rules, [
            'detail.required' => 'Detail is required',
            'detail.unique' => 'Detail has already been taken',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        try {

            // ✅ AMBIL DATA DARI INPUT (INI YANG BENAR)
            $data = [
                'detail' => $request->detail,
                'description' => $request->description,
                'master_id' => 3,
            ];

            if (! empty($id)) {

                // ✅ UPDATE
                $data['updated_at'] = now();
                $data['updated_by'] = Auth::id();

                DB::table('basic_code_detail')
                    ->where('id', $id)
                    ->update($data);

                return response()->json([
                    'action' => 'update',
                    'message' => 'Data updated successfully',
                ], 200);

            } else {

                // ✅ CREATE
                $data['created_at'] = now();
                $data['created_by'] = Auth::id();

                DB::table('basic_code_detail')->insert($data);

                return response()->json([
                    'action' => 'create',
                    'message' => 'Data created successfully',
                ], 201);
            }

        } catch (\Exception $e) {

            return response()->json([
                'error' => 'Error: '.$e->getMessage(),
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();

        try {

            $daftar = BasicCodeDetail::findOrFail($id);

            $daftar->delete();

            DB::commit();

            return response()->json([
                'message' => 'Data deleted successfully',
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Failed to delete data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
