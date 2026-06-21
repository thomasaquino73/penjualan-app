<?php

namespace App\Http\Controllers\Inventory\Barang;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\BasicCodeDetail;
use App\Models\Inventory\Barang;
use App\Models\Inventory\DataBarangConversion;
use App\Models\Inventory\DataBarangStok;
use App\Models\Inventory\StockBalance;
use App\Models\Inventory\Warehouse;
use App\Models\Purchase\Supplier;
use App\Models\Setting\Company;
use App\Models\StockMutation;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Laravel\Facades\Image;
use Yajra\DataTables\Facades\DataTables;

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
            $cutOffDate = Company::value('cut_off_date');
            // dd($cutOffDate);
            $query = Barang::query()
                ->where('status', '<>', 0)
                ->join('basic_code_detail as kategori', 'kategori.id', '=', 'data_barang.kategori_id')
                ->addSelect('data_barang.*')
                ->addSelect([
                  'current_stock' => StockMutation::query()
                        ->selectRaw("
                            COALESCE(
                                SUM(
                                    CASE
                                        WHEN type = 'in'
                                        THEN total_base_qty
                                        ELSE -total_base_qty
                                    END
                                ),
                            0)
                        ")
                        ->whereColumn(
                            'stock_mutations.data_barang_id',
                            'data_barang.id'
                        )
                        ->when($cutOffDate, function ($q) use ($cutOffDate) {
                            $q->whereDate('date_stock', '>=', $cutOffDate);
                        }),
                ])
                ->with([
                    'kategoriID',
                    'unitID',
                    'brandID',
                    'typeID',
                ])
                ->orderBy('kategori.detail')
                ->orderBy('nama_barang');
            if ($r->filled('kategori_id')) {
                $query->where('kategori_id', $r->kategori_id);
            }

            if ($r->filled('brand_id')) {
                $query->where('brand_id', $r->brand_id);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('created_at', function ($row) {
                    return $row->created_at
                        ? ($row->creator->fullname ?? 'Unknown')
                            .'<br><small class="text-muted">'
                            .$row->created_at->diffForHumans()
                            .'</small>'
                        : 'N/A';
                })
                ->addColumn('updated_at', function ($row) {
                    if (! $row->updated_at) {
                        return 'N/A';
                    }
                    $updaterName = $row->updater->fullname ?? 'Unknown';
                    $timeAgo = $updaterName !== 'Unknown'
                        ? $row->updated_at->diffForHumans()
                        : 'N/A';

                    return $updaterName
                        .'<br><small class="text-muted">'
                        .$timeAgo
                        .'</small>';
                })
                ->addColumn('stok', function ($row) {
                    $currentStock = (float) ($row->current_stock ?? 0);
                    $minStock = (float) ($row->primary_minimum_stock ?? 0);
                    $color = $currentStock <= $minStock
                        ? 'bg-danger'
                        : 'bg-primary';

                    return '<span class="badge '.$color.'">'
                        .number_format($currentStock, 0)
                        .' '.($row->unitID?->detail ?? '')
                        .'</span>';
                })
                ->addColumn('fotoProduk', function ($row) {
                    $avatarUrl = $row->photo_filename
                        ? asset($row->photo_filename)
                        : asset('image/no-images.jpg');

                    return '
                    <img
                        class="avatar avatar-md rounded-circle me-2 avatar-online detail"
                        src="'.$avatarUrl.'"
                        alt="Foto Produk"
                        data-gambar="'.$avatarUrl.'"
                        data-alias="'.$row->nama_barang.'">
                ';
                })
                ->addColumn('status', function ($row) {
                    return $row->status == 2
                        ? '<span class="badge bg-info">Active</span>'
                        : '<span class="badge bg-danger">Not Active</span>';
                })
                ->addColumn('brand', function ($row) {
                    return $row->brandID?->detail ?? 'No Brand';
                })
                ->addColumn('productType', function ($row) {
                    return $row->product_type == 'supply'
                        ? '<span class="badge bg-success">Supply</span>'
                        : '<span class="badge bg-secondary">Non Supply</span>';
                })
                ->addColumn('cekbok', function ($row) {
                    return '
                    <div class="form-check form-check-primary mt-3">
                        <input
                            class="form-check-input checkItem"
                            type="checkbox"
                            value="'.$row->id.'">
                    </div>
                ';
                })
                ->addColumn('kategori', function ($row) {
                    return $row->kategoriID?->detail ?? '-';
                })
                ->addColumn('harga', function ($row) {
                    return format_uang(
                        convert_currency(
                            $row->primary_price,
                            $row->currency_id ?? 1
                        )
                    );
                })
                ->addColumn('action', function ($row) {
                    $btn = '
                <div class="btn-group">
                    <button
                        type="button"
                        class="btn btn-primary dropdown-toggle waves-effect waves-light"
                        data-bs-toggle="dropdown">
                        <i class="ti ti-menu-2 ti-xs me-1"></i>
                    </button>

                    <ul class="dropdown-menu">
                ';

                    if (auth()->user()->can('barang-edit')) {

                        $btn .= '
                        <a
                            class="dropdown-item editPost"
                            href="'.route('data-barang.edit', $row->id).'">
                            <i class="far fa-edit"></i> Edit
                        </a>
                    ';
                    }

                    if (auth()->user()->can('barang-read')) {

                        $btn .= '
                        <a
                            class="dropdown-item"
                            href="'.route('data-barang.show', $row->id).'">
                            <i class="ti ti-list-details"></i> Detail
                        </a>
                    ';
                    }

                    if (auth()->user()->can('barang-delete')) {

                        $btn .= '
                        <a
                            class="dropdown-item"
                            href="javascript:void(0)"
                            id="delete"
                            data-id="'.$row->id.'"
                            data-name="'.$row->nama_barang.'">
                            <i class="ti ti-trash"></i> Delete
                        </a>
                    ';
                    }

                    $btn .= '
                    <a
                        class="dropdown-item"
                        href="'.route('data-barang.print', $row->id).'"
                        target="_blank">
                        <i class="ti ti-printer"></i> Print
                    </a>
                ';

                    $btn .= '
                    </ul>
                </div>
                ';

                    return $btn;
                })

                ->rawColumns([
                    'action',
                    'created_at',
                    'updated_at',
                    'harga',
                    'status',
                    'kategori',
                    'fotoProduk',
                    'productType',
                    'cekbok',
                    'stok',
                    'brand',
                ])

                ->make(true);
        }

        $x = [
            'title' => 'Product List',
            'breadcrumb' => [
                [
                    'label' => 'Dashboard',
                    'url' => route('dashboard'),
                ],
                [
                    'label' => 'Product',
                    'url' => '',
                ],
            ],
            'kategori' => BasicCodeDetail::where('master_id', 1)->get(),
            'brand' => BasicCodeDetail::where('master_id', 11)->get(),
        ];

        return view(
            'inventory.barang.data_barang.data_barang_index',
            $x
        );
    }

    private function generateProductId()
    {
        $last = Barang::whereNotNull('id_barang')
            ->orderBy('id', 'desc')
            ->first();

        if (! $last) {
            return 'P-0001';
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
        $company = Company::with('defaultCurrency')->first();

        return view('inventory.barang.data_barang.data_barang_create', [
            'title' => 'Add Product',
            'breadcrumb' => [
                ['label' => 'Product', 'url' => route('data-barang.index')],
                ['label' => 'Add Product', 'url' => ''],
            ],
            'idNumber' => $this->generateProductId(),
            'categories' => BasicCodeDetail::where('master_id', 1)->get(),
            'unit' => BasicCodeDetail::where('master_id', 2)->get(),
            'supplier' => Supplier::where('status', 1)->get(),
            'warehouses' => Warehouse::where('status', 1)->get(),
            'brand' => BasicCodeDetail::where('master_id', 11)->get(),
        ]);
    }
    // protected function uploadAvatar($avatar)
    // {
    //     // 1. Baca gambar asli
    //     $img = Image::read($avatar->getRealPath());

    //     // 2. Tambahkan Watermark dengan pengecekan file
    //     $watermarkPath = public_path('image/logo/watermark.png');
    //     if (file_exists($watermarkPath)) {
    //         // opsional: resize watermark agar tidak terlalu besar
    //         $watermark = Image::read($watermarkPath);
    //         $watermark->scale(width: 200);

    //         $img->place($watermark, 'bottom-right', 10, 10);
    //     }

    //     // 3. Tentukan nama dan lokasi
    //     $name = uniqid() . time();
    //     $extension = $avatar->getClientOriginalExtension();
    //     $filename = $name . '.' . $extension;
    //     $destination = public_path('image/foto_produk');

    //     // 4. Pastikan folder tujuan ada
    //     if (!file_exists($destination)) {
    //         mkdir($destination, 0775, true);
    //     }

    //     // 5. Simpan hasil gambar
    //     $img->save($destination . '/' . $filename);

    //     // 6. Kembalikan path untuk disimpan ke database
    //     return 'image/foto_produk/' . $filename;
    // }
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

            $data = $request->except(['_token', 'save_and_new', 'conversion', 'variants']);
            $itemsDetailRaw = $request->input('items_detail');
            unset($data['items_detail']);
            $data['created_by'] = Auth::id();
            $data['status'] = $request->has('status') ? 1 : 2;

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
            $conversions = $request->conversion ?? [];

            // pastikan selalu ada 2 index
            for ($i = 0; $i < 2; $i++) {

                $conv = $conversions[$i] ?? [];

                DataBarangConversion::create([
                    'data_barang_id' => $barang->id,
                    'to_unit_id' => $request->unit_id, // selalu dari unit utama
                    'from_unit_id' => $conv['to_unit'] ?? null,
                    'qty' => $conv['qty'] ?? 0,
                ]);
            }

            // 2. Looping data varian yang masuk
            foreach ($request->variants as $variantData) {

                // Satukan inputan label & value user menjadi array associative JSON
                // Contoh keluaran: ["Panjang" => "10cm", "Lebar" => "5cm", "Warna" => "Hijau"]
                $customSpecs = [];
                foreach ($variantData['specs'] as $spec) {
                    if (! empty($spec['label'])) {
                        $customSpecs[$spec['label']] = $spec['value'];
                    }
                }

                // Simpan ke database
                $barang->variants()->create([
                    'variant_name' => $variantData['name'],
                    'specifications' => $customSpecs, // Otomatis tersimpan sebagai JSON berkat casting
                ]);
            }

            // 3. Decode data array string JSON (`items_detail`) yang dikirim dari DataTables lokal
            $items = json_decode($request->items_detail, true);

            foreach ($items as $item) {
                $qty_input = $item['quantity'] ?? $item['qty'] ?? 0;
                $unit_id = $item['stok_unit_id'] ?? $request->unit_id; // unit saat transaksi

                // HITUNG KONVERSI KE BASE UNIT (PCS)
                // Ambil conversion rate dari tabel DataBarangConversion
                $conversion = DataBarangConversion::where('data_barang_id', $barang->id)
                    ->where('from_unit_id', $unit_id)
                    ->first();

                // Jika unit yang dipakai adalah unit dasar, rate = 1, jika tidak ambil dari DB
                $rate = ($unit_id == $request->unit_id) ? 1 : ($conversion->qty ?? 1);
                $total_base_qty = $qty_input * $rate;

                // A. SIMPAN KE DATA BARANG STOK (Tabel lama Anda)
                DataBarangStok::create([
                    'data_barang_id' => $barang->id,
                    'date_stock' => Carbon::parse($item['date'])->format('Y-m-d'),
                    'quantity' => $qty_input,
                    'stok_unit_id' => $unit_id,
                    'warehouse_id' => $item['warehouse_id'] ?? null,
                    'price' => floatval($item['unit_price'] ?? 0),
                ]);

                // B. SIMPAN KE STOCK_MUTATIONS (Tabel baru untuk Laporan/Audit)
                StockMutation::create([
                    'data_barang_id' => $barang->id,
                    'unit_id' => $unit_id,
                    'warehouse_id' => $item['warehouse_id'] ?? null,
                    'date_stock' => Carbon::parse($item['date'])->format('Y-m-d'),
                    'qty_transaksi' => $qty_input,
                    'total_base_qty' => $total_base_qty,
                    'type' => 'in', // Karena ini input stok awal
                    'keterangan' => 'Stok Awal',
                    'created_by' => Auth::id(),
                    'document_type' => 'initial_stock',
                ]);

                // C. UPDATE QUANTITY DI TABEL BARANG (Total Stok Akhir)
                // $barang->increment('quantity', $total_base_qty);
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

    public function show(string $id)
    {
        $idDetail = Barang::with([
            'variants',
            'stockHistories.warehouseID',
            'stockHistories.unitID',
            'mutations.unitID',
        ])->findOrFail($id);

        $cutOffDate = Company::value('cut_off_date');

        /*
        |--------------------------------------------------------------------------
        | OPENING BALANCE
        |--------------------------------------------------------------------------
        */
        $openingBalance = 0;

        /*
        |--------------------------------------------------------------------------
        | MUTASI SETELAH CUT OFF
        |--------------------------------------------------------------------------
        */
        $mutations = $idDetail->mutations()
            ->when($cutOffDate, function ($q) use ($cutOffDate) {
                $q->whereDate('date_stock', '>=', $cutOffDate);
            })
            ->orderBy('date_stock')
            ->orderBy('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | RUNNING BALANCE
        |--------------------------------------------------------------------------
        */
        $saldo = $openingBalance;

        foreach ($mutations as $mutation) {

            if ($mutation->type == 'in') {
                $saldo += $mutation->total_base_qty;
            } else {
                $saldo -= $mutation->total_base_qty;
            }

            $mutation->saldo_akhir = $saldo;
        }

        $currentStock = $saldo;

        /*
        |--------------------------------------------------------------------------
        | UNIT CONVERSION
        |--------------------------------------------------------------------------
        */
        $unitConversion = DataBarangConversion::where(
            'data_barang_id',
            $idDetail->id
        )
            ->where('qty', '>', 0)
            ->get();

        return view(
            'inventory.barang.data_barang.data_barang_detail',
            [
                'title' => 'Detail Product',

                'breadcrumb' => [
                    [
                        'label' => 'Product',
                        'url' => route('data-barang.index'),
                    ],
                    [
                        'label' => 'Detail Product',
                        'url' => '',
                    ],
                ],

                'detail' => $idDetail,
                'mutations' => $mutations,
                'unitConversion' => $unitConversion,
                'stok' => $unitConversion,
                'warehouseHistory' => $this->getWarehouse($idDetail->id),

                'cutOffDate' => $cutOffDate,
                'openingBalance' => $openingBalance,
                'currentStock' => $currentStock,
            ]
        );
    }

    private function getWarehouse($data_barang_id)
    {
        $date = request('date');
        $warehouse_id = request('warehouse_id');

        $query = StockMutation::query()
            ->with('warehouseID')
            ->select(
                'warehouse_id',
                DB::raw("
                COALESCE(
                    SUM(
                        CASE
                            WHEN type = 'in'
                            THEN total_base_qty
                            WHEN type = 'out'
                            THEN -total_base_qty
                            ELSE 0
                        END
                    ),
                0) as total_qty
            ")
            )
            ->where('data_barang_id', $data_barang_id);

        if (! empty($date)) {

            try {

                $formattedDate = Carbon::createFromFormat(
                    'd-m-Y',
                    $date
                )->format('Y-m-d');

                $query->where(function ($q) use ($formattedDate) {

                    $q->whereDate(
                        'date_stock',
                        '<=',
                        $formattedDate
                    )
                        ->orWhereNull('date_stock');
                });

            } catch (\Exception $e) {
            }
        }

        if (! empty($warehouse_id)) {

            $query->where(
                'warehouse_id',
                $warehouse_id
            );
        }

        return $query
            ->groupBy('warehouse_id')
            ->havingRaw('total_qty <> 0')
            ->get()
            ->map(function ($item) {

                return [
                    'warehouse_id' => $item->warehouse_id,
                    'warehouse_name' => optional(
                        $item->warehouseID
                    )->nama_gudang ?? '-',

                    'total_qty' => (float) $item->total_qty,
                ];
            });
    }

    private function getMutation($data_barang_id)
    {
        $date = request('date');

        $query = StockMutation::with([
            'unitID',
            'warehouseID',
        ])
            ->where(
                'data_barang_id',
                $data_barang_id
            )
            ->orderBy(
                'date_stock',
                'asc'
            )
            ->orderBy(
                'id',
                'asc'
            );

        if (! empty($date)) {

            try {

                $formattedDate = Carbon::createFromFormat(
                    'd-m-Y',
                    $date
                )->format('Y-m-d');

                $query->whereDate(
                    'date_stock',
                    '<=',
                    $formattedDate
                );

            } catch (\Exception $e) {
            }
        }

        $mutations = $query->get();

        $runningBalance = 0;

        foreach ($mutations as $mutation) {

            $baseQty = (float) $mutation->total_base_qty;

            if ($mutation->type === 'in') {

                $runningBalance += $baseQty;

            } else {

                $runningBalance -= $baseQty;
            }

            $mutation->saldo_akhir = $runningBalance;

            /*
             * Qty transaksi asli
             * contoh:
             * 20 PCS
             * 25 Pack
             */
            $mutation->qty_display = (float) $mutation->qty_transaksi;

            /*
             * Qty setelah konversi ke base unit
             * contoh:
             * 20 PCS
             * 300 PCS
             */
            $mutation->base_qty_display = $baseQty;
        }

        return $mutations
            ->sortByDesc(function ($item) {

                return $item->date_stock.'-'.$item->id;
            })
            ->values();
    }

    public function edit(string $id)
    {
        // Mengambil data dengan relasi yang diperlukan
        $idDetail = Barang::with([
            'variants',
            'stockHistories.warehouseID', // Pastikan relasi ini ada di Model Barang
            'stockHistories.unitID',      // Pastikan relasi ini ada di Model Barang
        ])->findOrFail($id);

        // Mengambil data referensi untuk form modal
        $categories = BasicCodeDetail::where('master_id', 1)->get();
        $unit = BasicCodeDetail::where('master_id', 2)->get();
        $warehouses = Warehouse::where('status', 1)->get();
        $suppliers = Supplier::where('status', 1)->get();

        return view('inventory.barang.data_barang.data_barang_edit', [
            'title' => 'Edit Product',
            'breadcrumb' => [
                ['label' => 'Product', 'url' => route('data-barang.index')],
                ['label' => 'Edit Product', 'url' => ''],
            ],
            'idNumber' => $this->generateProductId(),
            'categories' => $categories,
            'supplier' => $suppliers,
            'unit' => $unit,
            'sub_unit' => $unit,
            'warehouses' => $warehouses,
            'detail' => $idDetail,
            'brand' => BasicCodeDetail::where('master_id', 11)->get(),
        ]);
    }

    public function update(ProductRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $isSaveAndNew = $request->input('save_and_new') == '1';
            $barang = Barang::findOrFail($id);

            // ==================================================
            // 1. UPDATE DATA MASTER BARANG
            // ==================================================
            $data = $request->except(['_token', '_method', 'save_and_new', 'conversion', 'variants', 'items_detail']);
            $data['updated_by'] = Auth::id();
            $data['status'] = $request->has('status') ? 1 : 2;

            if ($request->hasFile('photo_filename')) {
                if ($barang->photo_filename && file_exists(public_path('uploads/products/'.$barang->photo_filename))) {
                    unlink(public_path('uploads/products/'.$barang->photo_filename));
                }
                $data['photo_filename'] = $this->uploadAvatar($request->file('photo_filename'));
            }
            $barang->update($data);

            // 1. Ambil data konversi yang valid saja
            $newConversions = [];
            if ($request->has('conversion')) {
                foreach ($request->conversion as $conv) {
                    if (! empty($conv['to_unit']) && $conv['to_unit'] !== 'Select Unit') {
                        $newConversions[] = $conv;
                    }
                }
            }

            // 2. Pastikan minimal ada 2 index (tambahkan array kosong jika kurang dari 2)
            while (count($newConversions) < 2) {
                $newConversions[] = ['to_unit' => null, 'qty' => 0];
            }

            // 3. Update database
            // Menggunakan transaction agar lebih aman
            DB::transaction(function () use ($barang, $request, $newConversions) {
                // Hapus data lama
                DataBarangConversion::where('data_barang_id', $barang->id)->delete();

                // Insert data yang sudah dipastikan minimal 2
                foreach ($newConversions as $conv) {
                    DataBarangConversion::create([
                        'data_barang_id' => $barang->id,
                        'to_unit_id' => $request->unit_id,
                        'from_unit_id' => $conv['to_unit'] ?? null,
                        'qty' => $conv['qty'] ?? 0,
                    ]);
                }
            });

            // ==================================================
            // 3. UPDATE VARIAN
            // ==================================================
            $barang->variants()->delete();
            if ($request->has('variants') && is_array($request->variants)) {
                foreach ($request->variants as $variantData) {
                    if (empty($variantData['name'])) {
                        continue;
                    }

                    $customSpecs = [];
                    foreach ($variantData['specs'] ?? [] as $spec) {
                        if (! empty($spec['label'])) {
                            $customSpecs[$spec['label']] = $spec['value'];
                        }
                    }

                    $barang->variants()->create([
                        'variant_name' => $variantData['name'],
                        'specifications' => $customSpecs,
                    ]);
                }
            }

            // ==================================================
            // 4. SINKRONISASI STOK & MUTASI (Audit Trail)
            // ==================================================
            $items = json_decode($request->items_detail, true) ?? [];

            // Hapus stok lama di tabel pendukung dan mutasi awal
            DataBarangStok::where('data_barang_id', $barang->id)->delete();
            StockMutation::where('data_barang_id', $barang->id)
                ->where('document_type', 'initial_stock')
                ->delete();

            $totalBaseQtyBaru = 0;

            foreach ($items as $item) {
                $qty_input = $item['quantity'] ?? $item['qty'] ?? 0;
                $unit_id = $item['stok_unit_id'] ?? $request->unit_id;

                // Hitung Konversi ke Base Unit (PCS)
                $conversion = DataBarangConversion::where('data_barang_id', $barang->id)
                    ->where('from_unit_id', $unit_id)->first();
                $rate = ($unit_id == $request->unit_id) ? 1 : ($conversion->qty ?? 1);
                $total_base_qty = $qty_input * $rate;

                $totalBaseQtyBaru += $total_base_qty;

                // Simpan ke tabel pendukung
                DataBarangStok::create([
                    'data_barang_id' => $barang->id,
                    'date_stock' => Carbon::parse($item['date_stock'])->format('Y-m-d'),
                    'quantity' => $qty_input,
                    'stok_unit_id' => $unit_id,
                    'warehouse_id' => $item['warehouse_id'] ?? null,
                    'price' => floatval($item['unit_price'] ?? 0),
                ]);

                // SIMPAN KE STOCK_MUTATIONS (Audit Trail/Buku Besar)
                StockMutation::create([
                    'data_barang_id' => $barang->id,
                    'unit_id' => $unit_id,
                    'warehouse_id' => $item['warehouse_id'] ?? null,
                    'date_stock' => Carbon::parse($item['date_stock'])->format('Y-m-d'),
                    'qty_transaksi' => $qty_input,
                    'total_base_qty' => $total_base_qty,
                    'type' => 'in',
                    'keterangan' => 'Stok Awal',
                    'document_type' => 'initial_stock',
                    'updated_by' => Auth::id(),
                ]);
            }

            // Update total saldo akhir
            $barang->update(['quantity' => $totalBaseQtyBaru]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product has been successfully updated.',
                'redirect' => $isSaveAndNew ? route('data-barang.create') : route('data-barang.index'),
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

                // ->addColumn('tipePersediaan', function ($row) {
                //     return $row->typeID->detail;
                // })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">
                      <button type="button" class="btn btn-primary dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-menu-2 ti-xs me-1"></i>
                      </button>
                      <ul class="dropdown-menu" style="">';

                    if (auth()->user()->can('barang-restore')) {
                        $btn .= '<a class="dropdown-item restore" href="javascript:void(0)"
                            data-id="'.$row->id.'"> <i class="ti ti-trash-off me-1"></i> Restore</a>';
                    }

                    return $btn;
                })
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'kategori', 'tipePersediaan', 'fotoProduk', 'productType', 'cekbok'])
                ->make(true);
        }

        $x = [
            'title' => 'Deleted Product List',
            'breadcrumb' => [
                ['label' => 'Product', 'url' => route('data-barang.index')],
                ['label' => 'Deleted Product', 'url' => ''],
            ],

        ];

        return view('inventory.barang.data_barang.data_barang_trash', $x);
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

    public function getCurrentStockAttribute()
    {
        return $this->mutations()
            ->selectRaw("SUM(CASE WHEN type = 'in' THEN total_base_qty ELSE -total_base_qty END) as total")
            ->value('total') ?? 0;
    }

    public function print($id)
    {
        $barang = Barang::with([
            'kategoriID',
            'unitID',
            'variants',
            'conversions.fromUnitID',
            'conversions.toUnitID',
            'mutations',
        ])->findOrFail($id);

        $cutOffDate = Company::value('cut_off_date');

        /*
        |--------------------------------------------------------------------------
        | SALDO AWAL (SAMPAI CUT OFF)
        |--------------------------------------------------------------------------
        */
        // $openingBalance = $barang->mutations()
        //     ->when($cutOffDate, function ($q) use ($cutOffDate) {
        //         $q->whereDate('date_stock', '>=', $cutOffDate);
        //     })
        //     ->selectRaw("
        //         COALESCE(
        //             SUM(
        //                 CASE
        //                     WHEN type = 'in'
        //                     THEN total_base_qty
        //                     ELSE -total_base_qty
        //                 END
        //             ),
        //         0) as total
        //     ")
        //     ->value('total') ?? 0;

        /*
|--------------------------------------------------------------------------
| OPENING BALANCE
|--------------------------------------------------------------------------
*/
        $openingBalance = 0;

        /*
        |--------------------------------------------------------------------------
        | MUTASI SETELAH CUT OFF
        |--------------------------------------------------------------------------
        */
        $mutations = $barang->mutations()
            ->when($cutOffDate, function ($q) use ($cutOffDate) {
                $q->whereDate('date_stock', '>', $cutOffDate);
            })
            ->orderBy('date_stock')
            ->orderBy('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | RUNNING BALANCE
        |--------------------------------------------------------------------------
        */
        $saldo = $openingBalance;

        $mutations->transform(function ($item) use (&$saldo) {

            $qty = $item->total_base_qty;

            if ($item->type == 'in') {
                $saldo += $qty;
            } else {
                $saldo -= $qty;
            }

            $item->saldo_akhir = $saldo;

            return $item;
        });

        /*
        |--------------------------------------------------------------------------
        | STOCK PER GUDANG
        |--------------------------------------------------------------------------
        */
        $warehouseHistory = $barang->mutations()
            ->when($cutOffDate, function ($q) use ($cutOffDate) {
                $q->whereDate('date_stock', '>=', $cutOffDate);
            })
            ->selectRaw("
        warehouse_id,
        SUM(
            CASE
                WHEN type='in'
                THEN total_base_qty
                ELSE -total_base_qty
            END
        ) as total_qty
    ")
            ->groupBy('warehouse_id')
            ->with('warehouseID')
            ->get()
            ->map(function ($item) {
                return [
                    'warehouse_name' => $item->warehouseID->nama_gudang ?? '-',
                    'total_qty' => $item->total_qty,
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | STOK SAAT INI
        |--------------------------------------------------------------------------
        */
        $currentStock = $saldo;

        $pdf = Pdf::loadView('pdf.barang_pdf', [
            'title' => 'Detail Barang',
            'detail' => $barang,
            'unitConversion' => $barang->conversions,
            'mutations' => $mutations,
            'warehouseHistory' => $warehouseHistory,
            'cutOffDate' => $cutOffDate,
            'openingBalance' => $openingBalance,
            'currentStock' => $currentStock,
        ]);

        return $pdf->stream('barang.pdf');
    }

    public function print_all()
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(300);

        $cutOffDate = Company::value('cut_off_date');

        /*
        |--------------------------------------------------------------------------
        | STOCK BERDASARKAN CUT OFF DATE
        |--------------------------------------------------------------------------
        */
        $stock = DB::table('stock_mutations')
            ->selectRaw("
            data_barang_id,

            COALESCE(
                SUM(
                    CASE
                        WHEN type = 'in'
                        THEN total_base_qty
                        ELSE -total_base_qty
                    END
                ),
            0) as stock
        ")
            ->when($cutOffDate, function ($q) use ($cutOffDate) {
                $q->whereDate('date_stock', '>=', $cutOffDate);
            })
            ->groupBy('data_barang_id');

        /*
        |--------------------------------------------------------------------------
        | DATA BARANG
        |--------------------------------------------------------------------------
        */
        $barangs = Barang::query()
            ->where('status', '<>', 0)

            ->leftJoinSub($stock, 'stock', function ($join) {
                $join->on(
                    'data_barang.id',
                    '=',
                    'stock.data_barang_id'
                );
            })

            ->join(
                'basic_code_detail',
                'basic_code_detail.id',
                '=',
                'data_barang.kategori_id'
            )

            ->with([
                'kategoriID',
                'unitID',
                'brandID',
                'typeID',
            ])

            ->select([
                'data_barang.*',
            ])

            ->addSelect(DB::raw('
            COALESCE(stock.stock, 0) as current_stock
        '))

            ->orderBy('basic_code_detail.detail')
            ->orderBy('data_barang.nama_barang')

            ->get();

        /*
        |--------------------------------------------------------------------------
        | PDF
        |--------------------------------------------------------------------------
        */
        $pdf = Pdf::loadView(
            'pdf.barang_all_pdf',
            [
                'barangs' => $barangs,
                'cutOffDate' => $cutOffDate,
            ]
        );

        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream('barang_all.pdf');
    }

    public function getStockBalance($productId, $warehouseId)
    {
        $balance = StockBalance::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        return response()->json([
            'qty' => $balance?->qty ?? 0,
        ]);
    }
}
