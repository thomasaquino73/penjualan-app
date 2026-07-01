<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReceiveItemRequest;
use App\Models\BasicCodeDetail;
use App\Models\Inventory\Barang;
use App\Models\Inventory\Warehouse;
use App\Models\Purchase\PurchaseOrder;
use App\Models\Purchase\PurchaseOrderDetail;
use App\Models\Purchase\ReceiveItem;
use App\Models\Purchase\ReceiveItemDetail;
use App\Models\Purchase\Supplier;
use App\Models\Setting\Company;
use App\Models\Setting\Shipping;
use App\Models\StockMutation;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ReceiveItemController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $routeName = $request->route()->getName();

            $permissionMap = [
                'receive-item.index' => 'receive_item-browse',
                'receive-item.show' => 'receive_item-read',
                'receive-item.create' => 'receive_item-create',
                'receive-item.store' => 'receive_item-create',
                'receive-item.edit' => 'receive_item-edit',
                'receive-item.update' => 'receive_item-edit',
                'receive-item.destroy' => 'receive_item-delete',
                'receive-item.trash' => 'receive_item-trash',
                'receive-item.restore' => 'receive_item-restore',
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
            // Ambil ID user yang sedang login
            $userId = Auth::user()->id;

            // Query dengan kondisi: Aktif DAN (Status BUKAN draft ATAU Status ADALAH draft kepunyaan sendiri)
            $query = ReceiveItem::where('active', '<>', 0)
                // ->where(function ($q) use ($userId) {
                //     $q->where('status', '<>', 'draft')
                //         ->orWhere(function ($subQ) use ($userId) {
                //             $subQ->where('status', 'draft')
                //                 ->where('created_by', $userId);
                //         });
                // })
                ->orderby('receive_item_code', 'desc');
            if ($r->status) {
                $query->where('status', $r->status);
            }

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
                ->addColumn('date', function ($row) {
                    return $row->receive_item_date ? Carbon::parse($row->receive_item_date)->format('d M Y') : 'N/A';
                })
                ->addColumn('supplier', function ($row) {
                    return $row->supplier_id ? $row->supplierId->nama_supplier : 'N/A';
                })
                ->addColumn('status', function ($row) {
                    switch ($row->status) {
                        case 'draft':
                            $badge = 'bg-label-secondary';
                            $text = 'Draft';
                            break;

                        case 'processing':
                            $badge = 'bg-label-info';
                            $text = 'Processing';
                            break;

                            // ─── TAMBAHAN BADGE UNTUK STATUS PARTIAL ──────────────────
                        case 'partial':
                            $badge = 'bg-warning text-dark';
                            $text = 'Partial RI';
                            break;

                        case 'closed':
                            $badge = 'bg-dark';
                            $text = 'Closed';
                            break;

                        case 'cancelled':
                            $badge = 'bg-danger';
                            $text = 'Cancelled';
                            break;

                        default:
                            $badge = 'bg-label-secondary';
                            $text = ucfirst($row->status);
                            break;
                    }

                    return '<span class="badge '.$badge.' text-uppercase">'.$text.'</span>';
                })
                ->addColumn('cekbok', function ($row) {

                    if (
                        auth()->user()->can('purchase_order-delete') &&
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
                ->addColumn('action', function ($row) {
                    $currentUserId = Auth::user()->id;
                    $user = Auth::user();

                    $btn = '<div class="btn-group">
                <button type="button" class="btn btn-primary dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown">
                    <i class="ti ti-menu-2 ti-xs me-1"></i>
                </button>
                <ul class="dropdown-menu">';

                    // ─── OWNER ACTION ─────────────────────────────
                    if ($row->created_by == $currentUserId) {

                        // ✅ EDIT
                        if ($user->can('receive-item-edit') && $row->status == 'draft') {
                            $btn .= '<a class="dropdown-item" href="'.route('receive-item.edit', $row->id).'">
                        <i class="far fa-edit me-1"></i> Edit
                     </a>';
                        }

                        // ✅ DELETE
                        if ($user->can('receive-item-delete') && $row->status == 'draft') {
                            $btn .= '<a class="dropdown-item" href="javascript:void(0)" id="delete"
                        data-id="'.$row->id.'" data-name="'.$row->receive_item_code.'">
                        <i class="ti ti-trash me-1"></i> Delete
                     </a>';
                        }
                    }

                    // ─── INFO JIKA SUDAH DIPROSES ─────────────────────────────
                    if ($row->status == 'processing') {
                        $btn .= '<a class="dropdown-item" href="'.route('receive-item.edit', $row->id).'">
                        <i class="far fa-edit me-1"></i> Edit
                     </a>';
                    }

                    //         if ($row->status != 'closed') {
                    //             $btn .= '<a class="dropdown-item"
                    //     href="javascript:void(0)" id="close"   data-id="'.$row->id.'" data-name="'.$row->receive_item_code.'">
                    //     <i class="ti ti-lock"></i> Close RI
                    //  </a>';
                    //         }

                    $btn .= '<a class="dropdown-item"
                href="'.route('receive-item.show', $row->id).'">
                <i class="ti ti-list-details"></i> Detail
             </a>';
                    $btn .= '<a class="dropdown-item" target="_blank"
                href="'.route('receive-item.print', $row->id).'">
                <i class="ti ti-printer"></i> Print
             </a>';

                    $btn .= '</ul></div>';

                    return $btn;
                })
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'cekbok', 'date', 'supplier'])
                ->make(true);
        }

        $x = [
            'title' => 'Receive Item List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Receive Item', 'url' => ''],
            ],
        ];

        return view('purchase.receive_item.receive_item_index', $x);
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
        $last = ReceiveItem::where('receive_item_code', 'like', "RI/$year/$month/%")
            ->orderBy('id', 'desc')
            ->first();

        if (! $last) {
            return "RI/$year/$month/0001";
        }

        $lastId = $last->receive_item_code;

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
            'title' => 'Receive Item New',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Receive Item', 'url' => ''],
            ],
            'supplier' => Supplier::where('status', 1)->get(),
            'company' => Company::first(),
            'idNumber' => $this->generateNumberId(),
            'shipping' => Shipping::where('status', 1)->get(),
            'product' => Barang::where('status', '<>', 0)->get(),
            'warehouse' => Warehouse::where('status', '<>', 0)->get(),
            'fob' => BasicCodeDetail::where('master_id', 7)->get(),

        ];

        return view('purchase.receive_item.receive_item_create', $x);
    }

    public function store(ReceiveItemRequest $request)
    {
        DB::beginTransaction();

        try {
            $currentYear = date('Y');
            $data = $request->validated();
            $itemsDetailRaw = $request->input('items_detail');
            unset($data['items_detail']);

            $data['created_by'] = Auth::id();
            $data['receive_item_date'] = Carbon::parse($request->receive_item_date)->format('Y-m-d');
            $data['supplier_id'] = $request->supplier_id;
            $data['shipping_id'] = $request->shipping_id;
            $data['fob_id'] = $request->fob_id;
            $data['tanggal_kirim'] = $request->tanggal_kirim ? Carbon::parse($request->tanggal_kirim)->format('Y-m-d') : null;

            // Generate Code
            do {
                $generatedCode = $this->generateNumberId();
                $exists = ReceiveItem::where('receive_item_code', $generatedCode)->exists();
            } while ($exists);
            $data['receive_item_code'] = $generatedCode;

            $ReceiveItem = ReceiveItem::create($data);

            if ($itemsDetailRaw) {
                $items = json_decode($itemsDetailRaw, true);
                $involvedPrIds = [];

                if (is_array($items) && count($items) > 0) {
                    foreach ($items as $item) {
                        $prDetailId = $item['purchase_order_detail_id'] ?? $item['pr_detail_id'] ?? $item['detail_id'] ?? null;
                        $qtyInputForm = floatval($item['quantity'] ?? $item['qty'] ?? 0);

                        // 1. Simpan Detail Penerimaan
                        ReceiveItemDetail::create([
                            'receive_item_id' => $ReceiveItem->id,
                            'purchase_order_detail_id' => $prDetailId,
                            'product_id' => $item['product_id'],
                            'qty' => $qtyInputForm,
                            'unit_id' => $item['unit_id'],
                            'warehouse_id' => $item['warehouse_id'],
                            'active' => 1,
                        ]);

                        // 2. Logika Sinkronisasi (Update PO Detail)
                        if ($prDetailId) {
                            $prDetail = DB::table("purchase_order_detail_{$currentYear}")->where('id', $prDetailId)->first();

                            if ($prDetail) {
                                // Hitung total qty yang sudah diterima untuk detail item ini
                                $totalPoForThisItem = ReceiveItemDetail::where('purchase_order_detail_id', $prDetailId)
                                    ->where('active', 1)
                                    ->sum('qty');

                                // Hitung Outstanding: (Total yang diminta - Total yang sudah diterima)
                                // Catatan: Pastikan kolom 'qty' di tabel PO Detail adalah jumlah yang dipesan
                                $outstanding = max(0, $prDetail->qty - $totalPoForThisItem);

                                // Update received_qty dan outstanding_qty
                                DB::table("purchase_order_detail_{$currentYear}")
                                    ->where('id', $prDetailId)
                                    ->update([
                                        'received_qty' => $totalPoForThisItem,
                                        'outstanding_qty' => $outstanding,
                                    ]);

                                if (! in_array($prDetail->purchase_order_id, $involvedPrIds)) {
                                    $involvedPrIds[] = $prDetail->purchase_order_id;
                                }
                            }
                        }

                        // 3. Simpan Mutasi Stok
                        StockMutation::create([
                            'data_barang_id' => $item['product_id'],
                            'unit_id' => $item['unit_id'],
                            'warehouse_id' => $item['warehouse_id'] ?? null,
                            'date_stock' => $data['receive_item_date'],
                            'qty_transaksi' => $qtyInputForm,
                            'total_base_qty' => $qtyInputForm,
                            'type' => 'in',
                            'keterangan' => 'Penerimaan Barang dari '.$ReceiveItem->supplierId->nama_supplier.', No.Dokumen : '.$request->no_dokumen,
                            'created_by' => Auth::id(),
                            'document_type' => 'receive_item',
                        ]);
                    }

                    // 4. Otomasi Status PO Master
                    foreach ($involvedPrIds as $prId) {
                        $allDetails = DB::table("purchase_order_detail_{$currentYear}")->where('purchase_order_id', $prId)->get();
                        $totalRequested = $allDetails->sum('qty');
                        $totalOrdered = $allDetails->sum('received_qty');

                        // $newStatus = ($totalOrdered >= $totalRequested) ? 'closed' : (($totalOrdered > 0) ? 'partial' : 'processing');
                        $newStatus = ($totalOrdered >= $totalRequested) ? 'completed' : (($totalOrdered > 0) ? 'partial' : 'processing');

                        DB::table("purchase_order_{$currentYear}")
                            ->where('id', $prId)
                            ->update(['status' => $newStatus]);
                    }
                }
            }

            DB::commit();

            $redirectUrl = $request->save_and_new == 1 ? route('receive-item.create') : route('receive-item.index');

            return response()->json([
                'success' => true,
                'message' => 'Receive Item saved successfully!',
                'redirect' => $redirectUrl,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getOrderDetail(Request $request)
    {
        $ids = $request->ids;

        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data PO yang dipilih.',
                'data' => [],
            ]);
        }

        $details = PurchaseOrderDetail::with([
            'produkID',
            'unitID',
            'warehouseID',
            'purchaseOrder',
        ])
            ->whereIn('purchase_order_id', $ids)
            ->where('active', 1)
            ->whereHas('purchaseOrder', function ($q) {
                // Sesuaikan dengan status yang valid di database Anda
                $q->whereIn('status', ['processing', 'partial'])->where('active', '<>', '0');
            })
            ->get();

        $formattedData = $details->map(function ($item) {
            // 1. Ambil nilai dasar
            $totalQty = (float) ($item->qty ?? 0);
            $receivedQty = (float) ($item->received_qty ?? 0);

            // 2. Hitung sisa yang benar
            $sisaQty = $totalQty - $receivedQty;

            // 3. Jika sisa 0 atau kurang, item ini tidak perlu diproses lagi
            if ($sisaQty <= 0) {
                return null;
            }

            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->produkID->nama_barang ?? '-',

                // Gunakan hasil perhitungan sisa yang benar
                'quantity' => $sisaQty,
                'qty' => $sisaQty,

                'received_qty' => $receivedQty,
                'unit_id' => $item->unit_id,
                'unit_name' => $item->unitID->detail ?? '-',
                'warehouse_id' => $item->warehouse_id,
                'nama_gudang' => $item->warehouseID->nama_gudang ?? '-',
                'order_code' => $item->purchaseOrder->code ?? '',
                'pr_status' => $item->purchaseOrder->status ?? '',
                // 'warehouse_id' => '',
                // 'warehouse' => '-',
            ];
        })->filter()->values();
        //    dd($details);

        return response()->json([
            'success' => true,
            'data' => $formattedData,
        ]);
    }

    public function getProcessingData(Request $request)
    {
        $orders = PurchaseOrder::with([
            'details' => function ($query) {
                $query->whereColumn('received_qty', '<', 'qty');
            },
        ])
            ->where('supplier_id', $request->supplier_id)
        // ->whereNotIn('status', ['draft', 'closed', 'completed'])
            ->whereIn('status', ['processing', 'partial'])
            ->where('active', '<>', '0')
            ->get();

        return response()->json($orders);
    }

    public function edit(string $id)
    {
        $year = date('Y');

        // 1. Load data Receive Item beserta relasinya
        $receiveItem = ReceiveItem::with([
            'purchaseOrder',
            'details.produkID',
            'details.unitID',
            'details.warehouseID',
            'details.purchaseOrderDetail.purchaseOrder',
        ])->findOrFail($id);

        // 2. Cek status: Apakah mengandung minimal satu item hasil serapan PO?
        $isFromPO = $receiveItem->details->whereNotNull('purchase_order_detail_id')->count() > 0;

        // 3. Mapping data detail
        $detailDataMapped = $receiveItem->details->map(function ($detail) use ($receiveItem, $year) {

            $orderCode = null;
            $sisaPo = null;    // Outstanding dari sisi PO
            $kuotaAsliPo = null; // Total qty di PO
            $totalDiambilLainnya = 0;

            if ($detail->purchase_order_detail_id) {
                $poDetail = $detail->purchaseOrderDetail;

                if ($poDetail) {
                    $kuotaAsliPo = (float) $poDetail->qty;
                    $sisaPo = (float) $poDetail->outstanding_qty;

                    // Menghitung berapa banyak yang sudah diambil oleh dokumen Receive Item LAINNYA
                    // Mengambil dari tabel detail penerimaan (bukan dari PO Detail lagi)
                    $totalDiambilLainnya = DB::table("receive_item_detail_{$year}")
                        ->where('purchase_order_detail_id', $detail->purchase_order_detail_id)
                        ->where('receive_item_id', '<>', $receiveItem->id) // Kecuali dokumen yang sedang diedit
                        ->where('active', 1)
                        ->sum('qty');

                    if ($poDetail->purchaseOrder) {
                        $orderCode = $poDetail->purchaseOrder->code;
                    }
                }
            }

            return [
                'id' => $detail->id,
                'receive_item_id' => $detail->receive_item_id,
                'purchase_order_detail_id' => $detail->purchase_order_detail_id,
                'order_code' => $orderCode,
                'product_id' => $detail->product_id,
                'data_produk' => $detail->produkID->nama_barang ?? 'Product Not Found',
                'quantity' => (float) $detail->qty,
                'unit_id' => $detail->unit_id,
                'unit' => $detail->unitID->detail ?? '-',
                'warehouse_id' => $detail->warehouse_id,
                'warehouse' => $detail->warehouseID->nama_gudang ?? '-',
                'sisa_po' => $sisaPo,
                'kuota_asli' => $kuotaAsliPo,
                'total_diambil_lainnya' => (float) $totalDiambilLainnya,
            ];
        });

        // 4. Susun variabel untuk view
        $x = [
            'title' => 'Edit Receive Item',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Receive Item', 'url' => route('receive-item.index')],
                ['label' => 'Edit', 'url' => ''],
            ],
            'supplier' => Supplier::where('status', 1)->get(),
            'company' => Company::first(),
            'idNumber' => $receiveItem->receive_item_code,
            'shipping' => Shipping::where('status', 1)->get(),
            'product' => Barang::where('status', '<>', 0)->get(),
            'warehouse' => Warehouse::where('status', '<>', 0)->get(),
            'fob' => BasicCodeDetail::where('master_id', 7)->get(),
            'model' => $receiveItem,
            'isFromPR' => $isFromPO, // Tetap gunakan variabel yang ada, disesuaikan konteksnya
            'jsonDetails' => $detailDataMapped,
        ];

        return view('purchase.receive_item.receive_item_edit', $x);
    }

    public function update(ReceiveItemRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $currentYear = date('Y');
            $receiveItem = ReceiveItem::findOrFail($id);

            // 1. ROLLBACK: Kembalikan received_qty dan hitung ulang outstanding_qty di PO Detail lama
            $oldDetails = ReceiveItemDetail::where('receive_item_id', $id)->get();
            foreach ($oldDetails as $oldDetail) {
                if ($oldDetail->purchase_order_detail_id) {
                    $poDetail = DB::table("purchase_order_detail_{$currentYear}")->where('id', $oldDetail->purchase_order_detail_id)->first();
                    if ($poDetail) {
                        $newReceived = max(0, $poDetail->received_qty - $oldDetail->qty);
                        $newOutstanding = $poDetail->qty - $newReceived;

                        DB::table("purchase_order_detail_{$currentYear}")->where('id', $oldDetail->purchase_order_detail_id)
                            ->update([
                                'received_qty' => $newReceived,
                                'outstanding_qty' => $newOutstanding,
                            ]);
                    }
                }
            }

            // 2. HAPUS: Hapus detail lama dan mutasi stok lama
            ReceiveItemDetail::where('receive_item_id', $id)->delete();
            StockMutation::where('document_type', 'receive_item')
                ->where('keterangan', 'like', "%No.Dokumen : {$receiveItem->no_dokumen}%")
                ->delete();

            // 3. UPDATE: Data Master
            $data = $request->validated();
            $itemsDetailRaw = $request->input('items_detail');
            unset($data['items_detail']);

            $data['receive_item_date'] = Carbon::parse($request->receive_item_date)->format('Y-m-d');
            $data['tanggal_kirim'] = $request->tanggal_kirim ? Carbon::parse($request->tanggal_kirim)->format('Y-m-d') : null;
            $data['updated_by'] = Auth::id();

            $receiveItem->update($data);

            // 4. SIMPAN: Data Detail Baru
            if ($itemsDetailRaw) {
                $items = is_array($itemsDetailRaw) ? $itemsDetailRaw : json_decode($itemsDetailRaw, true);
                $involvedPrIds = [];

                foreach ($items as $item) {
                    $prDetailId = $item['purchase_order_detail_id'] ?? $item['pr_detail_id'] ?? null;
                    $qtyInput = floatval($item['quantity'] ?? $item['qty'] ?? 0);

                    ReceiveItemDetail::create([
                        'receive_item_id' => $receiveItem->id,
                        'purchase_order_detail_id' => $prDetailId,
                        'product_id' => $item['product_id'],
                        'qty' => $qtyInput,
                        'unit_id' => $item['unit_id'],
                        'warehouse_id' => $item['warehouse_id'],
                        'active' => 1,
                    ]);

                    // Update PO Detail baru dengan perhitungan Outstanding
                    if ($prDetailId) {
                        $poDetail = DB::table("purchase_order_detail_{$currentYear}")->where('id', $prDetailId)->first();
                        if ($poDetail) {
                            // Ambil total akumulasi terbaru setelah data baru masuk
                            $totalReceived = ReceiveItemDetail::where('purchase_order_detail_id', $prDetailId)
                                ->where('active', 1)
                                ->sum('qty');

                            $outstanding = max(0, $poDetail->qty - $totalReceived);

                            DB::table("purchase_order_detail_{$currentYear}")->where('id', $prDetailId)
                                ->update([
                                    'received_qty' => $totalReceived,
                                    'outstanding_qty' => $outstanding,
                                ]);

                            $involvedPrIds[] = $poDetail->purchase_order_id;
                        }
                    }

                    // Simpan Mutasi Stok Baru
                    StockMutation::create([
                        'data_barang_id' => $item['product_id'],
                        'unit_id' => $item['unit_id'],
                        'warehouse_id' => $item['warehouse_id'],
                        'date_stock' => $data['receive_item_date'],
                        'qty_transaksi' => $qtyInput,
                        'total_base_qty' => $qtyInput,
                        'type' => 'in',
                        'keterangan' => 'Penerimaan Barang, No.Dokumen : '.$request->no_dokumen,
                        'created_by' => Auth::id(),
                        'document_type' => 'receive_item',
                    ]);
                }

                // 5. OTOMASI STATUS PO MASTER
                foreach (array_unique($involvedPrIds) as $prId) {
                    $allDetails = DB::table("purchase_order_detail_{$currentYear}")->where('purchase_order_id', $prId)->get();
                    $totalRequested = $allDetails->sum('qty');
                    $totalOrdered = $allDetails->sum('received_qty');

                    $newStatus = ($totalOrdered >= $totalRequested) ? 'completed' : (($totalOrdered > 0) ? 'partially_received' : 'processing');
                    DB::table("purchase_order_{$currentYear}")->where('id', $prId)->update(['status' => $newStatus]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'title' => 'Success',
                'message' => 'Receive Item berhasil diupdate',
                'redirect' => route('receive-item.index'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['status' => 'error', 'message' => 'Gagal: '.$e->getMessage()], 500);
        }
    }

    public function show($id) {}

    public function print($id)
    {
        $receiveItem = ReceiveItem::with(['details.produkID', 'details.unitID'])->findOrFail($id);
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
        $data = [
            'model' => $receiveItem,
            'company' => $company,
            'modelDetail' => $receiveItem->details,
            'logoBase64' => $logoBase64,
        ];

        $pdf = Pdf::loadView('pdf.receive_item_pdf', $data)
            ->setPaper('a4', 'portrait');

        // preview di browser
        $filename = $receiveItem->receive_item_code.'-'.$receiveItem->supplierID->nama_supplier;

        // replace forbidden filename chars
        $filename = preg_replace('/[\/\\\\:*?"<>|]/', '-', $filename);

        return $pdf->stream($filename.'.pdf');

        // kalau mau download:
        // return $pdf->download('purchase-order.pdf');
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            // 1. Cari PO yang akan dihapus
            $po = ReceiveItem::findOrFail($id);

            // 2. Ambil detail PO untuk mendapatkan referensi PR Detail yang terkait
            $poDetails = ReceiveItemDetail::where('receive_item_id', $po->id)->get();
            $involvedPrIds = [];

            foreach ($poDetails as $poDetail) {
                if ($poDetail->purchase_order_detail_id) {
                    // Catat ID PR Master-nya
                    $prDetail = PurchaseOrderDetail::where('id', $poDetail->purchase_order_detail_id)
                        ->first();

                    if ($prDetail && ! in_array($prDetail->receive_item_id, $involvedPrIds)) {
                        $involvedPrIds[] = $prDetail->receive_item_id;
                    }
                }
            }

            // 3. Nonaktifkan PO dan Detail PO
            $po->update(['active' => 0, 'updated_by' => Auth::id()]);
            ReceiveItemDetail::where('receive_item_id', $po->id)->update(['active' => 0]);

            // 4. Update Ulang received_qty di setiap PR Detail yang terdampak
            // Kita hitung ulang berdasarkan sisa PO yang masih 'active' = 1
            foreach ($poDetails as $poDetail) {
                if ($poDetail->purchase_order_detail_id) {
                    $totalRemainingPo = ReceiveItemDetail::where('purchase_order_detail_id', $poDetail->purchase_order_detail_id)
                        ->where('active', 1)
                        ->sum('qty');

                    DB::table('purchase_order_detail_'.date('Y'))
                        ->where('id', $poDetail->purchase_order_detail_id)
                        ->update(['received_qty' => $totalRemainingPo]);
                }
            }

            // 5. Update Status PR Master
            foreach ($involvedPrIds as $prId) {
                $allDetails = PurchaseOrderDetail::where('purchase_order_id', $prId)
                    ->get();

                $totalRequested = $allDetails->sum('qty');
                $totalOrdered = $allDetails->sum('received_qty');

                if ($totalOrdered >= $totalRequested) {
                    $status = 'closed';
                } elseif ($totalOrdered > 0) {
                    $status = 'partial';
                } else {
                    $status = 'processing';
                }

                PurchaseOrder::where('id', $prId)
                    ->update(['status' => $status]);
            }

            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'RI berhasil dibatalkan.'], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['status' => 'error', 'message' => 'Gagal membatalkan RI: '.$e->getMessage()], 500);
        }
    }
}
