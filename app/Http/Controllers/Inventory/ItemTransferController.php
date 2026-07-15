<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\ItemTransferRequest;
use App\Models\BasicCodeDetail;
use App\Models\Inventory\Barang;
use App\Models\Inventory\DataBarangConversion;
use App\Models\Inventory\ItemTransfer;
use App\Models\Inventory\ItemTransferDetail;
use App\Models\Inventory\StockBalance;
use App\Models\Inventory\Warehouse;
use App\Models\Setting\Company;
use App\Models\StockMutation;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ItemTransferController extends Controller
{
    public function index(Request $r)
    {
        if ($r->ajax()) {
            $userId = Auth::user()->id;
            $query = ItemTransfer::where('active', '<>', 0)->where(function ($q) use ($userId) {
                $q->where('status', '<>', 'draft')
                    ->orWhere(function ($subQ) use ($userId) {
                        $subQ->where('status', 'draft')
                            ->where('created_by', $userId);
                    });
            })
                ->orderby('transfer_code', 'desc');

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
                ->addColumn('transfer_date', function ($row) {
                    return Carbon::parse($row->transfer_date)->format('d-m-Y');
                })
                ->addColumn('from_warehouse', function ($row) {
                    return $row->fromWarehouse->nama_gudang;
                })
                ->addColumn('to_warehouse', function ($row) {
                    return $row->toWarehouse->nama_gudang;
                })
                ->addColumn('cekbok', function ($row) {

                    if (
                        auth()->user()->can('item_transfer-delete') &&
                        $row->status === 'draft'
                    ) {
                        return '
                            <div class="form-check form-check-primary">
                                <input class="form-check-input checkItem"
                                    type="checkbox"
                                    value="'.$row->id.'">
                            </div>
                        ';
                    }

                    return '';
                })
                ->addColumn('status', function ($row) {

                    switch ($row->status) {

                        case 'draft':
                            $badge = 'bg-label-secondary';
                            $text = 'Draft';
                            break;

                        case 'processing':
                            $badge = 'bg-label-warning';
                            $text = 'Processing';
                            break;
                        case 'completed':
                            $badge = 'bg-success';
                            $text = 'Completed';
                            break;
                        case 'cancelled':
                            $badge = 'bg-danger';
                            $text = 'Cancelled';
                            break;
                        default:
                            $badge = 'bg-label-secondary';
                            $text = ucfirst(str_replace('_', ' ', $row->status));
                            break;
                    }

                    $html = '
                        <div class="d-flex flex-column">
                            <span class="badge '.$badge.' text-uppercase">
                                '.$text.'
                            </span>
                    ';

                    // APPROVED INFO
                    if ($row->status == 'approved' && $row->approvedBy) {

                        $html .= '
                        <small class="text-muted mt-1">
                            Approved By : '.$row->approvedBy->fullname.'
                        </small>
                    ';
                    }
                    $html .= '</div>';

                    return $html;
                })

                ->addColumn('action', function ($row) {

                    $currentUserId = Auth::user()->id;
                    $user = auth()->user();

                    $btn = '
                            <div class="btn-group">
                                <button type="button"
                                    class="btn btn-primary dropdown-toggle waves-effect waves-light"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <i class="ti ti-menu-2 ti-xs me-1"></i>
                                </button>

                                <ul class="dropdown-menu">
                        ';

                    /*
                    |--------------------------------------------------------------------------
                    | 1. OWNER ACTION
                    |--------------------------------------------------------------------------
                    */

                    if ($row->created_by == $currentUserId) {

                        if ($row->status == 'draft') {

                            $btn .= '
                                <a class="dropdown-item process-data"
                                    href="javascript:void(0)"
                                    data-id="'.$row->id.'">

                                    <i class="ti ti-send me-1"></i>
                                    Send To Process
                                </a>
                            ';
                            $btn .= '<hr class="dropdown-divider">';
                        }

                    }
                    // EDIT
                    if (
                        $user->can('item_transfer-edit') &&
                        in_array($row->status, ['draft', 'processing'])
                    ) {

                        $btn .= '
                                <a class="dropdown-item"
                                    href="'.route('item-transfer.edit', $row->id).'">

                                    <i class="far fa-edit me-1"></i>
                                    Edit
                                </a>
                            ';
                    }

                    // DELETE
                    if (
                        $user->can('item_transfer-delete') &&
                      in_array($row->status, ['draft', 'processing'])
                    ) {

                        $btn .= '
                                <a class="dropdown-item text-danger"
                                    href="javascript:void(0)"
                                    id="delete"
                                    data-id="'.$row->id.'"
                                    data-name="'.$row->transfer_code.'">

                                    <i class="ti ti-trash me-1"></i>
                                    Delete
                                </a>
                            ';
                    }
                    /*
                    |--------------------------------------------------------------------------
                    | 2. APPROVAL ACTION
                    |--------------------------------------------------------------------------
                    */

                    // if (
                    //     $row->created_by != $currentUserId &&
                    //     $user->can('item_transfer-approval')
                    // ) {

                    //     if ($row->status == 'pending') {

                    //         $btn .= '
                    //                 <a class="dropdown-item text-success btn-approval"
                    //                     href="javascript:void(0)"
                    //                     data-status="approved"
                    //                     data-id="'.$row->id.'">

                    //                     <i class="ti ti-check me-1"></i>
                    //                     Approve
                    //                 </a>
                    //             ';

                    //         $btn .= '
                    //                 <a class="dropdown-item text-danger btn-approval"
                    //                     href="javascript:void(0)"
                    //                     data-status="rejected"
                    //                     data-id="'.$row->id.'">

                    //                     <i class="ti ti-x me-1"></i>
                    //                     Reject
                    //                 </a>
                    //             ';
                    //     }
                    // }

                    /*
                    |--------------------------------------------------------------------------
                    | 5. CANCEL
                    |--------------------------------------------------------------------------
                    */

                    if (
                        ! in_array($row->status, ['completed']) &&
                        $user->can('item_transfer-cancel')
                    ) {

                        $btn .= '
                        <a class="dropdown-item text-danger btn-cancel-po"
                            href="javascript:void(0)"
                            data-id="'.$row->id.'">

                            <i class="ti ti-circle-x me-1"></i>
                            Cancel
                        </a>
                    ';
                    }
                    /*
                    |--------------------------------------------------------------------------
                    | 7. PRINT
                    |--------------------------------------------------------------------------
                    */

                    $btn .= '
                    <a class="dropdown-item"
                        target="_blank"
                        href="'.route('item-transfer.print', $row->id).'">

                        <i class="ti ti-printer me-1"></i>
                        Print / PDF
                    </a>
                        ';

                    $btn .= '
                    </ul>
                </div>
            ';

                    return $btn;
                })
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'cekbok', 'transfer_date', 'from_warehouse', 'to_warehouse'])
                ->make(true);
        }

        $x = [
            'title' => 'Item Transfer List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Item Transfer', 'url' => ''],
            ],
        ];

        return view('inventory.itemTransfer.item_transfer_index', $x);
    }

    public function bulanRomawi($bulan)
    {
        $romawi = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];

        return $romawi[$bulan] ?? 'I';
    }

    private function generateNumberId()
    {
        $year = date('Y');
        $month = $this->bulanRomawi(date('n'));

        // 🔥 ambil data terakhir berdasarkan tahun & bulan yg sama
        $last = ItemTransfer::where('transfer_code', 'like', "IT/$year/$month/%")
            ->orderBy('id', 'desc')
            ->first();

        if (! $last) {
            return "IT/$year/$month/0001";
        }

        $lastId = $last->transfer_code;

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

    public function create()
    {
        $x = [
            'title' => 'Item Transfer New',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Item Transfer', 'url' => ''],
            ],
            'idNumber' => $this->generateNumberId(),
            'product' => Barang::with(['unitID'])->where('status', '<>', 0)->get(),
            'fromWarehouse' => Warehouse::where('status', '<>', 0)->get(),
            'toWarehouse' => Warehouse::where('status', '<>', 0)->get(),
        ];

        return view('inventory.itemTransfer.item_transfer_create', $x);
    }

    public function store(ItemTransferRequest $r)
    {
        DB::beginTransaction();

        try {
            $data = $r->except('save_and_new', 'items_detail');
            $itemsDetailRaw = $r->input('items_detail');
            unset($data['items_detail']);
            do {
                $generatedCode = $this->generateNumberId();
                $exists = ItemTransfer::where('transfer_code', $generatedCode)->exists();
            } while ($exists);
            $data['transfer_code'] = $generatedCode;
            $data['transfer_date'] = Carbon::parse($r->transfer_date)->format('Y-m-d');
            $data['created_by'] = Auth::id();
            $itemTransfer = ItemTransfer::create($data);
            if ($itemsDetailRaw) {

                $items = json_decode($itemsDetailRaw, true);

                if (is_array($items) && count($items) > 0) {

                    foreach ($items as $item) {

                        $qty = $item['quantity'] ?? $item['qty'];
                        $fromgudang = Warehouse::find($r->from_warehouse_id);
                        $fromnamaGudang = $fromgudang ? $fromgudang->nama_gudang : 'Unknown';
                        $togudang = Warehouse::find($r->to_warehouse_id);
                        $tonamaGudang = $togudang ? $togudang->nama_gudang : 'Unknown';
                        /*
                        |--------------------------------------------------------------------------
                        | Transfer Detail
                        |--------------------------------------------------------------------------
                        */
                        ItemTransferDetail::create([
                            'item_transfer_id' => $itemTransfer->id,
                            'data_barang_id' => $item['product_id'],
                            'qty' => $qty,
                            'unit_id' => $item['unit_id'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        /*
                        |--------------------------------------------------------------------------
                        | Stock Mutation OUT
                        |--------------------------------------------------------------------------
                        */
                        // StockMutation::create([
                        //     'data_barang_id' => $item['product_id'],
                        //     'unit_id' => $item['unit_id'],
                        //     'warehouse_id' => $r->from_warehouse_id,
                        //     'date_stock' => Carbon::parse($r->transfer_date)->format('Y-m-d'),
                        //     'qty_transaksi' => $qty,
                        //     'total_base_qty' => $qty,
                        //     'type' => 'out',
                        //     'document_number' => $itemTransfer->transfer_code,
                        //     'document_type' => 'item_transfer',
                        //     // 'item_transfer_id' => $itemTransfer->id,
                        //     'keterangan' => 'Keluar barang dari : '.$fromnamaGudang.' menuju '.$tonamaGudang,
                        //     'created_by' => Auth::id(),
                        // ]);

                        /*
                        |--------------------------------------------------------------------------
                        | Stock Mutation IN
                        |--------------------------------------------------------------------------
                        */
                        // StockMutation::create([
                        //     'data_barang_id' => $item['product_id'],
                        //     'unit_id' => $item['unit_id'],
                        //     'warehouse_id' => $r->to_warehouse_id,
                        //     'date_stock' => Carbon::parse($r->transfer_date)->format('Y-m-d'),
                        //     'qty_transaksi' => $qty,
                        //     'total_base_qty' => $qty,
                        //     'type' => 'in',
                        //     'document_number' => $itemTransfer->transfer_code,
                        //     'document_type' => 'item_transfer',
                        //     // 'item_transfer_id' => $itemTransfer->id,
                        //     'keterangan' => 'Masuk barang dari : '.$fromnamaGudang.' menuju '.$tonamaGudang,
                        //     'created_by' => Auth::id(),
                        // ]);

                        /*
                        |--------------------------------------------------------------------------
                        | Kurangi Stock Balance Gudang Asal
                        |--------------------------------------------------------------------------
                        */
                        // StockBalance::where([
                        //     'product_id' => $item['product_id'],
                        //     'warehouse_id' => $r->from_warehouse_id,
                        // ])->decrement('qty', $qty);

                        /*
                        |--------------------------------------------------------------------------
                        | Tambah Stock Balance Gudang Tujuan
                        |--------------------------------------------------------------------------
                        */
                        // StockBalance::updateOrCreate(
                        //     [
                        //         'product_id' => $item['product_id'],
                        //         'warehouse_id' => $r->to_warehouse_id,
                        //     ],
                        //     [
                        //         'qty' => 0,
                        //     ]
                        // );

                        // StockBalance::where([
                        //     'product_id' => $item['product_id'],
                        //     'warehouse_id' => $r->to_warehouse_id,
                        // ])->increment('qty', $qty);
                    }
                }
            }
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Data created successfully',
                'redirect' => route('item-transfer.index'), // Sesuaikan route
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data: '.$e->getMessage(),
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
        $itemTransfer = ItemTransfer::with([
            'fromWarehouse',
            'toWarehouse',
            'pic',
            'details',
            'details.produkID',
            'details.unitID',
        ])->findOrFail($id);
        $x = [
            'title' => 'Edit Item Transfer ',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Edit Item Transfer', 'url' => ''],
            ],
            'idNumber' => $this->generateNumberId(),
            'product' => Barang::where('status', '<>', 0)->get(),
            'fromWarehouse' => Warehouse::where('status', '<>', 0)->get(),
            'toWarehouse' => Warehouse::where('status', '<>', 0)->get(),
            'model' => $itemTransfer,
        ];

        return view('inventory.itemTransfer.item_transfer_edit', $x);
    }

    public function update(ItemTransferRequest $r, string $id)
    {
        DB::beginTransaction();

        try {

            $itemTransfer = ItemTransfer::findOrFail($id);

            /*
            |--------------------------------------------------------------------------
            | 1. Hapus Stock Mutation Lama
            |--------------------------------------------------------------------------
            */

            StockMutation::where([
                'document_id' => $itemTransfer->id,
                'document_type' => 'item_transfer',
            ])->delete();

            /*
            |--------------------------------------------------------------------------
            | 2. Hapus Detail Lama
            |--------------------------------------------------------------------------
            */

            ItemTransferDetail::where('item_transfer_id', $itemTransfer->id)
                ->delete();

            /*
            |--------------------------------------------------------------------------
            | 3. Update Header
            |--------------------------------------------------------------------------
            */

            $data = $r->except(
                'save_and_new',
                'items_detail'
            );

            $data['transfer_date'] = Carbon::parse(
                $r->transfer_date
            )->format('Y-m-d');

            $data['updated_by'] = Auth::id();

            $itemTransfer->update($data);

            /*
            |--------------------------------------------------------------------------
            | 4. Insert Detail Baru + Stock
            |--------------------------------------------------------------------------
            */

            $itemsDetailRaw = $r->input('items_detail');

            if ($itemsDetailRaw) {

                $items = json_decode($itemsDetailRaw, true);

                if (is_array($items) && count($items) > 0) {

                    $fromGudang = Warehouse::find($r->from_warehouse_id);

                    $toGudang = Warehouse::find($r->to_warehouse_id);

                    $fromNamaGudang = $fromGudang?->nama_gudang ?? 'Unknown';

                    $toNamaGudang = $toGudang?->nama_gudang ?? 'Unknown';

                    foreach ($items as $item) {

                        $productId = $item['product_id'];

                        $qtyInput = (float) ($item['quantity'] ?? $item['qty']);

                        $unitInput = $item['unit_id'];

                        /*
                        |--------------------------------------------------------------------------
                        | Hitung Base Qty
                        |--------------------------------------------------------------------------
                        */

                        $product = Barang::findOrFail($productId);

                        $baseUnitId = $product->unit_id;

                        $totalBaseQty = $qtyInput;

                        if ($unitInput != $baseUnitId) {

                            $conversion = DataBarangConversion::where(
                                'data_barang_id',
                                $productId
                            )
                                ->where(
                                    'from_unit_id',
                                    $unitInput
                                )
                                ->where(
                                    'to_unit_id',
                                    $baseUnitId
                                )
                                ->first();

                            if (! $conversion) {

                                throw new \Exception(
                                    "Konversi satuan tidak ditemukan untuk {$product->nama_barang}"
                                );

                            }

                            $totalBaseQty = $qtyInput * $conversion->qty;

                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Detail Transfer
                        |--------------------------------------------------------------------------
                        */

                        ItemTransferDetail::create([

                            'item_transfer_id' => $itemTransfer->id,

                            'data_barang_id' => $productId,

                            'qty' => $qtyInput,

                            'unit_id' => $unitInput,

                            'created_at' => now(),

                            'updated_at' => now(),

                        ]);

                        /*
                        |--------------------------------------------------------------------------
                        | Stock Mutation OUT
                        |--------------------------------------------------------------------------
                        */

                        StockMutation::create([

                            'data_barang_id' => $productId,

                            'unit_id' => $unitInput,

                            'warehouse_id' => $r->from_warehouse_id,

                            'date_stock' => $data['transfer_date'],

                            'qty_transaksi' => $qtyInput,

                            'total_base_qty' => $totalBaseQty,

                            'type' => 'out',

                            'document_id' => $itemTransfer->id,

                            'document_number' => $itemTransfer->transfer_code,

                            'document_type' => 'item_transfer',

                            'keterangan' => 'Keluar barang dari '.$fromNamaGudang.
                                ' menuju '.$toNamaGudang,

                            'created_by' => Auth::id(),

                        ]);

                        /*
                        |--------------------------------------------------------------------------
                        | Stock Mutation IN
                        |--------------------------------------------------------------------------
                        */

                        StockMutation::create([

                            'data_barang_id' => $productId,

                            'unit_id' => $unitInput,

                            'warehouse_id' => $r->to_warehouse_id,

                            'date_stock' => $data['transfer_date'],

                            'qty_transaksi' => $qtyInput,

                            'total_base_qty' => $totalBaseQty,

                            'type' => 'in',

                            'document_id' => $itemTransfer->id,

                            'document_number' => $itemTransfer->transfer_code,

                            'document_type' => 'item_transfer',

                            'keterangan' => 'Masuk barang dari '.$fromNamaGudang.
                                ' menuju '.$toNamaGudang,

                            'created_by' => Auth::id(),

                        ]);

                        /*
                        |--------------------------------------------------------------------------
                        | Update Stock Balance Gudang Asal
                        |--------------------------------------------------------------------------
                        */

                        StockBalance::where([

                            'product_id' => $productId,

                            'warehouse_id' => $r->from_warehouse_id,

                        ])
                            ->decrement(
                                'qty',
                                $totalBaseQty
                            );

                        /*
                        |--------------------------------------------------------------------------
                        | Update Stock Balance Gudang Tujuan
                        |--------------------------------------------------------------------------
                        */

                        StockBalance::firstOrCreate(

                            [

                                'product_id' => $productId,

                                'warehouse_id' => $r->to_warehouse_id,

                            ],

                            [

                                'qty' => 0,

                            ]

                        );

                        StockBalance::where([

                            'product_id' => $productId,

                            'warehouse_id' => $r->to_warehouse_id,

                        ])
                            ->increment(

                                'qty',

                                $totalBaseQty

                            );

                    }

                }

            }

            DB::commit();

            return response()->json([

                'status' => 'success',

                'message' => 'Data updated successfully',

                'redirect' => route('item-transfer.index'),

            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([

                'status' => 'error',

                'message' => 'Gagal mengupdate data : '.$e->getMessage(),

            ], 500);

        }
    }

    public function destroy(string $id)
    {
        DB::beginTransaction();

        try {

            $itemTransfer = ItemTransfer::findOrFail($id);

            /*
            |--------------------------------------------------------------------------
            | Hapus Stock Mutation
            | Karena transaksi dibatalkan
            |--------------------------------------------------------------------------
            */
            StockMutation::where([
                'document_id' => $itemTransfer->id,
                'document_type' => 'item_transfer',
            ])->delete();

            /*
            |--------------------------------------------------------------------------
            | Nonaktifkan Header
            |--------------------------------------------------------------------------
            */
            $itemTransfer->update([
                'active' => 0,
                'updated_by' => Auth::id(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Item Transfer berhasil dibatalkan.',
            ], 200);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membatalkan Item Transfer: '.$e->getMessage(),
            ], 500);

        }
    }

    public function submitToProcess(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            $transfer = ItemTransfer::with('details')->find($id);

            if (! $transfer) {
                return response()->json([
                    'error' => 'Data Item Transfer tidak ditemukan.',
                ], 404);
            }

            $transfer->update([
                'status' => 'processing',
                'updated_by' => Auth::id(),
                'updated_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Jalankan perpindahan stock hanya saat APPROVED
            |--------------------------------------------------------------------------
            */
            $fromGudang = Warehouse::find($transfer->from_warehouse_id);
            $toGudang = Warehouse::find($transfer->to_warehouse_id);

            foreach ($transfer->details as $detail) {

                /*
                |--------------------------------------------------------------------------
                | Cek stok gudang asal
                |--------------------------------------------------------------------------
                */
                $stock = StockBalance::where([
                    'product_id' => $detail->data_barang_id,
                    'warehouse_id' => $transfer->from_warehouse_id,
                ])->first();
                if (! $stock || $stock->qty < $detail->qty) {
                    throw new \Exception(
                        'Stock tidak mencukupi untuk barang ID '.$detail->product_id
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Mutation OUT
                |--------------------------------------------------------------------------
                */
                StockMutation::create([
                    'data_barang_id' => $detail->data_barang_id,
                    'unit_id' => $detail->unit_id,
                    'warehouse_id' => $transfer->from_warehouse_id,
                    'date_stock' => $transfer->transfer_date,
                    'qty_transaksi' => $detail->qty,
                    'total_base_qty' => $detail->qty,
                    'type' => 'out',
                    'document_id' => $transfer->id,
                    'document_number' => $transfer->transfer_code,
                    'document_type' => 'item_transfer',
                    'keterangan' => 'Keluar barang dari : '
                        .$fromGudang->nama_gudang
                        .' menuju '
                        .$toGudang->nama_gudang,
                    'created_by' => Auth::id(),
                ]);

                /*
                |--------------------------------------------------------------------------
                | Mutation IN
                |--------------------------------------------------------------------------
                */
                StockMutation::create([
                    'data_barang_id' => $detail->data_barang_id,
                    'unit_id' => $detail->unit_id,
                    'warehouse_id' => $transfer->to_warehouse_id,
                    'date_stock' => $transfer->transfer_date,
                    'qty_transaksi' => $detail->qty,
                    'total_base_qty' => $detail->qty,
                    'type' => 'in',
                    'document_id' => $transfer->id,
                    'document_number' => $transfer->transfer_code,
                    'document_type' => 'item_transfer',
                    'keterangan' => 'Masuk barang dari : '
                        .$fromGudang->nama_gudang
                        .' menuju '
                        .$toGudang->nama_gudang,
                    'created_by' => Auth::id(),
                ]);

                /*
                |--------------------------------------------------------------------------
                | Kurangi stok gudang asal
                |--------------------------------------------------------------------------
                */
                $stock->decrement('qty', $detail->qty);

                /*
                |--------------------------------------------------------------------------
                | Tambah stok gudang tujuan
                |--------------------------------------------------------------------------
                */
                StockBalance::updateOrCreate(
                    [
                        'product_id' => $detail->data_barang_id,
                        'warehouse_id' => $transfer->to_warehouse_id,
                    ],
                    [
                        'qty' => 0,
                    ]
                );

                StockBalance::where([
                    'product_id' => $detail->data_barang_id,
                    'warehouse_id' => $transfer->to_warehouse_id,
                ])->increment('qty', $detail->qty);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Item Transfer status successfully Processing!',
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function print($id)
    {
        $itemTransfer = ItemTransfer::with(['details.produkID', 'details.unitID'])->findOrFail($id);
        $company = Company::first();
        // 1. LOGIKA LOGO PERUSAHAAN (Base64)
        $logoBase64 = null;
        if ($company && $company->logo) {
            $path = public_path($company->logo);
            if (file_exists($path)) {
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($path);
                $logoBase64 = 'data:image/'.$type.';base64,'.base64_encode($data);
            }
        }

        // Stamp Approved
        $approvedStampBase64 = null;
        $approvedPath = public_path('image/stamps/approved.png');

        if (file_exists($approvedPath)) {
            $approvedStampBase64 =
                'data:image/png;base64,'.
                base64_encode(file_get_contents($approvedPath));
        }

        // Stamp Rejected
        $rejectedStampBase64 = null;
        $rejectedPath = public_path('image/stamps/rejected.png');

        if (file_exists($rejectedPath)) {
            $rejectedStampBase64 =
                'data:image/png;base64,'.
                base64_encode(file_get_contents($rejectedPath));
        }
        $data = [
            'model' => $itemTransfer,
            'company' => $company,
            'modelDetail' => $itemTransfer->details,
            'logoBase64' => $logoBase64,
            'approvedStampBase64' => $approvedStampBase64,
            'rejectedStampBase64' => $rejectedStampBase64,
        ];

        $pdf = Pdf::loadView('pdf.item_transfer_pdf', $data)
            ->setPaper('a4', 'portrait');

        // preview di browser
        $filename = $itemTransfer->transfer_code;

        // replace forbidden filename chars
        $filename = preg_replace('/[\/\\\\:*?"<>|]/', '-', $filename);

        return $pdf->stream($filename.'.pdf');
    }

    public function getStock(Request $request)
    {
        $productId = $request->product_id;
        $warehouseId = $request->from_warehouse_id;
        $unitId = $request->unit_id;

        $stock = $this->realStock(
            $productId,
            $warehouseId,
            $unitId
        );

        $unit = BasicCodeDetail::where('master_id', 2)->find($request->unit_id);

        return response()->json([
            'stock' => floor($stock),
            'unit' => $unit?->nama_unit ?? '',
        ]);
    }

    public function realStock($productId, $warehouseId, $unitId, $cutoffDate = null)
    {
        $today = now()->format('Y-m-d');

        if (! $cutoffDate) {
            $cutoffDate = Company::value('cut_off_date');
        }

        $startDate = $cutoffDate ?? '2000-01-01';

        $barang = Barang::find($productId);

        if (! $barang) {
            return 0;
        }

        // hitung stok dalam BASE UNIT
        $stock = DB::table('stock_mutations')
            ->where('data_barang_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->whereBetween('date_stock', [$startDate, $today])
            ->selectRaw("
            COALESCE(SUM(
                CASE 
                    WHEN type='in' THEN total_base_qty
                    WHEN type='out' THEN -total_base_qty
                    ELSE 0
                END
            ),0) as stock
        ")
            ->value('stock');

        // jika satuan yang dipilih adalah satuan dasar
        if ((int) $unitId === (int) $barang->unit_id) {
            return $stock;
        }

        // konversi base unit ke unit tampil
        $conversion = DataBarangConversion::where('data_barang_id', $productId)
            ->where('from_unit_id', $unitId)
            ->where('to_unit_id', $barang->unit_id)
            ->first();

        if (! $conversion || $conversion->qty <= 0) {
            return 0;
        }

        return round(
            $stock / $conversion->qty,
            2
        );
    }

    public function trash(Request $r)
    {
        if ($r->ajax()) {
            $userId = Auth::user()->id;
            $query = ItemTransfer::where('active', 0)->orderby('transfer_code', 'desc')->get();

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
                ->addColumn('transfer_date', function ($row) {
                    return Carbon::parse($row->transfer_date)->format('d-m-Y');
                })
                ->addColumn('from_warehouse', function ($row) {
                    return $row->fromWarehouse->nama_gudang;
                })
                ->addColumn('to_warehouse', function ($row) {
                    return $row->toWarehouse->nama_gudang;
                })
                ->addColumn('cekbok', function ($row) {

                    if (
                        auth()->user()->can('item_transfer-delete') &&
                        $row->status === 'draft'
                    ) {
                        return '
                            <div class="form-check form-check-primary">
                                <input class="form-check-input checkItem"
                                    type="checkbox"
                                    value="'.$row->id.'">
                            </div>
                        ';
                    }

                    return '';
                })
                ->addColumn('status', function ($row) {

                    switch ($row->status) {

                        case 'draft':
                            $badge = 'bg-label-secondary';
                            $text = 'Draft';
                            break;

                        case 'processing':
                            $badge = 'bg-label-warning';
                            $text = 'Processing';
                            break;
                        case 'completed':
                            $badge = 'bg-success';
                            $text = 'Completed';
                            break;
                        case 'cancelled':
                            $badge = 'bg-danger';
                            $text = 'Cancelled';
                            break;
                        default:
                            $badge = 'bg-label-secondary';
                            $text = ucfirst(str_replace('_', ' ', $row->status));
                            break;
                    }

                    $html = '
                        <div class="d-flex flex-column">
                            <span class="badge '.$badge.' text-uppercase">
                                '.$text.'
                            </span>
                    ';

                    // APPROVED INFO

                    $html .= '</div>';

                    return $html;
                })
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
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'cekbok', 'transfer_date', 'from_warehouse', 'to_warehouse'])
                ->make(true);
        }

        $x = [
            'title' => 'Deleted Item Transfer',
            'breadcrumb' => [
                ['label' => 'Item Transfer', 'url' => route('item-transfer.index')],
                ['label' => 'Deleted Item Transfer', 'url' => ''],
            ],
        ];

        return view('inventory.itemTransfer.item_transfer_trash', $x);
    }

    public function restore($id)
    {
        DB::beginTransaction();

        try {

            $itemTransfer = ItemTransfer::with('details')->findOrFail($id);

            // Cegah restore mutation dua kali
            $mutationExists = StockMutation::where([
                'document_id' => $itemTransfer->id,
                'document_type' => 'item_transfer',
            ])->exists();

            if ($mutationExists) {
                throw new \Exception('Stock mutation Item Transfer ini sudah ada.');
            }

            // Aktifkan kembali dokumen
            $itemTransfer->update([
                'active' => 1,
                'updated_by' => Auth::id(),
            ]);

            $fromGudang = Warehouse::find($itemTransfer->from_warehouse_id);
            $toGudang = Warehouse::find($itemTransfer->to_warehouse_id);

            $fromNama = $fromGudang->nama_gudang ?? 'Unknown';
            $toNama = $toGudang->nama_gudang ?? 'Unknown';

            foreach ($itemTransfer->details as $detail) {

                $product = Barang::findOrFail($detail->data_barang_id);

                $baseUnitId = $product->unit_id;

                $qtyInput = (float) $detail->qty;

                $unitInput = $detail->unit_id;

                $totalBaseQty = $qtyInput;

                // Konversi ke satuan dasar
                if ($unitInput != $baseUnitId) {

                    $conversion = DataBarangConversion::where('data_barang_id', $detail->data_barang_id)
                        ->where('from_unit_id', $unitInput)
                        ->where('to_unit_id', $baseUnitId)
                        ->first();

                    if (! $conversion) {
                        throw new \Exception(
                            "Konversi satuan tidak ditemukan untuk produk {$product->nama_barang}"
                        );
                    }

                    $totalBaseQty = $qtyInput * $conversion->qty;

                }

                /*
                |--------------------------------------------------------------------------
                | STOCK KELUAR DARI GUDANG ASAL
                |--------------------------------------------------------------------------
                */

                StockMutation::create([

                    'data_barang_id' => $detail->data_barang_id,

                    'unit_id' => $unitInput,

                    'warehouse_id' => $itemTransfer->from_warehouse_id,

                    'date_stock' => $itemTransfer->transfer_date,

                    'qty_transaksi' => $qtyInput,

                    'total_base_qty' => $totalBaseQty,

                    'type' => 'out',

                    'document_id' => $itemTransfer->id,

                    'document_number' => $itemTransfer->transfer_code,

                    'document_type' => 'item_transfer',

                    'keterangan' => "Keluar barang dari {$fromNama} menuju {$toNama}",

                    'created_by' => Auth::id(),

                ]);

                /*
                |--------------------------------------------------------------------------
                | STOCK MASUK KE GUDANG TUJUAN
                |--------------------------------------------------------------------------
                */

                StockMutation::create([

                    'data_barang_id' => $detail->data_barang_id,

                    'unit_id' => $unitInput,

                    'warehouse_id' => $itemTransfer->to_warehouse_id,

                    'date_stock' => $itemTransfer->transfer_date,

                    'qty_transaksi' => $qtyInput,

                    'total_base_qty' => $totalBaseQty,

                    'type' => 'in',

                    'document_id' => $itemTransfer->id,

                    'document_number' => $itemTransfer->transfer_code,

                    'document_type' => 'item_transfer',

                    'keterangan' => "Masuk barang dari {$fromNama} menuju {$toNama}",

                    'created_by' => Auth::id(),

                ]);

            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Item Transfer berhasil direstore.',
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);

        }
    }
}
