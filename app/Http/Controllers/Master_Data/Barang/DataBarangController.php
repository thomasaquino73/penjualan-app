<?php

namespace App\Http\Controllers\Master_Data\Barang;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\BasicCodeDetail;
use App\Models\Master_Data\Barang;
use App\Models\Master_Data\DataBarangConversion;
use App\Models\Master_Data\Warehouse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\DataTables;

class DataBarangController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $routeName = $request->route()->getName();

            $permissionMap = [
                'data-barang.index' => 'barang-browse',
                'data-barang.show' => 'barang-read',
                'data-barang.create' => 'barang-create',
                'data-barang.store' => 'barang-create',
                'data-barang.edit' => 'barang-edit',
                'data-barang.update' => 'barang-edit',
                'data-barang.destroy' => 'barang-delete',
                'data-barang.trash' => 'barang-trash',
                'data-barang.restore' => 'barang-restore',
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
            $query = Barang::where('status', '<>', 0)->orderBy('id_barang', 'desc')->get();

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
                ->addColumn('fotoProduk', function ($row) {
                    $avatarUrl = $row->photo_filename
                         ? asset($row->photo_filename)
                         : asset('image/no-images.jpg');

                    return '<img class="avatar avatar-md rounded-circle me-2 avatar-online detail"
                                src="'.$avatarUrl.'"
                                alt="Foto Produk"  data-gambar="'.asset($row->photo_filename).'"
                                data-alias="'.$row->nama_barang.'">';
                })
                ->addColumn('status', function ($row) {
                    if ($row->status == 1) {
                        return '<span class="badge bg-info">Active</span>';
                    } else {
                        return '<span class="badge bg-danger">Not Active</span>';
                    }
                })
                ->addColumn('productType', function ($row) {
                    if ($row->product_type == 'supply') {
                        return '<span class="badge bg-success">Supply</span>';
                    } else {
                        return '<span class="badge bg-secondary">Non Supply</span>';
                    }
                })
                ->addColumn('cekbok', function ($row) {
                    return '   <div class="form-check form-check-primary mt-3">
                                <input class="form-check-input checkItem" type="checkbox" value="'.$row->id.'"
                                    >
                            </div>';
                })
                ->addColumn('kategori', function ($row) {
                    return $row->kategoriID->detail;
                })
                ->addColumn('gudang', function ($row) {
                    return $row->warehouseID->nama_gudang;
                })
                // ->addColumn('tipePersediaan', function ($row) {
                //     return $row->typeID->detail;
                // })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">
                      <button type="button" class="btn btn-primary dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-menu-2 ti-xs me-1"></i>Action
                      </button>
                      <ul class="dropdown-menu" style="">';

                    if (auth()->user()->can('barang-edit')) {
                        $btn .= '<a class="dropdown-item editPost" href="'.route('data-barang.edit', $row->id).'"
                            data-id="'.$row->id.'"> <i class="far fa-edit"></i> Edit</a>';
                    }
                    if (auth()->user()->can('barang-read')) {
                        $btn .= '<a class="dropdown-item editPost" href="'.route('data-barang.show', $row->id).'"
                            data-id="'.$row->id.'"> <i class="ti ti-list-details"></i> Detail</a>';
                    }

                    if (auth()->user()->can('barang-delete')) {
                        $btn .= '<a class="dropdown-item" href="javascript:void(0)" id="delete"
                                data-id="'.$row->id.'"
                                data-name="'.$row->nama_barang.'"
                                ><i class="ti ti-trash"></i> Delete</a>';
                    }
                    $btn .= '<a class="dropdown-item" href="'.route('data-barang.print', $row->id).'" target="_blank">
                                    <i class="ti ti-printer"></i> Print
                                </a>';

                    return $btn;
                })
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'kategori', 'gudang', 'tipePersediaan', 'fotoProduk', 'productType', 'cekbok'])
                ->make(true);
        }

        $x = [
            'title' => 'Product List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Product', 'url' => ''],
            ],

        ];

        return view('master_data.barang.data_barang.data_barang_index', $x);
    }

    private function generateProductId()
    {
        $last = Barang::whereNotNull('id_barang')
            ->orderBy('id', 'desc')
            ->first();

        if (! $last) {
            return 'C-0001';
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

    public function generateId()
    {
        return response()->json([
            'id_pelanggan' => $this->generateProductId(),
        ]);
    }

    public function create()
    {

        return view('master_data.barang.data_barang.data_barang_create', [
            'title' => 'Add Product',
            'breadcrumb' => [
                ['label' => 'Product', 'url' => route('data-barang.index')],
                ['label' => 'Add Product', 'url' => ''],
            ],
            'idNumber' => $this->generateProductId(),
            'categories' => BasicCodeDetail::where('master_id', 1)->get(),
            'unit' => BasicCodeDetail::where('master_id', 2)->get(),
            'warehouses' => Warehouse::where('status', 1)->get(),
            'inventoryTypes' => BasicCodeDetail::where('master_id', 4)->get(),
        ]);
    }

    private function uploadAvatar($avatar)
    {
        $name = uniqid().time();
        $destination = 'image/foto_produk';
        $filePath = $avatar->move($destination, $name.'.'.$avatar->getClientOriginalExtension());

        return str_replace('\\', '/', $filePath);
    }

    // public function store(ProductRequest $request)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $isSaveAndNew = $request->input('save_and_new') == '1';
    //         $data = $request->except(['_token', 'save_and_new']);
    //         $data['created_by'] = Auth::id();

    //         if ($request->hasFile('photo_filename')) {
    //             $data['photo_filename'] = $this->uploadAvatar($request->file('photo_filename'));
    //         }
    //         Barang::create($data);

    //         DB::commit();

    //         if ($isSaveAndNew) {
    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'Product has been saved. You can now upload a new product.',
    //                 'redirect' => route('data-barang.create'),
    //             ]);
    //         } else {
    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'Product has been successfully added.',
    //                 'redirect' => route('data-barang.index'),
    //             ]);
    //         }
    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to create product: '.$e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function store(ProductRequest $request)
    {
        DB::beginTransaction();

        try {
            $isSaveAndNew = $request->input('save_and_new') == '1';

            $data = $request->except(['_token', 'save_and_new', 'conversion']);
            $data['created_by'] = Auth::id();
            $data['status'] = $request->has('status') ? 2 : 1;

            if ($request->hasFile('photo_filename')) {
                $data['photo_filename'] = $this->uploadAvatar($request->file('photo_filename'));
            }

            // =========================
            // 1. SAVE MAIN PRODUCT
            // =========================
            $barang = Barang::create($data);

            // =========================
            // 2. SAVE CONVERSION DATA
            // =========================
            if ($request->has('conversion')) {

                foreach ($request->conversion as $conv) {
                    // dd($request->conversion);
                    // skip kalau kosong
                    if (
                        empty($conv['from_unit']) ||
                        empty($conv['to_unit']) ||
                        empty($conv['qty'])
                    ) {
                        continue;
                    }

                    DataBarangConversion::create([
                        'data_barang_id' => $barang->id,
                        'from_unit_id' => $request->unit_id,
                        'to_unit_id' => $conv['to_unit'],
                        'qty' => $conv['qty'],
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product has been successfully saved.',
                'redirect' => $isSaveAndNew
                    ? route('data-barang.create')
                    : route('data-barang.index'),
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create product: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $idDetail = Barang::findorfail($id);

        return view('master_data.barang.data_barang.data_barang_detail', [
            'title' => 'Detail Product',
            'breadcrumb' => [
                ['label' => 'Product', 'url' => route('data-barang.index')],
                ['label' => 'Detail Product', 'url' => ''],
            ],
            'detail' => $idDetail,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $idDetail = Barang::findorfail($id);

        return view('master_data.barang.data_barang.data_barang_edit', [
            'title' => 'Edit Product',
            'breadcrumb' => [
                ['label' => 'Product', 'url' => route('data-barang.index')],
                ['label' => 'Edit Product', 'url' => ''],
            ],
            'idNumber' => $this->generateProductId(),
            'categories' => BasicCodeDetail::where('master_id', 1)->get(),
            'unit' => BasicCodeDetail::where('master_id', 2)->get(),
            'warehouses' => Warehouse::where('status', 1)->get(),
            'inventoryTypes' => BasicCodeDetail::where('master_id', 4)->get(),
            'detail' => $idDetail,
        ]);
    }

    public function update(ProductRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $isSaveAndNew = $request->input('save_and_new') == '1';
            $barang = Barang::findOrFail($id);
            $data = $request->except(['_token', '_method', 'save_and_new', 'conversion']);
            $data['updated_by'] = Auth::id();

            // jika upload foto baru
            if ($request->hasFile('photo_filename')) {
                // optional: hapus file lama kalau perlu
                // if ($barang->photo_filename) {
                //     Storage::delete($barang->photo_filename);
                // }

                $data['photo_filename'] = $this->uploadAvatar($request->file('photo_filename'));
            }

            $barang->update($data);

            // =========================
            // UPDATE CONVERSION
            // =========================

            // 1. HAPUS DATA LAMA
            DataBarangConversion::where('data_barang_id', $barang->id)->delete();

            // 2. INSERT ULANG
            if ($request->has('conversion')) {

                foreach ($request->conversion as $conv) {

                    if (
                        empty($conv['from_unit']) ||
                        empty($conv['to_unit']) ||
                        empty($conv['qty'])
                    ) {
                        continue;
                    }

                    DataBarangConversion::create([
                        'data_barang_id' => $barang->id,
                        'from_unit_id' => $conv['from_unit'],
                        'to_unit_id' => $conv['to_unit'],
                        'qty' => $conv['qty'],
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product has been successfully updated.',
                'redirect' => $isSaveAndNew
                    ? route('data-barang.create')
                    : route('data-barang.index'),
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update product: '.$e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {

        try {
            $table = Barang::findOrFail($id);
            $table->status = '0';
            $table->updated_by = Auth::user()->id;
            $table->save();
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function deleteMultiple(Request $request)
    {
        $ids = $request->ids;

        if (! $ids || count($ids) == 0) {
            return response()->json(['success' => false]);
        }

        Barang::whereIn('id', $ids)->update([
            'status' => '0',
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['success' => true]);
    }

    public function trash(Request $r)
    {
        if ($r->ajax()) {
            $query = Barang::where('status', 0)->orderBy('id_barang', 'desc')->get();

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
                ->addColumn('fotoProduk', function ($row) {
                    $avatarUrl = $row->photo_filename
                         ? asset($row->photo_filename)
                         : asset('image/no-images.jpg');

                    return '<img class="avatar avatar-md rounded-circle me-2 avatar-online detail"
                                src="'.$avatarUrl.'"
                                alt="Foto Produk"  data-gambar="'.asset($row->photo_filename).'"
                                data-alias="'.$row->nama_barang.'">';
                })
                ->addColumn('cekbok', function ($row) {
                    return '   <div class="form-check form-check-primary mt-3">
                                <input class="form-check-input checkItem" type="checkbox" value="'.$row->id.'"
                                    >
                            </div>';
                })
                ->addColumn('status', function ($row) {
                    if ($row->status == 1) {
                        return '<span class="badge bg-info">Active</span>';
                    } else {
                        return '<span class="badge bg-danger">Not Active</span>';
                    }
                })
                ->addColumn('productType', function ($row) {
                    if ($row->product_type == 'supply') {
                        return '<span class="badge bg-success">Supply</span>';
                    } else {
                        return '<span class="badge bg-secondary">Non Supply</span>';
                    }
                })
                ->addColumn('kategori', function ($row) {
                    return $row->kategoriID->detail;
                })
                ->addColumn('gudang', function ($row) {
                    return $row->warehouseID->nama_gudang;
                })
                // ->addColumn('tipePersediaan', function ($row) {
                //     return $row->typeID->detail;
                // })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">
                      <button type="button" class="btn btn-primary dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-menu-2 ti-xs me-1"></i>Action
                      </button>
                      <ul class="dropdown-menu" style="">';

                    if (auth()->user()->can('barang-restore')) {
                        $btn .= '<a class="dropdown-item restore" href="javascript:void(0)"
                            data-id="'.$row->id.'"> <i class="ti ti-trash-off me-1"></i> Restore</a>';
                    }

                    return $btn;
                })
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'kategori', 'gudang', 'tipePersediaan', 'fotoProduk', 'productType', 'cekbok'])
                ->make(true);
        }

        $x = [
            'title' => 'Deleted Product List',
            'breadcrumb' => [
                ['label' => 'Product', 'url' => route('data-barang.index')],
                ['label' => 'Deleted Product', 'url' => ''],
            ],

        ];

        return view('master_data.barang.data_barang.data_barang_trash', $x);
    }

    public function restore($id)
    {
        DB::beginTransaction();

        try {
            $barang = Barang::find($id);
            $barang->status = 1;
            $barang->updated_by = Auth::id();
            $barang->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'redirect' => true,
                'message' => 'Product successfully restored.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => true,
                'redirect' => true,
                'message' => 'Product successfully restored.',
            ]);
        }
    }

    public function restoreMultiple(Request $request)
    {
        $ids = $request->ids;

        if (! $ids || count($ids) == 0) {
            return response()->json(['success' => false]);
        }

        Barang::whereIn('id', $ids)->update([
            'status' => '1',
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['success' => true]);
    }

    public function print($id)
    {
        $barang = Barang::findOrFail($id);

        $pdf = Pdf::loadView('pdf.barang_pdf', compact('barang'));

        return $pdf->stream('barang.pdf');
        // kalau mau download → ->download('barang.pdf');
    }
}
