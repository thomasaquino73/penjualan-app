<?php

namespace App\Http\Controllers\Master_Data;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRequest;
use App\Models\Master_Data\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\DataTables;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $routeName = $request->route()->getName();

            $permissionMap = [
                'customer.index' => 'customer-browse',
                'customer.show' => 'customer-read',
                'customer.create' => 'customer-create',
                'customer.store' => 'customer-create',
                'customer.edit' => 'customer-edit',
                'customer.update' => 'customer-edit',
                'customer.destroy' => 'customer-delete',
                'customer.trash' => 'customer-trash',
                'customer.restore' => 'customer-restore',
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
        if ($r->ajax()) {
            $query = Customer::where('status', '<>', 0)->get();

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
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'cekbok'])
                ->make(true);
        }

        $x = [
            'title' => 'Customer List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Customer', 'url' => ''],
            ],
        ];

        return view('master_data.customer.customer_index', $x);
    }

    private function generateCustomerId()
    {
        $last = Customer::whereNotNull('id_pelanggan')
            ->orderBy('id', 'desc')
            ->first();

        if (! $last) {
            return 'C-0001';
        }

        $lastId = $last->id_pelanggan;

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
            'id_pelanggan' => $this->generateCustomerId(),
        ]);
    }

   public function store(CustomerRequest $request)
{
    try {
        $id = $request->input('id');
        $data = $request->validated();

        if (! empty($id)) {

            // ==========================================
            // PROCESS: UPDATE DATA
            // ==========================================
            $data['updated_by'] = Auth::id();

            Customer::where('id', $id)->update($data);

            return response()->json([
                'action' => 'update',
                'message' => 'Data updated successfully',
            ], 200);

        } else {

            // ==========================================
            // PROCESS: CREATE DATA (Safe from Race Condition)
            // ==========================================
            $data['created_by'] = Auth::id();
            $data['status'] = 1;

            // Mulai database transaction sebelum pengecekan ID
            DB::beginTransaction();

            try {
                // Menggunakan kolom 'id_pelanggan' sesuai DB kamu
                if (empty($data['id_pelanggan'])) {

                    // Looping otomatis jika ID keduluan diambil user lain
                    do {
                        $data['id_pelanggan'] = $this->generateCustomerId();

                        // Kunci baris dengan lockForUpdate agar request lain mengantre
                        $exists = Customer::where('id_pelanggan', $data['id_pelanggan'])->lockForUpdate()->exists();
                    } while ($exists);

                } else {
                    // Validasi jika input manual: pastikan belum terdaftar
                    $exists = Customer::where('id_pelanggan', $data['id_pelanggan'])->lockForUpdate()->exists();

                    if ($exists) {
                        DB::rollBack();

                        return response()->json([
                            'error' => 'ID Customer sudah digunakan',
                        ], 422);
                    }
                }

                // Simpan data ke database
                Customer::create($data);

                // Commit semua transaksi jika berhasil tanpa error
                DB::commit();

                return response()->json([
                    'action' => 'create',
                    'message' => 'Data created successfully',
                ], 201);

            } catch (\Exception $e) {
                // Batalkan transaksi jika terjadi error di dalam blok DB
                DB::rollBack();
                throw $e; // Lempar ke catch paling luar untuk response error 500
            }
        }

    } catch (\Exception $e) {
        // Menangkap semua error (termasuk dari throw $e di atas)
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
        $data = Customer::where($where)->first();

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
            $table = Customer::findOrFail($id);
            $table->status = '0';
            $table->updated_by = Auth::user()->id;
            $table->save();
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }

    // public function destroy(Request $request, $id)
    // {
    //     try {
    //         $customer = Customer::findOrFail($id);

    //         // 1. Check Relations: Is this Customer ID used in other tables?
    //         // Note: Ensure 'items' and 'purchases' are defined as relationships in the Customer Model
    //         $hasRelation = $customer->items()->exists() || $customer->purchases()->exists();

    //         if ($hasRelation) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Customer cannot be deleted or deactivated because it is linked to existing transaction records.'
    //             ], 422);
    //         }

    //         // 2. If no relations exist, proceed with deactivation
    //         $customer->status = '0';
    //         $customer->updated_by = Auth::user()->id;
    //         $customer->save();

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Customer has been successfully deactivated.'
    //         ]);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'An error occurred: ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function deleteMultiple(Request $request)
    {
        $ids = $request->ids;

        if (! $ids || count($ids) == 0) {
            return response()->json(['success' => false]);
        }

        Customer::whereIn('id', $ids)->update([
            'status' => '0',
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['success' => true]);
    }

    public function trash(Request $r)
    {
        if ($r->ajax()) {
            $query = Customer::where('status', 0)->get();

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

                    if (auth()->user()->can('customer-restore')) {
                        $btn .= '<a class="dropdown-item restore" href="javascript:void(0)"
                            data-id="'.$row->id.'"> <i class="ti ti-trash-off me-1"></i> Restore</a>';
                    }

                    return $btn;
                })
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'cekbok'])
                ->make(true);
        }

        $x = [
            'title' => 'Deleted Customer List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Deleted Customer', 'url' => ''],
            ],
        ];

        return view('master_data.customer.customer_trash', $x);
    }

    public function restore($id)
    {
        DB::beginTransaction();

        try {
            $album = Customer::find($id);
            $album->status = 1;
            $album->updated_by = Auth::id();
            $album->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'redirect' => true,
                'message' => 'Customer successfully restored.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => true,
                'redirect' => true,
                'message' => 'Customer successfully restored.',
            ]);
        }
    }

    public function restoreMultiple(Request $request)
    {
        $ids = $request->ids;

        if (! $ids || count($ids) == 0) {
            return response()->json(['success' => false]);
        }

        Customer::whereIn('id', $ids)->update([
            'status' => '1',
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['success' => true]);
    }
}
