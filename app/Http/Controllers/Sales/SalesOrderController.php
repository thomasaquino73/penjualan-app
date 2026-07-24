<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalesOrderRequest;
use App\Models\BasicCodeDetail;
use App\Models\Inventory\Barang;
use App\Models\Inventory\Warehouse;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderDetail;
use App\Models\Sales\SalesQuotation;
use App\Models\Sales\SalesQuotationDetail;
use App\Models\Setting\Company;
use App\Models\Setting\Shipping;
use App\Models\Setting\SyaratPembayaran;
use App\Models\Setting\Tax;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class SalesOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $routeName = $request->route()->getName();

            $permissionMap = [
                'sales-order.index' => 'sales_order-browse',
                'sales-order.show' => 'sales_order-read',
                'sales-order.create' => 'sales_order-create',
                'sales-order.store' => 'sales_order-create',
                'sales-order.edit' => 'sales_order-edit',
                'sales-order.update' => 'sales_order-edit',
                'sales-order.destroy' => 'sales_order-delete',
                'sales-order.trash' => 'sales_order-trash',
                'sales-order.restore' => 'sales_order-restore',
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
        // Ambil ID user yang sedang login
        $userId = Auth::user()->id;

        // Query dengan kondisi: Aktif DAN (Status BUKAN draft ATAU Status ADALAH draft kepunyaan sendiri)
        $query = SalesOrder::where('active', '<>', 0)
            ->where(function ($q) use ($userId) {
                $q->where('status', '<>', 'draft')
                    ->orWhere(function ($subQ) use ($userId) {
                        $subQ->where('status', 'draft')
                            ->where('created_by', $userId);
                    });
            })
            ->orderby('sales_order_code', 'desc');
        if ($r->status) {
            $query->where('status', $r->status);
        }
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
                ->addColumn('sales_order_date', function ($row) {
                    return $row->sales_order_date ? Carbon::parse($row->sales_order_date)->format('d M Y') : 'N/A';
                })
                ->addColumn('customer', function ($row) {
                    return $row->customerID->nama_customer ?? 'N/A';
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

                        case 'fully_delivered':
                            $badge = 'bg-label-success';
                            $text = 'Fully Delivered';
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
                        auth()->user()->can('sales_order-delete') &&
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
                ->addColumn('total', function ($row) {
                    return format_uang(convert_currency($row->grand_total, $row->currency_id ?? 1));
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

                    if (
                        $user->can('sales_order-edit') &&
                        in_array($row->status, ['draft', 'pending', 'processing'])
                    ) {

                        $btn .= '
                                <a class="dropdown-item"
                                    href="'.route('sales-order.edit', $row->id).'">

                                    <i class="far fa-edit me-1"></i>
                                    Edit SO
                                </a>
                            ';
                    }

                    // DELETE
                    if (
                        $user->can('sales_order-delete') &&
                           in_array($row->status, ['draft', 'pending', 'processing'])
                    ) {

                        $btn .= '
                                <a class="dropdown-item text-danger"
                                    href="javascript:void(0)"
                                    id="delete"
                                    data-id="'.$row->id.'"
                                    data-name="'.$row->sales_order_code.'">

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

                    /*
                    |--------------------------------------------------------------------------
                    | 4. RECEIVE ITEM
                    |--------------------------------------------------------------------------
                    */

                    /*
                    |--------------------------------------------------------------------------
                    | 5. CANCEL SO
                    |--------------------------------------------------------------------------
                    */

                    if (
                        ! in_array($row->status, ['completed', 'cancelled']) &&
                        $user->can('sales_order-cancel')
                    ) {

                        $btn .= '
            <a class="dropdown-item text-danger btn-cancel-po"
                href="javascript:void(0)"
                data-id="'.$row->id.'">

                <i class="ti ti-circle-x me-1"></i>
                Cancel SO
            </a>
        ';
                    }
                    if ($row->status == 'completed') {

                    } else {
                        $btn .= '<a class="dropdown-item"
                href="javascript:void(0)" id="close"   data-id="'.$row->id.'" data-name="'.$row->code.'">
                <i class="ti ti-lock"></i> Close SO
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
            href="'.route('sales-order.print', $row->id).'">

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
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'cekbok', 'sales_order_date', 'total', 'customer'])
                ->make(true);
        }

        $stats = $this->getStatistics($query);
        $x = [
            'title' => 'Sales Order List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Sales Order', 'url' => ''],
            ],
            'totalPurchase' => $stats['totalPurchase'],
            'partiallyReceived' => $stats['partiallyReceived'],
            'grandTotal' => $stats['grandTotal'],
            'completedReceived' => $stats['completedReceived'],
        ];

        return view('sales.salesOrder.sales_order_index', $x);
    }

    private function getStatistics($query)
    {
        $month = now()->month;
        $year = now()->year;

        return [
            'totalPurchase' => SalesOrder::where('active', '<>', 0)
                ->whereMonth('sales_order_date', $month)
                ->count(),

            'partiallyReceived' => SalesOrder::where('status', 'partially_received')
                ->whereMonth('sales_order_date', $month)
                ->count(),

            'grandTotal' => SalesOrder::where('active', '<>', 0)
                ->whereMonth('sales_order_date', $month)
                ->whereYear('sales_order_date', $year)
                ->whereNotIn('status', ['rejected', 'draft'])
                ->sum('grand_total'),

            'completedReceived' => SalesOrder::where('status', 'completed')
                ->whereMonth('sales_order_date', $month)
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

        $prefix = "SO/{$tahun}/{$bulanRomawi}/";

        $last = SalesOrder::where('sales_order_code', 'like', $prefix.'%')
            ->orderByRaw("
            CAST(
                REGEXP_REPLACE(
                    SUBSTRING_INDEX(sales_order_code,'/',-1),
                    '[^0-9]',
                    ''
                ) AS UNSIGNED
            ) DESC
        ")
            ->first();

        if ($last) {
            preg_match('/(\d+)/', substr($last->sales_order_code, strrpos($last->sales_order_code, '/') + 1), $match);
            $lastNumber = isset($match[1]) ? (int) $match[1] : 0;
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function create(Request $r)
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
            'title' => 'Sales Order New',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Sales Order', 'url' => ''],
            ],
            'customer' => Customer::where('status', '<>', 0)->get(),
            'idNumber' => $this->generateNumberId(),
            'product' => Barang::where('status', '<>', 0)->get(),
            'warehouse' => Warehouse::where('status', '<>', 0)->get(),
            'paymentTerm' => SyaratPembayaran::where('status', '<>', 0)->get(),
            'salesman' => User::where('status', '<>', 0)->get(),
            'shipping' => Shipping::where('status', 1)->get(),
            'fob' => BasicCodeDetail::where('master_id', 7)->get(),
            'taxes' => $taxes,
            'defaultTax' => $defaultTax,
            'company' => $company->defaultCurrency,

        ];

        return view('sales.salesOrder.sales_order_create', $x);
    }

    public function store(SalesOrderRequest $request)
    {
        DB::beginTransaction();

        try {
            $currentYear = date('Y');
            $data = $request->validated();
            $itemsDetailRaw = $request->input('items_detail');
            unset($data['items_detail']);

            // Persiapan data header Sales Order
            $data['created_by'] = Auth::id();
            $data['sales_order_date'] = Carbon::parse($request->sales_order_date)->format('Y-m-d');
            $data['tanggal_pengiriman'] = Carbon::parse($request->shipping_date)->format('Y-m-d');
            $data['kena_pajak'] = $request->has('kena_pajak') ? 1 : 0;
            $data['total_termasuk_pajak'] = $request->has('total_termasuk_pajak') ? 1 : 0;
            $data['sub_total'] = $request->sub_total;
            $data['disc_percent'] = $request->percent;
            $data['disc_nominal'] = $request->discount_all;
            $data['grand_total'] = $request->total_order;
            $data['taxpayer_data'] = $request->taxpayer_data;
            $data['tax_id'] = $request->tax_id;
            $data['tax_amount'] = $request->tax_amount;
            // Generate kode SO
            $salesOrder = null;
            $maxRetry = 10;
            $currentCode = $request->sales_order_code; // Ambil input awal dari user

            for ($attempt = 1; $attempt <= $maxRetry; $attempt++) {
                try {
                    $data['sales_order_code'] = $currentCode;
                    $salesOrder = SalesOrder::create($data);
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

            if (! $salesOrder) {
                throw new \Exception('Gagal membuat Sales Order: Nomor sudah penuh atau sistem sibuk.');
            }
            if ($itemsDetailRaw) {
                $items = json_decode($itemsDetailRaw, true);
                $involvedSqIds = [];

                if (is_array($items) && count($items) > 0) {
                    foreach ($items as $index => $item) {
                        $sqDetailId = $item['sales_quotation_detail_id'] ?? $item['detail_id'] ?? null;
                        $qtyInputForm = floatval($item['quantity'] ?? $item['qty'] ?? 0);
                        $unitPrice = floatval($item['unit_price'] ?? 0);
                        $discount = floatval($item['discount'] ?? 0);
                        $discountPercent = $item['discount_percent'] ?? 0;
                        $amount = ($qtyInputForm * $unitPrice) - $discount;
                        $doDetailId = $item['sales_quotation_detail_id'] ?? $item['detail_id'] ?? null;

                        // 1. Simpan ke Sales Order Detail
                        $detail = SalesOrderDetail::create([
                            'sales_order_id' => $salesOrder->id,
                            'sales_quotation_detail_id' => $sqDetailId,
                            'product_id' => $item['product_id'],
                            'qty' => $qtyInputForm,
                            'unit_id' => $item['unit_id'],
                            'warehouse_id' => ! empty($item['warehouse_id']) ? $item['warehouse_id'] : null,
                            'unit_price' => $unitPrice,
                            'discount_percent' => $discountPercent,
                            'discount' => $discount,
                            'amount' => $item['amount'] ?? $amount,
                            'urutan' => $index, // 🔥 INI KUNCI NYA
                            // 'so_qty' => 0, // Sinkronisasi: sq_qty di SO = qty SO
                            'outstanding_qty' => $qtyInputForm, // Karena SO adalah tahap akhir, outstanding di SO biasanya 0
                            'status' => 'open',
                            'active' => 1,
                            'created_by' => Auth::id(),
                        ]);

                      

                        // 2. Sinkronisasi ke Sales Quotation Detail (PR)
                        if ($sqDetailId) {
                            $sqDetail = DB::table("sales_quotation_detail_{$currentYear}")->where('id', $sqDetailId)->first();

                            if ($sqDetail) {
                                // Hitung total akumulasi qty yang sudah masuk SO untuk item ini
                                $totalSoForThisItem = SalesOrderDetail::where('sales_quotation_detail_id', $sqDetailId)
                                    ->where('active', 1)
                                    ->sum('qty');

                                // Update sq_qty dan outstanding_qty di SQ Detail
                                $newOutstanding = max(0, ($sqDetail->qty - $totalSoForThisItem));

                                DB::table("sales_quotation_detail_{$currentYear}")
                                    ->where('id', $sqDetailId)
                                    ->update([
                                        'sq_qty' => $totalSoForThisItem,
                                        'outstanding_qty' => $newOutstanding,
                                    ]);

                                if (! in_array($sqDetail->sales_quotation_id, $involvedSqIds)) {
                                    $involvedSqIds[] = $sqDetail->sales_quotation_id;
                                }
                            }
                        }
                    }

                    // 3. Otomasi status Sales Quotation Master
                    foreach ($involvedSqIds as $sqId) {
                        $allDetails = DB::table("sales_quotation_detail_{$currentYear}")
                            ->where('sales_quotation_id', $sqId)
                            ->get();

                        $totalRequested = $allDetails->sum('qty');
                        $totalOrdered = $allDetails->sum('sq_qty');

                        if ($totalOrdered >= $totalRequested) {
                            $newStatus = 'closed';
                        } elseif ($totalOrdered > 0) {
                            $newStatus = 'partial';
                        } else {
                            $newStatus = 'processing';
                        }

                        DB::table("sales_quotation_{$currentYear}")
                            ->where('id', $sqId)
                            ->update(['status' => $newStatus]);
                    }
                }
            }

            DB::commit();

            $redirectUrl = $request->save_and_new == 1
                ? route('sales-order.create') // Kembali kosongkan form untuk input data PR baru lagi
                : route('sales-order.index');  // Selesai dan kembali ke tabel index utama

            return response()->json([
                'success' => true,
                'message' => 'Sales Order saved successfully!',
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
        // Mengambil tahun berjalan untuk tabel dinamis
        $year = date('Y');

        // 1. Load data PO beserta relasinya
        // Pastikan model PurchaseOrder dan Detail sudah mendukung table name dinamis jika diperlukan
        $salesOrder = SalesOrder::with([
            'salesQuotation',
            'details.produkID',
            'details.unitID',
            'details.salesQuotationDetail.quotation',
        ])->findOrFail($id);

        // 2. Cek status PO global: Apakah mengandung minimal satu item hasil serapan PR?
        $isFromPR = $salesOrder->details->whereNotNull('sales_quotation_detail_id')->count() > 0;

        // 3. Mapping data detail
        $detailDataMapped = $salesOrder->details->map(function ($detail) use ($salesOrder, $year) {

            $quotationCode = null;
            $sisaPr = null;
            $kuotaAsliPr = null;
            $totalDiambilLainnya = 0;

            // Cek apakah item detail ini memiliki keterikatan dengan PR
            if ($detail->sales_quotation_detail_id) {
                // Ambil data referensi dari relasi
                $prDetail = $detail->salesQuotationDetail;

                if ($prDetail) {
                    $sisaPr = (float) $prDetail->outstanding_qty;
                    $kuotaAsliPr = (float) $prDetail->qty;

                    // HITUNG TOTAL YANG SUDAH DIAMBIL DI PO LAIN
                    // Menggunakan DB::table karena tabel bersifat dinamis per tahun
                    $totalDiambilLainnya = DB::table("sales_order_detail_{$year}")
                        ->where('sales_quotation_detail_id', $detail->sales_quotation_detail_id)
                        ->where('sales_order_id', '<>', $salesOrder->id) // Kecuali PO ini sendiri
                        ->where('active', 1)
                        ->sum('qty');

                    if ($prDetail->salesQuotation) {
                        $quotationCode = $prDetail->salesQuotation->code;
                    }
                }
            }

            return [
                'id' => $detail->id,
                'sales_order_id' => $detail->sales_order_id,
                'sales_quotation_detail_id' => $detail->sales_quotation_detail_id,
                'quotation_code' => $quotationCode,
                'product_id' => $detail->product_id,
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
        $taxes = Tax::where('is_active', true)
            ->whereIn('usage', ['purchase', 'both'])
            ->get();

        // 🔥 Ambil default tax (misalnya PPN)
        $defaultTax = Tax::where('is_active', true)
            ->where('is_default', true)
            ->whereIn('usage', ['purchase', 'both'])
            ->first();
        $status = ['processing', 'partial'];
        $x = [
            'title' => 'Edit Sales Order ',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Edit Sales Order', 'url' => ''],
            ],
            'customer' => Customer::where('status', '<>', 0)->get(),
            'idNumber' => $this->generateNumberId(),
            'product' => Barang::where('status', '<>', 0)->get(),
            'warehouse' => Warehouse::where('status', 1)->get(),
            'paymentTerm' => SyaratPembayaran::where('status', '<>', 0)->get(),
            'salesman' => User::where('status', '<>', 0)->get(),
            'shipping' => Shipping::where('status', 1)->get(),
            'fob' => BasicCodeDetail::where('master_id', 7)->get(),
            'model' => $salesOrder,
            'isFromPR' => $isFromPR,
            'jsonDetails' => $detailDataMapped,
            'taxes' => $taxes,
            'defaultTax' => $defaultTax,
        ];

        return view('sales.salesOrder.sales_order_edit', $x);
    }

    public function update(SalesOrderRequest $request, $id)
    {
        $validated = $request->validated();

        DB::beginTransaction();

        try {
            $currentYear = date('Y');
            // 1. Cek data master
            $salesOrder = DB::table("sales_order_{$currentYear}")->where('id', $id)->first();
            if (! $salesOrder) {
                throw new \Exception('Sales Order tidak ditemukan.');
            }
            $code = $request->sales_order_code;

            while (
                SalesOrder::where('sales_order_code', $code)
                    ->where('id', '!=', $salesOrder->id)
                    ->exists()
            ) {
                $code = $this->generateNumberId();
            }
            // 2. UPDATE MASTER
            SalesOrder::where('id', $id)->update([
                'customer_id' => $request->customer_id,
                'sales_order_code' => $request->sales_order_code,
                'salesman_id' => $request->salesman_id,
                'sales_order_date' => Carbon::parse($request->sales_order_date)->format('Y-m-d'),
                'tanggal_pengiriman' => Carbon::parse($request->shipping_date)->format('Y-m-d'),
                'sub_total' => $request->sub_total,
                'disc_percent' => $request->percent,
                'disc_nominal' => $request->discount_all,
                'grand_total' => $request->total_order,
                'jenis_pengiriman' => $request->jenis_pengiriman,
                'kena_pajak' => $request->has('kena_pajak') ? 1 : 0,
                'total_termasuk_pajak' => $request->has('total_termasuk_pajak') ? 1 : 0,
                'fob_id' => $request->fob_id,
                'address' => $request->address,
                'description' => $request->description,
                'taxpayer_data' => $request->taxpayer_data,
                'tax_id' => $request->tax_id,
                'tax_amount' => $request->tax_amount,
                'updated_by' => Auth::id(),
                'updated_at' => now(),
            ]);

            // 3. DECODE ITEMS
            $items = json_decode($request->items_detail, true);
            if (! is_array($items) || count($items) == 0) {
                throw new \Exception('Detail item tidak boleh kosong.');
            }

            // 4. REVERT QTY LAMA (Kembalikan stok/kuota ke SQ Detail)
            $oldDetails = DB::table("sales_order_detail_{$currentYear}")->where('sales_order_id', $id)->get();
            foreach ($oldDetails as $old) {
                if ($old->sales_quotation_detail_id) {
                    DB::table("sales_quotation_detail_{$currentYear}")
                        ->where('id', $old->sales_quotation_detail_id)
                        ->update([
                            'sq_qty' => DB::raw("sq_qty - {$old->qty}"),
                        ]);
                }
            }

            // 5. HAPUS DETAIL LAMA
            DB::table("sales_order_detail_{$currentYear}")->where('sales_order_id', $id)->delete();

            // 6. SIMPAN DETAIL BARU
            $affectedSqIds = [];
            foreach ($items as $index => $item) {
                $sqDetailId = (! empty($item['sales_quotation_detail_id']) && $item['sales_quotation_detail_id'] != 'null')
                            ? $item['sales_quotation_detail_id'] : null;
                $qty = floatval($item['quantity'] ?? $item['qty'] ?? 0);

                DB::table("sales_order_detail_{$currentYear}")->insert([
                    'sales_order_id' => $id,
                    'sales_quotation_detail_id' => $sqDetailId,
                    'product_id' => $item['product_id'],
                    'qty' => $qty,
                    'unit_id' => $item['unit_id'],
                    'warehouse_id' => $item['warehouse_id'],
                    'unit_price' => floatval($item['unit_price'] ?? 0),
                    'discount_percent' => $item['discount_percent'] ?? 0,
                    'discount' => floatval($item['discount'] ?? 0),
                    'amount' => $item['amount'] ?? 0,
                    'urutan' => $index,
                    // 'so_qty' => 0, // Sinkronisasi: SO sudah menyerap qty ini
                    'outstanding_qty' => $qty,    // SO adalah tahap akhir
                    'active' => 1,
                    'created_by' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($sqDetailId) {
                    DB::table("sales_quotation_detail_{$currentYear}")
                        ->where('id', $sqDetailId)
                        ->update(['sq_qty' => DB::raw("sq_qty + {$qty}")]);
                    $affectedSqIds[] = $sqDetailId;
                }
            }

            // 7. UPDATE STATUS MASTER QUOTATION
            $affectedMasterSqIds = [];
            foreach (array_unique($affectedSqIds) as $sqDetailId) {
                $sqDetail = DB::table("sales_quotation_detail_{$currentYear}")->where('id', $sqDetailId)->first();
                if ($sqDetail) {
                    $outstanding = max(0, $sqDetail->qty - $sqDetail->sq_qty);
                    DB::table("sales_quotation_detail_{$currentYear}")->where('id', $sqDetailId)->update([
                        'outstanding_qty' => $outstanding,
                    ]);
                    $affectedMasterSqIds[] = $sqDetail->sales_quotation_id;
                }
            }

            foreach (array_unique($affectedMasterSqIds) as $sqId) {
                $details = DB::table("sales_quotation_detail_{$currentYear}")->where('sales_quotation_id', $sqId)->get();
                $allCompleted = $details->every(fn ($d) => $d->sq_qty >= $d->qty);
                $anyPartial = $details->some(fn ($d) => $d->sq_qty > 0);

                DB::table("sales_quotation_{$currentYear}")->where('id', $sqId)->update([
                    'status' => $allCompleted ? 'closed' : ($anyPartial ? 'partial' : 'processing'),
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Sales Order berhasil diupdate',
                'redirect' => route('sales-order.index'),
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
            // 1. Cari SO yang akan dihapus
            $po = SalesOrder::findOrFail($id);

            // 2. Ambil detail SO untuk mendapatkan referensi PR Detail yang terkait
            $sqDetails = SalesOrderDetail::where('sales_order_id', $po->id)->get();
            $involvedPrIds = [];

            foreach ($sqDetails as $sqDetail) {
                if ($sqDetail->sales_quotation_detail_id) {
                    // Catat ID PR Master-nya
                    $prDetail = SalesQuotationDetail::where('id', $sqDetail->sales_quotation_detail_id)
                        ->first();

                    if ($prDetail && ! in_array($prDetail->sales_quotation_id, $involvedPrIds)) {
                        $involvedPrIds[] = $prDetail->sales_quotation_id;
                    }
                }
            }

            // 3. Nonaktifkan SO dan Detail SO
            $po->update(['active' => 0, 'updated_by' => Auth::id()]);
            SalesOrderDetail::where('sales_order_id', $po->id)->update(['active' => 0]);

            // 4. Update Ulang sq_qty di setiap PR Detail yang terdampak
            // Kita hitung ulang berdasarkan sisa SO yang masih 'active' = 1
            foreach ($sqDetails as $sqDetail) {
                if ($sqDetail->sales_quotation_detail_id) {

                    // Hitung ulang total qty SO yang masih aktif
                    $totalRemainingSo = SalesOrderDetail::where('sales_quotation_detail_id', $sqDetail->sales_quotation_detail_id)
                        ->where('active', 1)
                        ->sum('qty');

                    // Ambil data SQ Detail
                    $quotationDetail = SalesQuotationDetail::find($sqDetail->sales_quotation_detail_id);

                    if ($quotationDetail) {

                        $outstandingQty = $quotationDetail->qty - $totalRemainingSo;

                        // Hindari minus
                        if ($outstandingQty < 0) {
                            $outstandingQty = 0;
                        }

                        $quotationDetail->update([
                            'sq_qty' => $totalRemainingSo,
                            'outstanding_qty' => $outstandingQty,
                        ]);
                    }
                }
            }

            // 5. Update Status PR Master
            foreach ($involvedPrIds as $prId) {
                $allDetails = SalesQuotationDetail::where('sales_quotation_id', $prId)
                    ->get();

                $totalRequested = $allDetails->sum('qty');
                $totalOrdered = $allDetails->sum('sq_qty');

                if ($totalOrdered >= $totalRequested) {
                    $status = 'closed';
                } elseif ($totalOrdered > 0) {
                    $status = 'partial';
                } else {
                    $status = 'processing';
                }

                SalesQuotation::where('id', $prId)
                    ->update(['status' => $status]);
            }

            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'SO berhasil dibatalkan.'], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['status' => 'error', 'message' => 'Gagal membatalkan SO: '.$e->getMessage()], 500);
        }
    }

    public function trash(Request $r)
    {
        if ($r->ajax()) {
            $query = SalesOrder::where('active', '0')
                ->orderby('sales_order_code', 'desc')->get();

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
                ->addColumn('sales_order_date', function ($row) {
                    return $row->sales_order_date ? Carbon::parse($row->sales_order_date)->format('d M Y') : 'N/A';
                })
                ->addColumn('customer', function ($row) {
                    return $row->customerID->nama_customer ?? 'N/A';
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
                            $text = 'Partial SO';
                            break;

                        case 'closed':
                            $badge = 'bg-success';
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
                        auth()->user()->can('sales_order-delete') &&
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
                ->addColumn('total', function ($row) {
                    return format_uang(convert_currency($row->grand_total, $row->currency_id ?? 1));
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">
                      <button type="button" class="btn btn-primary dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-menu-2 ti-xs me-1"></i>
                      </button>
                      <ul class="dropdown-menu" style="">';

                    if (auth()->user()->can('sales_order-restore')) {
                        $btn .= '<a class="dropdown-item restore" href="javascript:void(0)"
                            data-id="'.$row->id.'"> <i class="ti ti-trash-off me-1"></i> Restore</a>';
                    }

                    return $btn;
                })
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'cekbok', 'sales_order_date', 'total', 'customer'])
                ->make(true);
        }

        $x = [
            'title' => 'Deleted Sales Order List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Deleted Sales Order', 'url' => ''],
            ],
        ];

        return view('sales.salesOrder.sales_order_trash', $x);
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
            $sqDetails = SalesOrderDetail::whereIn('sales_order_id', $ids)->get();
            $involvedPrIds = [];

            // 2. Tandai SO dan Detail SO sebagai tidak aktif (active = 0)
            SalesOrder::whereIn('id', $ids)->update([
                'active' => 0,
                'updated_by' => Auth::id(),
            ]);
            SalesOrderDetail::whereIn('sales_order_id', $ids)->update(['active' => 0]);

            // 3. Update sq_qty di PR Detail dan kumpulkan ID PR Master
            foreach ($sqDetails as $sqDetail) {

                if (! $sqDetail->sales_quotation_detail_id) {
                    continue;
                }

                // Hitung total qty SO yang masih aktif
                $totalSo = SalesOrderDetail::where('sales_quotation_detail_id', $sqDetail->sales_quotation_detail_id)
                    ->where('active', 1)
                    ->sum('qty');

                // Ambil Sales Quotation Detail
                $quotationDetail = DB::table('sales_quotation_detail_'.date('Y'))
                    ->where('id', $sqDetail->sales_quotation_detail_id)
                    ->first();

                if (! $quotationDetail) {
                    continue;
                }

                // Hitung ulang outstanding
                $outstandingQty = max(0, $quotationDetail->qty - $totalSo);

                // Update SQ Detail
                DB::table('sales_quotation_detail_'.date('Y'))
                    ->where('id', $quotationDetail->id)
                    ->update([
                        'sq_qty' => $totalSo,
                        'outstanding_qty' => $outstandingQty,
                    ]);

                // Simpan ID Sales Quotation untuk update status
                if (! in_array($quotationDetail->sales_quotation_id, $involvedPrIds)) {
                    $involvedPrIds[] = $quotationDetail->sales_quotation_id;
                }
            }

            // 4. Update Status PR Master berdasarkan akumulasi terbaru
            foreach ($involvedPrIds as $quotationId) {

                $details = DB::table('sales_quotation_detail_'.date('Y'))
                    ->where('sales_quotation_id', $quotationId)
                    ->get();

                $totalQty = $details->sum('qty');
                $totalSqQty = $details->sum('sq_qty');

                if ($totalSqQty >= $totalQty) {
                    $status = 'closed';
                } elseif ($totalSqQty > 0) {
                    $status = 'partial';
                } else {
                    $status = 'processing';
                }

                DB::table('sales_quotation_'.date('Y'))
                    ->where('id', $quotationId)
                    ->update([
                        'status' => $status,
                    ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sales Order berhasil dihapus dan status PR telah diperbarui.',
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
            // 1. Aktifkan kembali SO
            $po = SalesOrder::findOrFail($id);
            $po->update(['active' => 1, 'updated_by' => Auth::id()]);

            // 2. Aktifkan kembali Detail SO
            SalesOrderDetail::where('sales_order_id', $po->id)->update(['active' => 1]);

            // 3. Ambil semua detail SO yang baru saja diaktifkan
            $poDetails = SalesOrderDetail::where('sales_order_id', $po->id)->get();
            $involvedPrIds = [];

            // 4. Update ulang sq_qty di PR Detail
            foreach ($poDetails as $poDetail) {
                if ($poDetail->sales_quotation_detail_id) {
                    // Hitung total dari semua SO yang aktif
                    $totalPoForThisItem = SalesOrderDetail::where('sales_quotation_detail_id', $poDetail->sales_quotation_detail_id)
                        ->where('active', 1)
                        ->sum('qty');

                    // Update ke tabel PR Detail
                    $sqDetail = DB::table('sales_quotation_detail_'.date('Y'))
                        ->where('id', $poDetail->sales_quotation_detail_id)
                        ->first();

                    if ($sqDetail) {

                        $outstandingQty = $sqDetail->qty - $totalPoForThisItem;

                        // Hindari nilai minus
                        if ($outstandingQty < 0) {
                            $outstandingQty = 0;
                        }

                        DB::table('sales_quotation_detail_'.date('Y'))
                            ->where('id', $poDetail->sales_quotation_detail_id)
                            ->update([
                                'sq_qty' => $totalPoForThisItem,
                                'outstanding_qty' => $outstandingQty,
                            ]);

                        // Simpan SQ ID untuk update status
                        if (! in_array($sqDetail->sales_quotation_id, $involvedPrIds)) {
                            $involvedPrIds[] = $sqDetail->sales_quotation_id;
                        }
                    }

                    // Simpan ID PR untuk update status
                    $prDetail = DB::table('sales_quotation_detail_'.date('Y'))
                        ->where('id', $poDetail->sales_quotation_detail_id)
                        ->first();

                    if ($prDetail && ! in_array($prDetail->sales_quotation_id, $involvedPrIds)) {
                        $involvedPrIds[] = $prDetail->sales_quotation_id;
                    }
                }
            }

            // 5. Update Status PR Master
            foreach ($involvedPrIds as $prId) {
                $allDetails = DB::table('sales_quotation_detail_'.date('Y'))
                    ->where('sales_quotation_id', $prId)
                    ->get();

                $totalRequested = $allDetails->sum('qty');
                $totalOrdered = $allDetails->sum('sq_qty');

                if ($totalOrdered >= $totalRequested) {
                    $status = 'closed';
                } elseif ($totalOrdered > 0) {
                    $status = 'partial';
                } else {
                    $status = 'processing';
                }

                DB::table('sales_quotation_'.date('Y'))
                    ->where('id', $prId)
                    ->update(['status' => $status]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sales Order berhasil dikembalikan (restored).',
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

            // 1. Update status SO jadi aktif
            SalesOrder::whereIn('id', $ids)->update([
                'active' => 1,
                'updated_by' => Auth::id(),
            ]);

            // 2. Aktifkan kembali semua detail SO yang berkaitan dengan SO-SO tersebut
            SalesOrderDetail::whereIn('sales_order_id', $ids)->update(['active' => 1]);

            // 3. Ambil semua detail SO yang baru saja diaktifkan untuk sinkronisasi
            $poDetails = SalesOrderDetail::whereIn('sales_order_id', $ids)->get();
            $involvedPrIds = [];

            // 4. Update sq_qty di PR Detail dan kumpulkan ID PR Master
            foreach ($poDetails as $poDetail) {

                if (! $poDetail->sales_quotation_detail_id) {
                    continue;
                }

                // Hitung total qty SO aktif untuk SQ Detail ini
                $totalSo = SalesOrderDetail::where('sales_quotation_detail_id', $poDetail->sales_quotation_detail_id)
                    ->where('active', 1)
                    ->sum('qty');

                // Ambil SQ Detail
                $sqDetail = DB::table('sales_quotation_detail_'.date('Y'))
                    ->where('id', $poDetail->sales_quotation_detail_id)
                    ->first();

                if (! $sqDetail) {
                    continue;
                }

                // Hitung outstanding
                $outstandingQty = max(0, $sqDetail->qty - $totalSo);

                // Update SQ Detail
                DB::table('sales_quotation_detail_'.date('Y'))
                    ->where('id', $sqDetail->id)
                    ->update([
                        'sq_qty' => $totalSo,
                        'outstanding_qty' => $outstandingQty,
                    ]);

                // Simpan Sales Quotation ID untuk update status
                if (! in_array($sqDetail->sales_quotation_id, $involvedPrIds)) {
                    $involvedPrIds[] = $sqDetail->sales_quotation_id;
                }
            }

            // 5. Update Status PR Master berdasarkan akumulasi terbaru
            foreach ($involvedPrIds as $quotationId) {

                $details = DB::table('sales_quotation_detail_'.date('Y'))
                    ->where('sales_quotation_id', $quotationId)
                    ->get();

                $totalQty = $details->sum('qty');
                $totalSqQty = $details->sum('sq_qty');

                if ($totalSqQty >= $totalQty) {
                    $status = 'closed';
                } elseif ($totalSqQty > 0) {
                    $status = 'partial';
                } else {
                    $status = 'processing';
                }

                DB::table('sales_quotation_'.date('Y'))
                    ->where('id', $quotationId)
                    ->update([
                        'status' => $status,
                    ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sales Order terpilih berhasil dikembalikan.',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal merestore data: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getProcessingData(Request $request)
    {
        $orders = SalesQuotation::with([
            'details' => function ($query) {
                $query->whereColumn('sq_qty', '<', 'qty');
            },
        ])
            ->where('customer_id', $request->customer_id)
            ->whereNotIn('status', ['draft', 'closed', 'done'])
            ->get();

        return response()->json($orders);
    }

    public function getQuotationDetail(Request $request)
    {
        $year = date('Y');

        $ids = $request->quotation_ids;

        $details = DB::table("sales_quotation_detail_$year as d")
            ->join("sales_quotation_$year as q", 'q.id', '=', 'd.sales_quotation_id')
            ->join('data_barang as b', 'b.id', '=', 'd.product_id')
            ->join('basic_code_detail as u', 'u.id', '=', 'd.unit_id')
            ->select(
                'd.id',
                'd.sales_quotation_id',
                'q.sales_quotation_code',
                'd.product_id',
                'b.nama_barang',
                'd.outstanding_qty',
                'd.unit_price',
                'd.discount',
                'd.discount_percent',
                'd.amount',
                'u.detail as unit_name',
                'd.unit_id'
            )
            ->whereIn('d.sales_quotation_id', $ids)
            ->where('d.outstanding_qty', '<>', 0)
            ->where('d.active', 1)
            ->get();

        return response()->json($details);
    }

    // public function sendSupplier($id)
    // {
    //     $po = SalesOrder::findOrFail($id);

    //     // VALIDASI STATUS
    //     if ($po->status != 'approved') {

    //         return response()->json([
    //             'message' => 'Only approved SO can be sent.',
    //         ], 422);
    //     }

    //     // UPDATE STATUS
    //     $po->update([
    //         'status' => 'sent',
    //         'updated_by' => Auth::user()->id,
    //     ]);

    //     return response()->json([
    //         'message' => 'SO successfully sent to supplier.',
    //     ]);
    // }

    public function print($id)
    {
        $salesOrder = SalesOrder::with(['details.produkID', 'details.unitID'])->findOrFail($id);
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
            'model' => $salesOrder,
            'company' => $company,
            'modelDetail' => $salesOrder->details,
            'logoBase64' => $logoBase64,
        ];

        $pdf = Pdf::loadView('pdf.sales_order_pdf', $data)
            ->setPaper('a4', 'portrait');

        // preview di browser
        $filename = $salesOrder->sales_order_code.'-'.$salesOrder->customerID->nama_customer;

        // replace forbidden filename chars
        $filename = preg_replace('/[\/\\\\:*?"<>|]/', '-', $filename);
        $pdf->getDomPDF()->set_option('isPhpEnabled', true);

        return $pdf->stream($filename.'.pdf');

        // kalau mau download:
        // return $pdf->download('sales-order.pdf');
    }

    public function CloseDocument(Request $request, $id)
    {

        try {
            $table = SalesOrder::findOrFail($id);
            $table->status = 'closed';
            $table->updated_by = Auth::user()->id;
            $table->save();
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function getCustomerData($customerId)
    {
        // Pajak (ambil default)
        $pajak = DB::table('customer_pajak')
            ->where('customer_id', $customerId)
            ->first();
        $kontak = DB::table('customer_kontak')
            ->where('customer_id', $customerId)
            ->get();
        $customer = Customer::find($customerId);
        $address = collect([
            $customer->alamat_tagihan,
            collect([
                $customer->kota_tagihan,
                $customer->provinsi_tagihan,
                $customer->kodepos_tagihan,
            ])->filter()->implode(', '),
            $customer->negara_tagihan,
        ])->filter()->implode("\n");

        return response()->json([
            'pajak' => $pajak,
            'kontak' => $kontak,
            'address' => $address,
        ]);
    }

    public function processData($id)
    {
        // 1. Ambil tahun berjalan secara dinamis
        $year = date('Y');
        $tableName = "sales_order_{$year}";

        // 2. Gunakan Query Builder dengan nama tabel dinamis agar pencarian ID aman
        $poData = DB::table($tableName)->where('id', $id)->first();

        // Jika data memang benar-benar tidak ditemukan di database
        if (! $poData) {
            return response()->json(['success' => false, 'message' => 'Data Sales Order tidak ditemukan.'], 404);
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

        return response()->json(['success' => true, 'message' => 'Sales Order berhasil diajukan!']);
    }

    public function getQuotation($customerId)
    {
        $status = ['processing', 'partial'];

        $data = SalesQuotation::whereIn('status', $status)
            ->where('active', 1)
            ->where('customer_id', $customerId)
            ->select('id', 'sales_quotation_code')
            ->get();

        return response()->json($data);
    }

    public function getPriceHistory(Request $request)
    {
        $productId = $request->get('product_id');
        $customerId = $request->get('customer_id');

        $year = date('Y');
        $tableDetail = "sales_order_detail_{$year}";
        $tableMaster = "sales_order_{$year}";

        // Mengambil harga unik langsung dari database
        $history = DB::table($tableDetail)
            ->join($tableMaster, "{$tableDetail}.sales_order_id", '=', "{$tableMaster}.id")
            ->where("{$tableDetail}.product_id", $productId)
            ->where("{$tableMaster}.customer_id", $customerId)
            ->select(
                "{$tableDetail}.unit_price as harga", // Pastikan nama kolom benar
                DB::raw("MAX({$tableMaster}.sales_order_date) as tanggal")
            )
            ->groupBy("{$tableDetail}.unit_price")
            ->orderBy('tanggal', 'desc')
            ->limit(5)
            ->get();
        // $history = DB::table($tableDetail)
        //     ->join($tableMaster, "{$tableDetail}.sales_quotation_id", '=', "{$tableMaster}.id")
        //     ->where("{$tableDetail}.product_id", $productId)
        //     ->where("{$tableMaster}.customer_id", $customerId)
        //     // Kuncinya di sini: kelompokkan berdasarkan harga, lalu ambil tanggal terbaru dengan MAX()
        //     ->select(
        //         "{$tableDetail}.unit_price as harga",
        //         DB::raw("MAX({$tableMaster}.sales_quotation_date) as tanggal")
        //     )
        //     ->groupBy("{$tableDetail}.unit_price")
        //     // Urutkan berdasarkan tanggal terbaru (hasil dari MAX tanggal di atas)
        //     ->orderBy('tanggal', 'desc')
        //     ->limit(5)
        //     ->get();

        return response()->json([
            'success' => true,
            'history' => $history,
        ]);
    }
}
