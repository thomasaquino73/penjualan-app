<?php

namespace App\Http\Controllers\Master_Data\Barang;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\BasicCodeDetail;
use App\Models\Master_Data\Barang;
use App\Models\Master_Data\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\DataTables;

class DataBarangController extends Controller
{
    public function index(Request $r)
    {
        if ($r->ajax()) {
            $query = Barang::where('status', '<>', 0)->get();

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

                    if (auth()->user()->can('barang-delete')) {
                        $btn .= '<a class="dropdown-item" href="javascript:void(0)" id="delete"
                                data-id="'.$row->id.'"
                                data-name="'.$row->nama_barang.'"
                                ><i class="ti ti-trash"></i> Delete</a>';
                    }

                    return $btn;
                })
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'kategori', 'gudang', 'tipePersediaan', 'fotoProduk', 'productType'])
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
            'typePersediaan' => BasicCodeDetail::where('master_id', 4)->get(),
        ]);
    }

    private function uploadAvatar($avatar)
    {
        $name = uniqid().time();
        $destination = 'image/foto_produk';
        $filePath = $avatar->move($destination, $name.'.'.$avatar->getClientOriginalExtension());

        return str_replace('\\', '/', $filePath);
    }

    public function store(ProductRequest $request)
    {
        DB::beginTransaction();
        try {
                  $isSaveAndNew = $request->input('save_and_new') == '1';
            $data = $request->except(['_token', 'save_and_new']);
            $unit = BasicCodeDetail::find($request->unit_id);
            $data['created_by'] = Auth::id();
            $data['unit1'] = $unit->detail;
            $data['unit2'] = $unit->detail;
    
        
            if ($request->hasFile('photo_filename')) {
                $data['photo_filename'] = $this->uploadAvatar($request->file('photo_filename'));
            }
            Barang::create($data);

            DB::commit();

            if ($isSaveAndNew) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product has been saved. You can now upload a new product.',
                    'redirect' => route('data-barang.create'),
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Product has been successfully added.',
                    'redirect' => route('data-barang.index'),
                ]);
            }
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
        //
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
            'typePersediaan' => BasicCodeDetail::where('master_id', 4)->get(),
            'detail' => $idDetail,
        ]);
    }

    public function update(ProductRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $unit = BasicCodeDetail::find($request->unit_id);
            $isSaveAndNew = $request->input('save_and_new') == '1';

            $barang = Barang::findOrFail($id);

            $data = $request->except(['_token', '_method', 'save_and_new', 'unit1', 'unit2']);

            $data['updated_by'] = Auth::id();
            $data['unit1'] = $unit->detail;
            $data['unit2'] = $unit->detail;
            // jika upload foto baru
            if ($request->hasFile('photo_filename')) {
                // optional: hapus file lama kalau perlu
                // if ($barang->photo_filename) {
                //     Storage::delete($barang->photo_filename);
                // }

                $data['photo_filename'] = $this->uploadAvatar($request->file('photo_filename'));
            }

            $barang->update($data);

            DB::commit();

            if ($isSaveAndNew) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product has been updated. You can now add a new product.',
                    'redirect' => route('data-barang.create'),
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Product has been successfully updated.',
                    'redirect' => route('data-barang.index'),
                ]);
            }

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

    public function getSubUnit($id)
    {
        $data = Barang::where('id',$id)->first();

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'unit_1' => $data->unit_1,
                'unit_2' => $data->unit_2,
                'unit1' => $data->unit1,
                'unit2' => $data->unit2,
                'quantity1' => $data->quantity1,
                'quantity2' => $data->quantity2,
            ]
        ]);
    }
}
