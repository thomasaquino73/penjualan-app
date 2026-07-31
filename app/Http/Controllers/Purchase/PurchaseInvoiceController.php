<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseInvoiceRequest;
use App\Models\BasicCodeDetail;
use App\Models\Inventory\Barang;
use App\Models\Inventory\Warehouse;
use App\Models\Purchase\PurchaseInvoice;
use App\Models\Purchase\PurchaseInvoiceDetail;
use App\Models\Purchase\PurchaseRequisition;
use App\Models\Purchase\ReceiveItem;
use App\Models\Purchase\ReceiveItemDetail;
use App\Models\Purchase\Supplier;
use App\Models\Setting\Company;
use App\Models\Setting\Shipping;
use App\Models\Setting\SyaratPembayaran;
use App\Models\Setting\Tax;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PurchaseInvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $routeName = $request->route()->getName();

            $permissionMap = [
                'purchase-invoice.index' => 'purchase_invoice-browse',
                'purchase-invoice.show' => 'purchase_invoice-read',
                'purchase-invoice.create' => 'purchase_invoice-create',
                'purchase-invoice.store' => 'purchase_invoice-create',
                'purchase-invoice.edit' => 'purchase_invoice-edit',
                'purchase-invoice.update' => 'purchase_invoice-edit',
                'purchase-invoice.destroy' => 'purchase_invoice-delete',
                'purchase-invoice.trash' => 'purchase_invoice-trash',
                'purchase-invoice.restore' => 'purchase_invoice-restore',
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
            $userId = Auth::user()->id;

            // Query dengan kondisi: Aktif DAN (Status BUKAN draft ATAU Status ADALAH draft kepunyaan sendiri)
            $query = PurchaseInvoice::where('active', '<>', 0)
                ->where(function ($q) use ($userId) {
                    $q->where('status', '<>', 'draft')
                        ->orWhere(function ($subQ) use ($userId) {
                            $subQ->where('status', 'draft')
                                ->where('created_by', $userId);
                        });
                })
                ->orderby('code', 'desc');

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

                    switch ($row->status) {

                        case 'draft':
                            $badge = 'bg-label-secondary';
                            $text = 'Draft';
                            break;

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
                ->addColumn('date', function ($row) {
                    return Carbon::parse($row->datePO)->format('d-m-Y');
                })
                ->addColumn('tanggal_kirim', function ($row) {
                    return Carbon::parse($row->tanggal_kirim)->format('d-m-Y');
                })
                ->addColumn('amount', function ($row) {
                    // 1. Hitung total kotor (sum amount) dari detail item PO
                    $subTotal = PurchaseInvoiceDetail::where('purchase_invoice_id', $row->id)
                        ->where('active', 1)
                        ->sum('amount');

                    // 2. Hitung grand total: Subtotal dikurangi diskon nominal yang ada di tabel induk ($row)
                    // Gunakan ?? 0 jika kolom disc_nominal di database bisa bernilai null
                    $grandTotal = $subTotal - ($row->disc_nominal ?? 0);

                    // 3. Kembalikan nilai yang sudah dikonversi dan diformat
                    $selectedCurrencyId = session('currency_id', 1); // Ambil dari session, default 1 (IDR)

                    return format_uang(convert_currency($grandTotal, $selectedCurrencyId));
                })
                ->addColumn('supplier', function ($row) {
                    return $row->supplier->nama_supplier;
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
                                <a class="dropdown-item btn-processing"
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
                        $user->can('purchase_invoice-edit') &&
                        in_array($row->status, ['draft', 'pending', 'processing'])
                    ) {

                        $btn .= '
                                <a class="dropdown-item"
                                    href="'.route('purchase-invoice.edit', $row->id).'">

                                    <i class="far fa-edit me-1"></i>
                                    Edit PO
                                </a>
                            ';
                    }

                    // DELETE
                    if (
                        $user->can('purchase_invoice-delete') &&
                        $row->status == 'draft'
                    ) {

                        $btn .= '
                                <a class="dropdown-item text-danger"
                                    href="javascript:void(0)"
                                    id="delete"
                                    data-id="'.$row->id.'"
                                    data-name="'.$row->code.'">

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
                    //     $user->can('purchase_invoice-approval')
                    // ) {

                    //     if ($row->status == 'pending') {

                    //         $btn .= '
                    //                 <a class="dropdown-item text-success btn-approval-po"
                    //                     href="javascript:void(0)"
                    //                     data-status="approved"
                    //                     data-id="'.$row->id.'">

                    //                     <i class="ti ti-check me-1"></i>
                    //                     Approve PO
                    //                 </a>
                    //             ';

                    //         $btn .= '
                    //                 <a class="dropdown-item text-danger btn-approval-po"
                    //                     href="javascript:void(0)"
                    //                     data-status="rejected"
                    //                     data-id="'.$row->id.'">

                    //                     <i class="ti ti-x me-1"></i>
                    //                     Reject PO
                    //                 </a>
                    //             ';
                    //     }
                    // }

                    /*
                    |--------------------------------------------------------------------------
                    | 3. SEND TO SUPPLIER
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $row->status == 'approved'
                        // $row->status == 'approved' &&
                        // $user->can('purchase_invoice-send-supplier')
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
                        $user->can('purchase_invoice-receive')
                    ) {

                        $btn .= '
            <a class="dropdown-item text-primary"
                href="'.route('purchase-invoice.receive', $row->id).'">

                <i class="ti ti-package-import me-1"></i>
                Receive Item
            </a>
                ';
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | 5. CANCEL PO
                    |--------------------------------------------------------------------------
                    */

                    if (
                        ! in_array($row->status, ['completed', 'cancelled']) &&
                        $user->can('purchase_invoice-cancel')
                    ) {

                        $btn .= '
            <a class="dropdown-item text-danger btn-cancel-po"
                href="javascript:void(0)"
                data-id="'.$row->id.'">

                <i class="ti ti-circle-x me-1"></i>
                Cancel PO
            </a>
                 ';
                    }
                    if ($row->status == 'completed') {

                    } else {
                        $btn .= '<a class="dropdown-item"
                href="javascript:void(0)" id="close"   data-id="'.$row->id.'" data-name="'.$row->code.'">
                <i class="ti ti-lock"></i> Close PO
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
                    href="'.route('purchase-invoice.print', $row->id).'">

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
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'supplier', 'date', 'amount'])
                ->make(true);
        }

        $x = [
            'title' => 'Purchase Invoice List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Purchase Invoice', 'url' => ''],
            ],
        ];

        return view('purchase.purchase_invoice.purchase_invoice_index', $x);
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

        $prefix = "PI/{$tahun}/{$bulanRomawi}/";

        $last = PurchaseInvoice::where('code', 'like', $prefix.'%')
            ->orderByRaw("
            CAST(
                REGEXP_REPLACE(
                    SUBSTRING_INDEX(code,'/',-1),
                    '[^0-9]',
                    ''
                ) AS UNSIGNED
            ) DESC
        ")
            ->first();

        if ($last) {
            preg_match('/(\d+)/', substr($last->code, strrpos($last->code, '/') + 1), $match);
            $lastNumber = isset($match[1]) ? (int) $match[1] : 0;
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function create()
    {
        // 🔥 Ambil semua pajak aktif (khusus pembelian & general)
        $taxes = Tax::where('is_active', true)
            ->whereIn('usage', ['purchase', 'both'])
            ->get();

        // 🔥 Ambil default tax (misalnya PPN)
        $defaultTax = Tax::where('is_active', true)
            ->where('is_default', true)
            ->whereIn('usage', ['purchase', 'both'])
            ->first();
        $company = Company::with('defaultCurrency')->first();
        $status = ['processing', 'partial'];
        $x = [
            'title' => 'Purchase Invoice New',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Purchase Invoice', 'url' => ''],
            ],
            'supplier' => Supplier::where('status', 1)->get(),
            // 'company' => Company::first(),
            'idNumber' => $this->generateNumberId(),
            'shipping' => Shipping::where('status', 1)->get(),
            'warehouse' => Warehouse::where('status', 1)->get(),
            'paymentTerm' => SyaratPembayaran::where('status', 1)->get(),
            'product' => Barang::where('status', '<>', 0)->get(),
            'fob' => BasicCodeDetail::where('master_id', 7)->get(),
            'taxes' => $taxes,
            'defaultTax' => $defaultTax,
            'company' => $company->defaultCurrency,
            'number' => PurchaseRequisition::whereIn('status', $status)
                ->where('active', 1)
                ->get(),

        ];

        return view('purchase.purchase_invoice.purchase_invoice_create', $x);
    }

    public function store(PurchaseInvoiceRequest $request)
    {
        DB::beginTransaction();

        try {
            $currentYear = date('Y');
            $data = $request->validated();
            $itemsDetailRaw = $request->input('items_detail');
            unset($data['items_detail']);

            // ================== HEADER ==================
            $data['created_by'] = Auth::id();
            $data['updated_by'] = null;
            $data['vehicle_id'] = $request->vehicle_id;
            $data['no_faktur'] = $request->no_faktur;
            $data['sub_total'] = $request->sub_total;
            $data['disc_percent'] = $request->percent;
            $data['disc_nominal'] = $request->discount_all;
            $data['grand_total'] = $request->total_order;
            $data['payment_term'] = $request->payment_term;
            $data['kena_pajak'] = $request->has('kena_pajak') ? 1 : 0;
            $data['total_termasuk_pajak'] = $request->has('total_termasuk_pajak') ? 1 : 0;
            $data['shipping_address'] = $request->shipping_address;
            $data['description'] = $request->description;
            $data['taxpayer_data'] = $request->taxpayer_data;
            $data['tax_id'] = $request->tax_id;
            $data['tax_amount'] = $request->tax_amount;

            $data['datePO'] = Carbon::parse($request->datePO)->format('Y-m-d');
            $data['tanggal_kirim'] = $request->tanggal_kirim
                ? Carbon::parse($request->tanggal_kirim)->format('Y-m-d')
                : null;

            // ================== GENERATE CODE ==================
            $purchaseInvoice = null;
            $maxRetry = 10;
            $currentCode = $request->code;

            for ($attempt = 1; $attempt <= $maxRetry; $attempt++) {
                try {
                    $data['code'] = $currentCode;
                    $purchaseInvoice = PurchaseInvoice::create($data);
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

            if (! $purchaseInvoice) {
                throw new \Exception('Gagal membuat Purchase Invoice: Nomor sudah penuh atau sistem sibuk.');
            }

            // ================== DETAIL ==================
            if ($itemsDetailRaw) {
                $items = json_decode($itemsDetailRaw, true);
                $involvedPrIds = [];

                if (is_array($items) && count($items) > 0) {

                    foreach ($items as $index => $item) {

                        $prDetailId = $item['receive_item_detail_id']
                            ?? $item['pr_detail_id']
                            ?? $item['detail_id']
                            ?? null;

                        $qtyInputForm = floatval($item['quantity'] ?? $item['qty'] ?? 0);
                        $unitPrice = floatval($item['unit_price'] ?? 0);
                        $discount = floatval($item['discount'] ?? 0);

                        $amount = ($qtyInputForm * $unitPrice) - $discount;

                        // ✅ SIMPAN URUTAN DARI DRAG TABLE
                        PurchaseInvoiceDetail::create([
                            'purchase_invoice_id' => $purchaseInvoice->id,
                            'receive_item_detail_id' => $prDetailId,
                            'product_id' => $item['product_id'],
                            'qty' => $qtyInputForm,
                            'outstanding_qty' => $qtyInputForm,
                            'unit_id' => $item['unit_id'],
                            'unit_price' => $unitPrice,
                            'warehouse_id' => $item['warehouse_id'],
                            'discount' => $discount,
                            'discount_percent' => $item['discount_percent'] ?? 0,
                            'amount' => $item['amount'] ?? $amount,
                            'urutan' => $index, // 🔥 INI KUNCI NYA
                            'active' => 1,
                            'created_by' => Auth::id(),
                        ]);

                        // ================== SYNC PR ==================
                        if ($prDetailId) {
                            $tableName = "receive_item_detail_{$currentYear}";
                            $prDetail = DB::table($tableName)->where('id', $prDetailId)->first();

                            if ($prDetail) {

                                $totalPoForThisItem = PurchaseInvoiceDetail::where('receive_item_detail_id', $prDetailId)
                                    ->where('active', 1)
                                    ->sum('qty');

                                $outstandingQty = max(0, $prDetail->qty - $totalPoForThisItem);

                                DB::table($tableName)
                                    ->where('id', $prDetailId)
                                    ->update([
                                        'ri_qty' => $totalPoForThisItem,
                                        'outstanding_qty' => $outstandingQty,
                                        'updated_at' => now(),
                                    ]);

                                if (! in_array($prDetail->receive_item_id, $involvedPrIds)) {
                                    $involvedPrIds[] = $prDetail->receive_item_id;
                                }
                            }
                        }
                    }

                    // ================== UPDATE STATUS PR ==================
                    foreach ($involvedPrIds as $prId) {

                        $allDetails = DB::table("receive_item_detail_{$currentYear}")
                            ->where('receive_item_id', $prId)
                            ->get();

                        $totalRequested = $allDetails->sum('qty');
                        $totalOrdered = $allDetails->sum('ri_qty');

                        if ($totalOrdered >= $totalRequested) {
                            $newStatus = 'closed';
                        } elseif ($totalOrdered > 0) {
                            $newStatus = 'partial';
                        } else {
                            $newStatus = 'processing';
                        }

                        DB::table("receive_item_{$currentYear}")
                            ->where('id', $prId)
                            ->update([
                                'status' => $newStatus,
                                'updated_at' => now(),
                            ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase Invoice saved successfully!',
                'redirect' => $request->save_and_new == 1
                    ? route('purchase-invoice.create')
                    : route('purchase-invoice.index'),
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

    public function edit(string $id)
    {
        $year = date('Y');

        $purchaseInvoice = PurchaseInvoice::with([
            'details.produkID',
            'details.unitID',
            'details.warehouseID',
            'details.receiveItemDetail.receiveItem',
        ])->findOrFail($id);

        // Invoice berasal dari Receive Item?
        $isFromReceiveItem = $purchaseInvoice->details
            ->whereNotNull('receive_item_detail_id')
            ->count() > 0;

        $detailDataMapped = $purchaseInvoice->details->map(function ($detail) use ($purchaseInvoice, $year) {

            $receiveItemCode = null;
            $receivedQty = null;
            $outstandingQty = null;
            $totalInvoiceLainnya = 0;

            if ($detail->receive_item_detail_id) {

                $riDetail = $detail->receiveItemDetail;

                if ($riDetail) {

                    $receivedQty = (float) $riDetail->qty;
                    $outstandingQty = (float) $riDetail->outstanding_qty;

                    // Qty invoice lain (selain invoice yang sedang diedit)
                    $totalInvoiceLainnya = DB::table("purchase_invoice_detail_{$year}")
                        ->where('receive_item_detail_id', $detail->receive_item_detail_id)
                        ->where('purchase_invoice_id', '<>', $purchaseInvoice->id)
                        ->where('active', 1)
                        ->sum('qty');

                    if ($riDetail->receiveItem) {
                        $receiveItemCode = $riDetail->receiveItem->receive_item_code;
                    }
                }
            }

            return [

                'id' => $detail->id,

                'purchase_invoice_id' => $detail->purchase_invoice_id,

                'receive_item_detail_id' => $detail->receive_item_detail_id,

                'receive_item_code' => $receiveItemCode,

                'product_id' => $detail->product_id,

                'data_produk' => optional($detail->produkID)->nama_barang,

                'quantity' => (float) $detail->qty,

                'unit_id' => $detail->unit_id,

                'unit' => optional($detail->unitID)->detail,

                'warehouse_id' => $detail->warehouse_id,

                'warehouse' => optional($detail->warehouseID)->nama_gudang,

                'unit_price' => (float) $detail->unit_price,

                'discount' => (float) $detail->discount,

                'discount_percent' => $detail->discount_percent,

                'amount' => (float) $detail->amount,

                'received_qty' => $receivedQty,

                'outstanding_qty' => $outstandingQty,

                'total_invoice_lainnya' => (float) $totalInvoiceLainnya,

            ];
        });

        $taxes = Tax::where('is_active', true)
            ->whereIn('usage', ['purchase', 'both'])
            ->get();

        $defaultTax = Tax::where('is_active', true)
            ->where('is_default', true)
            ->whereIn('usage', ['purchase', 'both'])
            ->first();

        $status = [
            'processing',
            'partial',
        ];

        return view('purchase.purchase_invoice.purchase_invoice_edit', [

            'title' => 'Edit Purchase Invoice',

            'breadcrumb' => [
                [
                    'label' => 'Purchase Invoice',
                    'url' => route('purchase-invoice.index'),
                ],
                [
                    'label' => 'Edit Purchase Invoice',
                    'url' => '',
                ],
            ],

            'supplier' => Supplier::where('status', 1)->get(),

            'company' => Company::first(),

            'idNumber' => $purchaseInvoice->code,

            'shipping' => Shipping::where('status', 1)->get(),

            'warehouse' => Warehouse::where('status', 1)->get(),

            'paymentTerm' => SyaratPembayaran::where('status', 1)->get(),

            'product' => Barang::where('status', '<>', 0)->get(),

            'fob' => BasicCodeDetail::where('master_id', 7)->get(),

            'model' => $purchaseInvoice,

            'isFromReceiveItem' => $isFromReceiveItem,

            'jsonDetails' => $detailDataMapped,

            'taxes' => $taxes,

            'defaultTax' => $defaultTax,

            'number' => ReceiveItem::whereIn('status', $status)
                ->where('active', 1)
                ->get(),

        ]);
    }

   public function update(PurchaseInvoiceRequest $request, string $id)
    {
        DB::beginTransaction();

        try {
            $currentYear = date('Y');
            $purchaseInvoice = PurchaseInvoice::findOrFail($id);

            $data = $request->validated();
            $itemsDetailRaw = $request->input('items_detail');
            unset($data['items_detail']);

            // ================== HEADER ==================
            $data['updated_by'] = Auth::id();
            $data['vehicle_id'] = $request->vehicle_id;
            $data['no_faktur'] = $request->no_faktur;
            $data['sub_total'] = $request->sub_total;
            $data['disc_percent'] = $request->percent;
            $data['disc_nominal'] = $request->discount_all;
            $data['grand_total'] = $request->total_order;
            $data['payment_term'] = $request->payment_term;
            $data['kena_pajak'] = $request->has('kena_pajak') ? 1 : 0;
            $data['total_termasuk_pajak'] = $request->has('total_termasuk_pajak') ? 1 : 0;
            $data['shipping_address'] = $request->shipping_address;
            $data['description'] = $request->description;
            $data['taxpayer_data'] = $request->taxpayer_data;
            $data['tax_id'] = $request->tax_id;
            $data['tax_amount'] = $request->tax_amount;

            $data['datePO'] = Carbon::parse($request->datePO)->format('Y-m-d');
            $data['tanggal_kirim'] = $request->tanggal_kirim
                ? Carbon::parse($request->tanggal_kirim)->format('Y-m-d')
                : null;

            // Update Header Purchase Invoice (Kode biasanya tidak diubah saat edit, gunakan kode lama)
            $purchaseInvoice->update($data);

            // ================== KUMPULKAN PR ID LAMA (UNTUK RESET SYNC) ==================
            $oldDetails = PurchaseInvoiceDetail::where('purchase_invoice_id', $purchaseInvoice->id)->get();
            $affectedPrDetailIds = $oldDetails->pluck('receive_item_detail_id')->filter()->unique()->toArray();

            // Hapus detail lama
            PurchaseInvoiceDetail::where('purchase_invoice_id', $purchaseInvoice->id)->delete();

            // ================== DETAIL BARU ==================
            if ($itemsDetailRaw) {
                $items = json_decode($itemsDetailRaw, true);
                $involvedPrIds = [];

                if (is_array($items) && count($items) > 0) {
                    foreach ($items as $index => $item) {

                        $prDetailId = $item['receive_item_detail_id']
                            ?? $item['pr_detail_id']
                            ?? $item['detail_id']
                            ?? null;

                        $qtyInputForm = floatval($item['quantity'] ?? $item['qty'] ?? 0);
                        $unitPrice = floatval($item['unit_price'] ?? 0);
                        $discount = floatval($item['discount'] ?? 0);

                        $amount = ($qtyInputForm * $unitPrice) - $discount;

                        // ✅ SIMPAN URUTAN BARU DARI DRAG TABLE
                        PurchaseInvoiceDetail::create([
                            'purchase_invoice_id' => $purchaseInvoice->id,
                            'receive_item_detail_id' => $prDetailId,
                            'product_id' => $item['product_id'],
                            'qty' => $qtyInputForm,
                            'outstanding_qty' => $qtyInputForm,
                            'unit_id' => $item['unit_id'],
                            'unit_price' => $unitPrice,
                            'warehouse_id' => $item['warehouse_id'],
                            'discount' => $discount,
                            'discount_percent' => $item['discount_percent'] ?? 0,
                            'amount' => $item['amount'] ?? $amount,
                            'urutan' => $index, // 🔥 URUTAN ITEM TERBARU
                            'active' => 1,
                            'created_by' => Auth::id(),
                        ]);

                        if ($prDetailId && !in_array($prDetailId, $affectedPrDetailIds)) {
                            $affectedPrDetailIds[] = $prDetailId;
                        }
                    }

                    // ================== SYNC ULANG SEMUA PR TERDAMPAK ==================
                    foreach ($affectedPrDetailIds as $prDetailId) {
                        $tableName = "receive_item_detail_{$currentYear}";
                        $prDetail = DB::table($tableName)->where('id', $prDetailId)->first();

                        if ($prDetail) {
                            $totalPoForThisItem = PurchaseInvoiceDetail::where('receive_item_detail_id', $prDetailId)
                                ->where('active', 1)
                                ->sum('qty');

                            $outstandingQty = max(0, $prDetail->qty - $totalPoForThisItem);

                            DB::table($tableName)
                                ->where('id', $prDetailId)
                                ->update([
                                    'ri_qty' => $totalPoForThisItem,
                                    'outstanding_qty' => $outstandingQty,
                                    'updated_at' => now(),
                                ]);

                            if (!in_array($prDetail->receive_item_id, $involvedPrIds)) {
                                $involvedPrIds[] = $prDetail->receive_item_id;
                            }
                        }
                    }

                    // ================== UPDATE STATUS PR / RECEIVE ITEM ==================
                    foreach ($involvedPrIds as $prId) {
                        $allDetails = DB::table("receive_item_detail_{$currentYear}")
                            ->where('receive_item_id', $prId)
                            ->get();

                        $totalRequested = $allDetails->sum('qty');
                        $totalOrdered = $allDetails->sum('ri_qty');

                        if ($totalOrdered >= $totalRequested) {
                            $newStatus = 'closed';
                        } elseif ($totalOrdered > 0) {
                            $newStatus = 'partial';
                        } else {
                            $newStatus = 'processing';
                        }

                        DB::table("receive_item_{$currentYear}")
                            ->where('id', $prId)
                            ->update([
                                'status' => $newStatus,
                                'updated_at' => now(),
                            ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase Invoice updated successfully!',
                'redirect' => route('purchase-invoice.index'),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui data: '.$e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            // ==========================================================
            // Ambil Purchase Invoice
            // ==========================================================
            $purchaseInvoice = PurchaseInvoice::findOrFail($id);

            // ==========================================================
            // Ambil seluruh detail Purchase Invoice
            // ==========================================================
            $invoiceDetails = PurchaseInvoiceDetail::where(
                'purchase_invoice_id',
                $purchaseInvoice->id
            )->get();

            // Menyimpan Receive Item yang terdampak
            $receiveItemIds = [];

            foreach ($invoiceDetails as $detail) {

                if (empty($detail->receive_item_detail_id)) {
                    continue;
                }

                $receiveItemDetail = ReceiveItemDetail::find(
                    $detail->receive_item_detail_id
                );

                if (! $receiveItemDetail) {
                    continue;
                }

                if (! in_array($receiveItemDetail->receive_item_id, $receiveItemIds)) {
                    $receiveItemIds[] = $receiveItemDetail->receive_item_id;
                }
            }

            // ==========================================================
            // Soft Delete Purchase Invoice
            // ==========================================================
            $purchaseInvoice->update([
                'active' => 0,
                'updated_by' => Auth::id(),
            ]);

            PurchaseInvoiceDetail::where(
                'purchase_invoice_id',
                $purchaseInvoice->id
            )->update([
                'active' => 0,
                'updated_by' => Auth::id(),
            ]);

            // ==========================================================
            // Hitung ulang Receive Item Detail
            // ==========================================================
            foreach ($invoiceDetails as $detail) {

                if (empty($detail->receive_item_detail_id)) {
                    continue;
                }

                $receiveItemDetail = ReceiveItemDetail::find(
                    $detail->receive_item_detail_id
                );

                if (! $receiveItemDetail) {
                    continue;
                }

                // Total qty Purchase Invoice yang masih aktif
                $totalInvoice = PurchaseInvoiceDetail::where(
                    'receive_item_detail_id',
                    $detail->receive_item_detail_id
                )
                    ->where('active', 1)
                    ->sum('qty');

                // Outstanding
                $outstanding = $receiveItemDetail->qty - $totalInvoice;

                if ($outstanding < 0) {
                    $outstanding = 0;
                }

                $receiveItemDetail->update([
                    'ri_qty' => $totalInvoice,
                    'outstanding_qty' => $outstanding,
                ]);
            }

            // ==========================================================
            // Update Status Receive Item
            // ==========================================================
            foreach ($receiveItemIds as $receiveItemId) {

                $details = ReceiveItemDetail::where(
                    'receive_item_id',
                    $receiveItemId
                )
                    ->where('active', 1)
                    ->get();

                $totalQty = $details->sum('qty');
                $totalRiQty = $details->sum('ri_qty');

                if ($totalRiQty <= 0) {

                    $status = 'processing';

                } elseif ($totalRiQty < $totalQty) {

                    $status = 'partial';

                } else {

                    $status = 'closed';

                }

                ReceiveItem::where('id', $receiveItemId)
                    ->update([
                        'status' => $status,
                    ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Purchase Invoice berhasil dibatalkan.',
            ], 200);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membatalkan Purchase Invoice : '.$e->getMessage(),
            ], 500);
        }
    }

    public function trash(Request $r)
    {
        if ($r->ajax()) {
            $userId = Auth::user()->id;

            // Query dengan kondisi: Aktif DAN (Status BUKAN draft ATAU Status ADALAH draft kepunyaan sendiri)
            $query = PurchaseInvoice::where('active', 0)
                ->where(function ($q) use ($userId) {
                    $q->where('status', '<>', 'draft')
                        ->orWhere(function ($subQ) use ($userId) {
                            $subQ->where('status', 'draft')
                                ->where('created_by', $userId);
                        });
                })
                ->orderby('code', 'desc');

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

                    switch ($row->status) {

                        case 'draft':
                            $badge = 'bg-label-secondary';
                            $text = 'Draft';
                            break;

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
                ->addColumn('date', function ($row) {
                    return Carbon::parse($row->datePO)->format('d-m-Y');
                })
                ->addColumn('tanggal_kirim', function ($row) {
                    return Carbon::parse($row->tanggal_kirim)->format('d-m-Y');
                })
                ->addColumn('amount', function ($row) {
                    // 1. Hitung total kotor (sum amount) dari detail item PO
                    $subTotal = PurchaseInvoiceDetail::where('purchase_invoice_id', $row->id)
                        ->where('active', 1)
                        ->sum('amount');

                    // 2. Hitung grand total: Subtotal dikurangi diskon nominal yang ada di tabel induk ($row)
                    // Gunakan ?? 0 jika kolom disc_nominal di database bisa bernilai null
                    $grandTotal = $subTotal - ($row->disc_nominal ?? 0);

                    // 3. Kembalikan nilai yang sudah dikonversi dan diformat
                    $selectedCurrencyId = session('currency_id', 1); // Ambil dari session, default 1 (IDR)

                    return format_uang(convert_currency($grandTotal, $selectedCurrencyId));
                })
                ->addColumn('supplier', function ($row) {
                    return $row->supplier->nama_supplier;
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">
                      <button type="button" class="btn btn-primary dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-menu-2 ti-xs me-1"></i>
                      </button>
                      <ul class="dropdown-menu" style="">';

                    if (auth()->user()->can('purchase_invoice-restore')) {
                        $btn .= '<a class="dropdown-item restore" href="javascript:void(0)"
                            data-id="'.$row->id.'"> <i class="ti ti-trash-off me-1"></i> Restore</a>';
                    }

                    return $btn;
                })
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'supplier', 'date', 'amount'])
                ->make(true);
        }

        $x = [
            'title' => 'Purchase Invoice List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Purchase Invoice', 'url' => ''],
            ],
        ];

        return view('purchase.purchase_invoice.purchase_invoice_trash', $x);
    }

    public function restore($id)
{
    DB::beginTransaction();

    try {

        // ==========================================================
        // Aktifkan kembali Purchase Invoice
        // ==========================================================
        $purchaseInvoice = PurchaseInvoice::findOrFail($id);

        $purchaseInvoice->update([
            'active'     => 1,
            'updated_by' => Auth::id(),
        ]);

        PurchaseInvoiceDetail::where(
            'purchase_invoice_id',
            $purchaseInvoice->id
        )->update([
            'active'     => 1,
            'updated_by' => Auth::id(),
        ]);

        // ==========================================================
        // Ambil seluruh detail Purchase Invoice
        // ==========================================================
        $invoiceDetails = PurchaseInvoiceDetail::where(
            'purchase_invoice_id',
            $purchaseInvoice->id
        )->get();

        $receiveItemIds = [];

        foreach ($invoiceDetails as $detail) {

            if (empty($detail->receive_item_detail_id)) {
                continue;
            }

            $receiveItemDetail = ReceiveItemDetail::find(
                $detail->receive_item_detail_id
            );

            if (!$receiveItemDetail) {
                continue;
            }

            if (!in_array($receiveItemDetail->receive_item_id, $receiveItemIds)) {
                $receiveItemIds[] = $receiveItemDetail->receive_item_id;
            }

            // ======================================================
            // Hitung ulang seluruh Purchase Invoice aktif
            // ======================================================
            $totalInvoice = PurchaseInvoiceDetail::where(
                    'receive_item_detail_id',
                    $detail->receive_item_detail_id
                )
                ->where('active', 1)
                ->sum('qty');

            $outstanding = $receiveItemDetail->qty - $totalInvoice;

            if ($outstanding < 0) {
                $outstanding = 0;
            }

            $receiveItemDetail->update([
                'ri_qty'          => $totalInvoice,
                'outstanding_qty' => $outstanding,
            ]);
        }

        // ==========================================================
        // Update Status Receive Item
        // ==========================================================
        foreach ($receiveItemIds as $receiveItemId) {

            $details = ReceiveItemDetail::where(
                    'receive_item_id',
                    $receiveItemId
                )
                ->where('active', 1)
                ->get();

            $totalQty   = $details->sum('qty');
            $totalRiQty = $details->sum('ri_qty');

            if ($totalRiQty <= 0) {

                $status = 'processing';

            } elseif ($totalRiQty < $totalQty) {

                $status = 'partial';

            } else {

                $status = 'closed';

            }

            ReceiveItem::where('id', $receiveItemId)
                ->update([
                    'status' => $status,
                ]);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Purchase Invoice berhasil direstore.',
        ], 200);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => 'Gagal restore Purchase Invoice : '.$e->getMessage(),
        ], 500);
    }
}

    public function getProcessingData()
    {
        // Mengambil data PR beserta item detailnya yang belum lunas di-PO
        $orders = ReceiveItem::with(['details' => function ($query) {
            $query->whereRaw('ri_qty < qty'); // Hanya ambil item yang belum terpenuhi
        }])
            ->whereNotIn('status', ['draft', 'closed', 'done'])
            ->get();

        return response()->json($orders);
    }

    public function getReceiveDetail(Request $request)
    {
        $ids = $request->quotation_ids;

        if (empty($ids)) {
            return response()->json(['success' => false, 'data' => []]);
        }

        // Load relasi salesOrderDetail
        $details = ReceiveItemDetail::with([
            'produkID',
            'unitID',
            'receiveItem',
            'purchaseOrderDetail',
        ])
            ->whereIn('receive_item_id', $ids)
            ->get();
        $formattedData = $details->map(function ($item) {
            $sisaQty = ($item->outstanding_qty !== null && $item->outstanding_qty > 0)
                        ? (float) $item->outstanding_qty
                        : (float) $item->qty;

            $price = (float) ($item->purchaseOrderDetail->unit_price ?? 0);
            $discount = (float) ($item->purchaseOrderDetail->discount ?? 0);

            return [
                'id' => $item->id,
                'receive_item_id' => $item->receive_item_id,

                'purchase_invoice_id' => $item->purchaseOrderDetail->purchase_invoice_id ?? null,

                'product_id' => $item->id,
                'product_name' => $item->produkID->nama_barang ?? '-',

                'qty' => $sisaQty,

                'unit_id' => $item->unit_id,
                'unit_name' => $item->unitID->detail ?? '-',

                'warehouse_id' => $item->warehouse_id,
                'warehouse_name' => $item->warehouseID->nama_gudang ?? '-',

                'unit_price' => $price,
                'discount' => $discount,
                'amount' => (($price * $sisaQty) - $discount),

                'order_code' => $item->receiveItem->receive_item_code ?? '-',
            ];
        });

        return response()->json(['success' => true, 'data' => $formattedData]);
    }

    public function processData($id)
    {
        // 1. Ambil tahun berjalan secara dinamis
        $year = date('Y');
        $tableName = "purchase_invoice_{$year}";

        // 2. Gunakan Query Builder dengan nama tabel dinamis agar pencarian ID aman
        $poData = DB::table($tableName)->where('id', $id)->first();

        // Jika data memang benar-benar tidak ditemukan di database
        if (! $poData) {
            return response()->json(['success' => false, 'message' => 'Data Purchase Invoice tidak ditemukan.'], 404);
        }

        // 3. Validasi Keamanan: Pastikan hanya pembuat draft yang bisa mengajukannya
        if ($poData->status !== 'draft' || $poData->created_by != Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk mengajukan data ini.',
            ], 403);
        }

        // 4. Lakukan pembaruan status menggunakan Query Builder demi stabilitas tabel dinamis
        DB::table($tableName)->where('id', $id)->update([
            'status' => 'processing',
            'updated_by' => Auth::user()->id,
            'updated_at' => now(), // Mengisi timestamp bawaan laravel secara manual karena menggunakan Query Builder
        ]);

        return response()->json(['success' => true, 'message' => 'Purchase Invoice berhasil diajukan!']);
    }
}
