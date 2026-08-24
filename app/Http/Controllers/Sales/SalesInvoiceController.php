<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalesInvoiceRequest;
use App\Models\BasicCodeDetail;
use App\Models\DocumentTransactionHistory;
use App\Models\Inventory\Barang;
use App\Models\Inventory\DataBarangConversion;
use App\Models\Inventory\Warehouse;
use App\Models\Sales\ArApHistory;
use App\Models\Sales\Customer;
use App\Models\Sales\DeliveryOrder;
use App\Models\Sales\DeliveryOrderDetail;
use App\Models\Sales\ProformaInvoice;
use App\Models\Sales\SalesDownPayment;
use App\Models\Sales\SalesInvoice;
use App\Models\Sales\SalesInvoiceDetail;
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
use Yajra\DataTables\Facades\DataTables;

class SalesInvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $routeName = $request->route()->getName();

            $permissionMap = [
                'sales-invoice.index' => 'sales_invoice-browse',
                'sales-invoice.show' => 'sales_invoice-read',
                'sales-invoice.create' => 'sales_invoice-create',
                'sales-invoice.store' => 'sales_invoice-create',
                'sales-invoice.edit' => 'sales_invoice-edit',
                'sales-invoice.update' => 'sales_invoice-edit',
                'sales-invoice.destroy' => 'sales_invoice-delete',
                'sales-invoice.trash' => 'sales_invoice-trash',
                'sales-invoice.restore' => 'sales_invoice-restore',
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
            $query = SalesInvoice::where('active', '<>', 0)
                ->where(function ($q) use ($userId) {
                    $q->where('status', '<>', 'draft')
                        ->orWhere(function ($subQ) use ($userId) {
                            $subQ->where('status', 'draft')
                                ->where('created_by', $userId);
                        });
                })
                ->orderBy('sales_invoice_code', 'desc');
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
                ->addColumn('sales_invoice_date', function ($row) {
                    return $row->sales_invoice_date ? Carbon::parse($row->sales_invoice_date)->format('d M Y') : 'N/A';
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
                ->addColumn('cekbok', function ($row) {

                    if (
                        auth()->user()->can('sales_invoice-delete') &&
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
                                    Send To Processing
                                </a>
                            ';
                            $btn .= '<hr class="dropdown-divider">';

                        }

                    }
                    // EDIT
                    if (
                        $user->can('sales_invoice-edit') &&
                        in_array($row->status, ['draft', 'pending', 'processing'])
                    ) {

                        $btn .= '
                                <a class="dropdown-item"
                                    href="'.route('sales-invoice.edit', $row->id).'">

                                    <i class="far fa-edit me-1"></i>
                                    Edit
                                </a>
                            ';
                    }

                    // DELETE
                    if (
                        $user->can('sales_invoice-delete') &&
                        in_array($row->status, ['draft', 'pending', 'processing'])
                    ) {

                        $btn .= '
                                <a class="dropdown-item text-danger"
                                    href="javascript:void(0)"
                                    id="delete"
                                    data-id="'.$row->id.'"
                                    data-name="'.$row->sales_invoice_code.'">

                                    <i class="ti ti-trash me-1"></i>
                                    Delete
                                </a>
                            ';
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | 5. CANCEL SO
                    |--------------------------------------------------------------------------
                    */

                    if (
                        ! in_array($row->status, ['processing', 'draft'])
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
                    if ($row->status != 'closed') {
                        $btn .= '<a class="dropdown-item"
                href="javascript:void(0)" id="close"   data-id="'.$row->id.'" data-name="'.$row->sales_invoice_code.'">
                <i class="ti ti-lock"></i> Close
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
            href="'.route('sales-invoice.print', $row->id).'">

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
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'cekbok', 'sales_invoice_date', 'total', 'customer'])
                ->make(true);
        }

        $x = [
            'title' => 'Sales Invoice List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Sales Invoice', 'url' => ''],
            ],
        ];

        return view('sales.salesInvoice.sales_invoice_index', $x);
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

        $prefix = "SI/{$tahun}/{$bulanRomawi}/";

        $last = SalesInvoice::where('sales_invoice_code', 'like', $prefix.'%')
            ->orderByRaw("
            CAST(
                REGEXP_REPLACE(
                    SUBSTRING_INDEX(sales_invoice_code,'/',-1),
                    '[^0-9]',
                    ''
                ) AS UNSIGNED
            ) DESC
        ")
            ->first();

        if ($last) {
            preg_match('/(\d+)/', substr($last->sales_invoice_code, strrpos($last->sales_invoice_code, '/') + 1), $match);
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

        $x = [
            'title' => 'Sales Invoice New',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Sales Invoice', 'url' => ''],
            ],
            'customer' => Customer::where('status', '<>', 0)->get(),
            'idNumber' => $this->generateNumberId(),
            'product' => Barang::where('status', '<>', 0)->get(),
            'warehouse' => Warehouse::where('status', 1)->get(),
            'paymentTerm' => SyaratPembayaran::where('status', '<>', 0)->get(),
            'salesman' => User::where('status', '<>', 0)->get(),
            'shipping' => Shipping::where('status', 1)->get(),
            'fob' => BasicCodeDetail::where('master_id', 7)->get(),
            'taxes' => $taxes,
            'defaultTax' => $defaultTax,
            'company' => $company->defaultCurrency,

        ];

        return view('sales.salesInvoice.sales_invoice_create', $x);
    }

    private function parseNominal($value)
    {
        if (! $value) {
            return 0;
        }

        // format Indonesia: 99.000,00
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);

        return (float) $value;
    }

    public function store(SalesInvoiceRequest $request)
    {
        DB::beginTransaction();

        try {

            $currentYear = date('Y');

            $data = $request->validated();
            $itemsDetailRaw = $request->input('items_detail');

            unset($data['items_detail']);

            $data['created_by'] = Auth::id();
            $data['sales_invoice_date'] = Carbon::parse($request->sales_invoice_date)->format('Y-m-d');
            $data['tanggal_pengiriman'] = Carbon::parse($request->shipping_date)->format('Y-m-d');
            $data['kena_pajak'] = $request->has('kena_pajak');
            $data['total_termasuk_pajak'] = $request->has('total_termasuk_pajak');
            $data['sub_total'] = $request->sub_total;
            $data['disc_percent'] = $request->percent;
            $data['disc_nominal'] = $request->discount_all;
            $data['po_number'] = $request->po_number;
            $data['biaya_lain'] = $this->parseNominal($request->biaya_lain);
            $data['grand_total'] = $request->total_order;
            $data['taxpayer_data'] = $request->taxpayer_data;
            $data['tax_id'] = $request->tax_id;
            $data['tax_amount'] = $request->tax_amount;
            $data['total_dp'] = $this->parseNominal($request->total_dp);
            $data['sisa_pembayaran'] = $this->parseNominal($request->total_order) - $this->parseNominal($request->total_dp);
            $data['payment_type'] = $request->payment_type;
            $data['sales_order_id'] = $request->sales_order_id;
            $data['payment_type'] = $request->payment_type;
            $data['sales_order_id'] = $request->sales_order_id;

            // Jangan ambil dari request utama jika ID DP berada di items_detail
            $downPaymentId = null;

            if (! empty($itemsDetailRaw)) {

                $items = json_decode($itemsDetailRaw, true);

                if (is_array($items) && count($items)) {

                    $downPaymentId = collect($items)
                        ->where('is_down_payment', true)
                        ->pluck('sales_down_payment_id')
                        ->filter()
                        ->unique()
                        ->first();
                }
            }

            $data['sales_down_payment_id'] = $downPaymentId;

            if ($request->payment_type == 'pelunasan') {
                $data['pelunasan_id'] = $request->pelunasan_id;
                $data['proforma_id'] = null;

            } elseif ($request->payment_type == 'proforma') {
                $data['proforma_id'] = $request->proforma_id;
                $data['pelunasan_id'] = null;

            } else {
                $data['pelunasan_id'] = null;
                $data['proforma_id'] = null;
            }

            // do {
            //     $generatedCode = $this->generateNumberId();
            //     $exists = SalesInvoice::where('sales_invoice_code', $generatedCode)->exists();
            // } while ($exists);

            // Generate kode SO
            $salesInvoice = null;
            $maxRetry = 10;
            $currentCode = $request->sales_invoice_code; // Ambil input awal dari user

            for ($attempt = 1; $attempt <= $maxRetry; $attempt++) {
                try {
                    $data['sales_invoice_code'] = $currentCode;
                    $salesInvoice = SalesInvoice::create($data);
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

            if (! $salesInvoice) {
                throw new \Exception('Gagal membuat Sales Order: Nomor sudah penuh atau sistem sibuk.');
            }

            // $data['sales_invoice_code'] = $generatedCode;

            // $salesInvoice = SalesInvoice::create($data);

            if (! empty($itemsDetailRaw)) {

                $items = json_decode($itemsDetailRaw, true);

                if (is_array($items) && count($items)) {
                    $salesOrderIds = collect($items)
                        ->pluck('sales_order_id')
                        ->filter()
                        ->unique()
                        ->values();

                    // Jika semua detail berasal dari 1 Sales Order
                    $salesOrderId = $salesOrderIds->first();

                    $salesInvoice->update([
                        'sales_order_id' => $salesOrderId,
                    ]);
                    $detailIds = collect($items)
                        ->pluck('sales_order_detail_id')
                        ->filter()
                        ->values()
                        ->toArray();

                    $doDetails = DB::table("delivery_order_detail_{$currentYear}")
                        ->whereIn('id', $detailIds)
                        ->get()
                        ->keyBy('id');

                    $involvedDoIds = [];

                    foreach ($items as $index => $item) {

                        $doDetailId = $item['sales_order_detail_id'] ?? $item['detail_id'] ?? null;
                        $doId = $item['sales_order_code_id'] ?? $item['order_code'] ?? null;

                        $qty = (float) ($item['quantity'] ?? $item['qty'] ?? 0);
                        $unitPrice = (float) ($item['unit_price'] ?? 0);
                        $discount = (float) ($item['discount'] ?? 0);
                        $discountPercent = $item['discount_percent'] ?? 0;
                        $amount = $item['amount'] ?? (($qty * $unitPrice) - $discount);

                        $detail = SalesInvoiceDetail::create([
                            'sales_invoice_id' => $salesInvoice->id,
                            'sales_order_detail_id' => $doDetailId,
                            'sales_order_code_id' => $doId,
                            'product_id' => $item['product_id'],
                            'qty' => $qty,
                            'urutan' => $index,
                            'unit_id' => $item['unit_id'],
                            'warehouse_id' => $item['warehouse_id'],
                            'unit_price' => $unitPrice,
                            'discount_percent' => $discountPercent,
                            'discount' => $discount,
                            'amount' => $amount,
                            'so_qty' => $qty,
                            'outstanding_qty' => 0,
                            'active' => 1,
                            'created_by' => Auth::id(),
                        ]);

                        DocumentTransactionHistory::create([
                            'module' => 'sales',
                            'from_type' => 'DeliveryOrder',
                            'from_id' => $doDetailId,
                            'from_detail_id' => $doDetailId,
                            'to_type' => 'SalesInvoice',
                            'to_id' => $salesInvoice->id,
                            'to_detail_id' => $detail->id,
                            'transaction_type' => 'invoice',
                            'qty' => $detail->qty,
                            'unit_price' => $detail->unit_price,
                            'discount' => $detail->discount,
                            'amount' => $detail->amount,
                            'transaction_date' => $salesInvoice->sales_invoice_date,
                            'metadata' => json_encode([
                                'warehouse_id' => $detail->warehouse_id,
                                'product_id' => $detail->product_id,
                                'unit_id' => $detail->unit_id,
                            ]),
                        ]);

                        if ($doDetailId && isset($doDetails[$doDetailId])) {
                            $involvedDoIds[] = $doDetails[$doDetailId]->delivery_order_id;
                        }
                    }

                    foreach ($detailIds as $detailId) {

                        $doDetail = $doDetails[$detailId];

                        $totalInvoiceQty = SalesInvoiceDetail::where('sales_order_detail_id', $detailId)
                            ->sum('qty');

                        $outstanding = max(0, $doDetail->qty - $totalInvoiceQty);

                        DB::table("delivery_order_detail_{$currentYear}")
                            ->where('id', $detailId)
                            ->update([
                                'do_qty' => $totalInvoiceQty,
                                'outstanding_qty' => $outstanding,
                            ]);
                    }

                    foreach (array_unique($involvedDoIds) as $deliveryOrderId) {

                        $details = DB::table("delivery_order_detail_{$currentYear}")
                            ->where('delivery_order_id', $deliveryOrderId)
                            ->get();

                        $totalQty = $details->sum('qty');
                        $totalInvoice = $details->sum('do_qty');

                        if ($totalInvoice == 0) {
                            $status = 'processing';
                        } elseif ($totalInvoice < $totalQty) {
                            $status = 'partial';
                        } else {
                            $status = 'confirmed';
                        }

                        DB::table("delivery_order_{$currentYear}")
                            ->where('id', $deliveryOrderId)
                            ->update([
                                'status' => $status,
                            ]);
                    }
                }
            }

            ArApHistory::create([
                'type' => 'receivable',
                'party_id' => $salesInvoice->customer_id,
                'transaction_type' => 'invoice',
                'reference_type' => 'sales_invoice',
                'reference_id' => $salesInvoice->id,
                'document_no' => $salesInvoice->sales_invoice_code,
                'transaction_date' => $salesInvoice->sales_invoice_date,
                'debit' => $salesInvoice->grand_total,
                'credit' => 0,
            ]);

            DB::commit();

            $redirectUrl = $request->save_and_new == 1
                ? route('sales-invoice.create')
                : route('sales-invoice.index');

            return response()->json([
                'success' => true,
                'message' => 'Sales Invoice saved successfully!',
                'redirect' => $redirectUrl,
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id)
    {
        //
    }

    

    public function edit(string $id)
    {
        $year = date('Y');

        /*
        |--------------------------------------------------------------------------
        | 1. Ambil Sales Invoice beserta relasi
        |--------------------------------------------------------------------------
        */
        $salesInvoice = SalesInvoice::with([
            'salesOrder',
            'customerID',
            'paymentTermID',
            'details.produkID',
            'details.unitID',
            'details.warehouseID',
            'details.salesOrderDetail.salesOrder',
        ])->findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | 2. Cek apakah Sales Invoice ini berasal dari Sales Order
        |--------------------------------------------------------------------------
        */
        $isFromSO = $salesInvoice->details
            ->whereNotNull('sales_order_detail_id')
            ->count() > 0;

        /*
        |--------------------------------------------------------------------------
        | 3. Sales Order ID
        |--------------------------------------------------------------------------
        */
        $salesOrderId = $salesInvoice->sales_order_id;

        /*
        |--------------------------------------------------------------------------
        | 4. Mapping detail
        |--------------------------------------------------------------------------
        */
        $detailDataMapped = $salesInvoice->details
            ->sortBy('urutan')
            ->values()
            ->map(function ($detail) use ($salesInvoice, $year) {

                $orderCode = null;
                $salesOrderIdDetail = null;

                $sisaSO = null;
                $kuotaAsliSO = null;
                $totalDiambilLainnya = 0;

                /*
                |--------------------------------------------------------------------------
                | Jika detail berasal dari Sales Order
                |--------------------------------------------------------------------------
                */
                if ($detail->sales_order_detail_id) {

                    /*
                    | Ambil Sales Order ID dari Sales Order Detail
                    */
                    if ($detail->salesOrderDetail) {

                        $salesOrderIdDetail =
                            $detail->salesOrderDetail->sales_order_id;

                        /*
                        | Ambil kode Sales Order
                        */
                        if ($detail->salesOrderDetail->salesOrder) {
                            $orderCode =
                                $detail->salesOrderDetail->salesOrder->sales_order_code
                                ?? $detail->salesOrderDetail->salesOrder->code
                                ?? null;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Qty asli Sales Order
                        |--------------------------------------------------------------------------
                        */
                        $kuotaAsliSO = (float) $detail
                            ->salesOrderDetail
                            ->qty;

                        /*
                        |--------------------------------------------------------------------------
                        | Hitung qty yang sudah digunakan oleh
                        | Sales Invoice lain
                        |--------------------------------------------------------------------------
                        */
                        $totalDiambilLainnya = DB::table(
                            "sales_invoice_detail_{$year}"
                        )
                            ->where(
                                'sales_order_detail_id',
                                $detail->sales_order_detail_id
                            )
                            ->where(
                                'sales_invoice_id',
                                '<>',
                                $salesInvoice->id
                            )
                            ->where('active', 1)
                            ->sum('qty');

                        /*
                        |--------------------------------------------------------------------------
                        | Sisa Sales Order
                        |--------------------------------------------------------------------------
                        */
                        $sisaSO = max(
                            0,
                            $kuotaAsliSO - $totalDiambilLainnya
                        );
                    }
                }

                return [
                    'id' => $detail->id,

                    'sales_invoice_id' => $detail->sales_invoice_id,

                    /*
                    |--------------------------------------------------------------------------
                    | HEADER SALES ORDER
                    |--------------------------------------------------------------------------
                    */
                    'sales_order_id' => $salesOrderIdDetail,

                    'urutan' => (int) $detail->urutan,

                    /*
                    |--------------------------------------------------------------------------
                    | DETAIL SALES ORDER
                    |--------------------------------------------------------------------------
                    */
                    'sales_order_detail_id' => $detail->sales_order_detail_id,

                    'order_code' => $orderCode,

                    /*
                    |--------------------------------------------------------------------------
                    | PRODUCT
                    |--------------------------------------------------------------------------
                    */
                    'product_id' => $detail->product_id,

                    'data_produk' => $detail->produkID->nama_barang
                        ?? 'Product Not Found',

                    /*
                    |--------------------------------------------------------------------------
                    | QUANTITY
                    |--------------------------------------------------------------------------
                    */
                    'quantity' => (float) $detail->qty,

                    /*
                    |--------------------------------------------------------------------------
                    | UNIT
                    |--------------------------------------------------------------------------
                    */
                    'unit_id' => $detail->unit_id,

                    'unit' => $detail->unitID->detail
                        ?? '-',

                    /*
                    |--------------------------------------------------------------------------
                    | WAREHOUSE
                    |--------------------------------------------------------------------------
                    */
                    'warehouse_id' => $detail->warehouse_id,

                    'warehouse' => $detail->warehouseID->nama_gudang
                        ?? '-',

                    /*
                    |--------------------------------------------------------------------------
                    | PRICE
                    |--------------------------------------------------------------------------
                    */
                    'unit_price' => (float) $detail->unit_price,

                    'discount' => (float) $detail->discount,

                    'discount_percent' => $detail->discount_percent,

                    'amount' => (float) $detail->amount,

                    'tax' => (float) ($detail->tax ?? 0),

                    /*
                    |--------------------------------------------------------------------------
                    | SALES ORDER INFO
                    |--------------------------------------------------------------------------
                    */
                    'sisa_so' => $sisaSO,

                    'kuota_asli_so' => $kuotaAsliSO,

                    'total_diambil_lainnya' => (float) $totalDiambilLainnya,
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | 5. Pajak
        |--------------------------------------------------------------------------
        */
        $taxes = Tax::where('is_active', true)
            ->whereIn('usage', ['sales', 'both'])
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 6. Default tax
        |--------------------------------------------------------------------------
        */
        $defaultTax = Tax::where('is_active', true)
            ->where('is_default', true)
            ->whereIn('usage', ['sales', 'both'])
            ->first();

        /*
        |--------------------------------------------------------------------------
        | 7. Data untuk View
        |--------------------------------------------------------------------------
        */
        $x = [
            'title' => 'Edit Sales Invoice',

            'breadcrumb' => [
                [
                    'label' => 'Sales Invoice',
                    'url' => route('sales-invoice.index'),
                ],
                [
                    'label' => 'Edit Sales Invoice',
                    'url' => '',
                ],
            ],

            'customer' => Customer::where('status', '<>', 0)->get(),

            /*
            | Jangan generate nomor baru untuk EDIT
            */
            'idNumber' => $salesInvoice->sales_invoice_code,

            'product' => Barang::where('status', '<>', 0)->get(),

            'warehouse' => Warehouse::where('status', 1)->get(),

            'paymentTerm' => SyaratPembayaran::where(
                'status',
                '<>',
                0
            )->get(),

            'salesman' => User::where(
                'status',
                '<>',
                0
            )->get(),

            'shipping' => Shipping::where(
                'status',
                1
            )->get(),

            'fob' => BasicCodeDetail::where(
                'master_id',
                7
            )->get(),

            'model' => $salesInvoice,

            'isFromSO' => $isFromSO,

            'salesOrderId' => $salesOrderId,

            'jsonDetails' => $detailDataMapped,

            'taxes' => $taxes,

            'defaultTax' => $defaultTax,
        ];

        return view(
            'sales.salesInvoice.sales_invoice_edit',
            $x
        );
    }

    public function update(SalesInvoiceRequest $request, $id)
    {
        DB::beginTransaction();

        try {

            $currentYear = date('Y');

            /*
            |--------------------------------------------------------------------------
            | AMBIL SALES INVOICE
            |--------------------------------------------------------------------------
            */
            $salesInvoice = SalesInvoice::find($id);

            if (! $salesInvoice) {
                throw new \Exception('Sales Invoice tidak ditemukan.');
            }

            /*
            |--------------------------------------------------------------------------
            | VALIDASI ITEMS DETAIL
            |--------------------------------------------------------------------------
            */
            $itemsDetailRaw = $request->input('items_detail');

            if (empty($itemsDetailRaw)) {
                throw new \Exception('Detail item tidak boleh kosong.');
            }

            $items = json_decode($itemsDetailRaw, true);

            if (! is_array($items) || count($items) === 0) {
                throw new \Exception('Detail item tidak boleh kosong.');
            }

            /*
            |--------------------------------------------------------------------------
            | AMBIL SALES ORDER ID DARI ITEMS DETAIL
            |--------------------------------------------------------------------------
            |
            | Sama seperti STORE:
            |
            | items_detail
            |      ↓
            | sales_order_id
            |
            */
            $salesOrderIds = collect($items)
                ->pluck('sales_order_id')
                ->filter(function ($value) {
                    return ! empty($value);
                })
                ->map(function ($value) {
                    return (int) $value;
                })
                ->unique()
                ->values();

            /*
            |--------------------------------------------------------------------------
            | SALES ORDER ID
            |--------------------------------------------------------------------------
            |
            | Idealnya satu invoice hanya berasal dari satu Sales Order.
            |
            */
            if ($salesOrderIds->count() > 1) {
                throw new \Exception(
                    'Sales Invoice tidak dapat menggunakan lebih dari satu Sales Order.'
                );
            }

            $salesOrderId = $salesOrderIds->first();

            /*
            |--------------------------------------------------------------------------
            | UPDATE HEADER SALES INVOICE
            |--------------------------------------------------------------------------
            */
            $paymentData = [
            'sales_down_payment_id' => null,
            'proforma_id' => null,
            'pelunasan_id' => null,
        ];

        if ($request->payment_type === 'pelunasan') {

            // $paymentData['sales_down_payment_id'] = $downPaymentId;

            $paymentData['pelunasan_id'] = $request->pelunasan_id;

        } elseif ($request->payment_type === 'proforma') {

            $paymentData['proforma_id'] = $request->proforma_id;
        }

            // Update Sales Invoice
            $salesInvoice->update(array_merge([

                'customer_id' => $request->customer_id,

                /*
                |--------------------------------------------------------------------------
                | Sales Order Relation
                |--------------------------------------------------------------------------
                */
                // 'sales_order_id' => $salesOrderId,
                'sales_order_id' => $request->sales_order_id,

                'sales_invoice_code' => $request->sales_invoice_code,

                'salesman_id' => $request->salesman_id,

                'payment_term_id' => $request->payment_term_id,

                'sales_invoice_date' => Carbon::parse(
                    $request->sales_invoice_date
                )->format('Y-m-d'),

                'tanggal_pengiriman' => ! empty($request->shipping_date)
                    ? Carbon::parse($request->shipping_date)->format('Y-m-d')
                    : null,

                /*
                |--------------------------------------------------------------------------
                | Total
                |--------------------------------------------------------------------------
                */
                'sub_total' => $this->parseNominal($request->sub_total),
                'biaya_lain' => $this->parseNominal($request->biaya_lain),

                'disc_percent' => $request->percent,

                'disc_nominal' => $this->parseNominal($request->discount_all),

                'po_number' => $request->po_number,

                'grand_total' => $this->parseNominal($request->total_order),

                /*
                |--------------------------------------------------------------------------
                | Pengiriman
                |--------------------------------------------------------------------------
                */
                'jenis_pengiriman' => $request->jenis_pengiriman,

                /*
                |--------------------------------------------------------------------------
                | Pajak
                |--------------------------------------------------------------------------
                */
                'kena_pajak' => $request->has('kena_pajak')
                    ? 1
                    : 0,

                'total_termasuk_pajak' => $request->has('total_termasuk_pajak')
                    ? 1
                    : 0,

                /*
                |--------------------------------------------------------------------------
                | Address
                |--------------------------------------------------------------------------
                */
                'fob_id' => $request->fob_id,

                'address' => $request->address,

                'description' => $request->description,

                /*
                |--------------------------------------------------------------------------
                | Tax
                |--------------------------------------------------------------------------
                */
                'taxpayer_data' => $request->taxpayer_data,

                'tax_id' => $request->tax_id,

                'tax_amount' => $this->parseNominal($request->tax_amount),

                /*
                |--------------------------------------------------------------------------
                | Payment
                |--------------------------------------------------------------------------
                */
                'total_dp' => $this->parseNominal($request->total_dp),

                'sisa_pembayaran' => $this->parseNominal($request->total_order) - $this->parseNominal($request->total_dp),

                'payment_type' => $request->payment_type,

                /*
                |--------------------------------------------------------------------------
                | Audit
                |--------------------------------------------------------------------------
                */
                'updated_by' => Auth::id(),

            ], $paymentData));

            /*
            |--------------------------------------------------------------------------
            | SIMPAN ID DELIVERY ORDER YANG TERDAMPAK
            |--------------------------------------------------------------------------
            */
            $affectedDoIds = [];

            /*
            |--------------------------------------------------------------------------
            | AMBIL DETAIL LAMA
            |--------------------------------------------------------------------------
            */
            $oldDetails = SalesInvoiceDetail::where(
                'sales_invoice_id',
                $salesInvoice->id
            )->get();

            /*
            |--------------------------------------------------------------------------
            | KEMBALIKAN QTY DELIVERY ORDER DARI DETAIL LAMA
            |--------------------------------------------------------------------------
            |
            | Sebelum detail invoice dihapus, kita hitung ulang DO.
            |
            */
            foreach ($oldDetails as $detail) {

                /*
                |--------------------------------------------------------------------------
                | Jika tidak memiliki SO detail
                |--------------------------------------------------------------------------
                */
                if (! $detail->sales_order_detail_id) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Ambil Delivery Order Detail
                |--------------------------------------------------------------------------
                */
                $doDetail = DB::table(
                    "delivery_order_detail_{$currentYear}"
                )
                    ->where('id', $detail->sales_order_detail_id)
                    ->first();

                if (! $doDetail) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Hitung total invoice lain
                |--------------------------------------------------------------------------
                |
                | Invoice yang sedang diedit dikecualikan.
                |
                */
                $totalInvoiceQty = SalesInvoiceDetail::where(
                    'sales_order_detail_id',
                    $detail->sales_order_detail_id
                )
                    ->where(
                        'sales_invoice_id',
                        '<>',
                        $salesInvoice->id
                    )
                    ->sum('qty');

                /*
                |--------------------------------------------------------------------------
                | Outstanding
                |--------------------------------------------------------------------------
                */
                $outstanding = max(
                    0,
                    (float) $doDetail->qty - (float) $totalInvoiceQty
                );

                /*
                |--------------------------------------------------------------------------
                | Update Delivery Order Detail
                |--------------------------------------------------------------------------
                */
                DB::table(
                    "delivery_order_detail_{$currentYear}"
                )
                    ->where('id', $detail->sales_order_detail_id)
                    ->update([
                        'do_qty' => $totalInvoiceQty,
                        'outstanding_qty' => $outstanding,
                    ]);

                /*
                |--------------------------------------------------------------------------
                | Simpan Delivery Order ID
                |--------------------------------------------------------------------------
                */
                if (! empty($doDetail->delivery_order_id)) {

                    $affectedDoIds[] = $doDetail->delivery_order_id;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | HAPUS DOCUMENT HISTORY LAMA
            |--------------------------------------------------------------------------
            */
            DocumentTransactionHistory::where(
                'to_type',
                'SalesInvoice'
            )
                ->where(
                    'to_id',
                    $salesInvoice->id
                )
                ->delete();

            /*
            |--------------------------------------------------------------------------
            | HAPUS DETAIL SALES INVOICE LAMA
            |--------------------------------------------------------------------------
            */
            SalesInvoiceDetail::where(
                'sales_invoice_id',
                $salesInvoice->id
            )->delete();

            /*
            |--------------------------------------------------------------------------
            | DETAIL BARU
            |--------------------------------------------------------------------------
            */
            $involvedDoIds = [];

            foreach (array_values($items) as $index => $item) {

                /*
                |--------------------------------------------------------------------------
                | SALES ORDER DETAIL ID
                |--------------------------------------------------------------------------
                */
                $salesOrderDetailId =
                    $item['sales_order_detail_id']
                    ?? $item['detail_id']
                    ?? null;

                /*
                |--------------------------------------------------------------------------
                | DELIVERY ORDER ID
                |--------------------------------------------------------------------------
                |
                | Prioritas:
                |
                | sales_order_code_id
                | delivery_order_id
                | order_code
                |
                */
                $deliveryOrderId =
                    $item['sales_order_code_id']
                    ?? $item['delivery_order_id']
                    ?? $item['order_code']
                    ?? null;

                /*
                |--------------------------------------------------------------------------
                | Normalisasi ID
                |--------------------------------------------------------------------------
                */
                $salesOrderDetailId = ! empty($salesOrderDetailId)
                    ? (int) $salesOrderDetailId
                    : null;

                $deliveryOrderId = ! empty($deliveryOrderId)
                    ? (int) $deliveryOrderId
                    : null;

                /*
                |--------------------------------------------------------------------------
                | QTY
                |--------------------------------------------------------------------------
                */
                $qty = (float) (
                    $item['quantity']
                    ?? $item['qty']
                    ?? 0
                );

                /*
                |--------------------------------------------------------------------------
                | UNIT PRICE
                |--------------------------------------------------------------------------
                */
                $unitPrice = (float) (
                    $item['unit_price']
                    ?? 0
                );

                /*
                |--------------------------------------------------------------------------
                | DISCOUNT
                |--------------------------------------------------------------------------
                */
                $discount = (float) (
                    $item['discount']
                    ?? 0
                );

                /*
                |--------------------------------------------------------------------------
                | DISCOUNT PERCENT
                |--------------------------------------------------------------------------
                */
                $discountPercent = (float) (
                    $item['discount_percent']
                    ?? 0
                );

                /*
                |--------------------------------------------------------------------------
                | AMOUNT
                |--------------------------------------------------------------------------
                */
                $amount = isset($item['amount'])
                    ? (float) $item['amount']
                    : (
                        ($qty * $unitPrice)
                        - $discount
                    );

                /*
                |--------------------------------------------------------------------------
                | PRODUCT
                |--------------------------------------------------------------------------
                */
                $productId = $item['product_id'] ?? null;

                /*
                |--------------------------------------------------------------------------
                | UNIT
                |--------------------------------------------------------------------------
                */
                $unitId = $item['unit_id'] ?? null;

                /*
                |--------------------------------------------------------------------------
                | WAREHOUSE
                |--------------------------------------------------------------------------
                */
                $warehouseId = $item['warehouse_id'] ?? null;

                /*
                |--------------------------------------------------------------------------
                | SIMPAN SALES INVOICE DETAIL
                |--------------------------------------------------------------------------
                */
                $salesInvoiceDetail = SalesInvoiceDetail::create([
                    'sales_invoice_id' => $salesInvoice->id,
                    'sales_order_detail_id' => $salesOrderDetailId,
                    'sales_order_code_id' => $deliveryOrderId,
                    'product_id' => $productId,
                    'qty' => $qty,
                    'urutan' => $index + 1,
                    'unit_id' => $unitId,
                    'warehouse_id' => $warehouseId,
                    'unit_price' => $unitPrice,
                    'discount_percent' => $discountPercent,
                    'discount' => $discount,
                    'amount' => $amount,
                    'so_qty' => $qty,
                    'outstanding_qty' => 0,
                    'status' => 'open',
                    'active' => 1,
                    'created_by' => Auth::id(),
                ]);

                DocumentTransactionHistory::create([
                    'module' => 'sales',
                    'from_type' => 'DeliveryOrder',
                    /*
                    |--------------------------------------------------------------------------
                    | PENTING
                    |--------------------------------------------------------------------------
                    | Store Anda menggunakan $doDetailId untuk from_id.
                    | Kita samakan agar konsisten.
                    */
                    'from_id' => $salesOrderDetailId,
                    'from_detail_id' => $salesOrderDetailId,
                    'to_type' => 'SalesInvoice',
                    'to_id' => $salesInvoice->id,
                    'to_detail_id' => $salesInvoiceDetail->id,
                    'transaction_type' => 'invoice',
                    'qty' => $salesInvoiceDetail->qty,
                    'unit_price' => $salesInvoiceDetail->unit_price,
                    'discount' => $salesInvoiceDetail->discount,
                    'amount' => $salesInvoiceDetail->amount,
                    'transaction_date' => $salesInvoice->sales_invoice_date,
                    'metadata' => json_encode([
                        'warehouse_id' => $salesInvoiceDetail->warehouse_id,
                        'product_id' => $salesInvoiceDetail->product_id,
                        'unit_id' => $salesInvoiceDetail->unit_id,
                        'sales_order_id' => $salesOrderId,
                        'sales_order_detail_id' => $salesOrderDetailId,
                        'delivery_order_id' => $deliveryOrderId,
                    ]),
                ]);

                ArApHistory::create([
                    'type' => 'receivable',
                    'party_id' => $salesInvoice->customer_id,
                    'transaction_type' => 'invoice',
                    'reference_type' => 'sales_invoice',
                    'reference_id' => $salesInvoice->id,
                    'document_no' => $salesInvoice->sales_invoice_code,
                    'transaction_date' => $salesInvoice->sales_invoice_date,
                    'debit' => $salesInvoice->grand_total,
                    'credit' => 0,
                ]);

                /*
                |--------------------------------------------------------------------------
                | UPDATE DELIVERY ORDER DETAIL
                |--------------------------------------------------------------------------
                */
                if ($salesOrderDetailId) {

                    $doDetail = DB::table(
                        "delivery_order_detail_{$currentYear}"
                    )
                        ->where('id', $salesOrderDetailId)
                        ->first();

                    if ($doDetail) {

                        /*
                        |--------------------------------------------------------------------------
                        | Total Qty Invoice
                        |--------------------------------------------------------------------------
                        |
                        | Semua invoice termasuk invoice yang sedang diupdate
                        | sudah menggunakan detail baru.
                        |
                        */
                        $totalInvoiceQty = SalesInvoiceDetail::where(
                            'sales_order_detail_id',
                            $salesOrderDetailId
                        )->sum('qty');

                        /*
                        |--------------------------------------------------------------------------
                        | Outstanding
                        |--------------------------------------------------------------------------
                        */
                        $outstanding = max(
                            0,
                            (float) $doDetail->qty
                            - (float) $totalInvoiceQty
                        );

                        /*
                        |--------------------------------------------------------------------------
                        | Update DO Detail
                        |--------------------------------------------------------------------------
                        */
                        DB::table(
                            "delivery_order_detail_{$currentYear}"
                        )
                            ->where('id', $salesOrderDetailId)
                            ->update([
                                'do_qty' => $totalInvoiceQty,
                                'outstanding_qty' => $outstanding,
                            ]);

                        /*
                        |--------------------------------------------------------------------------
                        | Simpan Delivery Order ID
                        |--------------------------------------------------------------------------
                        */
                        if (! empty($doDetail->delivery_order_id)) {

                            $involvedDoIds[] =
                                $doDetail->delivery_order_id;
                        }
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | GABUNGKAN DO LAMA + DO BARU
            |--------------------------------------------------------------------------
            */
            $affectedDoIds = array_unique(
                array_merge(
                    $affectedDoIds,
                    $involvedDoIds
                )
            );

            /*
            |--------------------------------------------------------------------------
            | UPDATE STATUS DELIVERY ORDER
            |--------------------------------------------------------------------------
            */
            foreach ($affectedDoIds as $deliveryOrderId) {

                if (empty($deliveryOrderId)) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Ambil semua detail DO
                |--------------------------------------------------------------------------
                */
                $details = DB::table(
                    "delivery_order_detail_{$currentYear}"
                )
                    ->where(
                        'delivery_order_id',
                        $deliveryOrderId
                    )
                    ->get();

                /*
                |--------------------------------------------------------------------------
                | Total Qty DO
                |--------------------------------------------------------------------------
                */
                $totalQty = $details->sum(function ($detail) {
                    return (float) $detail->qty;
                });

                /*
                |--------------------------------------------------------------------------
                | Total Qty yang sudah diinvoice
                |--------------------------------------------------------------------------
                */
                $totalInvoice = $details->sum(function ($detail) {
                    return (float) $detail->do_qty;
                });

                /*
                |--------------------------------------------------------------------------
                | Tentukan Status
                |--------------------------------------------------------------------------
                */
                if ($totalInvoice <= 0) {

                    $status = 'processing';

                } elseif ($totalInvoice < $totalQty) {

                    $status = 'partial';

                } else {

                    $status = 'confirmed';
                }

                /*
                |--------------------------------------------------------------------------
                | Update Delivery Order Header
                |--------------------------------------------------------------------------
                */
                DB::table(
                    "delivery_order_{$currentYear}"
                )
                    ->where(
                        'id',
                        $deliveryOrderId
                    )
                    ->update([
                        'status' => $status,
                    ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Sales Invoice berhasil diupdate.',
                'redirect' => route('sales-invoice.index'),
            ]);
        } catch (\Throwable $e) {
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
            $po = SalesInvoice::findOrFail($id);

            // 2. Ambil detail SO untuk mendapatkan referensi PR Detail yang terkait
            $sqDetails = SalesInvoiceDetail::where('sales_invoice_id', $po->id)->get();
            $involvedPrIds = [];

            foreach ($sqDetails as $sqDetail) {
                if ($sqDetail->sales_order_detail_id) {
                    // Catat ID PR Master-nya
                    $prDetail = SalesQuotationDetail::where('id', $sqDetail->sales_order_detail_id)
                        ->first();

                    if ($prDetail && ! in_array($prDetail->sales_order_id, $involvedPrIds)) {
                        $involvedPrIds[] = $prDetail->sales_order_id;
                    }
                }
            }

            // 3. Nonaktifkan SO dan Detail SO
            $po->update(['active' => 0, 'updated_by' => Auth::id()]);
            SalesInvoiceDetail::where('sales_invoice_id', $po->id)->update(['active' => 0]);

            // 4. Update Ulang so_qty di setiap PR Detail yang terdampak
            // Kita hitung ulang berdasarkan sisa SO yang masih 'active' = 1
            foreach ($sqDetails as $sqDetail) {
                if ($sqDetail->sales_order_detail_id) {
                    $totalRemainingPo = SalesInvoiceDetail::where('sales_order_detail_id', $sqDetail->sales_order_detail_id)
                        ->where('active', 1)
                        ->sum('qty');

                    DB::table('sales_order_detail_'.date('Y'))
                        ->where('id', $sqDetail->sales_order_detail_id)
                        ->update(['so_qty' => $totalRemainingPo]);
                }
            }

            // 5. Update Status PR Master
            foreach ($involvedPrIds as $prId) {
                $allDetails = SalesQuotationDetail::where('sales_order_id', $prId)
                    ->get();

                $totalRequested = $allDetails->sum('qty');
                $totalOrdered = $allDetails->sum('so_qty');

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
            $query = SalesInvoice::where('active', '0')
                ->orderby('sales_invoice_code', 'desc')->get();

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
                ->addColumn('sales_invoice_date', function ($row) {
                    return $row->sales_invoice_date ? Carbon::parse($row->sales_invoice_date)->format('d M Y') : 'N/A';
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
                        auth()->user()->can('sales_invoice-delete') &&
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

                    if (auth()->user()->can('sales_invoice-restore')) {
                        $btn .= '<a class="dropdown-item restore" href="javascript:void(0)"
                            data-id="'.$row->id.'"> <i class="ti ti-trash-off me-1"></i> Restore</a>';
                    }

                    return $btn;
                })
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'cekbok', 'sales_invoice_date', 'total', 'customer'])
                ->make(true);
        }

        $x = [
            'title' => 'Deleted Sales Invoice List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Deleted Sales Invoice', 'url' => ''],
            ],
        ];

        return view('sales.salesInvoice.sales_invoice_trash', $x);
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
            $sqDetails = SalesInvoiceDetail::whereIn('sales_invoice_id', $ids)->get();
            $involvedPrIds = [];

            // 2. Tandai SO dan Detail SO sebagai tidak aktif (active = 0)
            SalesInvoice::whereIn('id', $ids)->update([
                'active' => 0,
                'updated_by' => Auth::id(),
            ]);
            SalesInvoiceDetail::whereIn('sales_invoice_id', $ids)->update(['active' => 0]);

            // 3. Update so_qty di PR Detail dan kumpulkan ID PR Master
            // foreach ($sqDetails as $sqDetail) {
            //     if ($sqDetail->sales_order_detail_id) {
            //         // Hitung total dari SO yang tersisa (yang masih aktif)
            //         $totalRemainingPo = SalesInvoiceDetail::where('sales_order_detail_id', $sqDetail->sales_order_detail_id)
            //             ->where('active', 1)
            //             ->sum('qty');

            //         // Update ke tabel PR Detail
            //         DB::table('sales_order_detail_'.date('Y'))
            //             ->where('id', $sqDetail->sales_order_detail_id)
            //             ->update(['so_qty' => $totalRemainingPo]);

            //         // Simpan ID PR untuk update status nanti
            //         $prDetail = DB::table('sales_order_detail_'.date('Y'))
            //             ->where('id', $sqDetail->sales_order_detail_id)
            //             ->first();

            //         if ($prDetail && ! in_array($prDetail->sales_order_id, $involvedPrIds)) {
            //             $involvedPrIds[] = $prDetail->sales_order_id;
            //         }
            //     }
            // }

            // 4. Update Status PR Master berdasarkan akumulasi terbaru
            // foreach ($involvedPrIds as $prId) {
            //     $allDetails = DB::table('sales_order_detail_'.date('Y'))
            //         ->where('sales_order_id', $prId)
            //         ->get();

            //     $totalRequested = $allDetails->sum('qty');
            //     $totalOrdered = $allDetails->sum('so_qty');

            //     if ($totalOrdered >= $totalRequested) {
            //         $status = 'closed';
            //     } elseif ($totalOrdered > 0) {
            //         $status = 'partial';
            //     } else {
            //         $status = 'processing';
            //     }

            //     DB::table('sales_order_'.date('Y'))
            //         ->where('id', $prId)
            //         ->update(['status' => $status]);
            // }

            DB::commit();

            return response()->json([
                'success' => true,
                // 'message' => 'Sales Invoice berhasil dihapus dan status PR telah diperbarui.',
                'message' => 'Sales Invoice berhasil dihapus .',
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
            $po = SalesInvoice::findOrFail($id);
            $po->update(['active' => 1, 'updated_by' => Auth::id()]);

            // 2. Aktifkan kembali Detail SO
            SalesInvoiceDetail::where('sales_invoice_id', $po->id)->update(['active' => 1]);

            // 3. Ambil semua detail SO yang baru saja diaktifkan
            // $poDetails = SalesInvoiceDetail::where('sales_invoice_id', $po->id)->get();
            // $involvedPrIds = [];

            // 4. Update ulang so_qty di PR Detail
            // foreach ($poDetails as $poDetail) {
            //     if ($poDetail->sales_order_detail_id) {
            //         // Hitung total dari semua SO yang aktif
            //         $totalPoForThisItem = SalesInvoiceDetail::where('sales_order_detail_id', $poDetail->sales_order_detail_id)
            //             ->where('active', 1)
            //             ->sum('qty');

            //         // Update ke tabel PR Detail
            //         DB::table('sales_order_detail_'.date('Y'))
            //             ->where('id', $poDetail->sales_order_detail_id)
            //             ->update(['so_qty' => $totalPoForThisItem]);

            //         // Simpan ID PR untuk update status
            //         $prDetail = DB::table('sales_order_detail_'.date('Y'))
            //             ->where('id', $poDetail->sales_order_detail_id)
            //             ->first();

            //         if ($prDetail && ! in_array($prDetail->sales_order_id, $involvedPrIds)) {
            //             $involvedPrIds[] = $prDetail->sales_order_id;
            //         }
            //     }
            // }

            // 5. Update Status PR Master
            // foreach ($involvedPrIds as $prId) {
            //     $allDetails = DB::table('sales_order_detail_'.date('Y'))
            //         ->where('sales_order_id', $prId)
            //         ->get();

            //     $totalRequested = $allDetails->sum('qty');
            //     $totalOrdered = $allDetails->sum('so_qty');

            //     if ($totalOrdered >= $totalRequested) {
            //         $status = 'closed';
            //     } elseif ($totalOrdered > 0) {
            //         $status = 'partial';
            //     } else {
            //         $status = 'processing';
            //     }

            //     DB::table('sales_order_'.date('Y'))
            //         ->where('id', $prId)
            //         ->update(['status' => $status]);
            // }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sales Invoice berhasil dikembalikan (restored).',
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
            SalesInvoice::whereIn('id', $ids)->update([
                'active' => 1,
                'updated_by' => Auth::id(),
            ]);

            // 2. Aktifkan kembali semua detail SO yang berkaitan dengan SO-SO tersebut
            SalesInvoiceDetail::whereIn('sales_invoice_id', $ids)->update(['active' => 1]);

            // 3. Ambil semua detail SO yang baru saja diaktifkan untuk sinkronisasi
            $poDetails = SalesInvoiceDetail::whereIn('sales_invoice_id', $ids)->get();
            $involvedPrIds = [];

            // 4. Update so_qty di PR Detail dan kumpulkan ID PR Master
            foreach ($poDetails as $poDetail) {
                if ($poDetail->sales_order_detail_id) {
                    // Hitung total dari semua SO yang aktif
                    $totalPoForThisItem = SalesInvoiceDetail::where('sales_order_detail_id', $poDetail->sales_order_detail_id)
                        ->where('active', 1)
                        ->sum('qty');

                    // Update ke tabel PR Detail
                    DB::table('sales_order_detail_'.date('Y'))
                        ->where('id', $poDetail->sales_order_detail_id)
                        ->update(['so_qty' => $totalPoForThisItem]);

                    // Simpan ID PR untuk update status nanti (hindari duplikat)
                    $prDetail = DB::table('sales_order_detail_'.date('Y'))
                        ->where('id', $poDetail->sales_order_detail_id)
                        ->first();

                    if ($prDetail && ! in_array($prDetail->sales_order_id, $involvedPrIds)) {
                        $involvedPrIds[] = $prDetail->sales_order_id;
                    }
                }
            }

            // 5. Update Status PR Master berdasarkan akumulasi terbaru
            foreach ($involvedPrIds as $prId) {
                $allDetails = DB::table('sales_order_detail_'.date('Y'))
                    ->where('sales_order_id', $prId)
                    ->get();

                $totalRequested = $allDetails->sum('qty');
                $totalOrdered = $allDetails->sum('so_qty');

                if ($totalOrdered >= $totalRequested) {
                    $status = 'closed';
                } elseif ($totalOrdered > 0) {
                    $status = 'partial';
                } else {
                    $status = 'processing';
                }

                DB::table('sales_order_'.date('Y'))
                    ->where('id', $prId)
                    ->update(['status' => $status]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sales Invoice terpilih berhasil dikembalikan.',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal merestore data: '.$e->getMessage(),
            ], 500);
        }
    }

    public function print($id)
    {
        $currentYear = date('Y');

        /*
        |--------------------------------------------------------------------------
        | Ambil Sales Invoice
        |--------------------------------------------------------------------------
        */
        $salesInvoice = SalesInvoice::with([
            'salesOrder',
            'customerID',
            'paymentTermID',
            'details.produkID',
            'details.unitID',
            'salesDownPayments',
        ])->findOrFail($id);

        $modelDetail = $salesInvoice->details->sortBy('urutan')->values();

        $company = Company::first();
        $salesOrder = $salesInvoice->salesOrder;
        $downPayments = collect();
        $totalDP = 0;
        $totalDPPaid = 0;
        $remainingDP = 0;

        if ($salesInvoice->sales_order_id) {
            $downPayments = SalesDownPayment::from(
                "sales_down_payments_{$currentYear} as dp"
            )
                ->where(
                    'dp.sales_order_id',
                    $salesInvoice->sales_order_id
                )
                ->where(
                    'dp.customer_id',
                    $salesInvoice->customer_id
                )
                ->where(
                    'dp.active',
                    1
                )
                ->whereNotIn(
                    'dp.status',
                    [
                        'cancelled',
                        'closed',
                    ]
                )
                ->orderBy(
                    'dp.id',
                    'asc'
                )
                ->get();

            /*
            |--------------------------------------------------------------------------
            | Total DP dibuat
            |--------------------------------------------------------------------------
            */
            $totalDP = $downPayments->sum(function ($dp) {

                return (float) $dp->down_payment_amount;

            });

            /*
            |--------------------------------------------------------------------------
            | Total DP sudah dibayar
            |--------------------------------------------------------------------------
            */
            $totalDPPaid = $downPayments->sum(function ($dp) {

                return (float) $dp->paid_amount;

            });

            /*
            |--------------------------------------------------------------------------
            | Sisa DP
            |--------------------------------------------------------------------------
            |
            | Total DP - DP sudah dibayar
            |
            */
            $remainingDP = max(
                0,
                $totalDP - $totalDPPaid
            );

        }

        /*
        |--------------------------------------------------------------------------
        | Pembayaran Invoice langsung
        |--------------------------------------------------------------------------
        |
        | Tidak termasuk DP
        |
        */
        $invoicePaidAmount = (float) $salesInvoice->paid_amount;

        /*
        |--------------------------------------------------------------------------
        | Total Invoice
        |--------------------------------------------------------------------------
        */
        $invoiceTotal = (float) $salesInvoice->grand_total;

        /*
        |--------------------------------------------------------------------------
        | Total pembayaran invoice
        |--------------------------------------------------------------------------
        |
        | Jangan tambah DP lagi
        |
        | Karena DP adalah transaksi terpisah
        |
        */
        $totalInvoicePaid = $invoicePaidAmount;

        /*
        |--------------------------------------------------------------------------
        | Sisa Invoice
        |--------------------------------------------------------------------------
        */
        $remainingInvoice = max(
            0,
            $invoiceTotal - $totalInvoicePaid
        );

        /*
        |--------------------------------------------------------------------------
        | Total Pembayaran Customer
        |--------------------------------------------------------------------------
        |
        | Untuk tampilan histori:
        |
        | DP Paid + Invoice Paid
        |
        */
        $totalCustomerPayment =
            $totalDPPaid +
            $invoicePaidAmount;

        /*
        |--------------------------------------------------------------------------
        | Status Pembayaran Invoice
        |--------------------------------------------------------------------------
        */
        if ($totalInvoicePaid >= $invoiceTotal) {

            $paymentStatus = 'Paid';

        } elseif ($totalInvoicePaid > 0) {

            $paymentStatus = 'Partial';

        } else {

            $paymentStatus = 'Unpaid';

        }

        /*
        |--------------------------------------------------------------------------
        | Persentase Pembayaran Invoice
        |--------------------------------------------------------------------------
        */
        $paymentPercent = 0;

        if ($invoiceTotal > 0) {

            $paymentPercent =
                round(
                    ($totalInvoicePaid / $invoiceTotal) * 100,
                    2
                );

        }

        $data = [

            /*
            | Invoice
            */
            'model' => $salesInvoice,

            /*
            | Company
            */
            'company' => $company,

            /*
            | Sales Order
            */
            'salesOrder' => $salesOrder,

            /*
            | Detail Produk
            */
            'modelDetail' => $modelDetail,

            /*
            |--------------------------------------------------------------------------
            | Down Payment
            |--------------------------------------------------------------------------
            */
            'downPayments' => $downPayments,

            'totalDP' => $totalDP,

            'totalDPPaid' => $totalDPPaid,

            'remainingDP' => $remainingDP,

            /*
            |--------------------------------------------------------------------------
            | Payment
            |--------------------------------------------------------------------------
            */
            'paymentHistories' => $downPayments,

            'invoicePaidAmount' => $invoicePaidAmount,

            'totalInvoicePaid' => $totalInvoicePaid,

            'totalCustomerPayment' => $totalCustomerPayment,

            'remainingInvoice' => $remainingInvoice,

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */
            'paymentStatus' => $paymentStatus,

            'paymentPercent' => $paymentPercent,

        ];

        /*
        |--------------------------------------------------------------------------
        | Filename
        |--------------------------------------------------------------------------
        */
        $filename = str_replace(
            ['/', '\\'],
            '-',
            $salesInvoice->sales_invoice_code
        );

        /*
        |--------------------------------------------------------------------------
        | Generate PDF
        |--------------------------------------------------------------------------
        */
        $customerName = $salesInvoice->customerID?->nama_customer
        ?? $salesInvoice->salesOrder?->customerID?->nama_customer
        ?? 'Customer';

        return Pdf::loadView('pdf.sales_invoice_pdf', $data)
            ->setPaper('a4', 'portrait')
            ->stream($filename.' - '.$customerName.'.pdf');
    }

    public function getPriceHistory(Request $request)
    {
        $productId = $request->get('product_id');
        $customerId = $request->get('customer_id');

        $year = date('Y');
        $tableDetail = "sales_invoice_detail_{$year}";
        $tableMaster = "sales_invoice_{$year}";

        // Mengambil harga unik langsung dari database
        $history = DB::table($tableDetail)
            ->join($tableMaster, "{$tableDetail}.sales_invoice_id", '=', "{$tableMaster}.id")
            ->where("{$tableDetail}.product_id", $productId)
            ->where("{$tableMaster}.customer_id", $customerId)
            // Kuncinya di sini: kelompokkan berdasarkan harga, lalu ambil tanggal terbaru dengan MAX()
            ->select(
                "{$tableDetail}.unit_price as harga",
                DB::raw("MAX({$tableMaster}.sales_invoice_date) as tanggal")
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

    public function getUnitsByProduct($id)
    {
        $product = Barang::find($id);
        $defaultPrice = $product ? $product->default_price : 0;
        // 1. Ambil semua baris data konversi berdasarkan data_barang_id
        $conversions = DataBarangConversion::with(['toUnitID', 'fromUnitID'])
            ->where('data_barang_id', $id)
            ->get();

        if ($conversions->isEmpty()) {
            return response()->json([]);
        }

        $result = [];
        $addedIds = []; // Array penampung untuk menghindari ID kembar di dropdown

        // 2. Cek apakah ada SALAH SATU atau SEMUA baris yang to_unit_id-nya terisi (TIDAK NULL)
        $hasToUnit = $conversions->contains(function ($item) {
            return ! is_null($item->getRawOriginal('to_unit_id')) && $item->getRawOriginal('to_unit_id') !== '';
        });

        if ($hasToUnit) {
            // --- KONDISI A: to_unit_id ada yang terisi -> Tampilkan dari to_unit_id DAN from_unit_id ---

            // Ambil SEMUA data to_unit_id yang valid (tidak null)
            foreach ($conversions as $item) {
                $toId = $item->getRawOriginal('to_unit_id');

                if (! is_null($toId) && ! in_array($toId, $addedIds)) {
                    $result[] = [
                        'id' => $toId,
                        'name' => $item->toUnitID ? $item->toUnitID->detail : 'Unit '.$toId,
                    ];
                    $addedIds[] = $toId;
                }
            }

            // Tambahkan JUGAdari darifrom_unit_id (ambil 1 data saja)
            $firstFromUnit = $conversions->first(function ($item) {
                return ! is_null($item->getRawOriginal('from_unit_id'));
            });

            if ($firstFromUnit) {
                $fromId = $firstFromUnit->getRawOriginal('from_unit_id');
                if (! in_array($fromId, $addedIds)) {
                    $result[] = [
                        'id' => $fromId,
                        'name' => $firstFromUnit->fromUnitID ? $firstFromUnit->fromUnitID->detail : 'Unit '.$fromId,
                    ];
                }
            }

        } else {
            // --- KONDISI B: to_unit_id KOSONG SEMUA -> Hanya tampilkan 1 data dari from_unit_id ---

            $firstFromUnit = $conversions->first(function ($item) {
                return ! is_null($item->getRawOriginal('from_unit_id'));
            });

            if ($firstFromUnit) {
                $fromId = $firstFromUnit->getRawOriginal('from_unit_id');
                $result[] = [
                    'id' => $fromId,
                    'name' => $firstFromUnit->fromUnitID ? $firstFromUnit->fromUnitID->detail : 'Unit '.$fromId,
                ];
            }
        }

        // Kembalikan data array JSON ter-filter ke JavaScript
        // return response()->json($result);
        return response()->json([
            'units' => $result, // Ubah struktur agar units dibungkus
            'default_price' => $defaultPrice,
        ]);
    }

    public function processData($id)
    {
        // 1. Ambil tahun berjalan secara dinamis
        $year = date('Y');
        $tableName = "sales_invoice_{$year}";

        // 2. Gunakan Query Builder dengan nama tabel dinamis agar pencarian ID aman
        $poData = DB::table($tableName)->where('id', $id)->first();

        // Jika data memang benar-benar tidak ditemukan di database
        if (! $poData) {
            return response()->json(['success' => false, 'message' => 'Data Sales Invoice tidak ditemukan.'], 404);
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

        return response()->json(['success' => true, 'message' => 'Sales Invoice berhasil diajukan!']);
    }

    public function getDelivery($customerId)
    {

        $orders = DeliveryOrder::where('customer_id', $customerId)
            ->where('status', 'processing')
            ->get();

        return response()->json($orders);
    }

    public function getDeliveryDetail(Request $request)
    {
        $ids = $request->quotation_ids;

        if (empty($ids)) {
            return response()->json(['success' => false, 'data' => []]);
        }

        // Load relasi salesOrderDetail
        $details = DeliveryOrderDetail::with([
            'produkID',
            'unitID',
            'deliveryOrder',
            'salesOrderDetail',
        ])
            ->whereIn('delivery_order_id', $ids)
            ->get();
        $formattedData = $details->map(function ($item) {
            $sisaQty = ($item->outstanding_qty !== null && $item->outstanding_qty > 0)
                        ? (float) $item->outstanding_qty
                        : (float) $item->qty;

            $price = (float) ($item->salesOrderDetail->unit_price ?? 0);
            $discount = (float) ($item->salesOrderDetail->discount ?? 0);

            return [
                'id' => $item->id,
                'delivery_order_id' => $item->delivery_order_id,

                'sales_order_id' => $item->salesOrderDetail->sales_order_id ?? null,

                'product_id' => $item->data_barang_id,
                'product_name' => $item->produkID->nama_barang ?? '-',

                'qty' => $sisaQty,

                'unit_id' => $item->unit_id,
                'unit_name' => $item->unitID->detail ?? '-',

                'warehouse_id' => $item->warehouse_id,
                'warehouse_name' => $item->warehouseID->nama_gudang ?? '-',

                'unit_price' => $price,
                'discount' => $discount,
                'amount' => (($price * $sisaQty) - $discount),

                'order_code' => $item->deliveryOrder->delivery_order_code ?? '-',
            ];
        });

        return response()->json(['success' => true, 'data' => $formattedData]);
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

    public function getAddress($customerId)
    {
        $customer = Customer::find($customerId);

        if (! $customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer tidak ditemukan',
            ]);
        }

        // Billing Address
        $billingAddress = trim(implode("\n", array_filter([
            $customer->alamat_tagihan,
            $customer->kota_tagihan,
            $customer->provinsi_tagihan,
            $customer->kodepos_tagihan,
            $customer->negara_tagihan,
        ])));

        // Delivery Address
        $deliveryAddresses = DB::table('customer_pengiriman')->where('customer_id', $customerId)
            ->orderByDesc('default_pengiriman')
            ->get()
            ->map(function ($item) {

                $address = trim(implode("\n", array_filter([
                    $item->alamat_pengiriman,
                    $item->kota_pengiriman,
                    $item->provinsi_pengiriman,
                    $item->kodepos_pengiriman,
                    $item->negara_pengiriman,
                ])));

                return [
                    'title' => 'Delivery Address',
                    'receiver' => $item->nama_penerima,
                    'phone' => $item->handphone_penerima,
                    'address' => $address,
                    'default' => $item->default_pengiriman,
                ];
            });

        return response()->json([
            'success' => true,
            'billing' => [
                'title' => 'Billing Address',
                'address' => $billingAddress,
            ],
            'delivery' => $deliveryAddresses,
        ]);
    }

    public function getReference($customerId, $type)
    {
        $year = date('Y');

        try {

            if ($type == 'proforma') {

                $data = ProformaInvoice::from("proforma_invoice_$year as p")
                    ->where('p.customer_id', $customerId)
                    ->where('p.active', 1)
                    ->whereNotIn('p.status', ['draft', 'cancelled', 'closed'])
                    ->select(
                        'p.id',
                        'p.sales_order_id',
                        'p.proforma_invoice_code as code',
                        'p.proforma_invoice_date as date',
                        'p.grand_total as amount'
                    )
                    ->get();

            } else {

                $data = SalesDownPayment::from("sales_down_payments_$year as dp")
                    ->where('dp.customer_id', $customerId)
                    ->where('dp.active', 1)
                    ->where('dp.remaining_amount', '>', 0)
                    ->whereNotIn('dp.status', ['cancelled', 'closed'])
                    ->select(
                        'dp.id',
                        'dp.sales_order_id',
                        'dp.sales_downpayment_code as code',
                        'dp.sales_downpayment_date as date',
                        'dp.down_payment_amount as amount'
                    )
                    ->get();

            }

            return response()->json($data);

        } catch (\Throwable $e) {

            return response()->json([
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);

        }
    }

    public function getDownPayment($customerId)
    {
        $year = date('Y');

        $orders = SalesDownPayment::where('customer_id', $customerId)
            ->where('active', 1)
            ->whereNotExists(function ($query) use ($year) {
                $query->select(DB::raw(1))
                    ->from("sales_invoice_{$year} as si")
                    ->whereColumn(
                        'si.sales_down_payment_id',
                        "sales_down_payments_{$year}.id"
                    );
            })
            ->get();

        return response()->json($orders);
    }

    public function getDownPaymentDetail(Request $request)
    {
        $ids = $request->sales_down_payment_ids;
        if (! $ids || ! is_array($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'Down Payment belum dipilih.',
                'data' => [],
            ]);
        }
        $downPayments = SalesDownPayment::whereIn('id', $ids)
            ->where('active', 1)
            ->where('remaining_amount', '>', 0)
            ->get();
        $data = $downPayments->map(function ($dp) {
            $amount = (float) $dp->remaining_amount;

            return [
                'id' => $dp->id,
                'data_produk' => $dp->description
                    ? 'Down Payment - '.$dp->description
                    : 'Down Payment '.$dp->sales_downpayment_code,
                'product_id' => $dp->description
                    ? 'Down Payment - '.$dp->description
                    : 'Down Payment '.$dp->sales_downpayment_code,
                'quantity' => 1,
                'unit' => null,
                'unit_id' => null,
                'unit_price' => $amount,
                'discount' => 0,
                'amount' => $amount,
                'warehouse' => null,
                'warehouse_id' => null,
                'sales_down_payment_id' => $dp->id,
                'sales_downpayment_code' => $dp->sales_downpayment_code,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data->values(),
        ]);
    }
}
