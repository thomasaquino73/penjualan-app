<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseDownPaymentRequest;
use App\Models\Purchase\PurchaseDownPayment;
use App\Models\Purchase\PurchaseOrder;
use App\Models\Purchase\Supplier;
use App\Models\Sales\ArApHistory;
use App\Models\Setting\Company;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PurchaseDownPaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $routeName = $request->route()->getName();

            $permissionMap = [
                'purchase-down-payment.index' => 'purchase_down_payment-browse',
                'purchase-down-payment.show' => 'purchase_down_payment-read',
                'purchase-down-payment.create' => 'purchase_down_payment-create',
                'purchase-down-payment.store' => 'purchase_down_payment-create',
                'purchase-down-payment.edit' => 'purchase_down_payment-edit',
                'purchase-down-payment.update' => 'purchase_down_payment-edit',
                'purchase-down-payment.destroy' => 'purchase_down_payment-delete',
                'purchase-down-payment.trash' => 'purchase_down_payment-trash',
                'purchase-down-payment.restore' => 'purchase_down_payment-restore',
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
            $query = PurchaseDownPayment::where('active', '<>', 0)
                ->whereYear('created_at', now()->year)
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
                ->addColumn('supplier', function ($row) {
                    if ($row->supplier_id) {
                        return $row->supplierID->nama_supplier;
                    }

                    return 'N/A';
                })
                ->addColumn('total', function ($row) {
                    return format_uang(convert_currency($row->down_payment_amount, $row->currency_id ?? 1));
                })
                ->addColumn('age', function ($row) {
                    $date = Carbon::parse($row->purchase_downpayment_date);
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
                            $user->can('purchase_down_payment-edit') &&
                              in_array($row->status, ['unpaid'])
                        ) {

                            $btn .= '
                                <a class="dropdown-item"
                                    href="'.route('purchase-down-payment.edit', $row->id).'">
                                    <i class="far fa-edit me-1"></i>
                                    Edit
                                </a>
                            ';
                        }

                        // DELETE
                        if (
                            $user->can('purchase_down_payment-delete') &&
                              in_array($row->status, ['unpaid'])
                        ) {

                            $btn .= '
                                <a class="dropdown-item text-danger"
                                    href="javascript:void(0)"
                                    id="delete"
                                    data-id="'.$row->id.'"
                                    data-name="'.$row->purchase_downpayment_code.'">

                                    <i class="ti ti-trash me-1"></i>
                                    Delete
                                </a>
                            ';
                        }
                    }

                    $btn .= '
                    <a class="dropdown-item"
                        target="_blank"
                        href="'.route('purchase-down-payment.print', $row->id).'">

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
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'supplier', 'total', 'age'])
                ->make(true);
        }

        $x = [
            'title' => 'Purchase Down Payment List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Purchase Down Payment', 'url' => ''],
            ],
        ];

        return view('purchase.purchaseDownPayment.purchase_down_payment_index', $x);
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

        $prefix = "PDP/{$tahun}/{$bulanRomawi}/";

        $last = PurchaseDownPayment::where('purchase_downpayment_code', 'like', $prefix.'%')
            ->orderByRaw("
            CAST(
                REGEXP_REPLACE(
                    SUBSTRING_INDEX(purchase_downpayment_code,'/',-1),
                    '[^0-9]',
                    ''
                ) AS UNSIGNED
            ) DESC
        ")
            ->first();

        if ($last) {
            preg_match('/(\d+)/', substr($last->purchase_downpayment_code, strrpos($last->purchase_downpayment_code, '/') + 1), $match);
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
            'title' => 'Purchase Down Payment New',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Purchase Down Payment', 'url' => ''],
            ],
            'supplier' => Supplier::where('status', '<>', 0)->get(),
            'idNumber' => $this->generateNumberId(),
            'suppBank' => DB::table('supplier_rekening')->get(),
            'company' => $company->defaultCurrency,

        ];

        return view('purchase.purchaseDownPayment.purchase_down_payment_create', $x);
    }

    public function store(PurchaseDownPaymentRequest $request)
    {
        DB::beginTransaction();

        try {

            $data = $request->except(['total_order', 'save_and_new']);

            // Ambil total_order dari request
            $data['purchase_order_amount'] = $this->parseNominal(
                $request->input('total_order', 0)
            );

            // Bersihkan nominal DP
            $data['down_payment_amount'] = $this->parseNominal(
                $request->input('down_payment_amount', 0)
            );

            $data['purchase_downpayment_date'] = Carbon::parse(
                $request->purchase_downpayment_date
            )->format('Y-m-d');

            $data['due_date'] = $request->due_date
                ? Carbon::parse($request->due_date)->format('Y-m-d')
                : null;
            $data['created_by'] = Auth::user()->id;

            $purchaseDownPayment = PurchaseDownPayment::create($data);
            ArApHistory::create([
                'type' => 'payable',
                'party_id' => $purchaseDownPayment->supplier_id,
                'transaction_type' => 'down_payment',
                'reference_type' => 'purchase_down_payment',
                'reference_id' => $purchaseDownPayment->id,
                'document_no' => $purchaseDownPayment->purchase_downpayment_code,
                'transaction_date' => $purchaseDownPayment->purchase_downpayment_date,
                'debit' => $purchaseDownPayment->down_payment_amount,
                'credit' => 0,
            ]);
            DB::commit();
            $redirectUrl = $request->save_and_new == 1
                            ? route('purchase-down-payment.create')
                            : route('purchase-down-payment.index');

            return response()->json([
                'status' => 'success',
                'message' => 'Purchase Down Payment berhasil disimpan.',
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

    public function edit($id)
    {

        $company = Company::with('defaultCurrency')->first();
        $sdp = PurchaseDownPayment::findorfail($id);

        $x = [
            'title' => 'Edit Purchase Down Payment ',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Edit Purchase Down Payment', 'url' => ''],
            ],
            'supplier' => Supplier::where('status', '<>', 0)->get(),
            'idNumber' => $this->generateNumberId(),
            'suppBank' => DB::table('supplier_rekening')->get(),
            'poNumber' => PurchaseOrder::where('active', '<>', 0)->get(),
            'company' => $company->defaultCurrency,
            'model' => $sdp,

        ];

        return view('purchase.purchaseDownPayment.purchase_down_payment_edit', $x);
    }

    public function update(PurchaseDownPaymentRequest $request, $id)
    {
        DB::beginTransaction();

        try {

            $purchaseDownPayment = PurchaseDownPayment::findOrFail($id);

            $data = $request->except(['total_order']);

            // Ambil total Purchase Order dari request
            $data['purchase_order_amount'] = $this->parseNominal(
                $request->input('total_order', 0)
            );

            // Bersihkan nominal DP
            $data['down_payment_amount'] = $this->parseNominal(
                $request->input('down_payment_amount', 0)
            );

            // Format tanggal DP
            $data['purchase_downpayment_date'] = Carbon::parse(
                $request->purchase_downpayment_date
            )->format('Y-m-d');

            // Format due date
            $data['due_date'] = $request->due_date
                ? Carbon::parse($request->due_date)->format('Y-m-d')
                : null;

            // User yang melakukan update
            $data['updated_by'] = Auth::id();

            $purchaseDownPayment->update($data);

            ArApHistory::create([
                'type' => 'payable',
                'party_id' => $purchaseDownPayment->supplier_id,
                'transaction_type' => 'down_payment',
                'reference_type' => 'purchase_down_payment',
                'reference_id' => $purchaseDownPayment->id,
                'document_no' => $purchaseDownPayment->purchase_downpayment_code,
                'transaction_date' => $purchaseDownPayment->purchase_downpayment_date,
                'debit' => $purchaseDownPayment->down_payment_amount,
                'credit' => 0,
            ]);
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Purchase Down Payment berhasil diperbarui.',
                'redirect' => route('purchase-down-payment.index'),
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui data: '.$e->getMessage(),
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

    public function destroy(string $id)
    {

        try {
            $table = PurchaseDownPayment::findOrFail($id);
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
            $table = PurchaseDownPayment::findOrFail($id);
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
            $query = PurchaseDownPayment::where('active', 0)
                ->orderBy('purchase_downpayment_code', 'desc');
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
                ->addColumn('supplier', function ($row) {
                    if ($row->supplier_id) {
                        return $row->supplierID->nama_supplier;
                    }

                    return 'N/A';
                })
                ->addColumn('total', function ($row) {
                    return format_uang(convert_currency($row->down_payment_amount, $row->currency_id ?? 1));
                })
                ->addColumn('age', function ($row) {
                    $date = Carbon::parse($row->purchase_downpayment_date);
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

                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">
                      <button type="button" class="btn btn-primary dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-menu-2 ti-xs me-1"></i>
                      </button>
                      <ul class="dropdown-menu" style="">';

                    if (auth()->user()->can('purchase_down_payment-restore')) {
                        $btn .= '<a class="dropdown-item restore" href="javascript:void(0)"
                            data-id="'.$row->id.'"> <i class="ti ti-trash-off me-1"></i> Restore</a>';
                    }

                    return $btn;
                })
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'supplier', 'total', 'age'])
                ->make(true);
        }

        $x = [
            'title' => 'Deleted Purchase Down Payment List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Deleted Purchase Down Payment', 'url' => ''],
            ],
        ];

        return view('purchase.purchaseDownPayment.purchase_down_payment_trash', $x);
    }

    public function getPurchaseOrderEdit($supplierId)
    {
        $purchaseOrders = PurchaseOrder::where('supplier_id', $supplierId)
            ->where('active', 1)
            ->whereIn('status', [
                'processing',
                'partially_received',
                'fully_received',
            ])
            ->orderByDesc('datePO')
            ->get([
                'id',
                'code',
                'datePO',
                'grand_total',
            ]);

        return response()->json($purchaseOrders);
    }

    public function getPurchaseDownPaymentData($id)
    {
        $year = date('Y');

        $downPayment = DB::table("purchase_down_payments_{$year}")
            ->where('id', $id)
            ->where('active', 1)
            ->first();

        if (! $downPayment) {
            return response()->json([
                'message' => 'Purchase Down Payment tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'id' => $downPayment->id,
            'supplier_id' => $downPayment->supplier_id,
            'purchase_downpayment_code' => $downPayment->purchase_downpayment_code,
            'purchase_downpayment_date' => $downPayment->purchase_downpayment_date,
            'purchase_order_id' => $downPayment->purchase_order_id,
            'bank_id' => $downPayment->bank_id,
            'address' => $downPayment->address,
            'invoice_number' => $downPayment->invoice_number,

            'purchase_order_amount' => $downPayment->purchase_order_amount,
            'down_payment_percent' => $downPayment->down_payment_percent,
            'down_payment_amount' => $downPayment->down_payment_amount,
            'paid_amount' => $downPayment->paid_amount,
            'remaining_amount' => $downPayment->remaining_amount,

            'due_date' => $downPayment->due_date,
            'description' => $downPayment->description,
            'status' => $downPayment->status,
        ]);
    }

    public function getPurchaseOrder($supplierId)
    {
        $year = date('Y');

        $purchaseOrders = PurchaseOrder::where('supplier_id', $supplierId)
            ->where('active', 1)
            ->whereIn('status', [
                'processing',
                'partially_received',
                'fully_received',
            ])
            ->orderByDesc('datePO')
            ->get([
                'id',
                'code',
                'datePO',
                'grand_total',
            ]);

        $table = "purchase_down_payments_{$year}";

        // Ambil total DP per Purchase Order
        $downPayments = DB::table($table)
            ->select(
                'purchase_order_id',
                DB::raw('SUM(down_payment_amount) as total_down_payment')
            )
            ->where('active', 1)
            ->where('status', '!=', 'closed')
            ->groupBy('purchase_order_id')
            ->pluck('total_down_payment', 'purchase_order_id');

        // Hanya tampilkan SO yang masih memiliki sisa DP
        $purchaseOrders = $purchaseOrders
            ->filter(function ($purchaseOrder) use ($downPayments) {

                $totalDP = (float) ($downPayments[$purchaseOrder->id] ?? 0);

                $remaining = (float) $purchaseOrder->grand_total - $totalDP;

                return $remaining > 0;
            })
            ->values();

        return response()->json($purchaseOrders);
    }

    public function getPurchaseOrderTotal($id)
    {
        $year = date('Y');

        $purchaseOrder = DB::table("purchase_order_{$year}")
            ->where('id', $id)
            ->first();

        if (! $purchaseOrder) {
            return response()->json([
                'success' => false,
            ]);
        }

        return response()->json([
            'success' => true,
            'grand_total' => $purchaseOrder->grand_total,
        ]);
    }

    public function getPurchaseOrderDownPayment($purchaseOrderId)
    {
        $year = date('Y');

        $table = "purchase_down_payments_{$year}";

        $purchaseOrder = PurchaseOrder::findOrFail($purchaseOrderId);

        // Semua DP aktif untuk Purchase Order ini
        $downPayments = DB::table($table)
            ->where('purchase_order_id', $purchaseOrderId)
            ->where('active', 1)
            ->where('status', '!=', 'closed')
            ->get();

        // Total nominal DP yang sudah dibuat
        $totalDownPayment = $downPayments->sum(function ($dp) {
            return (float) $dp->down_payment_amount;
        });

        // Sisa yang masih bisa dibuat sebagai DP
        $remainingAmount = max(
            0,
            (float) $purchaseOrder->grand_total - $totalDownPayment
        );

        return response()->json([
            'purchase_order_id' => $purchaseOrder->id,
            'code' => $purchaseOrder->code,

            'purchase_order_amount' => (float) $purchaseOrder->grand_total,

            'total_down_payment' => $totalDownPayment,

            'remaining_amount' => $remainingAmount,
        ]);
    }

    // public function getPurchaseOrderDownPayment($purchaseOrderId)
    // {
    //     $year = date('Y');
    //     $table = "purchase_down_payments_{$year}";

    //     $purchaseOrder = PurchaseOrder::findOrFail($purchaseOrderId);

    //     // Ambil semua DP aktif untuk Purchase Order ini
    //     $downPayments = DB::table($table)
    //         ->where('purchase_order_id', $purchaseOrderId)
    //         ->where('active', 1)
    //         ->where('status', '!=', 'closed')
    //         ->get();

    //     // Total nominal DP yang sudah dibuat (hanya dijumlahkan, tanpa kalkulasi sisa)
    //     $totalDownPayment = $downPayments->sum(function ($dp) {
    //         return (float) $dp->down_payment_amount;
    //     });

    //     return response()->json([
    //         'purchase_order_id' => $purchaseOrder->id,
    //         'code' => $purchaseOrder->code,
    //         'purchase_order_amount' => (float) $purchaseOrder->grand_total,
    //         'total_down_payment' => $totalDownPayment,
    //     ]);
    // }
    // public function getPurchaseOrderTotal($id)
    // {
    //     $year = date('Y');

    //     $purchaseOrder = DB::table("purchase_order_{$year}")
    //         ->where('id', $id)
    //         ->first();

    //     if (! $purchaseOrder) {
    //         return response()->json([
    //             'success' => false,
    //         ]);
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'grand_total' => $purchaseOrder->grand_total,
    //     ]);
    // }

    //     public function getPurchaseOrder($supplierId, Request $request = null)
    // {
    //     // Ambil parameter jika dikirim via query string (misal: ?current_dp_id=5)
    //     $currentDpId = request('current_dp_id');
    //     $year = date('Y');

    //     $purchaseOrders = PurchaseOrder::where('supplier_id', $supplierId)
    //         ->where('active', 1)
    //         ->whereIn('status', [
    //             'processing',
    //             'partially_received',
    //             'fully_received',
    //         ])
    //         ->orderByDesc('datePO')
    //         ->get([
    //             'id',
    //             'code',
    //             'datePO',
    //             'grand_total',
    //         ]);

    //     $table = "purchase_down_payments_{$year}";

    //     // Ambil total DP per Purchase Order
    //     $downPaymentsQuery = DB::table($table)
    //         ->select(
    //             'purchase_order_id',
    //             DB::raw('SUM(down_payment_amount) as total_down_payment')
    //         )
    //         ->where('active', 1)
    //         ->where('status', '!=', 'closed');

    //     // Jika sedang mode edit, abaikan/kecualikan nominal DP dari transaksi yang sedang diedit ini sendiri
    //     if ($currentDpId) {
    //         $downPaymentsQuery->where('id', '!=', $currentDpId);
    //     }

    //     $downPayments = $downPaymentsQuery->groupBy('purchase_order_id')
    //         ->pluck('total_down_payment', 'purchase_order_id');

    //     // Filter PO: tampilkan jika sisa DP > 0 ATAU jika PO tersebut adalah PO yang sedang digunakan oleh DP yang sedang diedit
    //     $purchaseOrders = $purchaseOrders
    //         ->filter(function ($purchaseOrder) use ($downPayments, $currentDpId, $table, $year) {

    //             // Jika dalam mode edit, cek apakah PO ini dipakai oleh DP yang sedang diedit
    //             if ($currentDpId) {
    //                 $isUsedByCurrentEdit = DB::table($table)
    //                     ->where('id', $currentDpId)
    //                     ->where('purchase_order_id', $purchaseOrder->id)
    //                     ->exists();

    //                 if ($isUsedByCurrentEdit) {
    //                     return true; // Pastikan PO yang sedang dipilih/diedit tetap muncul
    //                 }
    //             }

    //             $totalDP = (float) ($downPayments[$purchaseOrder->id] ?? 0);
    //             $remaining = (float) $purchaseOrder->grand_total - $totalDP;

    //             return $remaining > 0;
    //         })
    //         ->values();

    //     return response()->json($purchaseOrders);
    // }

    //     public function getPurchaseDownPaymentData($id)
    // {
    //     $year = date('Y');

    //     $downPayment = DB::table("purchase_down_payments_{$year}")
    //         ->where('id', $id)
    //         ->where('active', 1)
    //         ->first();

    //     if (!$downPayment) {
    //         return response()->json([
    //             'message' => 'Purchase Down Payment tidak ditemukan.'
    //         ], 404);
    //     }

    //     return response()->json([
    //         'id' => $downPayment->id,
    //         'supplier_id' => $downPayment->supplier_id,
    //         'purchase_downpayment_code' => $downPayment->purchase_downpayment_code,
    //         'purchase_downpayment_date' => $downPayment->purchase_downpayment_date,
    //         'purchase_order_id' => $downPayment->purchase_order_id,
    //         'bank_id' => $downPayment->bank_id,
    //         'address' => $downPayment->address,
    //         'invoice_number' => $downPayment->invoice_number,

    //         'purchase_order_amount' => $downPayment->purchase_order_amount,
    //         'down_payment_percent' => $downPayment->down_payment_percent,
    //         'down_payment_amount' => $downPayment->down_payment_amount,
    //         'paid_amount' => $downPayment->paid_amount,
    //         'remaining_amount' => $downPayment->remaining_amount,

    //         'due_date' => $downPayment->due_date,
    //         'description' => $downPayment->description,
    //         'status' => $downPayment->status,
    //     ]);
    // }

    public function print($id)
    {
        $currentYear = date('Y');

        $purchaseDownPayment = PurchaseDownPayment::with([
            'supplierID',
            'purchaseOrder',
        ])->findOrFail($id);
        $bank = DB::table("purchase_down_payments_{$currentYear} as pdp")
            ->leftJoin(
                'supplier_rekening as sr',
                'pdp.bank_id',
                '=',
                'sr.id'
            )
            ->leftJoin(
                'basic_code_detail as bcd',
                'sr.nama_bank',
                '=',
                'bcd.id'
            )
            ->where('pdp.id', $id)
            ->select(
                'sr.id as bank_account_id',
                'sr.nomor_rekening',
                'sr.nama_rekening',
                'bcd.id as bank_id',
                'bcd.detail as bank_name'
            )
            ->first();
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

        $purchaseOrder = $purchaseDownPayment->purchaseOrder;

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
            $purchaseDownPayment->purchase_downpayment_code
        );
        $pdf = Pdf::loadView(
            'pdf.purchase_down_payment_pdf',
            [
                'purchaseDownPayment' => $purchaseDownPayment,
                'purchaseOrder' => $purchaseOrder,
                'company' => $company,
                'currency' => $currency,
                'bank' => $bank,
                'logoBase64' => $logoBase64,
            ]
        );

        $pdf->setPaper('A5', 'landscape');

        return $pdf->stream($filename.'.pdf');
    }
}
