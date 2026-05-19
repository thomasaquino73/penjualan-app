<?php

namespace App\Http\Controllers\Master_Data;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupplierRequest;
use App\Models\BasicCodeDetail;
use App\Models\Master_Data\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\DataTables;

class SupplierController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $routeName = $request->route()->getName();

            $permissionMap = [
                'supplier.index' => 'supplier-browse',
                'supplier.show' => 'supplier-read',
                'supplier.create' => 'supplier-create',
                'supplier.store' => 'supplier-create',
                'supplier.edit' => 'supplier-edit',
                'supplier.update' => 'supplier-edit',
                'supplier.destroy' => 'supplier-delete',
                'supplier.trash' => 'supplier-trash',
                'supplier.restore' => 'supplier-restore',
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
            $query = Supplier::where('status', '<>', 0)->get();

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

                    if (auth()->user()->can('supplier-edit')) {
                        $btn .= '<a class="dropdown-item editPost" href="'.route('supplier.edit', $row->id).'"
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
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'cekbok'])
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

    private function generateNumberId()
    {
        $last = Supplier::whereNotNull('id_supplier')
            ->orderBy('id', 'desc')
            ->first();

        if (! $last) {
            return 'SP-0001';
        }

        $lastId = $last->id_supplier;

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
            'id_supplier' => $this->generateNumberId(),
        ]);
    }

    // public function store(SupplierRequest $request)
    // {
    //     try {
    //         $id = $request->input('id');
    //         $data = $request->validated();

    //         if (! empty($id)) {

    //             // ==========================================
    //             // PROCESS: UPDATE DATA
    //             // ==========================================
    //             $data['updated_by'] = Auth::id();

    //             Supplier::where('id', $id)->update($data);

    //             return response()->json([
    //                 'action' => 'update',
    //                 'message' => 'Data updated successfully',
    //             ], 200);

    //         } else {

    //             // ==========================================
    //             // PROCESS: CREATE DATA (Safe from Race Condition)
    //             // ==========================================
    //             $data['created_by'] = Auth::id();
    //             $data['status'] = 1;

    //             // Mulai database transaction sebelum pengecekan ID
    //             DB::beginTransaction();

    //             try {
    //                 // Menggunakan kolom 'id_supplier' sesuai DB kamu
    //                 if (empty($data['id_supplier'])) {

    //                     // Looping otomatis jika ID keduluan diambil user lain
    //                     do {
    //                         $data['id_supplier'] = $this->generateSupplierId();

    //                         // Kunci baris dengan lockForUpdate agar request lain mengantre
    //                         $exists = Supplier::where('id_supplier', $data['id_supplier'])->lockForUpdate()->exists();
    //                     } while ($exists);

    //                 } else {
    //                     // Validasi jika input manual: pastikan belum terdaftar
    //                     $exists = Supplier::where('id_supplier', $data['id_supplier'])->lockForUpdate()->exists();

    //                     if ($exists) {
    //                         DB::rollBack();

    //                         return response()->json([
    //                             'error' => 'ID Supplier sudah digunakan',
    //                         ], 422);
    //                     }
    //                 }

    //                 // Simpan data ke database
    //                 Supplier::create($data);

    //                 // Commit semua transaksi jika berhasil tanpa error
    //                 DB::commit();

    //                 return response()->json([
    //                     'action' => 'create',
    //                     'message' => 'Data created successfully',
    //                 ], 201);

    //             } catch (\Exception $e) {
    //                 // Batalkan transaksi jika terjadi error di dalam blok DB
    //                 DB::rollBack();
    //                 throw $e; // Lempar ke catch paling luar untuk response error 500
    //             }
    //         }

    //     } catch (\Exception $e) {
    //         // Menangkap semua error (termasuk dari throw $e di atas)
    //         return response()->json([
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }
    public function create()
    {
        $x = [
            'title' => 'Supplier List New',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Supplier New', 'url' => ''],
            ],
            'idNumber' => $this->generateNumberId(),
            'paymentTerm' => BasicCodeDetail::where('master_id', 4)->get(),
            'databank' => BasicCodeDetail::where('master_id', 5)->get(),

        ];

        return view('master_data.supplier.supplier_create', $x);
    }

    public function store(SupplierRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
               $itemsDetailRaw = $request->input('items_detail');
            unset($data['items_detail']);
            $data['created_by'] = Auth::id();
            if (empty($data['id_supplier'])) {
                do {
                    $data['id_supplier'] = $this->generateSupplierId();
                    $exists = Supplier::where('id_supplier', $data['id_supplier'])->lockForUpdate()->exists();
                } while ($exists);
            } else {
                $exists = Supplier::where('id_supplier', $data['id_supplier'])->lockForUpdate()->exists();
                if ($exists) {
                    DB::rollBack();

                    return response()->json([
                        'error' => 'ID Supplier sudah digunakan',
                    ], 422);
                }
            }
            $supplier = Supplier::create($data);
            DB::table('supplier_kontak')->insert([
                'supplier_id' => $supplier->id,
                'sapaan' => $request->sapaan,
                'contact_person' => $request->contact_person,
                'posisi_jabatan' => $request->posisi_jabatan,
                'email_kontak' => $request->email_kontak,
                'handphone_kontak' => $request->handphone_kontak,
                'notel_bisnis_kontak' => $request->notel_bisnis_kontak,
                'faximili_kontak' => $request->faximili_kontak,
                'no_whatsapp_kontak' => $request->no_whatsapp_kontak,
                'website_kontak' => $request->website_kontak,
                'catatan' => $request->catatan,
            ]);
            DB::table('supplier_pembelian')->insert([
                'supplier_id' => $supplier->id,
                'payment_term' => $request->payment_term,
                'discount' => $request->discount,
                'default_deskripsi' => $request->default_deskripsi,
            ]);
            DB::table('supplier_pajak')->insert([
                'supplier_id' => $supplier->id,
                'tipe_id_pajak' => $request->tipe_id_pajak,
                'nomor_wajib_pajak' => $request->nomor_wajib_pajak,
                'nama_wajib_pajak' => $request->nama_wajib_pajak,
                'id_tku' => $request->id_tku,
                'alamat_pajak' => $request->alamat_pajak,
                'kota_pajak' => $request->kota_pajak,
                'kodepos_pajak' => $request->kodepos_pajak,
                'provinsi_pajak' => $request->provinsi_pajak,
                'negara_pajak' => $request->negara_pajak,
            ]);

              // 6. Proses simpan data ke tabel detail 'purchase_order_detail'
            if ($itemsDetailRaw) {
                // Bongkar string JSON data barang dari AJAX menjadi array PHP
                $items = json_decode($itemsDetailRaw, true);

                if (is_array($items) && count($items) > 0) {
                    foreach ($items as $item) {

                        DB::table('supplier_rekening')->insert([
                            'supplier_id' => $supplier->id,
                            'nama_bank' => $item['nama_bank'] ?? null,
                            'nomor_rekening' => $item['nomor_rekening'] ?? null,
                            'nama_rekening' => $item['nama_rekening'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'action' => 'create',
                'redirect' => route('supplier.index'),
                'message' => 'Data created successfully',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

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
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }

    // public function destroy(Request $request, $id)
    // {
    //     try {
    //         $supplier = Supplier::findOrFail($id);

    //         // 1. Check Relations: Is this Supplier ID used in other tables?
    //         // Note: Ensure 'items' and 'purchases' are defined as relationships in the Supplier Model
    //         $hasRelation = $supplier->items()->exists() || $supplier->purchases()->exists();

    //         if ($hasRelation) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Supplier cannot be deleted or deactivated because it is linked to existing transaction records.'
    //             ], 422);
    //         }

    //         // 2. If no relations exist, proceed with deactivation
    //         $supplier->status = '0';
    //         $supplier->updated_by = Auth::user()->id;
    //         $supplier->save();

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Supplier has been successfully deactivated.'
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

        Supplier::whereIn('id', $ids)->update([
            'status' => '0',
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['success' => true]);
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

                    if (auth()->user()->can('supplier-restore')) {
                        $btn .= '<a class="dropdown-item restore" href="javascript:void(0)"
                            data-id="'.$row->id.'"> <i class="ti ti-trash-off me-1"></i> Restore</a>';
                    }

                    return $btn;
                })
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'cekbok'])
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

    public function restoreMultiple(Request $request)
    {
        $ids = $request->ids;

        if (! $ids || count($ids) == 0) {
            return response()->json(['success' => false]);
        }

        Supplier::whereIn('id', $ids)->update([
            'status' => '1',
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['success' => true]);
    }
}
