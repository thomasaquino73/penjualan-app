<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReceiveItemRequest;
use App\Models\BasicCodeDetail;
use App\Models\Inventory\Barang;
use App\Models\Inventory\DataBarangConversion;
use App\Models\Inventory\StockBalance;
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
use Exception;
use Illuminate\Database\QueryException;
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
                ->addColumn('action', function ($row) {
                    $user = Auth::user();

                    $btn = '<div class="btn-group">
                    <button type="button" class="btn btn-primary dropdown-toggle waves-effect waves-light"
                        data-bs-toggle="dropdown">
                        <i class="ti ti-menu-2 ti-xs me-1"></i>
                    </button>
                    <ul class="dropdown-menu">';

                    // Edit
                    if ($user->can('receive_item-edit') && $row->status !== 'closed') {
                        $btn .= '
                        <a class="dropdown-item" href="'.route('receive-item.edit', $row->id).'">
                            <i class="far fa-edit me-1"></i> Edit
                        </a>';
                    }

                    // Delete
                    if ($user->can('receive_item-delete')) {
                        $btn .= '
                        <a class="dropdown-item" href="javascript:void(0)" id="delete"
                            data-id="'.$row->id.'"
                            data-name="'.$row->receive_item_code.'">
                            <i class="ti ti-trash me-1"></i> Delete
                        </a>';
                    }

                    // Detail

                    // $btn .= '
                    //     <a class="dropdown-item" href="' . route('receive-item.show', $row->id) . '">
                    //         <i class="ti ti-list-details me-1"></i> Detail
                    //     </a>';

                    // Print

                    $btn .= '
                        <a class="dropdown-item" target="_blank"
                            href="'.route('receive-item.print', $row->id).'">
                            <i class="ti ti-printer me-1"></i> Print
                        </a>';

                    $btn .= '</ul></div>';

                    return $btn;
                })
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'date', 'supplier'])
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
        $tahun = date('Y');
        $bulan = date('n');
        $bulanRomawi = $this->bulanRomawi($bulan);

        // Prefix yang akan dicari
        $prefix = "RI/{$tahun}/{$bulanRomawi}/";

        // Ambil nomor terakhir pada bulan & tahun yang sama
        $last = ReceiveItem::where('receive_item_code', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();

        if ($last) {
            // Ambil 4 digit terakhir
            $lastNumber = (int) substr($last->code, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            // Jika belum ada pada bulan ini mulai dari 0001
            $nextNumber = 1;
        }

        return $prefix.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
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
            $data['address'] = $request->address;
            $data['tanggal_kirim'] = $request->tanggal_kirim ? Carbon::parse($request->tanggal_kirim)->format('Y-m-d') : null;

            // Generate Code
            $receiveItem = null;
            $maxRetry = 10;
            $currentCode = $request->receive_item_code; // Ambil input awal dari user

            for ($attempt = 1; $attempt <= $maxRetry; $attempt++) {
                try {
                    $data['receive_item_code'] = $currentCode;
                    $receiveItem = ReceiveItem::create($data);
                    break; // Berhasil, keluar dari loop
                } catch (QueryException $e) {
                    // Cek jika error adalah Duplicate Entry (1062)
                    if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062) {

                        // LOGIKA PENTING: Ubah $currentCode ke nomor berikutnya
                        // Menggunakan regex untuk mencari angka di akhir string
                        if (preg_match('/^(.*?)(\d+)$/', $currentCode, $matches)) {
                            $prefix = $matches[1];
                            $lastNumber = (int) $matches[2];
                            $length = strlen($matches[2]);

                            // Tambahkan 1 ke nomor, lalu format ulang
                            $currentCode = $prefix.str_pad($lastNumber + 1, $length, '0', STR_PAD_LEFT);
                        } else {
                            // Jika tidak ada format angka, tambahkan -1
                            $currentCode .= '-1';
                        }

                        usleep(50000); // Tunggu sebentar sebelum retry

                        continue;
                    }
                    throw $e; // Jika error bukan 1062, lempar error asli
                }
            }

            if (! $receiveItem) {
                throw new Exception('Gagal membuat Receive Item: Nomor sudah penuh atau sistem sibuk.');
            }

            if ($itemsDetailRaw) {
                $items = json_decode($itemsDetailRaw, true);
                $involvedPrIds = [];

                if (is_array($items) && count($items) > 0) {
                    foreach ($items as $item) {
                        $prDetailId = $item['purchase_order_detail_id'] ?? $item['pr_detail_id'] ?? $item['detail_id'] ?? null;
                        $qtyInputForm = floatval($item['quantity'] ?? $item['qty'] ?? 0);

                        // 1. Simpan Detail Penerimaan
                        ReceiveItemDetail::create([
                            'receive_item_id' => $receiveItem->id,
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

                        // =====================================================
                        // 3. Simpan Mutasi Stok (Selalu disimpan dalam Base Unit)
                        // =====================================================

                        $product = Barang::findOrFail($item['product_id']);

                        $baseUnitId = $product->unit_id; // satuan dasar barang

                        $qtyInput = (float) ($item['quantity'] ?? 0);
                        $unitInput = $item['unit_id'];

                        $totalBaseQty = $qtyInput;

                        // Jika transaksi bukan menggunakan satuan dasar
                        if ($unitInput != $baseUnitId) {

                            $conversion = DataBarangConversion::where('data_barang_id', $item['product_id'])
                                ->where('from_unit_id', $unitInput)
                                ->where('to_unit_id', $baseUnitId)
                                ->first();

                            if (! $conversion) {
                                throw new Exception(
                                    "Konversi satuan tidak ditemukan untuk produk {$product->nama_barang}"
                                );
                            }

                            $totalBaseQty = $qtyInput * $conversion->qty;
                        }

                        StockMutation::create([
                            'data_barang_id' => $item['product_id'],
                            'unit_id' => $unitInput, // unit transaksi
                            'warehouse_id' => $item['warehouse_id'],
                            'date_stock' => $data['receive_item_date'],

                            // Qty sesuai transaksi
                            'qty_transaksi' => $qtyInput,

                            // Qty dalam satuan dasar
                            'total_base_qty' => $totalBaseQty,

                            'type' => 'in',

                            'keterangan' => 'Penerimaan Barang dari '.$receiveItem->supplierId->nama_supplier.
                                                  ', No.Dokumen : '.$request->no_dokumen,

                            'document_id' => $receiveItem->id,
                            'document_number' => $receiveItem->receive_item_code,
                            'document_type' => 'receive_item',

                            'created_by' => Auth::id(),
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

        } catch (Exception $e) {
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

    // public function update(ReceiveItemRequest $request, $id)
    // {
    //     DB::beginTransaction();

    //     try {
    //         $currentYear = date('Y');
    //         $receiveItem = ReceiveItem::findOrFail($id);

    //         // 1. ROLLBACK: Kembalikan received_qty dan hitung ulang outstanding_qty di PO Detail lama
    //         $oldDetails = ReceiveItemDetail::where('receive_item_id', $id)->get();
    //         foreach ($oldDetails as $oldDetail) {
    //             if ($oldDetail->purchase_order_detail_id) {
    //                 $poDetail = DB::table("purchase_order_detail_{$currentYear}")->where('id', $oldDetail->purchase_order_detail_id)->first();
    //                 if ($poDetail) {
    //                     $newReceived = max(0, $poDetail->received_qty - $oldDetail->qty);
    //                     $newOutstanding = $poDetail->qty - $newReceived;

    //                     DB::table("purchase_order_detail_{$currentYear}")->where('id', $oldDetail->purchase_order_detail_id)
    //                         ->update([
    //                             'received_qty' => $newReceived,
    //                             'outstanding_qty' => $newOutstanding,
    //                         ]);
    //                 }
    //             }
    //         }

    //         // 2. HAPUS: Hapus detail lama dan mutasi stok lama
    //         ReceiveItemDetail::where('receive_item_id', $id)->delete();
    //         StockMutation::where('document_type', 'receive_item')
    //             ->where('keterangan', 'like', "%No.Dokumen : {$receiveItem->no_dokumen}%")
    //             ->delete();

    //         // 3. UPDATE: Data Master
    //         $data = $request->validated();
    //         $itemsDetailRaw = $request->input('items_detail');
    //         unset($data['items_detail']);

    //         $data['receive_item_date'] = Carbon::parse($request->receive_item_date)->format('Y-m-d');
    //         $data['tanggal_kirim'] = $request->tanggal_kirim ? Carbon::parse($request->tanggal_kirim)->format('Y-m-d') : null;
    //         $data['updated_by'] = Auth::id();
    //         $data['address'] = $request->address;

    //         $receiveItem->update($data);

    //         // 4. SIMPAN: Data Detail Baru
    //         if ($itemsDetailRaw) {
    //             $items = is_array($itemsDetailRaw) ? $itemsDetailRaw : json_decode($itemsDetailRaw, true);
    //             $involvedPrIds = [];

    //             foreach ($items as $item) {
    //                 $prDetailId = $item['purchase_order_detail_id'] ?? $item['pr_detail_id'] ?? null;
    //                 $qtyInput = floatval($item['quantity'] ?? $item['qty'] ?? 0);

    //                 ReceiveItemDetail::create([
    //                     'receive_item_id' => $receiveItem->id,
    //                     'purchase_order_detail_id' => $prDetailId,
    //                     'product_id' => $item['product_id'],
    //                     'qty' => $qtyInput,
    //                     'unit_id' => $item['unit_id'],
    //                     'warehouse_id' => $item['warehouse_id'],
    //                     'active' => 1,
    //                 ]);

    //                 // Update PO Detail baru dengan perhitungan Outstanding
    //                 if ($prDetailId) {
    //                     $poDetail = DB::table("purchase_order_detail_{$currentYear}")->where('id', $prDetailId)->first();
    //                     if ($poDetail) {
    //                         // Ambil total akumulasi terbaru setelah data baru masuk
    //                         $totalReceived = ReceiveItemDetail::where('purchase_order_detail_id', $prDetailId)
    //                             ->where('active', 1)
    //                             ->sum('qty');

    //                         $outstanding = max(0, $poDetail->qty - $totalReceived);

    //                         DB::table("purchase_order_detail_{$currentYear}")->where('id', $prDetailId)
    //                             ->update([
    //                                 'received_qty' => $totalReceived,
    //                                 'outstanding_qty' => $outstanding,
    //                             ]);

    //                         $involvedPrIds[] = $poDetail->purchase_order_id;
    //                     }
    //                 }

    //                 // Simpan Mutasi Stok Baru
    //                 StockMutation::create([
    //                     'data_barang_id' => $item['product_id'],
    //                     'unit_id' => $item['unit_id'],
    //                     'warehouse_id' => $item['warehouse_id'],
    //                     'date_stock' => $data['receive_item_date'],
    //                     'qty_transaksi' => $qtyInput,
    //                     'total_base_qty' => $qtyInput,
    //                     'type' => 'in',
    //                     'keterangan' => 'Penerimaan Barang, No.Dokumen : '.$request->no_dokumen,
    //                     'created_by' => Auth::id(),
    //                     'document_type' => 'receive_item',
    //                 ]);
    //             }

    //             // 5. OTOMASI STATUS PO MASTER
    //             foreach (array_unique($involvedPrIds) as $prId) {
    //                 $allDetails = DB::table("purchase_order_detail_{$currentYear}")->where('purchase_order_id', $prId)->get();
    //                 $totalRequested = $allDetails->sum('qty');
    //                 $totalOrdered = $allDetails->sum('received_qty');

    //                 $newStatus = ($totalOrdered >= $totalRequested) ? 'completed' : (($totalOrdered > 0) ? 'partially_received' : 'processing');
    //                 DB::table("purchase_order_{$currentYear}")->where('id', $prId)->update(['status' => $newStatus]);
    //             }
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'status' => 'success',
    //             'title' => 'Success',
    //             'message' => 'Receive Item berhasil diupdate',
    //             'redirect' => route('receive-item.index'),
    //         ]);

    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         return response()->json(['status' => 'error', 'message' => 'Gagal: '.$e->getMessage()], 500);
    //     }
    // }

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
            $data['address'] = $request->address;

            $receiveItem->update($data);

            // 4. SIMPAN: Data Detail Baru
            if ($itemsDetailRaw) {
                $items = is_array($itemsDetailRaw) ? $itemsDetailRaw : json_decode($itemsDetailRaw, true);
                $involvedPrIds = [];

                foreach ($items as $item) {
                    // $prDetailId = $item['purchase_order_detail_id'] ?? $item['pr_detail_id'] ?? null;
                    $prDetailId = null;

                    if (! empty($item['purchase_order_detail_id'])) {
                        $prDetailId = (int) $item['purchase_order_detail_id'];
                    } elseif (! empty($item['pr_detail_id'])) {
                        $prDetailId = (int) $item['pr_detail_id'];
                    } elseif (! empty($item['detail_id'])) {
                        $poDetail = DB::table("purchase_order_detail_{$currentYear}")
                            ->where('product_id', $item['product_id'])
                            ->first();

                        if ($poDetail) {
                            $prDetailId = $poDetail->id;
                        }
                    }
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
                    $product = Barang::findOrFail($item['product_id']);

                    $baseUnitId = $product->unit_id;
                    $unitInput = $item['unit_id'];

                    $totalBaseQty = $qtyInput;

                    if ($unitInput != $baseUnitId) {

                        $conversion = DataBarangConversion::where('data_barang_id', $item['product_id'])
                            ->where('from_unit_id', $unitInput)
                            ->where('to_unit_id', $baseUnitId)
                            ->first();

                        if (! $conversion) {
                            throw new Exception(
                                "Konversi satuan untuk {$product->nama_barang} belum dibuat."
                            );
                        }

                        $totalBaseQty = $qtyInput * $conversion->qty;
                    }
                    StockMutation::create([
                        'data_barang_id' => $item['product_id'],
                        'unit_id' => $unitInput,
                        'warehouse_id' => $item['warehouse_id'],
                        'date_stock' => $data['receive_item_date'],

                        'qty_transaksi' => $qtyInput,
                        'total_base_qty' => $totalBaseQty,

                        'type' => 'in',

                        'keterangan' => 'Penerimaan Barang, No.Dokumen : '.$request->no_dokumen,

                        'created_by' => Auth::id(),

                        'document_type' => 'receive_item',
                        'document_id' => $receiveItem->id,
                        'document_number' => $receiveItem->receive_item_code,
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

        } catch (Exception $e) {
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

            $ri = ReceiveItem::findOrFail($id);

            // Ambil detail RI aktif
            $riDetails = ReceiveItemDetail::where('receive_item_id', $ri->id)
                ->where('active', 1)
                ->get();

            $involvedPoIds = [];

            /*
            |--------------------------------------------------------------------------
            | 1. KURANGI STOCK BALANCE + HAPUS STOCK MUTATION
            |--------------------------------------------------------------------------
            */
            foreach ($riDetails as $detail) {

                // Ambil mutation RI
                $mutation = StockMutation::where([
                    'document_id' => $ri->id,
                    'document_type' => 'receive_item',
                    'data_barang_id' => $detail->product_id,
                    'warehouse_id' => $detail->warehouse_id,
                ])->first();

                if ($mutation) {

                    // Kurangi stock balance berdasarkan base qty
                    StockBalance::where([
                        'product_id' => $detail->product_id,
                        'warehouse_id' => $detail->warehouse_id,
                    ])
                        ->decrement(
                            'qty',
                            $mutation->total_base_qty
                        );

                    // Hapus mutation
                    $mutation->delete();

                }

                /*
                |--------------------------------------------------------------------------
                | Ambil PO yang terkait
                |--------------------------------------------------------------------------
                */

                if ($detail->purchase_order_detail_id) {

                    $poDetail = DB::table(
                        'purchase_order_detail_'.date('Y')
                    )
                        ->where('id', $detail->purchase_order_detail_id)
                        ->first();

                    if ($poDetail && ! in_array($poDetail->purchase_order_id, $involvedPoIds)) {

                        $involvedPoIds[] = $poDetail->purchase_order_id;

                    }
                }

            }

            /*
            |--------------------------------------------------------------------------
            | 2. NONAKTIFKAN RECEIVE ITEM
            |--------------------------------------------------------------------------
            */

            $ri->update([
                'active' => 0,
                'updated_by' => Auth::id(),
            ]);

            ReceiveItemDetail::where('receive_item_id', $ri->id)
                ->update([
                    'active' => 0,
                ]);

            /*
            |--------------------------------------------------------------------------
            | 3. UPDATE ULANG PO DETAIL
            |--------------------------------------------------------------------------
            */

            foreach ($riDetails as $detail) {

                if ($detail->purchase_order_detail_id) {

                    $totalReceived = ReceiveItemDetail::where(
                        'purchase_order_detail_id',
                        $detail->purchase_order_detail_id
                    )
                        ->where('active', 1)
                        ->sum('qty');

                    DB::table(
                        'purchase_order_detail_'.date('Y')
                    )
                        ->where(
                            'id',
                            $detail->purchase_order_detail_id
                        )
                        ->update([
                            'received_qty' => $totalReceived,
                            'outstanding_qty' => DB::raw(
                                "qty - {$totalReceived}"
                            ),
                        ]);

                }

            }

            /*
            |--------------------------------------------------------------------------
            | 4. UPDATE STATUS PO HEADER
            |--------------------------------------------------------------------------
            */

            foreach ($involvedPoIds as $poId) {

                $details = DB::table(
                    'purchase_order_detail_'.date('Y')
                )
                    ->where(
                        'purchase_order_id',
                        $poId
                    )
                    ->get();

                $requested = $details->sum('qty');

                $received = $details->sum('received_qty');

                $status =
                    $received >= $requested
                        ? 'completed'
                        : ($received > 0 ? 'partial' : 'processing');

                DB::table(
                    'purchase_order_'.date('Y')
                )
                    ->where('id', $poId)
                    ->update([
                        'status' => $status,
                    ]);

            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Receive Item berhasil dibatalkan.',
            ], 200);

        } catch (Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membatalkan RI: '.$e->getMessage(),
            ], 500);

        }
    }

    public function trash(Request $r)
    {
        if ($r->ajax()) {
            // Ambil ID user yang sedang login
            $userId = Auth::user()->id;

            // Query dengan kondisi: Aktif DAN (Status BUKAN draft ATAU Status ADALAH draft kepunyaan sendiri)
            $query = ReceiveItem::where('active', 0)
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
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">
                      <button type="button" class="btn btn-primary dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-menu-2 ti-xs me-1"></i>
                      </button>
                      <ul class="dropdown-menu" style="">';

                    if (auth()->user()->can('delivery_order-restore')) {
                        $btn .= '<a class="dropdown-item restore" href="javascript:void(0)"
                            data-id="'.$row->id.'"> <i class="ti ti-trash-off me-1"></i> Restore</a>';
                    }

                    return $btn;
                })
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'date', 'supplier'])
                ->make(true);
        }

        $x = [
            'title' => 'Deleted Receive Item ',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Deleted Receive Item', 'url' => ''],
            ],
        ];

        return view('purchase.receive_item.receive_item_trash', $x);
    }

    public function restore($id)
    {
        DB::beginTransaction();

        try {

            $ri = ReceiveItem::with('details')->findOrFail($id);

            // Cegah restore stok dua kali
            $mutationExists = StockMutation::where([
                'document_id' => $ri->id,
                'document_type' => 'receive_item',
            ])->exists();

            if ($mutationExists) {
                throw new Exception('Stock mutation Receive Item ini sudah ada.');
            }

            $ri->update([
                'active' => 1,
                'updated_by' => Auth::id(),
            ]);

            foreach ($ri->details as $detail) {

                $product = Barang::findOrFail($detail->product_id);

                $baseUnitId = $product->unit_id;

                $qtyInput = (float) $detail->qty;

                $unitInput = $detail->unit_id;

                $totalBaseQty = $qtyInput;

                // Konversi ke satuan dasar
                if ($unitInput != $baseUnitId) {

                    $conversion = DataBarangConversion::where('data_barang_id', $detail->product_id)
                        ->where('from_unit_id', $unitInput)
                        ->where('to_unit_id', $baseUnitId)
                        ->first();

                    if (! $conversion) {

                        throw new Exception(
                            "Konversi satuan tidak ditemukan untuk produk {$product->nama_barang}"
                        );

                    }

                    $totalBaseQty = $qtyInput * $conversion->qty;

                }

                /*
                |--------------------------------------------------------------------------
                | Tambahkan kembali Stock Balance
                |--------------------------------------------------------------------------
                */

                StockBalance::updateOrCreate(
                    [
                        'product_id' => $detail->product_id,
                        'warehouse_id' => $detail->warehouse_id,
                    ],
                    [
                        'qty' => DB::raw(
                            "qty + {$totalBaseQty}"
                        ),
                    ]
                );

                /*
                |--------------------------------------------------------------------------
                | Buat ulang Stock Mutation IN
                |--------------------------------------------------------------------------
                */

                StockMutation::create([

                    'data_barang_id' => $detail->product_id,

                    // unit transaksi
                    'unit_id' => $unitInput,

                    'warehouse_id' => $detail->warehouse_id,

                    'date_stock' => $ri->receive_item_date,

                    // qty transaksi
                    'qty_transaksi' => $qtyInput,

                    // qty base unit
                    'total_base_qty' => $totalBaseQty,

                    'type' => 'in',

                    'document_id' => $ri->id,

                    'document_number' => $ri->receive_item_code,

                    'document_type' => 'receive_item',

                    'keterangan' => sprintf(
                        'Penerimaan barang dari supplier %s No RI %s',
                        $ri->supplierId->nama_supplier ?? 'Supplier Tidak Diketahui',
                        $ri->receive_item_code
                    ),

                    'created_by' => Auth::id(),

                ]);

            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Receive Item berhasil direstore.',
            ]);

        } catch (Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);

        }
    }
}
