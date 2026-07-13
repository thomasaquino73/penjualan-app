<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeliveryOrderRequest;
use App\Models\BasicCodeDetail;
use App\Models\Inventory\Barang;
use App\Models\Inventory\StockBalance;
use App\Models\Inventory\Warehouse;
use App\Models\Sales\Customer;
use App\Models\Sales\DeliveryOrder;
use App\Models\Sales\DeliveryOrderDetail;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderDetail;
use App\Models\Setting\Company;
use App\Models\Setting\Shipping;
use App\Models\StockMutation;
use App\Models\User;
use App\Services\StockService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class DeliveryOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $routeName = $request->route()->getName();

            $permissionMap = [
                'delivery-order.index' => 'delivery_order-browse',
                'delivery-order.show' => 'delivery_order-read',
                'delivery-order.create' => 'delivery_order-create',
                'delivery-order.store' => 'delivery_order-create',
                'delivery-order.edit' => 'delivery_order-edit',
                'delivery-order.update' => 'delivery_order-edit',
                'delivery-order.destroy' => 'delivery_order-delete',
                'delivery-order.trash' => 'delivery_order-trash',
                'delivery-order.restore' => 'delivery_order-restore',
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

            //        $query = SalesOrder::where('active', '<>', 0)
            //     ->where(function ($q) use ($userId) {
            //         $q->where('status', '<>', 'draft')
            //             ->orWhere(function ($subQ) use ($userId) {
            //                 $subQ->where('status', 'draft')
            //                     ->where('created_by', $userId);
            //             });
            //     })
            //     ->orderby('sales_order_code', 'desc');
            // if ($r->status) {
            //     $query->where('status', $r->status);
            // }
            $query = DeliveryOrder::where('active', '<>', 0)
                ->where(function ($q) use ($userId) {
                    $q->where('status', '<>', 'draft')
                        ->orWhere(function ($subQ) use ($userId) {
                            $subQ->where('status', 'draft')
                                ->where('created_by', $userId);
                        });
                })
                ->orderby('delivery_order_code', 'desc');
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
                ->addColumn('delivery_order_date', function ($row) {
                    return $row->delivery_order_date ? Carbon::parse($row->delivery_order_date)->format('d M Y') : 'N/A';
                })
                ->addColumn('customer', function ($row) {
                    return $row->customerID->nama_customer ?? 'N/A';
                })
                ->addColumn('status', function ($row) {

                    switch ($row->status) {

                        case 'processing':
                            $badge = 'bg-label-warning';
                            $text = 'Processing';
                            break;

                        case 'approved':
                            $badge = 'bg-label-success';
                            $text = 'Approved';
                            break;

                        case 'sent':
                            $badge = 'bg-label-primary';
                            $text = 'Sent To Supplier';
                            break;

                        case 'partially_received':
                            $badge = 'bg-label-info';
                            $text = 'Partially Received';
                            break;

                        case 'completed':
                            $badge = 'bg-success';
                            $text = 'Completed';
                            break;

                        case 'rejected':
                            $badge = 'bg-label-danger';
                            $text = 'Rejected';
                            break;

                        case 'cancelled':
                            $badge = 'bg-danger';
                            $text = 'Cancelled';
                            break;
                        case 'closed':
                            $badge = 'bg-dark';
                            $text = 'Closed';
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

                    // REJECTED INFO
                    if ($row->status == 'rejected' && $row->rejectedBy) {

                        $html .= '
                            <small class="text-muted mt-1">
                                Rejected By : '.$row->rejectedBy->fullname.'
                            </small>
                        ';
                    }

                    // OUTSTANDING INFO
                    if (
                        in_array($row->status, ['partially_received']) &&
                        $row->total_outstanding_qty > 0
                    ) {

                        $html .= '
                            <small class="text-warning mt-1">
                                Outstanding : '.number_format($row->total_outstanding_qty).'
                            </small>
                        ';
                    }

                    $html .= '</div>';

                    return $html;
                })
                ->addColumn('cekbok', function ($row) {

                    if (
                        auth()->user()->can('delivery_order-delete') &&
                        $row->status === 'processing'
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
                // ->addColumn('total', function ($row) {
                //     // 1. Hitung total kotor (sum amount) dari detail item DO
                //     $subTotal = DeliveryOrderDetail::where('delivery_order_id', $row->id)
                //         ->where('active', 1)
                //         ->sum('amount');

                //     // 2. Hitung grand total: Subtotal dikurangi diskon nominal yang ada di tabel induk ($row)
                //     // Gunakan ?? 0 jika kolom disc_nominal di database bisa bernilai null
                //     $grandTotal = $subTotal - ($row->disc_nominal ?? 0);

                //     // 3. Kembalikan nilai yang sudah dikonversi dan diformat
                //     return format_uang(convert_currency($grandTotal, $row->currency_id ?? 1));
                // })
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

                    if (
                        $user->can('delivery_order-edit') &&
                        in_array($row->status, ['processing', 'rejected'])
                    ) {

                        $btn .= '
                                <a class="dropdown-item"
                                    href="'.route('delivery-order.edit', $row->id).'">

                                    <i class="far fa-edit me-1"></i>
                                    Edit
                                </a>
                            ';
                    }

                    // DELETE
                    if (
                        $user->can('delivery_order-delete') &&
                        $row->status == 'processing'
                    ) {

                        $btn .= '
                                <a class="dropdown-item text-danger"
                                    href="javascript:void(0)"
                                    id="delete"
                                    data-id="'.$row->id.'"
                                    data-name="'.$row->delivery_order_code.'">

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

                    if (
                        $row->created_by !== $currentUserId &&
                        $user->can('delivery_order-approval')
                    ) {

                        if ($row->status == 'pending') {

                            $btn .= '
                                    <a class="dropdown-item text-success btn-approval-po"
                                        href="javascript:void(0)"
                                        data-status="approved"
                                        data-id="'.$row->id.'">

                                        <i class="ti ti-check me-1"></i>
                                        Approve
                                    </a>
                                ';

                            $btn .= '
                                    <a class="dropdown-item text-danger btn-approval-po"
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
                    | 3. SEND TO SUPPLIER
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $row->status == 'approved'
                        // $row->status == 'approved' &&
                        // $user->can('delivery_order-send-supplier')
                    ) {

                        $btn .= '
                            <a class="dropdown-item text-info btn-send-supplier"
                                href="javascript:void(0)"
                                data-id="'.$row->id.'">

                                <i class="ti ti-mail-fast me-1"></i>
                                Send To Supplier
                            </a>
                        ';
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | 4. RECEIVE ITEM
                    |--------------------------------------------------------------------------
                    */

                    if (
                        in_array($row->status, ['sent', 'partially_received']) &&
                        $user->can('delivery_order-receive')
                    ) {

                        $btn .= '
            <a class="dropdown-item text-primary"
                href="'.route('delivery-order.receive', $row->id).'">

                <i class="ti ti-package-import me-1"></i>
                Receive Item
            </a>
        ';
                    }

                    /*
                    |--------------------------------------------------------------------------
                    |--------------------------------------------------------------------------
                    */

                    if (
                        ! in_array($row->status, ['completed', 'cancelled']) &&
                        $user->can('delivery_order-cancel')
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
                    if ($row->status != 'delivered') {
                        $btn .= '<a class="dropdown-item"
                href="javascript:void(0)" id="delivered"   data-id="'.$row->id.'" data-name="'.$row->delivery_order_code.'">
                <i class="ti ti-lock"></i> Delivered
             </a>';
                    }
                    /*
                    |--------------------------------------------------------------------------
                    | 7. PRINT
                    |--------------------------------------------------------------------------
                    */

                    $btn .= '
        <a class="dropdown-item"
            target="_blank"
            href="'.route('delivery-order.print', $row->id).'">

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
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'cekbok', 'delivery_order_date', 'customer'])
                ->make(true);
        }

        $x = [
            'title' => 'Delivery Order List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Delivery Order', 'url' => ''],
            ],
        ];

        return view('sales.deliveryOrder.delivery_order_index', $x);
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
        $prefix = "DO/{$tahun}/{$bulanRomawi}/";

        // Ambil nomor terakhir pada bulan & tahun yang sama
        $last = DeliveryOrder::where('delivery_order_code', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();

        if ($last) {
            // Ambil 4 digit terakhir
            $lastNumber = (int) substr($last->delivery_order_code, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            // Jika belum ada pada bulan ini mulai dari 0001
            $nextNumber = 1;
        }

        return $prefix.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function create(Request $r)
    {
        $status = ['processing', 'partial'];
        $x = [
            'title' => 'Delivery Order New',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Delivery Order', 'url' => ''],
            ],
            'customer' => Customer::where('status', '<>', 0)->get(),
            'idNumber' => $this->generateNumberId(),
            'product' => Barang::with(['unitID'])->where('status', '<>', 0)->get(),
            'warehouse' => Warehouse::where('status', '<>', 0)->get(),
            'shipping' => Shipping::where('status', 1)->get(),
            'fob' => BasicCodeDetail::where('master_id', 7)->get(),
            // 'sqNumber' => SalesOrder::whereIn('status', $status)
            //     ->where('active', 1)
            //     ->where('customer_id', $r->customer_id)
            //     ->get(),

        ];

        return view('sales.deliveryOrder.delivery_order_create', $x);
    }

    public function store(DeliveryOrderRequest $r, StockService $stockService)
    {
        DB::beginTransaction();

        try {
            $currentYear = date('Y');
            $data = $r->except('save_and_new', 'items_detail');
            $itemsDetailRaw = $r->input('items_detail');
            unset($data['items_detail']);
            $data['delivery_order_date'] = Carbon::parse($r->delivery_order_date)->format('Y-m-d');
            $data['created_by'] = Auth::id();

            $deliveryOrder = null;
            $maxRetry = 10;
            $currentCode = $r->delivery_order_code;

            // 1. Logic untuk generate nomor DO unik
            for ($attempt = 1; $attempt <= $maxRetry; $attempt++) {
                try {
                    $data['delivery_order_code'] = $currentCode;
                    $deliveryOrder = DeliveryOrder::create($data);
                    break;
                } catch (QueryException $e) {
                    if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062) {
                        if (preg_match('/^(.*?)(\d+)$/', $currentCode, $matches)) {
                            $prefix = $matches[1];
                            $lastNumber = (int) $matches[2];
                            $length = strlen($matches[2]);
                            $currentCode = $prefix.str_pad($lastNumber + 1, $length, '0', STR_PAD_LEFT);
                        } else {
                            $currentCode .= '-1';
                        }
                        usleep(50000);

                        continue;
                    }
                    throw $e;
                }
            }

            if (! $deliveryOrder) {
                throw new \Exception('Gagal membuat Delivery Order: Sistem sibuk.');
            }

            if ($itemsDetailRaw) {
                $items = json_decode($itemsDetailRaw, true);
                $involvedSqIds = [];

                if (is_array($items) && count($items) > 0) {
                    foreach ($items as $index => $item) {
                        $soDetailId = $item['sales_order_detail_id'] ?? $item['detail_id'] ?? null;
                        $qty = $item['quantity'] ?? $item['qty'];

                        // Lock stock untuk validasi
                        DB::table('stock_mutations')
                            ->where('data_barang_id', $item['product_id'])
                            ->where('warehouse_id', $item['warehouse_id'])
                            ->where('unit_id', $item['unit_id'])
                            ->lockForUpdate()
                            ->get();

                        $realStock = $stockService->realStock($item['product_id'], $item['warehouse_id'], $item['unit_id']);

                        if ($realStock < $qty) {
                            throw new \Exception("Stok barang {$item['product_name']} tidak mencukupi. Tersedia: {$realStock}, Permintaan: {$qty}");
                        }

                        // 2. Simpan ke DeliveryOrderDetail
                        DeliveryOrderDetail::create([
                            'delivery_order_id' => $deliveryOrder->id,
                            'sales_order_detail_id' => $soDetailId,
                            'urutan' => $index,
                            'data_barang_id' => $item['product_id'],
                            'qty' => $qty,
                            'do_qty' => 0,
                            'outstanding_qty' => $qty,
                            'unit_id' => $item['unit_id'],
                            'warehouse_id' => $item['warehouse_id'],
                        ]);

                        // 3. Stock Mutation
                        $customer = Customer::find($r->customer_id);
                        StockMutation::create([
                            'data_barang_id' => $item['product_id'],
                            'document_id' => $deliveryOrder->id,
                            'unit_id' => $item['unit_id'],
                            'warehouse_id' => $item['warehouse_id'],
                            'date_stock' => $data['delivery_order_date'],
                            'qty_transaksi' => $qty,
                            'total_base_qty' => $qty,
                            'type' => 'out',
                            'document_number' => $deliveryOrder->delivery_order_code,
                            'document_type' => 'delivery_order',
                            'keterangan' => 'Pengiriman ke '.($customer->nama_customer ?? 'Customer').' via DO '.$deliveryOrder->delivery_order_code,
                            'created_by' => Auth::id(),
                        ]);

                        // 4. Update Sales Order Detail & kumpulkan ID SO yang terlibat
                        if ($soDetailId) {
                            $tableDetailName = "sales_order_detail_{$currentYear}";
                            $sqDetail = DB::table($tableDetailName)->where('id', $soDetailId)->first();

                            if ($sqDetail) {
                                $totalDelivered = DB::table("delivery_order_detail_{$currentYear}")
                                    ->where('sales_order_detail_id', $soDetailId)
                                    ->sum('qty');

                                $newOutstanding = max(0, ($sqDetail->qty - $totalDelivered));

                                DB::table($tableDetailName)->where('id', $soDetailId)->update([
                                    'so_qty' => $totalDelivered, // Ini adalah total akumulasi kirim
                                    'outstanding_qty' => $newOutstanding,
                                ]);

                                if (! in_array($sqDetail->sales_order_id, $involvedSqIds)) {
                                    $involvedSqIds[] = $sqDetail->sales_order_id;
                                }
                            }
                        }
                    }

                    // 5. Update Status SO Header
                    foreach ($involvedSqIds as $sqId) {
                        $allDetails = DB::table("sales_order_detail_{$currentYear}")
                            ->where('sales_order_id', $sqId)
                            ->get();

                        $totalRequested = $allDetails->sum('qty');
                        $totalDelivered = $allDetails->sum('so_qty');

                        // Mengikuti enum di skema Anda: open, partial, completed, cancelled
                        if ($totalDelivered >= $totalRequested) {
                            $newStatus = 'completed';
                        } elseif ($totalDelivered > 0) {
                            $newStatus = 'partial';
                        } else {
                            $newStatus = 'processing';
                        }

                        DB::table("sales_order_{$currentYear}")
                            ->where('id', $sqId)
                            ->update(['status' => $newStatus]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil disimpan',
                'redirect' => route('delivery-order.index'),
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
    public function edit(string $id, Request $r)
    {
        $year = date('Y');
        $deliveryOrder = DeliveryOrder::with([
            'details.produkID',
            'details.unitID',
            'details.warehouseID',
            'details.salesOrderDetail.salesOrder',
        ])->findOrFail($id);
        $isFromPR = $deliveryOrder->details->whereNotNull('sales_order_detail_id')->count() > 0;
        $detailDataMapped = $deliveryOrder->details->map(function ($detail) use ($deliveryOrder, $year) {

            $orderCode = null;
            $sisaPr = null;
            $kuotaAsliPr = null;
            $totalDiambilLainnya = 0;

            // Cek apakah item detail ini memiliki keterikatan dengan PR
            if ($detail->sales_order_detail_id) {
                // Ambil data referensi dari relasi
                $prDetail = $detail->salesOrderDetail;

                if ($prDetail) {
                    $sisaPr = (float) $prDetail->outstanding_qty;
                    $kuotaAsliPr = (float) $prDetail->qty;

                    // HITUNG TOTAL YANG SUDAH DIAMBIL DI PO LAIN
                    // Menggunakan DB::table karena tabel bersifat dinamis per tahun
                    $totalDiambilLainnya = DB::table("delivery_order_detail_{$year} as dod")
                        ->join("delivery_order_{$year} as do", 'do.id', '=', 'dod.delivery_order_id')
                        ->where('dod.sales_order_detail_id', $detail->sales_order_detail_id)
                        ->where('dod.delivery_order_id', '<>', $deliveryOrder->id)
                        ->where('do.active', 1)
                        ->sum('dod.qty');

                    if ($prDetail->salesOrder) {
                        $orderCode = $prDetail->salesOrder->sales_order_code;
                    }
                }
            }

            return [
                'id' => $detail->id,
                'sales_order_id' => $detail->sales_order_id,
                'sales_order_detail_id' => $detail->sales_order_detail_id,
                'order_code' => $orderCode,
                'product_id' => $detail->data_barang_id,
                'data_produk' => $detail->produkID->nama_barang ?? 'Product Not Found',
                'quantity' => (float) $detail->qty,
                'unit_id' => $detail->unit_id,
                'unit' => $detail->unitID->detail ?? '-',
                'warehouse_id' => $detail->warehouse_id,
                'warehouse' => $detail->warehouseID->nama_gudang ?? '-',
                'unit_price' => (float) $detail->unit_price,
                'discount_percent' => $detail->discount_percent,
                'discount' => (float) $detail->discount,
                'amount' => (float) $detail->amount,
                'tax' => (float) ($detail->tax ?? 0),
                'sisa_pr' => $sisaPr,
                'kuota_asli' => $kuotaAsliPr,
                'total_diambil_lainnya' => (float) $totalDiambilLainnya, // Dikirim ke frontend
            ];
        });
        $status = ['processing', 'partial'];
        $x = [
            'title' => 'Delivery Order New',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Delivery Order', 'url' => ''],
            ],
            'customer' => Customer::where('status', '<>', 0)->get(),
            'idNumber' => $this->generateNumberId(),
            'product' => Barang::where('status', '<>', 0)->get(),
            'warehouse' => Warehouse::where('status', '<>', 0)->get(),
            'shipping' => Shipping::where('status', 1)->get(),
            'fob' => BasicCodeDetail::where('master_id', 7)->get(),
            'model' => $deliveryOrder,
            'isFromPR' => $isFromPR,
            'sqNumber' => SalesOrder::whereIn('status', $status)
                ->where('active', 1)
                ->where('customer_id', $r->customer_id)
                ->get(),
        ];

        return view('sales.deliveryOrder.delivery_order_edit', $x);
    }

    public function update(DeliveryOrderRequest $r, $id)
    {
        DB::beginTransaction();

        try {

            $deliveryOrder = DeliveryOrder::findOrFail($id);
            $code = $r->delivery_order_code;

            while (
                DeliveryOrder::where('delivery_order_code', $code)
                    ->where('id', '!=', $deliveryOrder->id)
                    ->exists()
            ) {
                $code = $this->generateNumberId();
            }
            /*
            |--------------------------------------------------------------------------
            | 1. KEMBALIKAN STOCK DARI DATA LAMA
            |--------------------------------------------------------------------------
            */
            $oldDetails = DeliveryOrderDetail::where('delivery_order_id', $id)->get();

            foreach ($oldDetails as $old) {
                // Balikin stok
                // $stock = StockBalance::where([
                //     'product_id' => $old->data_barang_id,
                //     'warehouse_id' => $old->warehouse_id,
                // ])->first();

                // if ($stock) {
                //     $stock->increment('qty', $old->qty);
                // }

                // Hapus mutation lama
                StockMutation::where([
                    'document_number' => $deliveryOrder->delivery_order_code,
                    'data_barang_id' => $old->data_barang_id,
                ])->delete();
            }

            // Hapus detail lama
            DeliveryOrderDetail::where('delivery_order_id', $id)->delete();

            /*
            |--------------------------------------------------------------------------
            | 2. UPDATE HEADER
            |--------------------------------------------------------------------------
            */
            $data = $r->except('save_and_new', 'items_detail');
            $data['delivery_order_date'] = Carbon::parse($r->delivery_order_date)->format('Y-m-d');
            $data['updated_by'] = Auth::user()->id;

            $deliveryOrder->update($data);

            /*
            |--------------------------------------------------------------------------
            | 3. INSERT DATA BARU (SAMA SEPERTI STORE)
            |--------------------------------------------------------------------------
            */
            $itemsDetailRaw = $r->input('items_detail');

            if ($itemsDetailRaw) {
                $items = json_decode($itemsDetailRaw, true);

                if (is_array($items) && count($items) > 0) {

                    foreach ($items as $index => $item) {
                        $soDetailId = $item['sales_order_detail_id'] ?? $item['detail_id'] ?? null;
                        $qty = $item['quantity'] ?? $item['qty'];

                        /*
                        |--------------------------------------------------------------------------
                        | Detail
                        |--------------------------------------------------------------------------
                        */
                        DeliveryOrderDetail::create([
                            'delivery_order_id' => $deliveryOrder->id,
                            'sales_order_detail_id' => $soDetailId,
                            'urutan' => $index,
                            'data_barang_id' => $item['product_id'],
                            'qty' => $qty,
                            'unit_id' => $item['unit_id'],
                            'warehouse_id' => $item['warehouse_id'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        /*
                        |--------------------------------------------------------------------------
                        | Stock Mutation OUT
                        |--------------------------------------------------------------------------
                        */
                        $customer = Customer::find($r->customer_id);
                        $namaCustomer = $customer
                            ? $customer->nama_customer
                            : 'Customer Tidak Diketahui';

                        StockMutation::create([
                            'document_id' => $deliveryOrder->id,
                            'data_barang_id' => $item['product_id'],
                            'unit_id' => $item['unit_id'],
                            'warehouse_id' => $item['warehouse_id'],
                            'date_stock' => Carbon::parse($r->delivery_order_date)->format('Y-m-d'),
                            'qty_transaksi' => $qty,
                            'total_base_qty' => $qty,
                            'type' => 'out',
                            'document_number' => $deliveryOrder->delivery_order_code,
                            'document_type' => 'delivery_order',
                            'keterangan' => sprintf(
                                'Pengiriman barang ke customer %s melalui DO %s',
                                $namaCustomer,
                                $deliveryOrder->delivery_order_code
                            ),
                            'created_by' => Auth::id(),
                        ]);

                        StockMutation::where([
                            'document_number' => $deliveryOrder->delivery_order_code,
                            'data_barang_id' => $old->data_barang_id,
                            'warehouse_id' => $old->warehouse_id,
                        ])->delete();
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Data updated successfully',
                'redirect' => route('delivery-order.index'),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal update data: '.$e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $do = DeliveryOrder::findOrFail($id);
            $do->update(['active' => 0, 'updated_by' => Auth::id()]);
            StockMutation::where('document_type', 'delivery_order')
                ->where('document_id', $do->id)
                ->delete();
            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'DO berhasil dibatalkan.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['status' => 'error', 'message' => 'Gagal membatalkan DO: '.$e->getMessage()], 500);
        }
    }

    public function deliveredItem(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $do = DeliveryOrder::findOrFail($id);

            $do->update([
                'status' => 'delivered',
                'updated_by' => Auth::id(),
            ]);

            // Jika stok memang harus dikurangi saat barang dikirim
            StockMutation::where('document_type', 'delivery_order')
                ->where('document_id', $do->id)
                ->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Barang berhasil dikirim.',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengirim Barang: '.$e->getMessage(),
            ], 500);
        }
    }

    public function trash(Request $r)
    {
        if ($r->ajax()) {
            // Ambil ID user yang sedang login
            $userId = Auth::user()->id;

            // Query dengan kondisi: Aktif DAN (Status BUKAN processing ATAU Status ADALAH processing kepunyaan sendiri)
            $query = DeliveryOrder::where('active', 0)
                ->orderby('delivery_order_code', 'desc')->get();
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
                ->addColumn('delivery_order_date', function ($row) {
                    return $row->delivery_order_date ? Carbon::parse($row->delivery_order_date)->format('d M Y') : 'N/A';
                })
                ->addColumn('customer', function ($row) {
                    return $row->customerID->nama_customer ?? 'N/A';
                })

                ->addColumn('cekbok', function ($row) {

                    if (
                        auth()->user()->can('delivery_order-delete') &&
                        $row->status === 'processing'
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
                // ->addColumn('total', function ($row) {
                //     // 1. Hitung total kotor (sum amount) dari detail item DO
                //     $subTotal = DeliveryOrderDetail::where('delivery_order_id', $row->id)
                //         ->where('active', 1)
                //         ->sum('amount');

                //     // 2. Hitung grand total: Subtotal dikurangi diskon nominal yang ada di tabel induk ($row)
                //     // Gunakan ?? 0 jika kolom disc_nominal di database bisa bernilai null
                //     $grandTotal = $subTotal - ($row->disc_nominal ?? 0);

                //     // 3. Kembalikan nilai yang sudah dikonversi dan diformat
                //     return format_uang(convert_currency($grandTotal, $row->currency_id ?? 1));
                // })
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
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'cekbok', 'delivery_order_date', 'customer'])
                ->make(true);
        }

        $x = [
            'title' => 'Deleted Delivery Order ',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Deleted Delivery Order', 'url' => ''],
            ],
        ];

        return view('sales.deliveryOrder.delivery_order_trash', $x);
    }

    public function deleteMultiple(Request $request)
    {
        DB::beginTransaction();

        try {
            $ids = $request->ids;

            if (! $ids || count($ids) == 0) {
                return response()->json(['success' => false, 'message' => 'Tidak ada data yang dipilih.'], 400);
            }

            // 1. Ambil semua detail dari SO yang akan dihapus untuk sinkronisasi PR
            DeliveryOrderDetail::whereIn('delivery_order_id', $ids)->get();
            $involvedPrIds = [];

            // 2. Tandai SO dan Detail SO sebagai tidak aktif (active = 0)
            DeliveryOrder::whereIn('id', $ids)->update([
                'active' => 0,
                'updated_by' => Auth::id(),
            ]);
            $this->deleteStockMutation($ids);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Delivery Order berhasil dihapus dan status PR telah diperbarui.',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: '.$e->getMessage(),
            ], 500);
        }
    }

    private function deleteStockMutation($documentIds)
    {
        StockMutation::where('document_type', 'delivery_order')
            ->whereIn('document_id', (array) $documentIds)
            ->delete();
    }

    public function restore($id)
    {
        DB::beginTransaction();

        try {
            $do = DeliveryOrder::with('details')->findOrFail($id);

            $do->update([
                'active' => 1,
                'updated_by' => Auth::id(),
            ]);

            foreach ($do->details as $detail) {

                StockMutation::create([
                    'data_barang_id' => $detail->data_barang_id,
                    'unit_id' => $detail->unit_id,
                    'warehouse_id' => $detail->warehouse_id,
                    'date_stock' => $do->delivery_order_date,
                    'qty_transaksi' => $detail->qty,
                    'total_base_qty' => $detail->qty,
                    'keterangan' => sprintf(
                        'Pengiriman barang ke customer %s melalui DO %s',
                        $do->customerID->nama_customer ?? 'Customer Tidak Diketahui',
                        $do->delivery_order_code
                    ),
                    'type' => 'out',
                    'document_id' => $do->id,
                    'document_number' => $do->delivery_order_code,
                    'document_type' => 'delivery_order',
                    'created_by' => Auth::id(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Delivery Order berhasil direstore.',
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function restoreMultiple(Request $request)
    {
        DB::beginTransaction();

        try {
            $ids = $request->ids;

            if (! $ids || count($ids) == 0) {
                return response()->json(['success' => false, 'message' => 'Tidak ada data yang dipilih.'], 400);
            }

            // 1. Update status SO jadi aktif
            DeliveryOrder::whereIn('id', $ids)->update([
                'active' => 1,
                'updated_by' => Auth::id(),
            ]);

            $do = DeliveryOrder::whereIn('id', $ids)->first();

            foreach ($do->details as $detail) {

                StockMutation::create([
                    'data_barang_id' => $detail->data_barang_id,
                    'unit_id' => $detail->unit_id,
                    'warehouse_id' => $detail->warehouse_id,
                    'date_stock' => $do->delivery_order_date,
                    'qty_transaksi' => $detail->qty,
                    'total_base_qty' => $detail->qty,
                    'keterangan' => sprintf(
                        'Pengiriman barang ke customer %s melalui DO %s',
                        $do->customerID->nama_customer ?? 'Customer Tidak Diketahui',
                        $do->delivery_order_code
                    ),
                    'type' => 'out',
                    'document_id' => $do->id,
                    'document_number' => $do->delivery_order_code,
                    'document_type' => 'delivery_order',
                    'created_by' => Auth::id(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Delivery Order terpilih berhasil dikembalikan.',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal merestore data: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getOrderDetail(Request $request)
    {
        $ids = $request->ids;

        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data SO yang dipilih.',
                'data' => [],
            ]);
        }
        $header = SalesOrder::where('id', $ids)->get();

        $details = SalesOrderDetail::with([
            'produkID',
            'unitID',
            'salesOrder',
            'warehouseID',
        ])
            ->whereIn('sales_order_id', $ids)
            ->where('active', 1)
            ->whereHas('salesOrder', function ($q) {
                // Sesuaikan dengan status yang valid di database Anda
                $q->whereIn('status', ['processing', 'partial']);
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
                'order_code' => $item->salesOrder->sales_order_code ?? '',
                'pr_status' => $item->salesOrder->status ?? '',
                'warehouse_id' => $item->warehouse_id,
                'warehouse' => $item->warehouseID?->nama_gudang ?? '-',
            ];
        })->filter()->values();
        //    dd($details);

        return response()->json([
            'success' => true,
            'data' => $formattedData,
            'header' => $header,
        ]);
    }

    public function getQuotation($customerId)
    {
        $status = ['processing', 'partial'];

        $data = SalesOrder::whereIn('status', $status)
            ->where('active', 1)
            ->where('customer_id', $customerId)
            ->select('id', 'sales_order_code')
            ->get();

        return response()->json($data);
    }

    public function getQuotationDetail(Request $request)
    {
        $year = date('Y');

        $ids = $request->order_ids;

        $details = DB::table("sales_order_detail_$year as d")
            ->join('warehouse as w', 'w.id', '=', 'd.warehouse_id')
            ->join('data_barang as b', 'b.id', '=', 'd.product_id')
            ->join('basic_code_detail as u', 'u.id', '=', 'd.unit_id')
            ->select(
                'd.id',
                'd.sales_order_id',
                'd.product_id',
                'b.nama_barang',
                'd.outstanding_qty',
                'd.warehouse_id',
                'w.nama_gudang as warehouse_name',
                'u.detail as unit_name',
                'd.unit_id'
            )
            ->whereIn('d.sales_order_id', $ids)
            ->where('d.active', 1)
            ->get();

        return response()->json($details);
    }

    public function getStock(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:data_barang,id',
            'warehouse_id' => 'required|integer|exists:warehouse,id',
        ]);

        // ambil cut off date dari company
        $company = Company::first(); // atau pakai company aktif kalau multi-company

        $cutoffDate = $company?->cut_off_date;

        $stock = $this->realStock(
            $request->product_id,
            $request->warehouse_id,
            $request->unit_id,
            $cutoffDate
        );

        $barang = Barang::with('unitID')
            ->find($request->product_id);
        $unit = BasicCodeDetail::where('master_id', 2)->find($request->unit_id);

        return response()->json([
            'success' => true,
            'stock' => $stock ?? 0,
            'unit' => $unit?->detail ?? '',
            'cutoff_date' => $cutoffDate,
        ]);

    }

    public function realStock($productId, $warehouseId, $unitId, $cutoffDate = null)
    {
        $unitId = (int) $unitId;
        $today = now()->format('Y-m-d');

        // Jika cutoffDate null, kita anggap mulai dari tanggal yang sangat lampau atau hari ini
        $startDate = $cutoffDate ?? $today;

        return DB::table('stock_mutations')
            ->where('data_barang_id', (int) $productId)
            ->where('warehouse_id', (int) $warehouseId)
            ->where('unit_id', $unitId)
            // Filter rentang: date_stock harus >= cutoffDate DAN <= hari ini
            ->whereBetween('date_stock', [$startDate, $today])
            ->selectRaw("
            SUM(CASE WHEN type = 'in' THEN qty_transaksi ELSE 0 END)
            -
            SUM(CASE WHEN type = 'out' THEN qty_transaksi ELSE 0 END)
            as stock
        ")
            ->value('stock') ?? 0;
    }

    public function print($id)
    {
        $deliveryOrder = DeliveryOrder::with([
            'customerID',
            'details.produkID',
            'details.unitID',
            'details.warehouseID',
        ])->findOrFail($id);
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
        $pdf = Pdf::loadView('pdf.deliveryOrder.delivery_order', [
            'model' => $deliveryOrder,
            'company' => $company,
            'logoBase64' => $logoBase64,
            'totalQty' => $this->hitungTotalQty($deliveryOrder),
            'totalBarang' => $this->hitungTotalBarang($deliveryOrder),
        ]);

        $filename = preg_replace('/[\/\\\\:*?"<>|]/', '-', $deliveryOrder->delivery_order_code);

        return $pdf->setPaper('a5', 'landscape')
            ->stream($filename.'.pdf');
    }

    private function hitungTotalQty($deliveryOrder)
    {
        return $deliveryOrder->details->sum('qty');
    }

    private function hitungTotalBarang($deliveryOrder)
    {
        return $deliveryOrder->details->count();

        // Jika ingin menghitung jenis barang unik:
        // return $deliveryOrder->details->unique('data_barang_id')->count();
    }

    public function getKontakByCustomer($customer_id)
    {
        $customer = Customer::find($customer_id);
        if (! $customer_id) {
            return response()->json([
                'success' => false,
                'message' => 'Customer tidak ditemukan.',
            ]);
        }

        $address = collect([
            $customer->alamat_tagihan,
            collect([
                $customer->kota_tagihan,
                $customer->provinsi_tagihan,
                $customer->kodepos_tagihan,
            ])->filter()->implode(', '),
            $customer->negara_tagihan,
        ])->filter()->implode("\n");

        $kontak = DB::table('customer_kontak')
            ->where('customer_id', $customer_id)
            ->get();

        $pajak = DB::table('customer_pajak')
            ->where('customer_id', $customer_id)
            ->first();

        return response()->json([
            'kontak' => $kontak,
            'pajak' => $pajak,
            'address' => $address,
        ]);
    }
}
