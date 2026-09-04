<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalesDownPaymentRequest;
use App\Models\Sales\ArApHistory;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesDownPayment;
use App\Models\Sales\SalesOrder;
use App\Models\Setting\Company;
use App\Models\Setting\SyaratPembayaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SalesDownPaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $routeName = $request->route()->getName();

            $permissionMap = [
                'sales-down-payment.index' => 'sales_down_payment-browse',
                'sales-down-payment.show' => 'sales_down_payment-read',
                'sales-down-payment.create' => 'sales_down_payment-create',
                'sales-down-payment.store' => 'sales_down_payment-create',
                'sales-down-payment.edit' => 'sales_down_payment-edit',
                'sales-down-payment.update' => 'sales_down_payment-edit',
                'sales-down-payment.destroy' => 'sales_down_payment-delete',
                'sales-down-payment.trash' => 'sales_down_payment-trash',
                'sales-down-payment.restore' => 'sales_down_payment-restore',
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
            $query = SalesDownPayment::where('active', '<>', 0)
                ->orderBy('created_at', 'desc');
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
                ->addColumn('customer', function ($row) {
                    if ($row->customer_id) {
                        return $row->customerID->nama_customer;
                    }

                    return 'N/A';
                })
                ->addColumn('taxpayer_id_type', function ($row) {
                    return DB::table('customer_pajak')
                        ->where('customer_id', $row->customer_id)
                        ->value('tipe_id_pajak') ?? 'N/A';
                })
                ->addColumn('total', function ($row) {
                    return format_uang(convert_currency($row->down_payment_amount, $row->currency_id ?? 1));
                })
                ->addColumn('age', function ($row) {
                    $date = Carbon::parse($row->sales_downpayment_date);
                    $diff = $date->diff(now());

                    if ($diff->y > 0) {
                        return "{$diff->y} Tahun {$diff->m} Bulan {$diff->d} Hari";
                    }

                    if ($diff->m > 0) {
                        return "{$diff->m} Bulan {$diff->d} Hari";
                    }

                    return "{$diff->d} Hari";
                })
                ->addColumn('status', function ($row) {

                    switch ($row->status) {

                        case 'unpaid':
                            $badge = 'bg-label-secondary';
                            $text = 'Unpaid';
                            break;

                        case 'Paid':
                            $badge = 'bg-label-success';
                            $text = 'Processing';
                            break;
                        default:
                            $badge = 'bg-label-danger';
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
                        in_array($row->status, ['partial']) &&
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

                        // EDIT
                        if (
                            $user->can('sales_down_payment-edit') &&
                              in_array($row->status, ['unpaid'])
                        ) {

                            $btn .= '
                                <a class="dropdown-item"
                                    href="'.route('sales-down-payment.edit', $row->id).'">
                                    <i class="far fa-edit me-1"></i>
                                    Edit
                                </a>
                            ';
                        }

                        // DELETE
                        if (
                            $user->can('sales_down_payment-delete') &&
                              in_array($row->status, ['unpaid'])
                        ) {

                            $btn .= '
                                <a class="dropdown-item text-danger"
                                    href="javascript:void(0)"
                                    id="delete"
                                    data-id="'.$row->id.'"
                                    data-name="'.$row->sales_downpayment_code.'">

                                    <i class="ti ti-trash me-1"></i>
                                    Delete
                                </a>
                            ';
                        }
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | 5. CANCEL SO
                    |--------------------------------------------------------------------------
                    */

                    // if (
                    //     ! in_array($row->status, ['processing', 'draft'])
                    // ) {

                    //     $btn .= '
                    //         <a class="dropdown-item text-danger btn-cancel-po"
                    //             href="javascript:void(0)"
                    //             data-id="'.$row->id.'">

                    //             <i class="ti ti-circle-x me-1"></i>
                    //             Cancel
                    //         </a>
                    //     ';
                    // }
                    // if ($row->status != 'closed') {
                    //     $btn .= '<a class="dropdown-item"
                    //         href="javascript:void(0)" id="close"   data-id="'.$row->id.'" data-name="'.$row->proforma_invoice_code.'">
                    //         <i class="ti ti-lock"></i> Close
                    //     </a>';
                    // }
                    /*
                    |--------------------------------------------------------------------------
                    | 7. PRINT
                    |--------------------------------------------------------------------------
                    */

                    $btn .= '
                    <a class="dropdown-item"
                        target="_blank"
                        href="'.route('sales-down-payment.print', $row->id).'">

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
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'taxpayer_id_type', 'customer', 'total', 'age'])
                ->make(true);
        }

        $x = [
            'title' => 'Sales Down Payment List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Sales Down Payment', 'url' => ''],
            ],
        ];

        return view('sales.salesDownPayment.sales_down_payment_index', $x);
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

        $prefix = "SDP/{$tahun}/{$bulanRomawi}/";

        $last = SalesDownPayment::where('sales_downpayment_code', 'like', $prefix.'%')
            ->orderByRaw("
            CAST(
                REGEXP_REPLACE(
                    SUBSTRING_INDEX(sales_downpayment_code,'/',-1),
                    '[^0-9]',
                    ''
                ) AS UNSIGNED
            ) DESC
        ")
            ->first();

        if ($last) {
            preg_match('/(\d+)/', substr($last->sales_downpayment_code, strrpos($last->sales_downpayment_code, '/') + 1), $match);
            $lastNumber = isset($match[1]) ? (int) $match[1] : 0;
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function create()
    {

        $company = Company::with('defaultCurrency')->first();

        $x = [
            'title' => 'Sales Down Payment New',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Sales Down Payment', 'url' => ''],
            ],
            'customer' => Customer::where('status', '<>', 0)->get(),
            'idNumber' => $this->generateNumberId(),
            'paymentTerm' => SyaratPembayaran::where('status', '<>', 0)->get(),
            'company' => $company->defaultCurrency,

        ];

        return view('sales.salesDownPayment.sales_down_payment_create', $x);
    }

    public function store(SalesDownPaymentRequest $request)
    {
        DB::beginTransaction();

        try {

            $data = $request->except(['total_order', 'save_and_new', 'total_payment']);

            // Ambil total_order dari request
            $data['sales_order_amount'] = $this->parseNominal(
                $request->input('total_order', 0)
            );

            // Bersihkan nominal DP
            $data['down_payment_amount'] = $this->parseNominal(
                $request->input('down_payment_amount', 0)
            );

            $data['sales_downpayment_date'] = Carbon::parse(
                $request->sales_downpayment_date
            )->format('Y-m-d');

            $data['due_date'] = $request->due_date
                ? Carbon::parse($request->due_date)->format('Y-m-d')
                : null;
            $data['created_by'] = Auth::user()->id;
            $totalOrder = $this->parseNominal($request->input('total_order', 0));
            $downPayment = $this->parseNominal($request->input('down_payment_amount', 0));
            $totalPayment = $this->parseNominal($request->input('total_payment', 0));

            $data['remaining_amount'] = $totalOrder - $downPayment - $totalPayment;

            $sdp = SalesDownPayment::create($data);
            ArApHistory::create([
                'type' => 'receivable',
                'party_id' => $sdp->customer_id,
                'transaction_type' => 'down_payment',
                'reference_type' => 'sales_down_payment',
                'reference_id' => $sdp->id,
                'document_no' => $sdp->sales_downpayment_code,
                'transaction_date' => $sdp->sales_downpayment_date,
                'debit' => 0,
                'credit' => $sdp->down_payment_amount,
            ]);
            DB::commit();
            $redirectUrl = $request->save_and_new == 1
                            ? route('sales-down-payment.create')
                            : route('sales-down-payment.index');

            return response()->json([
                'status' => 'success',
                'message' => 'Sales Down Payment berhasil disimpan.',
                'redirect' => $redirectUrl,
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data: '.$e->getMessage(),
            ], 500);
        }
    }

    private function parseNominal($value)
    {
        if ($value === null || $value === '') {
            return 0;
        }

        $value = trim((string) $value);

        // Kalau sudah berupa angka standar: 1381090.5
        if (is_numeric($value)) {
            return (float) $value;
        }

        // Kalau format Indonesia: 1.381.090,50
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);

        return (float) $value;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    public function edit($id)
    {

        $company = Company::with('defaultCurrency')->first();
        $sdp = SalesDownPayment::findorfail($id);

        $x = [
            'title' => 'Edit Sales Down Payment ',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Edit Sales Down Payment', 'url' => ''],
            ],
            'customer' => Customer::where('status', '<>', 0)->get(),
            'idNumber' => $this->generateNumberId(),
            'paymentTerm' => SyaratPembayaran::where('status', '<>', 0)->get(),
            'company' => $company->defaultCurrency,
            'model' => $sdp,

        ];

        return view('sales.salesDownPayment.sales_down_payment_edit', $x);
    }

    public function update(SalesDownPaymentRequest $request, $id)
    {
        DB::beginTransaction();

        try {

            $salesDownPayment = SalesDownPayment::findOrFail($id);
            // Ambil semua data kecuali total_order
            $data = $request->except(['total_order', 'total_payment']);
            // Ambil Total Sales Order
            $data['sales_order_amount'] = $this->parseNominal(
                $request->input('total_order', 0)
            );
            // Bersihkan nominal Down Payment
            $data['down_payment_amount'] = $this->parseNominal(
                $request->input('down_payment_amount', 0)
            );
            // Format tanggal Sales Down Payment
            $data['sales_downpayment_date'] = Carbon::parse(
                $request->sales_downpayment_date
            )->format('Y-m-d');
            // Format Due Date
            $data['due_date'] = $request->due_date
                ? Carbon::parse($request->due_date)->format('Y-m-d')
                : null;
            // User yang melakukan update
            $data['updated_by'] = Auth::user()->id;
            $totalOrder = $this->parseNominal($request->input('total_order', 0));
            $downPayment = $this->parseNominal($request->input('down_payment_amount', 0));
            $totalPayment = $this->parseNominal($request->input('total_payment', 0));

            $data['remaining_amount'] = $totalOrder - $downPayment - $totalPayment;
            // Update data
            $salesDownPayment->update($data);
            ArApHistory::create([
                'type' => 'receivable',
                'party_id' => $salesDownPayment->customer_id,
                'transaction_type' => 'invoice',
                'reference_type' => 'sales_down_payment',
                'reference_id' => $salesDownPayment->id,
                'document_no' => $salesDownPayment->sales_downpayment_code,
                'transaction_date' => $salesDownPayment->sales_downpayment_date,
                'debit' => 0,
                'credit' => $salesDownPayment->down_payment_amount,
            ]);
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Sales Down Payment berhasil diperbarui.',
                'redirect' => route('sales-down-payment.index'),
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui data: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        try {
            $table = SalesDownPayment::findOrFail($id);
            $table->active = '0';
            $table->updated_by = Auth::user()->id;
            $table->save();
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function restore(string $id)
    {

        try {
            $table = SalesDownPayment::findOrFail($id);
            $table->active = '1';
            $table->updated_by = Auth::user()->id;
            $table->save();
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function trash(Request $r)
    {
        if ($r->ajax()) {
            // Ambil ID user yang sedang login
            $userId = Auth::user()->id;

            // Query dengan kondisi: Aktif DAN (Status BUKAN draft ATAU Status ADALAH draft kepunyaan sendiri)
            $query = SalesDownPayment::where('active', 0)
                ->orderBy('sales_downpayment_code', 'desc');
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
                ->addColumn('customer', function ($row) {
                    if ($row->customer_id) {
                        return $row->customerID->nama_customer;
                    }

                    return 'N/A';
                })
                ->addColumn('taxpayer_id_type', function ($row) {
                    return DB::table('customer_pajak')
                        ->where('customer_id', $row->customer_id)
                        ->value('tipe_id_pajak') ?? 'N/A';
                })
                ->addColumn('total', function ($row) {
                    return format_uang(convert_currency($row->down_payment_amount, $row->currency_id ?? 1));
                })
                ->addColumn('age', function ($row) {
                    $date = Carbon::parse($row->sales_downpayment_date);
                    $diff = $date->diff(now());

                    if ($diff->y > 0) {
                        return "{$diff->y} Tahun {$diff->m} Bulan {$diff->d} Hari";
                    }

                    if ($diff->m > 0) {
                        return "{$diff->m} Bulan {$diff->d} Hari";
                    }

                    return "{$diff->d} Hari";
                })
                ->addColumn('status', function ($row) {

                    switch ($row->status) {

                        case 'unpaid':
                            $badge = 'bg-label-secondary';
                            $text = 'Unpaid';
                            break;

                        case 'Paid':
                            $badge = 'bg-label-success';
                            $text = 'Processing';
                            break;
                        default:
                            $badge = 'bg-label-danger';
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
                        in_array($row->status, ['partial']) &&
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
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">
                      <button type="button" class="btn btn-primary dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-menu-2 ti-xs me-1"></i>
                      </button>
                      <ul class="dropdown-menu" style="">';

                    if (auth()->user()->can('sales_down_payment-restore')) {
                        $btn .= '<a class="dropdown-item restore" href="javascript:void(0)"
                            data-id="'.$row->id.'"> <i class="ti ti-trash-off me-1"></i> Restore</a>';
                    }

                    return $btn;
                })

                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'taxpayer_id_type', 'customer', 'total', 'age'])
                ->make(true);
        }

        $x = [
            'title' => 'Deleted Sales Down Payment List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Deleted Sales Down Payment', 'url' => ''],
            ],
        ];

        return view('sales.salesDownPayment.sales_down_payment_trash', $x);
    }
    // public function getSalesOrderEdit($customerId)
    // {
    //     $year = date('Y');

    //     $salesOrders = SalesOrder::where('customer_id', $customerId)
    //         ->where('active', 1)
    //         ->whereIn('status', [
    //             'processing',
    //             'partial',
    //             'fully_delivered',
    //         ])
    //         ->orderByDesc('sales_order_date')
    //         ->get([
    //             'id',
    //             'sales_order_code',
    //             'sales_order_date',
    //             'grand_total',
    //         ]);

    //     return response()->json($salesOrders);
    // }
    // public function getSalesOrder($customerId)
    // {
    //     $year = date('Y');

    //     $salesOrders = SalesOrder::where('customer_id', $customerId)
    //         ->where('active', 1)
    //         ->whereIn('status', [
    //             'processing',
    //             'partial',
    //             'fully_delivered',
    //         ])
    //         ->orderByDesc('sales_order_date')
    //         ->get([
    //             'id',
    //             'sales_order_code',
    //             'sales_order_date',
    //             'grand_total',
    //         ]);

    //     $table = "sales_down_payments_{$year}";

    //     // Ambil total DP per Sales Order
    //     $downPayments = DB::table($table)
    //         ->select(
    //             'sales_order_id',
    //             DB::raw('SUM(down_payment_amount) as total_down_payment')
    //         )
    //         ->where('active', 1)
    //         ->where('status', '!=', 'cancelled')
    //         ->groupBy('sales_order_id')
    //         ->pluck('total_down_payment', 'sales_order_id');

    //     // Hanya tampilkan SO yang masih memiliki sisa DP
    //     $salesOrders = $salesOrders
    //         ->filter(function ($salesOrder) use ($downPayments) {

    //             $totalDP = (float) ($downPayments[$salesOrder->id] ?? 0);

    //             $remaining = (float) $salesOrder->grand_total - $totalDP;

    //             return $remaining > 0;
    //         })
    //         ->values();

    //     return response()->json($salesOrders);
    // }

    // public function getSalesOrderTotal($id)
    // {
    //     $year = date('Y');

    //     $salesOrder = DB::table("sales_order_{$year}")
    //         ->where('id', $id)
    //         ->first();

    //     if (! $salesOrder) {
    //         return response()->json([
    //             'success' => false,
    //         ]);
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'grand_total' => $salesOrder->grand_total,
    //     ]);
    // }

    // public function getSalesOrderDownPayment($salesOrderId)
    // {
    //     $year = date('Y');

    //     $table = "sales_down_payments_{$year}";

    //     $salesOrder = SalesOrder::findOrFail($salesOrderId);

    //     // Semua DP aktif untuk Sales Order ini
    //     $downPayments = DB::table($table)
    //         ->where('sales_order_id', $salesOrderId)
    //         ->where('active', 1)
    //         ->where('status', '!=', 'closed')
    //         ->get();

    //     // Total nominal DP yang sudah dibuat
    //     $totalDownPayment = $downPayments->sum(function ($dp) {
    //         return (float) $dp->down_payment_amount;
    //     });

    //     // Sisa yang masih bisa dibuat sebagai DP
    //     $remainingAmount = max(
    //         0,
    //         (float) $salesOrder->grand_total - $totalDownPayment
    //     );

    //     return response()->json([
    //         'sales_order_id' => $salesOrder->id,
    //         'code' => $salesOrder->code,

    //         'sales_order_amount' => (float) $salesOrder->grand_total,

    //         'total_down_payment' => $totalDownPayment,

    //         'remaining_amount' => $remainingAmount,
    //     ]);
    // }

    public function getSalesOrderEdit($customerId)
    {
        $salesOrders = SalesOrder::where('customer_id', $customerId)
            ->where('active', 1)
            ->whereIn('status', [
                'processing',
                'partial',
                'fully_delivered',
            ])
            ->orderByDesc('sales_order_date')
            ->get([
                'id',
                'sales_order_code',
                'sales_order_date',
                'grand_total',
            ]);

        return response()->json($salesOrders);
    }

    public function getSalesDownPaymentData($id)
    {
        $year = date('Y');

        $downPayment = DB::table("sales_down_payments_{$year}")
            ->where('id', $id)
            ->where('active', 1)
            ->first();

        if (! $downPayment) {
            return response()->json([
                'message' => 'Sales Down Payment tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'id' => $downPayment->id,
            'customer_id' => $downPayment->customer_id,
            'sales_downpayment_code' => $downPayment->sales_downpayment_code,
            'sales_downpayment_date' => $downPayment->sales_downpayment_date,
            'sales_order_id' => $downPayment->sales_order_id,
            'bank_id' => $downPayment->bank_id,
            'address' => $downPayment->address,
            'invoice_number' => $downPayment->invoice_number,

            'sales_order_amount' => $downPayment->sales_order_amount,
            'down_payment_percent' => $downPayment->down_payment_percent,
            'down_payment_amount' => $downPayment->down_payment_amount,
            'paid_amount' => $downPayment->paid_amount,
            'remaining_amount' => $downPayment->remaining_amount,

            'due_date' => $downPayment->due_date,
            'description' => $downPayment->description,
            'status' => $downPayment->status,
        ]);
    }

    public function getSalesOrder($customerId)
    {
        $year = date('Y');

        $salesOrders = SalesOrder::where('customer_id', $customerId)
            ->where('active', 1)
            ->whereIn('status', [
                'processing',
                'partial',
                'fully_delivered',
            ])
            ->orderByDesc('sales_order_date')
            ->get([
                'id',
                'sales_order_code',
                'sales_order_date',
                'grand_total',
            ]);

        $table = "sales_down_payments_{$year}";

        // Ambil total DP per Sales Order
        $downPayments = DB::table($table)
            ->select(
                'sales_order_id',
                DB::raw('SUM(down_payment_amount) as total_down_payment')
            )
            ->where('active', 1)
            ->where('status', '!=', 'closed')
            ->groupBy('sales_order_id')
            ->pluck('total_down_payment', 'sales_order_id');

        // Hanya tampilkan SO yang masih memiliki sisa DP
        $salesOrders = $salesOrders
            ->filter(function ($salesOrder) use ($downPayments) {

                $totalDP = (float) ($downPayments[$salesOrder->id] ?? 0);

                $remaining = (float) $salesOrder->grand_total - $totalDP;

                return $remaining > 0;
            })
            ->values();

        return response()->json($salesOrders);
    }

    public function getSalesOrderTotal($id)
    {
        $year = date('Y');

        $salesOrder = DB::table("sales_order_{$year}")
            ->where('id', $id)
            ->first();

        if (! $salesOrder) {
            return response()->json([
                'success' => false,
            ]);
        }

        return response()->json([
            'success' => true,
            'grand_total' => $salesOrder->grand_total,
        ]);
    }

    public function getSalesOrderDownPayment($salesOrderId)
    {
        $year = date('Y');
        $table = "sales_down_payments_{$year}";

        $salesOrder = SalesOrder::findOrFail($salesOrderId);

        $downPayments = DB::table($table)
            ->where('sales_order_id', $salesOrderId)
            ->where('active', 1)
            ->where('status', '!=', 'closed')
            ->get();

        $totalDownPayment = $downPayments->sum(function ($dp) {
            return (float) $dp->down_payment_amount;
        });

        $remainingAmount = max(
            0,
            (float) $salesOrder->grand_total - $totalDownPayment
        );

        return response()->json([
            'sales_order_id' => $salesOrder->id,
            'sales_order_code' => $salesOrder->sales_order_code,
            'sales_order_amount' => (float) $salesOrder->grand_total,
            'total_down_payment' => $totalDownPayment,
            'remaining_amount' => $remainingAmount,
        ]);
    }

    public function print($id)
    {
        $currentYear = date('Y');

        $salesDownPayment = SalesDownPayment::with([
            'customerID',
            'paymentTermID',
            'salesOrder',
        ])->findOrFail($id);

        $company = Company::with('defaultCurrency')->first();

        /*
        |--------------------------------------------------------------------------
        | Logo Company
        |--------------------------------------------------------------------------
        */
        $logoBase64 = null;

        if ($company && $company->logo) {

            $logoPath = public_path($company->logo);

            if (file_exists($logoPath)) {

                $type = pathinfo($logoPath, PATHINFO_EXTENSION);

                $logoBase64 = 'data:image/'.$type.';base64,'.
                    base64_encode(file_get_contents($logoPath));
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Data Sales Down Payment
        |--------------------------------------------------------------------------
        */

        $salesOrder = $salesDownPayment->salesOrder;

        /*
        |--------------------------------------------------------------------------
        | Currency
        |--------------------------------------------------------------------------
        */

        $currency = $company->defaultCurrency->currency_code ?? 'IDR';

        /*
        |--------------------------------------------------------------------------
        | PDF
        |--------------------------------------------------------------------------
        */

        $filename = str_replace(
            ['/', '\\'],
            '-',
            $salesDownPayment->sales_downpayment_code
        );
        $pdf = Pdf::loadView(
            'pdf.sales_down_payment_pdf',
            [
                'salesDownPayment' => $salesDownPayment,
                'salesOrder' => $salesOrder,
                'company' => $company,
                'currency' => $currency,
                'logoBase64' => $logoBase64,
            ]
        );

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream($filename.'.pdf');
    }
}
