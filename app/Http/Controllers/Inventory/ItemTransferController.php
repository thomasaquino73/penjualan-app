<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\ItemTransferRequest;
use App\Models\Inventory\Barang;
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
            $query = ItemTransfer::where('status', '<>', 0)->where(function ($q) use ($userId) {
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

                        case 'pending':
                            $badge = 'bg-label-warning';
                            $text = 'Pending Approval';
                            break;

                        case 'approved':
                            $badge = 'bg-label-success';
                            $text = 'Approved';
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

                        // SEND TO APPROVAL
                        if ($row->status == 'draft') {

                            $btn .= '
                                <a class="dropdown-item btn-submit"
                                    href="javascript:void(0)"
                                    data-id="'.$row->id.'">

                                    <i class="ti ti-send me-1"></i>
                                    Send To Approval
                                </a>
                            ';
                        }

                        // EDIT
                        if (
                            $user->can('item_transfer-edit') &&
                            in_array($row->status, ['draft', 'rejected'])
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
                            $row->status == 'draft'
                        ) {

                            $btn .= '
                                <a class="dropdown-item text-danger"
                                    href="javascript:void(0)"
                                    id="delete"
                                    data-id="'.$row->id.'"
                                    data-name="'.$row->item_transfer_code.'">

                                    <i class="ti ti-trash me-1"></i>
                                    Delete
                                </a>
                            ';
                        }
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | 2. APPROVAL ACTION
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $row->created_by !== $currentUserId &&
                        $user->can('item_transfer-approval')
                    ) {

                        if ($row->status == 'pending') {

                            $btn .= '
                                    <a class="dropdown-item text-success btn-approval"
                                        href="javascript:void(0)"
                                        data-status="approved"
                                        data-id="'.$row->id.'">

                                        <i class="ti ti-check me-1"></i>
                                        Approve
                                    </a>
                                ';

                            $btn .= '
                                    <a class="dropdown-item text-danger btn-approval"
                                        href="javascript:void(0)"
                                        data-status="rejected"
                                        data-id="'.$row->id.'">

                                        <i class="ti ti-x me-1"></i>
                                        Reject
                                    </a>
                                ';
                        }
                    }

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
            'product' => Barang::where('status', '<>', 0)->get(),
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
            | Rollback Stock Lama
            |--------------------------------------------------------------------------
            // */
            // $oldDetails = ItemTransferDetail::where('item_transfer_id', $itemTransfer->id)->get();

            // foreach ($oldDetails as $detail) {

            //     // Kembalikan stok ke gudang asal lama
            //     StockBalance::updateOrCreate(
            //         [
            //             'product_id' => $detail->data_barang_id,
            //             'warehouse_id' => $itemTransfer->from_warehouse_id,
            //         ],
            //         ['qty' => 0]
            //     );

            //     StockBalance::where([
            //         'product_id' => $detail->data_barang_id,
            //         'warehouse_id' => $itemTransfer->from_warehouse_id,
            //     ])->increment('qty', $detail->qty);

            //     // Kurangi stok dari gudang tujuan lama
            //     StockBalance::where([
            //         'product_id' => $detail->data_barang_id,
            //         'warehouse_id' => $itemTransfer->to_warehouse_id,
            //     ])->decrement('qty', $detail->qty);
            // }

            /*
            |--------------------------------------------------------------------------
            | Hapus Mutation Lama
            |--------------------------------------------------------------------------
            */
            // StockMutation::where('document_type', 'item_transfer')
            //     ->where('document_number', $itemTransfer->transfer_code)
            //     ->delete();

            /*
            |--------------------------------------------------------------------------
            | Hapus Detail Lama
            |--------------------------------------------------------------------------
            */
            ItemTransferDetail::where('item_transfer_id', $itemTransfer->id)->delete();

            /*
            |--------------------------------------------------------------------------
            | Update Header
            |--------------------------------------------------------------------------
            */
            $data = $r->except('save_and_new', 'items_detail');

            $data['transfer_date'] = Carbon::parse(
                $r->transfer_date
            )->format('Y-m-d');

            $data['updated_by'] = Auth::id();

            $itemTransfer->update($data);

            /*
            |--------------------------------------------------------------------------
            | Simpan Detail Baru
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

                        $qty = $item['quantity'] ?? $item['qty'];

                        /*
                        |--------------------------------------------------------------------------
                        | Detail
                        |--------------------------------------------------------------------------
                        */
                        ItemTransferDetail::create([
                            'item_transfer_id' => $itemTransfer->id,
                            'data_barang_id' => $item['product_id'],
                            'qty' => $qty,
                            'unit_id' => $item['unit_id'],
                        ]);

                        /*
                        |--------------------------------------------------------------------------
                        | Mutation OUT
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
                        //     'keterangan' => 'Keluar barang dari : '.
                        //         $fromNamaGudang.
                        //         ' menuju '.
                        //         $toNamaGudang,
                        //     'created_by' => Auth::id(),
                        // ]);

                        /*
                        |--------------------------------------------------------------------------
                        | Mutation IN
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
                        //     'keterangan' => 'Masuk barang dari : '.
                        //         $fromNamaGudang.
                        //         ' menuju '.
                        //         $toNamaGudang,
                        //     'created_by' => Auth::id(),
                        // ]);

                        /*
                        |--------------------------------------------------------------------------
                        | Stock Balance Gudang Asal
                        |--------------------------------------------------------------------------
                        */
                        // StockBalance::updateOrCreate(
                        //     [
                        //         'product_id' => $item['product_id'],
                        //         'warehouse_id' => $r->from_warehouse_id,
                        //     ],
                        //     [
                        //         'qty' => 0,
                        //     ]
                        // );

                        // StockBalance::where([
                        //     'product_id' => $item['product_id'],
                        //     'warehouse_id' => $r->from_warehouse_id,
                        // ])->decrement('qty', $qty);

                        /*
                        |--------------------------------------------------------------------------
                        | Stock Balance Gudang Tujuan
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function submitToPending($id)
    {
        // 1. Ambil tahun berjalan secara dinamis
        $tableName = 'item_transfer';

        // 2. Gunakan Query Builder dengan nama tabel dinamis agar pencarian ID aman
        $poData = DB::table($tableName)->where('id', $id)->first();

        // Jika data memang benar-benar tidak ditemukan di database
        if (! $poData) {
            return response()->json(['success' => false, 'message' => 'Data Sales Order tidak ditemukan.'], 404);
        }

        // 3. Validasi Keamanan: Pastikan hanya pembuat draft yang bisa mengajukannya
        if ($poData->status !== 'draft' || $poData->created_by !== Auth::user()->id) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki akses untuk mengajukan data ini.'], 403);
        }

        // 4. Lakukan pembaruan status menggunakan Query Builder demi stabilitas tabel dinamis
        DB::table($tableName)->where('id', $id)->update([
            'status' => 'pending',
            'updated_by' => Auth::user()->id,
            'updated_at' => now(), // Mengisi timestamp bawaan laravel secara manual karena menggunakan Query Builder
        ]);

        return response()->json(['success' => true, 'message' => 'Item Transfer berhasil diajukan!']);
    }

    public function changeStatus(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            $transfer = ItemTransfer::with('details')->find($id);

            if (! $transfer) {
                return response()->json([
                    'error' => 'Data Item Transfer tidak ditemukan.',
                ], 404);
            }

            if ($transfer->created_by === Auth::id()) {
                return response()->json([
                    'error' => 'You may not approve/reject documents you create yourself!',
                ], 403);
            }

            if ($transfer->status === 'approved') {
                return response()->json([
                    'error' => 'Document already approved.',
                ], 400);
            }

            $statusTarget = $request->status;

            if (! in_array($statusTarget, ['approved', 'rejected'])) {
                return response()->json([
                    'error' => 'Status target tidak valid.',
                ], 400);
            }

            $transfer->update([
                'status' => $statusTarget,
                'pic_by' => Auth::id(),
                'pic_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Jalankan perpindahan stock hanya saat APPROVED
            |--------------------------------------------------------------------------
            */
            if ($statusTarget === 'approved') {

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
                    // dd([
                    //     'product_id' => $detail->data_barang_id,
                    //     'warehouse_id' => $transfer->from_warehouse_id,
                    //     'stock_balance' => $stock,
                    //     'qty_transfer' => $detail->qty,
                    // ]);
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
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Item Transfer status successfully {$statusTarget}!",
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

    public function realStock($productId, $warehouseId, $cutoffDate = null)
    {
        $barang = Barang::findOrFail($productId);

        return $barang->mutations()
            ->where('warehouse_id', $warehouseId)
            ->when($cutoffDate, function ($q) use ($cutoffDate) {
                $q->whereDate('date_stock', '<=', $cutoffDate);
            })
            ->selectRaw("
            COALESCE(
                SUM(
                    CASE
                        WHEN type = 'in'
                        THEN total_base_qty
                        ELSE -total_base_qty
                    END
                ),
                0
            ) as total
        ")
            ->value('total');
    }

    public function getStock(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'warehouse_id' => 'required|integer',
        ]);

        $stock = $this->realStock(
            $request->product_id,
            $request->warehouse_id,
            $request->cutoff_date
        );

        $barang = Barang::with('unitID')
            ->find($request->product_id);

        return response()->json([
            'success' => true,
            'stock' => $stock,
            'unit' => $barang?->unitID?->detail ?? '',
        ]);
    }
}
