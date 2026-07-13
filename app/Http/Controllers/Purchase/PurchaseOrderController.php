<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseOrderRequest;
use App\Models\BasicCodeDetail;
use App\Models\Inventory\Barang;
use App\Models\Inventory\Warehouse;
use App\Models\Purchase\PurchaseOrder;
use App\Models\Purchase\PurchaseOrderDetail;
use App\Models\Purchase\PurchaseRequisition;
use App\Models\Purchase\PurchaseRequisitionDetail;
use App\Models\Purchase\Supplier;
use App\Models\Setting\Company;
use App\Models\Setting\CompanyDeliveryAddress;
use App\Models\Setting\Shipping;
use App\Models\Setting\SyaratPembayaran;
use App\Models\Setting\Tax;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Dotenv\Exception\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class PurchaseOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $routeName = $request->route()->getName();

            $permissionMap = [
                'purchase-order.index' => 'purchase_order-browse',
                'purchase-order.show' => 'purchase_order-read',
                'purchase-order.create' => 'purchase_order-create',
                'purchase-order.store' => 'purchase_order-create',
                'purchase-order.edit' => 'purchase_order-edit',
                'purchase-order.update' => 'purchase_order-edit',
                'purchase-order.destroy' => 'purchase_order-delete',
                'purchase-order.trash' => 'purchase_order-trash',
                'purchase-order.restore' => 'purchase_order-restore',
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
        $userId = Auth::user()->id;

        // Query dengan kondisi: Aktif DAN (Status BUKAN draft ATAU Status ADALAH draft kepunyaan sendiri)
        $query = PurchaseOrder::where('active', '<>', 0)
            ->where(function ($q) use ($userId) {
                $q->where('status', '<>', 'draft')
                    ->orWhere(function ($subQ) use ($userId) {
                        $subQ->where('status', 'draft')
                            ->where('created_by', $userId);
                    });
            })
            ->orderby('code', 'desc');
        if ($r->ajax()) {
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

                        case 'partial':
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
                ->addColumn('date', function ($row) {
                    return Carbon::parse($row->datePO)->format('d-m-Y');
                })
                ->addColumn('tanggal_kirim', function ($row) {
                    return Carbon::parse($row->tanggal_kirim)->format('d-m-Y');
                })
                ->addColumn('amount', function ($row) {
                    $selectedCurrencyId = session('currency_id', 1); // Ambil dari session, default 1 (IDR)

                    return format_uang(convert_currency($row->grand_total, $selectedCurrencyId));
                })
                ->addColumn('supplier', function ($row) {
                    return $row->supplier->nama_supplier;
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
                                <a class="dropdown-item btn-process"
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
                        $user->can('purchase_order-edit') &&
                        in_array($row->status, ['draft', 'pending', 'processing'])
                    ) {

                        $btn .= '
                                <a class="dropdown-item"
                                    href="'.route('purchase-order.edit', $row->id).'">

                                    <i class="far fa-edit me-1"></i>
                                    Edit PO
                                </a>
                            ';
                    }

                    // DELETE
                    if (
                        $user->can('purchase_order-delete') &&
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
                    //     $user->can('purchase_order-approval')
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
                        // $user->can('purchase_order-send-supplier')
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
                        $user->can('purchase_order-receive')
                    ) {

                        $btn .= '
            <a class="dropdown-item text-primary"
                href="'.route('purchase-order.receive', $row->id).'">

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
                        $user->can('purchase_order-cancel')
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
                    href="'.route('purchase-order.print', $row->id).'">

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
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'cekbok', 'supplier', 'date', 'amount'])
                ->make(true);
        }
        $stats = $this->getStatistics($query);
        $x = [
            'title' => 'Purchase Order List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Purchase Order', 'url' => ''],
            ],
            'totalPurchase' => $stats['totalPurchase'],
            'partiallyReceived' => $stats['partiallyReceived'],
            'grandTotal' => $stats['grandTotal'],
            'completedReceived' => $stats['completedReceived'],
        ];

        return view('purchase.purchase_order.purchase_order_index', $x);
    }

    private function getStatistics($query)
    {
        $month = now()->month;
        $year = now()->year;

        return [
            'totalPurchase' => PurchaseOrder::where('active', '<>', 0)
                ->whereMonth('datePO', $month)
                ->count(),

            'partiallyReceived' => PurchaseOrder::where('status', 'partially_received')
                ->whereMonth('datePO', $month)
                ->count(),

            'grandTotal' => PurchaseOrder::where('active', '<>', 0)
                ->whereMonth('datePO', $month)
                ->whereYear('datePO', $year)
                ->whereNotIn('status', ['rejected', 'draft', 'pending'])
                ->sum('grand_total'),

            'completedReceived' => PurchaseOrder::where('status', 'completed')
                ->whereMonth('datePO', $month)
                ->count(),
        ];
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
        $prefix = "PO/{$tahun}/{$bulanRomawi}/";

        // Ambil nomor terakhir pada bulan & tahun yang sama
        $last = PurchaseOrder::where('code', 'like', $prefix.'%')
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

    public function table_pr(Request $r)
    {
        if ($r->ajax()) {
            $query = PurchaseOrderDetail::with('produkID')
                ->where('active', '<>', 0)
                ->get();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('data_produk', function ($row) {
                    return $row->produkID->nama_barang;
                })

                ->rawColumns(['data_produk'])
                ->make(true);
        }
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
            'title' => 'Purchase Order New',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Purchase Order', 'url' => ''],
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

        return view('purchase.purchase_order.purchase_order_create', $x);
    }

    public function getProcessingData()
    {
        // Mengambil data PR beserta item detailnya yang belum lunas di-PO
        $orders = PurchaseRequisition::with(['details' => function ($query) {
            $query->whereRaw('po_qty < qty'); // Hanya ambil item yang belum terpenuhi
        }])
            ->whereNotIn('status', ['draft', 'closed', 'done'])
            ->get();

        return response()->json($orders);
    }

    public function getQuotationDetail(Request $request)
    {
        $year = date('Y');

        $ids = $request->quotation_ids;

        $details = DB::table("purchase_requisition_detail_$year as d")
            ->join("purchase_requisition_$year as q", 'q.id', '=', 'd.purchase_requisition_id')
            ->join('data_barang as b', 'b.id', '=', 'd.product_id')
            ->join('basic_code_detail as u', 'u.id', '=', 'd.unit_id')
            ->select(
                'd.id',
                'd.purchase_requisition_id',
                'q.code',
                'd.product_id',
                'b.nama_barang',
                'd.outstanding_qty',
                'u.detail as unit_name',
                'd.unit_id'
            )
            ->whereIn('d.purchase_requisition_id', $ids)
            ->where('d.active', 1)
            ->get();

        return response()->json($details);
    }

    public function getPrice($id)
    {
        // Mencari data barang berdasarkan ID
        $product = Barang::find($id);

        if ($product) {
            return response()->json([
                'success' => true,
                'price' => $product->price,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Product tidak ditemukan',
        ], 404);
    }

    // public function store(PurchaseOrderRequest $request)
    // {
    //     DB::beginTransaction();

    //     try {
    //         $currentYear = date('Y');
    //         $data = $request->validated();
    //         $itemsDetailRaw = $request->input('items_detail');
    //         unset($data['items_detail']);

    //         // Mengambil data tambahan
    //         $data['created_by'] = Auth::id();
    //         $data['updated_by'] = null;
    //         $data['vehicle_id'] = $request->vehicle_id;
    //         $data['sub_total'] = $request->sub_total;
    //         $data['disc_percent'] = $request->percent;
    //         $data['disc_nominal'] = $request->discount_all;
    //         $data['grand_total'] = $request->total_order;
    //         $data['payment_term'] = $request->payment_term;
    //         $data['kena_pajak'] = $request->has('kena_pajak') ? 1 : 0;
    //         $data['total_termasuk_pajak'] = $request->has('total_termasuk_pajak') ? 1 : 0;
    //         $data['shipping_address'] = $request->shipping_address;
    //         $data['description'] = $request->description;
    //         $data['taxpayer_data'] = $request->taxpayer_data;
    //         $data['tax_id'] = $request->tax_id;
    //         $data['tax_amount'] = $request->tax_amount;
    //         $data['datePO'] = Carbon::parse($request->datePO)->format('Y-m-d');
    //         $data['tanggal_kirim'] = $request->tanggal_kirim ? Carbon::parse($request->tanggal_kirim)->format('Y-m-d') : null;

    //         // --- GENERATE CODE DENGAN LOCKING UNTUK MENCEGAH DUPLIKAT ---
    //         // Kita gunakan lockForUpdate agar proses generate tidak bentrok jika diklik bersamaan
    //         // $data['code'] = $request->code;
    //         // $purchaseOrder = PurchaseOrder::create($data);

    //         $purchaseOrder = null;
    //         $maxRetry = 10;
    //         $currentCode = $request->code; // Ambil input awal dari user

    //         for ($attempt = 1; $attempt <= $maxRetry; $attempt++) {
    //             try {
    //                 $data['code'] = $currentCode;
    //                 $purchaseOrder = PurchaseOrder::create($data);
    //                 break; // Berhasil, keluar dari loop
    //             } catch (QueryException $e) {
    //                 // Cek jika error adalah Duplicate Entry (1062)
    //                 if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062) {

    //                     // LOGIKA PENTING: Ubah $currentCode ke nomor berikutnya
    //                     // Menggunakan regex untuk mencari angka di akhir string
    //                     if (preg_match('/^(.*?)(\d+)$/', $currentCode, $matches)) {
    //                         $prefix = $matches[1];
    //                         $lastNumber = (int) $matches[2];
    //                         $length = strlen($matches[2]);

    //                         // Tambahkan 1 ke nomor, lalu format ulang
    //                         $currentCode = $prefix.str_pad($lastNumber + 1, $length, '0', STR_PAD_LEFT);
    //                     } else {
    //                         // Jika tidak ada format angka, tambahkan -1
    //                         $currentCode .= '-1';
    //                     }

    //                     usleep(50000); // Tunggu sebentar sebelum retry

    //                     continue;
    //                 }
    //                 throw $e; // Jika error bukan 1062, lempar error asli
    //             }
    //         }

    //         if (! $purchaseOrder) {
    //             throw new \Exception('Gagal membuat Purchase Order: Nomor sudah penuh atau sistem sibuk.');
    //         }

    //         if ($itemsDetailRaw) {
    //             $items = json_decode($itemsDetailRaw, true);
    //             $involvedPrIds = [];

    //             if (is_array($items) && count($items) > 0) {
    //                 foreach ($items as $item) {
    //                     $prDetailId = $item['purchase_requisition_detail_id'] ?? $item['pr_detail_id'] ?? $item['detail_id'] ?? null;
    //                     $qtyInputForm = floatval($item['quantity'] ?? $item['qty'] ?? 0);
    //                     $unitPrice = floatval($item['unit_price'] ?? 0);
    //                     $discount = floatval($item['discount'] ?? 0);
    //                     $amount = ($qtyInputForm * $unitPrice) - $discount;

    //                     PurchaseOrderDetail::create([
    //                         'purchase_order_id' => $purchaseOrder->id,
    //                         'purchase_requisition_detail_id' => $prDetailId,
    //                         'product_id' => $item['product_id'],
    //                         'qty' => $qtyInputForm,
    //                         'outstanding_qty' => $qtyInputForm,
    //                         'unit_id' => $item['unit_id'],
    //                         'unit_price' => $unitPrice,
    //                         'warehouse_id' => $item['warehouse_id'],
    //                         'discount' => $discount,
    //                         'discount_percent' => $item['discount_percent'] ?? 0,
    //                         'amount' => $item['amount'] ?? $amount,
    //                         'active' => 1,
    //                         'created_by' => Auth::id(),
    //                     ]);

    //                     // --- LOGIKA SINKRONISASI PO KE PR ---
    //                     if ($prDetailId) {
    //                         $tableName = "purchase_requisition_detail_{$currentYear}";
    //                         $prDetail = DB::table($tableName)->where('id', $prDetailId)->first();

    //                         if ($prDetail) {
    //                             $totalPoForThisItem = PurchaseOrderDetail::where('purchase_requisition_detail_id', $prDetailId)
    //                                 ->where('active', 1)
    //                                 ->sum('qty');

    //                             $outstandingQty = max(0, $prDetail->qty - $totalPoForThisItem);

    //                             DB::table($tableName)
    //                                 ->where('id', $prDetailId)
    //                                 ->update([
    //                                     'po_qty' => $totalPoForThisItem,
    //                                     'outstanding_qty' => $outstandingQty,
    //                                     'updated_at' => now(),
    //                                 ]);

    //                             if (! in_array($prDetail->purchase_requisition_id, $involvedPrIds)) {
    //                                 $involvedPrIds[] = $prDetail->purchase_requisition_id;
    //                             }
    //                         }
    //                     }
    //                 }

    //                 // --- OTOMASI STATUS PR MASTER ---
    //                 foreach ($involvedPrIds as $prId) {
    //                     $allDetails = DB::table("purchase_requisition_detail_{$currentYear}")
    //                         ->where('purchase_requisition_id', $prId)
    //                         ->get();

    //                     $totalRequested = $allDetails->sum('qty');
    //                     $totalOrdered = $allDetails->sum('po_qty');

    //                     if ($totalOrdered >= $totalRequested) {
    //                         $newStatus = 'closed';
    //                     } elseif ($totalOrdered > 0) {
    //                         $newStatus = 'partial';
    //                     } else {
    //                         $newStatus = 'processing';
    //                     }

    //                     DB::table("purchase_requisition_{$currentYear}")
    //                         ->where('id', $prId)
    //                         ->update(['status' => $newStatus, 'updated_at' => now()]);
    //                 }
    //             }
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Purchase Order saved successfully!',
    //             'redirect' => $request->save_and_new == 1 ? route('purchase-order.create') : route('purchase-order.index'),
    //         ], 200);

    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Gagal menyimpan data: '.$e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function store(PurchaseOrderRequest $request)
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
            $purchaseOrder = null;
            $maxRetry = 10;
            $currentCode = $request->code;

            for ($attempt = 1; $attempt <= $maxRetry; $attempt++) {
                try {
                    $data['code'] = $currentCode;
                    $purchaseOrder = PurchaseOrder::create($data);
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

            if (! $purchaseOrder) {
                throw new \Exception('Gagal membuat Purchase Order: Nomor sudah penuh atau sistem sibuk.');
            }

            // ================== DETAIL ==================
            if ($itemsDetailRaw) {
                $items = json_decode($itemsDetailRaw, true);
                $involvedPrIds = [];

                if (is_array($items) && count($items) > 0) {

                    foreach ($items as $index => $item) {

                        $prDetailId = $item['purchase_requisition_detail_id']
                            ?? $item['pr_detail_id']
                            ?? $item['detail_id']
                            ?? null;

                        $qtyInputForm = floatval($item['quantity'] ?? $item['qty'] ?? 0);
                        $unitPrice = floatval($item['unit_price'] ?? 0);
                        $discount = floatval($item['discount'] ?? 0);

                        $amount = ($qtyInputForm * $unitPrice) - $discount;

                        // ✅ SIMPAN URUTAN DARI DRAG TABLE
                        PurchaseOrderDetail::create([
                            'purchase_order_id' => $purchaseOrder->id,
                            'purchase_requisition_detail_id' => $prDetailId,
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
                            $tableName = "purchase_requisition_detail_{$currentYear}";
                            $prDetail = DB::table($tableName)->where('id', $prDetailId)->first();

                            if ($prDetail) {

                                $totalPoForThisItem = PurchaseOrderDetail::where('purchase_requisition_detail_id', $prDetailId)
                                    ->where('active', 1)
                                    ->sum('qty');

                                $outstandingQty = max(0, $prDetail->qty - $totalPoForThisItem);

                                DB::table($tableName)
                                    ->where('id', $prDetailId)
                                    ->update([
                                        'po_qty' => $totalPoForThisItem,
                                        'outstanding_qty' => $outstandingQty,
                                        'updated_at' => now(),
                                    ]);

                                if (! in_array($prDetail->purchase_requisition_id, $involvedPrIds)) {
                                    $involvedPrIds[] = $prDetail->purchase_requisition_id;
                                }
                            }
                        }
                    }

                    // ================== UPDATE STATUS PR ==================
                    foreach ($involvedPrIds as $prId) {

                        $allDetails = DB::table("purchase_requisition_detail_{$currentYear}")
                            ->where('purchase_requisition_id', $prId)
                            ->get();

                        $totalRequested = $allDetails->sum('qty');
                        $totalOrdered = $allDetails->sum('po_qty');

                        if ($totalOrdered >= $totalRequested) {
                            $newStatus = 'closed';
                        } elseif ($totalOrdered > 0) {
                            $newStatus = 'partial';
                        } else {
                            $newStatus = 'processing';
                        }

                        DB::table("purchase_requisition_{$currentYear}")
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
                'message' => 'Purchase Order saved successfully!',
                'redirect' => $request->save_and_new == 1
                    ? route('purchase-order.create')
                    : route('purchase-order.index'),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data: '.$e->getMessage(),
            ], 500);
        }
    }

    public function edit(string $id)
    {
        // Mengambil tahun berjalan untuk tabel dinamis
        $year = date('Y');

        // 1. Load data PO beserta relasinya
        // Pastikan model PurchaseOrder dan Detail sudah mendukung table name dinamis jika diperlukan
        $purchaseOrder = PurchaseOrder::with([
            'purchaseRequisition',
            'details.produkID',
            'details.unitID',
            'details.warehouseID',
            'details.purchaseRequisitionDetail.requisition',
        ])->findOrFail($id);

        // 2. Cek status PO global: Apakah mengandung minimal satu item hasil serapan PR?
        $isFromPR = $purchaseOrder->details->whereNotNull('purchase_requisition_detail_id')->count() > 0;

        // 3. Mapping data detail
        $detailDataMapped = $purchaseOrder->details->map(function ($detail) use ($purchaseOrder, $year) {

            $requisitionCode = null;
            $sisaPr = null;
            $kuotaAsliPr = null;
            $totalDiambilLainnya = 0;

            // Cek apakah item detail ini memiliki keterikatan dengan PR
            if ($detail->purchase_requisition_detail_id) {
                // Ambil data referensi dari relasi
                $prDetail = $detail->purchaseRequisitionDetail;

                if ($prDetail) {
                    $sisaPr = (float) $prDetail->outstanding_qty;
                    $kuotaAsliPr = (float) $prDetail->qty;

                    // HITUNG TOTAL YANG SUDAH DIAMBIL DI PO LAIN
                    // Menggunakan DB::table karena tabel bersifat dinamis per tahun
                    $totalDiambilLainnya = DB::table("purchase_order_detail_{$year}")
                        ->where('purchase_requisition_detail_id', $detail->purchase_requisition_detail_id)
                        ->where('purchase_order_id', '<>', $purchaseOrder->id) // Kecuali PO ini sendiri
                        ->where('active', 1)
                        ->sum('qty');

                    if ($prDetail->purchaseRequisition) {
                        $requisitionCode = $prDetail->purchaseRequisition->code;
                    }
                }
            }

            return [
                'id' => $detail->id,
                'purchase_order_id' => $detail->purchase_order_id,
                'purchase_requisition_detail_id' => $detail->purchase_requisition_detail_id,
                'requisition_code' => $requisitionCode,
                'product_id' => $detail->product_id,
                'data_produk' => $detail->produkID->nama_barang ?? 'Product Not Found',
                'quantity' => (float) $detail->qty,
                'unit_id' => $detail->unit_id,
                'unit' => $detail->unitID->detail ?? '-',
                'warehouse_id' => $detail->warehouse_id,
                'warehouse' => $detail->warehouseID->nama_gudang ?? '-',
                'unit_price' => (float) $detail->unit_price,
                'discount' => (float) $detail->discount,
                'discount_percent' => $detail->discount_percent,
                'amount' => (float) $detail->amount,
                'tax' => (float) ($detail->tax ?? 0),
                'sisa_pr' => $sisaPr,
                'kuota_asli' => $kuotaAsliPr,
                'total_diambil_lainnya' => (float) $totalDiambilLainnya,

            ];
        });

        // 🔥 Ambil semua pajak aktif (khusus pembelian & general)
        $taxes = Tax::where('is_active', true)
            ->whereIn('usage', ['purchase', 'both'])
            ->get();

        // 🔥 Ambil default tax (misalnya PPN)
        $defaultTax = Tax::where('is_active', true)
            ->where('is_default', true)
            ->whereIn('usage', ['purchase', 'both'])
            ->first();
        $status = ['processing', 'partial'];

        // 4. Susun semua variabel ke dalam array compact
        $x = [
            'title' => 'Edit Purchase Order',
            'breadcrumb' => [
                ['label' => 'Purchase Order', 'url' => route('purchase-order.index')],
                ['label' => 'Edit Purchase Order', 'url' => ''],
            ],
            'supplier' => Supplier::where('status', 1)->get(),
            'company' => Company::first(),
            'idNumber' => $this->generateNumberId(),
            'shipping' => Shipping::where('status', 1)->get(),
            'warehouse' => Warehouse::where('status', 1)->get(),
            'paymentTerm' => SyaratPembayaran::where('status', 1)->get(),
            'product' => Barang::where('status', '<>', 0)->get(),
            'fob' => BasicCodeDetail::where('master_id', 7)->get(),
            'model' => $purchaseOrder,
            'isFromPR' => $isFromPR,
            'jsonDetails' => $detailDataMapped,
            'taxes' => $taxes,
            'defaultTax' => $defaultTax,
            'number' => PurchaseRequisition::whereIn('status', $status)
                ->where('active', 1)
                ->get(),
        ];

        return view('purchase.purchase_order.purchase_order_edit', $x);
    }

    public function update(PurchaseOrderRequest $request, string $id)
    {
        DB::beginTransaction();

        try {
            $currentYear = date('Y');

            $purchaseOrder = PurchaseOrder::findOrFail($id);

            $syaratPembayaran = SyaratPembayaran::find($request->payment_term);

            /*
            |--------------------------------------------------------------------------
            | UPDATE MASTER PO
            |--------------------------------------------------------------------------
            */
            $purchaseOrder->update([
                'supplier_id' => $request->supplier_id,
                'code' => $request->code,
                'datePO' => Carbon::parse($request->datePO)->format('Y-m-d'),
                'tanggal_kirim' => $request->tanggal_kirim
                                    ? Carbon::parse($request->tanggal_kirim)->format('Y-m-d')
                                    : null,
                'kena_pajak' => $request->has('kena_pajak') ? 1 : 0,
                'total_termasuk_pajak' => $request->has('total_termasuk_pajak') ? 1 : 0,
                'fob_id' => $request->fob_id,
                'vehicle_id' => $request->vehicle_id,
                'payment_term' => $request->payment_term,
                'shipping_address' => $request->shipping_address,
                'description' => $request->description,
                'taxpayer_data' => $request->taxpayer_data,
                'tax_id' => $request->tax_id,
                'tax_amount' => $request->tax_amount,
                'sub_total' => $request->sub_total,
                'disc_percent' => $request->percent,
                'disc_nominal' => $request->discount_all,
                'grand_total' => $request->total_order,

                'updated_by' => Auth::id(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | ITEMS
            |--------------------------------------------------------------------------
            */
            $items = json_decode($request->items_detail, true);

            if (! is_array($items) || count($items) == 0) {
                throw new \Exception('Minimal harus ada 1 item.');
            }

            /*
            |--------------------------------------------------------------------------
            | AMBIL DETAIL LAMA
            |--------------------------------------------------------------------------
            */
            $oldDetails = PurchaseOrderDetail::where(
                'purchase_order_id',
                $purchaseOrder->id
            )->get();

            $affectedPrIds = [];

            /*
            |--------------------------------------------------------------------------
            | KEMBALIKAN SELURUH QTY LAMA KE PR
            |--------------------------------------------------------------------------
            */
            foreach ($oldDetails as $old) {

                if (! $old->purchase_requisition_detail_id) {
                    continue;
                }

                $prDetail = DB::table("purchase_requisition_detail_{$currentYear}")
                    ->where('id', $old->purchase_requisition_detail_id)
                    ->first();

                if (! $prDetail) {
                    continue;
                }

                DB::table("purchase_requisition_detail_{$currentYear}")
                    ->where('id', $prDetail->id)
                    ->update([
                        'po_qty' => DB::raw("
                        CASE
                            WHEN po_qty - {$old->qty} < 0
                            THEN 0
                            ELSE po_qty - {$old->qty}
                        END
                    "),
                    ]);

                $affectedPrIds[] = $prDetail->purchase_requisition_id;
            }

            /*
            |--------------------------------------------------------------------------
            | HAPUS DETAIL PO LAMA
            |--------------------------------------------------------------------------
            */
            PurchaseOrderDetail::where(
                'purchase_order_id',
                $purchaseOrder->id
            )->delete();

            /*
            |--------------------------------------------------------------------------
            | SIMPAN DETAIL BARU Bersama Urutan Hasil Drag & Drop
            |--------------------------------------------------------------------------
            */
            foreach ($items as $index => $item) {

                $prDetailId = $item['purchase_requisition_detail_id'] ?? null;

                if ($prDetailId == '' || $prDetailId == 'null') {
                    $prDetailId = null;
                }

                $qty = floatval($item['quantity'] ?? 0);

                /*
                |--------------------------------------------------------------------------
                | VALIDASI ITEM PR
                |--------------------------------------------------------------------------
                */
                if ($prDetailId) {

                    $prDetail = DB::table("purchase_requisition_detail_{$currentYear}")
                        ->where('id', $prDetailId)
                        ->first();

                    if (! $prDetail) {
                        throw new \Exception(
                            "PR Detail ID {$prDetailId} tidak ditemukan."
                        );
                    }

                    $sisaPR =
                        floatval($prDetail->qty)
                        - floatval($prDetail->po_qty);

                    if ($qty > $sisaPR) {
                        throw new \Exception(
                            "Qty {$item['data_produk']} melebihi sisa PR. Maksimal {$sisaPR}"
                        );
                    }

                    DB::table("purchase_requisition_detail_{$currentYear}")
                        ->where('id', $prDetailId)
                        ->update([
                            'po_qty' => DB::raw("po_qty + {$qty}"),
                        ]);

                    $affectedPrIds[] = $prDetail->purchase_requisition_id;
                }

                $unitPrice = floatval($item['unit_price'] ?? 0);
                $discount = floatval($item['discount'] ?? 0);
                $discountPercent = $item['discount_percent'] ?? 0;

                // ✅ Simpan data dengan menyuntikkan kolom 'urutan' berbasis index array JavaScript terkini
                PurchaseOrderDetail::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'purchase_requisition_detail_id' => $prDetailId,
                    'product_id' => $item['product_id'],
                    'qty' => $qty,
                    'outstanding_qty' => $qty,
                    'unit_id' => $item['unit_id'],
                    'warehouse_id' => $item['warehouse_id'],
                    'unit_price' => $unitPrice,
                    'discount' => $discount,
                    'discount_percent' => $discountPercent,
                    'amount' => $item['amount'] ?? (($qty * $unitPrice) - $discount),
                    'urutan' => $index,
                    'active' => 1,
                    'created_by' => $purchaseOrder->created_by,
                    'updated_by' => Auth::id(),
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | UPDATE OUTSTANDING (Hanya untuk PR yang terdampak agar tidak melambatkan server)
            |--------------------------------------------------------------------------
            */
            $affectedPrIds = array_unique($affectedPrIds);

            if (! empty($affectedPrIds)) {
                DB::table("purchase_requisition_detail_{$currentYear}")
                    ->whereIn('purchase_requisition_id', $affectedPrIds)
                    ->update([
                        'outstanding_qty' => DB::raw('qty - po_qty'),
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | UPDATE STATUS PR
            |--------------------------------------------------------------------------
            */
            foreach ($affectedPrIds as $prId) {

                $details = DB::table("purchase_requisition_detail_{$currentYear}")
                    ->where('purchase_requisition_id', $prId)
                    ->where('active', 1)
                    ->get();

                $isClosed = true;
                $hasPO = false;

                foreach ($details as $detail) {

                    if ($detail->po_qty > 0) {
                        $hasPO = true;
                    }

                    if ($detail->po_qty < $detail->qty) {
                        $isClosed = false;
                    }
                }

                $status = 'processing';

                if ($isClosed) {
                    $status = 'closed';
                } elseif ($hasPO) {
                    $status = 'partial';
                }

                DB::table("purchase_requisition_{$currentYear}")
                    ->where('id', $prId)
                    ->update([
                        'status' => $status,
                    ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'title' => 'Success',
                'message' => 'Purchase Order berhasil diupdate',
                'redirect' => route('purchase-order.index'),
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            // 1. Cari PO yang akan dihapus
            $po = PurchaseOrder::findOrFail($id);

            // 2. Ambil detail PO untuk mendapatkan referensi PR Detail yang terkait
            $poDetails = PurchaseOrderDetail::where('purchase_order_id', $po->id)->get();
            $involvedPrIds = [];

            foreach ($poDetails as $poDetail) {
                if ($poDetail->purchase_requisition_detail_id) {
                    // Catat ID PR Master-nya
                    $prDetail = PurchaseRequisitionDetail::where('id', $poDetail->purchase_requisition_detail_id)
                        ->first();

                    if ($prDetail && ! in_array($prDetail->purchase_requisition_id, $involvedPrIds)) {
                        $involvedPrIds[] = $prDetail->purchase_requisition_id;
                    }
                }
            }

            // 3. Nonaktifkan PO dan Detail PO
            $po->update(['active' => 0, 'updated_by' => Auth::id()]);
            PurchaseOrderDetail::where('purchase_order_id', $po->id)->update(['active' => 0]);

            // 4. Update Ulang po_qty di setiap PR Detail yang terdampak
            // Kita hitung ulang berdasarkan sisa PO yang masih 'active' = 1
            foreach ($poDetails as $poDetail) {
                if ($poDetail->purchase_requisition_detail_id) {
                    $totalRemainingPo = PurchaseOrderDetail::where('purchase_requisition_detail_id', $poDetail->purchase_requisition_detail_id)
                        ->where('active', 1)
                        ->sum('qty');

                    DB::table('purchase_requisition_detail_'.date('Y'))
                        ->where('id', $poDetail->purchase_requisition_detail_id)
                        ->update(['po_qty' => $totalRemainingPo]);
                }
            }

            // 5. Update Status PR Master
            foreach ($involvedPrIds as $prId) {
                $allDetails = PurchaseRequisitionDetail::where('purchase_requisition_id', $prId)
                    ->get();

                $totalRequested = $allDetails->sum('qty');
                $totalOrdered = $allDetails->sum('po_qty');

                if ($totalOrdered >= $totalRequested) {
                    $status = 'closed';
                } elseif ($totalOrdered > 0) {
                    $status = 'partial';
                } else {
                    $status = 'processing';
                }

                PurchaseRequisition::where('id', $prId)
                    ->update(['status' => $status]);
            }

            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'PO berhasil dibatalkan.'], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['status' => 'error', 'message' => 'Gagal membatalkan PO: '.$e->getMessage()], 500);
        }
    }

    public function trash(Request $r)
    {
        if ($r->ajax()) {
            $query = PurchaseOrder::where('active', 0)->get();

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

                        case 'pending':
                            $badge = 'bg-label-warning';
                            $text = 'Pending Approval';
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
                ->addColumn('date', function ($row) {
                    return Carbon::parse($row->datePO)->format('d-m-Y');
                })
                ->addColumn('tanggal_kirim', function ($row) {
                    return Carbon::parse($row->tanggal_kirim)->format('d-m-Y');
                })
                ->addColumn('amount', function ($row) {
                    // 1. Hitung total kotor (sum amount) dari detail item PO
                    $subTotal = PurchaseOrderDetail::where('purchase_order_id', $row->id)
                        ->where('active', 1)
                        ->sum('amount');

                    // 2. Hitung grand total: Subtotal dikurangi diskon nominal yang ada di tabel induk ($row)
                    // Gunakan ?? 0 jika kolom disc_nominal di database bisa bernilai null
                    $grandTotal = $subTotal - ($row->disc_nominal ?? 0);

                    // 3. Kembalikan nilai yang sudah dikonversi dan diformat
                    return format_uang(convert_currency($grandTotal, $row->currency_id ?? 1));
                })
                ->addColumn('supplier', function ($row) {
                    return $row->supplier->nama_supplier;
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

                    if (auth()->user()->can('purchase_order-restore')) {
                        $btn .= '<a class="dropdown-item restore" href="javascript:void(0)"
                            data-id="'.$row->id.'"> <i class="ti ti-trash-off me-1"></i> Restore</a>';
                    }

                    return $btn;
                })
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'cekbok', 'supplier', 'date', 'amount'])
                ->make(true);
        }

        $x = [
            'title' => 'Deleted Purchase Order List',
            'breadcrumb' => [
                ['label' => 'Purchase Order', 'url' => route('purchase-order.index')],
                ['label' => 'Deleted Purchase Order', 'url' => ''],
            ],

        ];

        return view('purchase.purchase_order.purchase_order_trash', $x);
    }

    public function deleteMultiple(Request $request)
    {
        DB::beginTransaction();

        try {
            $ids = $request->ids;

            if (! $ids || count($ids) == 0) {
                return response()->json(['success' => false, 'message' => 'Tidak ada data yang dipilih.'], 400);
            }

            // 1. Ambil semua detail dari PO yang akan dihapus untuk sinkronisasi PR
            $poDetails = PurchaseOrderDetail::whereIn('purchase_order_id', $ids)->get();
            $involvedPrIds = [];

            // 2. Tandai PO dan Detail PO sebagai tidak aktif (active = 0)
            PurchaseOrder::whereIn('id', $ids)->update([
                'active' => 0,
                'updated_by' => Auth::id(),
            ]);
            PurchaseOrderDetail::whereIn('purchase_order_id', $ids)->update(['active' => 0]);

            // 3. Update po_qty di PR Detail dan kumpulkan ID PR Master
            foreach ($poDetails as $poDetail) {
                if ($poDetail->purchase_requisition_detail_id) {
                    // Hitung total dari PO yang tersisa (yang masih aktif)
                    $totalRemainingPo = PurchaseOrderDetail::where('purchase_requisition_detail_id', $poDetail->purchase_requisition_detail_id)
                        ->where('active', 1)
                        ->sum('qty');

                    // Update ke tabel PR Detail
                    DB::table('purchase_requisition_detail_'.date('Y'))
                        ->where('id', $poDetail->purchase_requisition_detail_id)
                        ->update(['po_qty' => $totalRemainingPo]);

                    // Simpan ID PR untuk update status nanti
                    $prDetail = DB::table('purchase_requisition_detail_'.date('Y'))
                        ->where('id', $poDetail->purchase_requisition_detail_id)
                        ->first();

                    if ($prDetail && ! in_array($prDetail->purchase_requisition_id, $involvedPrIds)) {
                        $involvedPrIds[] = $prDetail->purchase_requisition_id;
                    }
                }
            }

            // 4. Update Status PR Master berdasarkan akumulasi terbaru
            foreach ($involvedPrIds as $prId) {
                $allDetails = DB::table('purchase_requisition_detail_'.date('Y'))
                    ->where('purchase_requisition_id', $prId)
                    ->get();

                $totalRequested = $allDetails->sum('qty');
                $totalOrdered = $allDetails->sum('po_qty');

                if ($totalOrdered >= $totalRequested) {
                    $status = 'closed';
                } elseif ($totalOrdered > 0) {
                    $status = 'partial';
                } else {
                    $status = 'processing';
                }

                DB::table('purchase_requisition_'.date('Y'))
                    ->where('id', $prId)
                    ->update(['status' => $status]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase Order berhasil dihapus dan status PR telah diperbarui.',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: '.$e->getMessage(),
            ], 500);
        }
    }

    public function restore($id)
    {
        DB::beginTransaction();

        try {
            // 1. Aktifkan kembali PO
            $po = PurchaseOrder::findOrFail($id);
            $po->update(['active' => 1, 'updated_by' => Auth::id()]);

            // 2. Aktifkan kembali Detail PO
            PurchaseOrderDetail::where('purchase_order_id', $po->id)->update(['active' => 1]);

            // 3. Ambil semua detail PO yang baru saja diaktifkan
            $poDetails = PurchaseOrderDetail::where('purchase_order_id', $po->id)->get();
            $involvedPrIds = [];

            // 4. Update ulang po_qty di PR Detail
            foreach ($poDetails as $poDetail) {
                if ($poDetail->purchase_requisition_detail_id) {
                    // Hitung total dari semua PO yang aktif
                    $totalPoForThisItem = PurchaseOrderDetail::where('purchase_requisition_detail_id', $poDetail->purchase_requisition_detail_id)
                        ->where('active', 1)
                        ->sum('qty');

                    // Update ke tabel PR Detail
                    DB::table('purchase_requisition_detail_'.date('Y'))
                        ->where('id', $poDetail->purchase_requisition_detail_id)
                        ->update(['po_qty' => $totalPoForThisItem]);

                    // Simpan ID PR untuk update status
                    $prDetail = DB::table('purchase_requisition_detail_'.date('Y'))
                        ->where('id', $poDetail->purchase_requisition_detail_id)
                        ->first();

                    if ($prDetail && ! in_array($prDetail->purchase_requisition_id, $involvedPrIds)) {
                        $involvedPrIds[] = $prDetail->purchase_requisition_id;
                    }
                }
            }

            // 5. Update Status PR Master
            foreach ($involvedPrIds as $prId) {
                $allDetails = DB::table('purchase_requisition_detail_'.date('Y'))
                    ->where('purchase_requisition_id', $prId)
                    ->get();

                $totalRequested = $allDetails->sum('qty');
                $totalOrdered = $allDetails->sum('po_qty');

                if ($totalOrdered >= $totalRequested) {
                    $status = 'closed';
                } elseif ($totalOrdered > 0) {
                    $status = 'partial';
                } else {
                    $status = 'processing';
                }

                DB::table('purchase_requisition_'.date('Y'))
                    ->where('id', $prId)
                    ->update(['status' => $status]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase Order berhasil dikembalikan (restored).',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal merestore data: '.$e->getMessage(),
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

            // 1. Update status PO jadi aktif
            PurchaseOrder::whereIn('id', $ids)->update([
                'active' => 1,
                'updated_by' => Auth::id(),
            ]);

            // 2. Aktifkan kembali semua detail PO yang berkaitan dengan PO-PO tersebut
            PurchaseOrderDetail::whereIn('purchase_order_id', $ids)->update(['active' => 1]);

            // 3. Ambil semua detail PO yang baru saja diaktifkan untuk sinkronisasi
            $poDetails = PurchaseOrderDetail::whereIn('purchase_order_id', $ids)->get();
            $involvedPrIds = [];

            // 4. Update po_qty di PR Detail dan kumpulkan ID PR Master
            foreach ($poDetails as $poDetail) {
                if ($poDetail->purchase_requisition_detail_id) {
                    // Hitung total dari semua PO yang aktif
                    $totalPoForThisItem = PurchaseOrderDetail::where('purchase_requisition_detail_id', $poDetail->purchase_requisition_detail_id)
                        ->where('active', 1)
                        ->sum('qty');

                    // Update ke tabel PR Detail
                    DB::table('purchase_requisition_detail_'.date('Y'))
                        ->where('id', $poDetail->purchase_requisition_detail_id)
                        ->update(['po_qty' => $totalPoForThisItem]);

                    // Simpan ID PR untuk update status nanti (hindari duplikat)
                    $prDetail = DB::table('purchase_requisition_detail_'.date('Y'))
                        ->where('id', $poDetail->purchase_requisition_detail_id)
                        ->first();

                    if ($prDetail && ! in_array($prDetail->purchase_requisition_id, $involvedPrIds)) {
                        $involvedPrIds[] = $prDetail->purchase_requisition_id;
                    }
                }
            }

            // 5. Update Status PR Master berdasarkan akumulasi terbaru
            foreach ($involvedPrIds as $prId) {
                $allDetails = DB::table('purchase_requisition_detail_'.date('Y'))
                    ->where('purchase_requisition_id', $prId)
                    ->get();

                $totalRequested = $allDetails->sum('qty');
                $totalOrdered = $allDetails->sum('po_qty');

                if ($totalOrdered >= $totalRequested) {
                    $status = 'closed';
                } elseif ($totalOrdered > 0) {
                    $status = 'partial';
                } else {
                    $status = 'processing';
                }

                DB::table('purchase_requisition_'.date('Y'))
                    ->where('id', $prId)
                    ->update(['status' => $status]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase Order terpilih berhasil dikembalikan.',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal merestore data: '.$e->getMessage(),
            ], 500);
        }
    }

    public function processData($id)
    {
        // 1. Ambil tahun berjalan secara dinamis
        $year = date('Y');
        $tableName = "purchase_order_{$year}";

        // 2. Gunakan Query Builder dengan nama tabel dinamis agar pencarian ID aman
        $poData = DB::table($tableName)->where('id', $id)->first();

        // Jika data memang benar-benar tidak ditemukan di database
        if (! $poData) {
            return response()->json(['success' => false, 'message' => 'Data Purchase Order tidak ditemukan.'], 404);
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

        return response()->json(['success' => true, 'message' => 'Purchase Order berhasil diajukan!']);
    }

    public function show(string $id) {}

    public function print($id)
    {
        $purchaseOrder = PurchaseOrder::with(['details.produkID', 'details.unitID'])->findOrFail($id);
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
            'model' => $purchaseOrder,
            'company' => $company,
            'modelDetail' => $purchaseOrder->details,
            'logoBase64' => $logoBase64,
        ];

        $pdf = Pdf::loadView('pdf.purchase_order_pdf', $data)
            ->setPaper('a4', 'portrait');

        // preview di browser
        $filename = $purchaseOrder->code.'-'.$purchaseOrder->supplier->nama_supplier;

        // replace forbidden filename chars
        $filename = preg_replace('/[\/\\\\:*?"<>|]/', '-', $filename);
        $pdf->getDomPDF()->set_option('isPhpEnabled', true);

        return $pdf->stream($filename.'.pdf');

        // kalau mau download:
        // return $pdf->download('purchase-order.pdf');
    }

    public function getPriceHistory(Request $request)
    {
        $productId = $request->get('product_id');
        $supplierId = $request->get('supplier_id');

        $year = date('Y');
        $tableDetail = "purchase_order_detail_{$year}";
        $tableMaster = "purchase_order_{$year}";

        // Mengambil harga unik langsung dari database
        $history = DB::table($tableDetail)
            ->join($tableMaster, "{$tableDetail}.purchase_order_id", '=', "{$tableMaster}.id")
            ->where("{$tableDetail}.product_id", $productId)
            ->where("{$tableMaster}.supplier_id", $supplierId)
            // Kuncinya di sini: kelompokkan berdasarkan harga, lalu ambil tanggal terbaru dengan MAX()
            ->select(
                "{$tableDetail}.unit_price as harga",
                DB::raw("MAX({$tableMaster}.datePO) as tanggal")
            )
            ->groupBy("{$tableDetail}.unit_price")
            // Urutkan berdasarkan tanggal terbaru (hasil dari MAX tanggal di atas)
            ->orderBy('tanggal', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'history' => $history,
        ]);
    }

    // public function getCompanyAddresses($companyId)
    // {

    //     $addresses = CompanyDeliveryAddress::where('company_id', 1)->where('active', 1)->get();

    //     return response()->json([
    //         'success' => true,
    //         'data' => $addresses,
    //     ]);
    // }

    public function getSupplierAddress($supplierId)
    {
        $supplier = Supplier::find($supplierId);

        if (! $supplier) {
            return response()->json([
                'success' => false,
                'message' => 'Supplier tidak ditemukan.',
            ]);
        }

        $address = collect([
            $supplier->alamat_pembayaran,
            collect([
                $supplier->kota,
                $supplier->provinsi,
                $supplier->kodepos,
            ])->filter()->implode(', '),
            $supplier->negara,
        ])->filter()->implode("\n");

        return response()->json([
            'success' => true,
            'data' => [
                'address_name' => $supplier->nama_supplier,
                'address' => $address,
            ],
        ]);
    }

    public function sendSupplier($id)
    {
        $po = PurchaseOrder::findOrFail($id);

        // VALIDASI STATUS
        if ($po->status != 'approved') {

            return response()->json([
                'message' => 'Only approved PO can be sent.',
            ], 422);
        }

        // UPDATE STATUS
        $po->update([
            'status' => 'sent',
            'updated_by' => Auth::user()->id,
        ]);

        return response()->json([
            'message' => 'PO successfully sent to supplier.',
        ]);
    }

    // public function getRequisitionDetail(Request $request)
    // {
    //     $ids = $request->ids;

    //     if (empty($ids)) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Tidak ada data PR yang dipilih.',
    //             'data' => [],
    //         ]);
    //     }

    //     $details = PurchaseRequisitionDetail::with([
    //         'produkID',
    //         'unitID',
    //         'requisition',
    //     ])
    //         ->whereIn('purchase_requisition_id', $ids)
    //         ->where('active', 1)
    //         ->whereHas('requisition', function ($q) {
    //             $q->whereIn('status', ['processing', 'partial']);
    //         })
    //         ->get();

    //     $formattedData = $details->map(function ($item) {

    //         return [
    //             'id' => $item->id,

    //             // relasi PR
    //             'purchase_requisition_detail_id' => $item->id,
    //             'purchase_requisition_id' => $item->purchase_requisition_id,

    //             // produk
    //             'product_id' => $item->product_id,
    //             'product_name' => $item->produkID->nama_barang ?? '',
    //             'data_produk' => $item->produkID->nama_barang ?? '',

    //             // qty langsung dari PR
    //             'quantity' => (float) $item->qty,
    //             'qty' => (float) $item->qty,

    //             // unit
    //             'unit_id' => $item->unit_id,
    //             'unit' => $item->unitID->detail ?? '',
    //             'unit_name' => $item->unitID->detail ?? '',

    //             // harga default
    //             'unit_price' => 0,
    //             'discount' => 0,
    //             'amount' => 0,
    //             'tax' => 0,

    //             // informasi PR
    //             'requisition_code' => $item->requisition->code ?? '',
    //             'required_date' => optional($item->requisition)->date,

    //             // hanya status
    //             'pr_status' => $item->requisition->status ?? '',

    //             'notes' => $item->notes ?? '',
    //         ];
    //     });

    //     return response()->json([
    //         'success' => true,
    //         'data' => $formattedData,
    //     ]);
    // }

    public function getRequisitionDetail(Request $request)
    {
        $ids = $request->ids;
        $usedDetailIds = $request->used_detail_ids ?? [];

        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data PR yang dipilih.',
                'data' => [],
            ]);
        }

        $details = PurchaseRequisitionDetail::with([
            'produkID',
            'unitID',
            'requisition',
        ])
            ->whereIn('purchase_requisition_id', $ids)
            ->where('active', 1)

            // hanya yang masih memiliki outstanding
            ->where('outstanding_qty', '>', 0)

            // jangan tampilkan lagi yang sudah ada di tabel PO saat ini
            ->when(! empty($usedDetailIds), function ($query) use ($usedDetailIds) {
                $query->whereNotIn('id', $usedDetailIds);
            })

            // hanya PR yang masih bisa diproses
            ->whereHas('requisition', function ($q) {
                $q->whereIn('status', ['processing', 'partial']);
            })

            ->orderBy('purchase_requisition_id')
            ->orderBy('id')
            ->get();

        $formattedData = $details->map(function ($item) {

            return [

                'id' => $item->id,

                // ===========================
                // Relasi Purchase Requisition
                // ===========================
                'purchase_requisition_detail_id' => $item->id,
                'purchase_requisition_id' => $item->purchase_requisition_id,

                // ===========================
                // Produk
                // ===========================
                'product_id' => $item->product_id,
                'product_name' => $item->produkID->nama_barang ?? '',
                'data_produk' => $item->produkID->nama_barang ?? '',

                // ===========================
                // Qty
                // ===========================

                // Qty yang boleh dipilih = Outstanding
                'quantity' => (float) $item->outstanding_qty,
                'qty' => (float) $item->outstanding_qty,

                // Informasi qty
                'pr_qty' => (float) $item->qty,
                'po_qty' => (float) $item->po_qty,
                'outstanding_qty' => (float) $item->outstanding_qty,

                // ===========================
                // Unit
                // ===========================
                'unit_id' => $item->unit_id,
                'unit' => $item->unitID->detail ?? '',
                'unit_name' => $item->unitID->detail ?? '',

                // ===========================
                // Harga
                // ===========================
                'unit_price' => 0,
                'discount' => 0,
                'amount' => 0,
                'tax' => 0,

                // ===========================
                // Informasi PR
                // ===========================
                'requisition_code' => $item->requisition->code ?? '',
                'required_date' => $item->required_date,
                'pr_date' => optional($item->requisition)->date,
                'pr_status' => $item->requisition->status ?? '',

                // ===========================
                // Catatan
                // ===========================
                'notes' => $item->notes ?? '',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedData,
        ]);
    }

    public function CloseDocument(Request $request, $id)
    {

        try {
            $table = PurchaseOrder::findOrFail($id);
            $table->status = 'closed';
            $table->updated_by = Auth::user()->id;
            $table->save();
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function getSupplierData($supplierId)
    {
        $supplier = Supplier::findOrFail($supplierId);
        // Rekening
        $rekening = DB::table('supplier_rekening')
            ->leftJoin(
                'basic_code_detail',
                'supplier_rekening.nama_bank',
                '=',
                'basic_code_detail.id'
            )
            ->select(
                'supplier_rekening.id',
                'supplier_rekening.nama_bank as bank_id',
                'supplier_rekening.nomor_rekening',
                'supplier_rekening.nama_rekening',
                'basic_code_detail.detail as bank_name'
            )
            ->where('supplier_rekening.supplier_id', $supplierId)
            ->get();

        // Pajak (ambil default)
        $pajak = DB::table('supplier_pajak')
            ->where('supplier_id', $supplierId)
            ->first();

        return response()->json([
            'rekening' => $rekening,
            'pajak' => $pajak,
            'supplier' => [
                'alamat_pembayaran' => $supplier->alamat_pembayaran,
                'kota' => $supplier->kota,
                'kodepos' => $supplier->kodepos,
                'provinsi' => $supplier->provinsi,
                'negara' => $supplier->negara,
            ],
        ]);
    }
}
