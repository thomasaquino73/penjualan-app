<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesDownPayment;
use App\Models\Sales\SalesOrder;
use App\Models\Setting\Company;
use App\Models\Setting\SyaratPembayaran;
use App\Models\Setting\Tax;
use Carbon\Carbon;
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
                    return format_uang(convert_currency($row->paid_amount, $row->currency_id ?? 1));
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

                        // SEND TO APPROVAL
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

                        // EDIT
                        if (
                            $user->can('proforma_invoice-edit') &&
                              in_array($row->status, ['draft', 'pending', 'processing'])
                        ) {

                            $btn .= '
                                <a class="dropdown-item"
                                    href="'.route('proforma-invoice.edit', $row->id).'">

                                    <i class="far fa-edit me-1"></i>
                                    Edit
                                </a>
                            ';
                        }

                        // DELETE
                        if (
                            $user->can('proforma_invoice-delete') &&
                             in_array($row->status, ['draft', 'pending'])
                        ) {

                            $btn .= '
                                <a class="dropdown-item text-danger"
                                    href="javascript:void(0)"
                                    id="delete"
                                    data-id="'.$row->id.'"
                                    data-name="'.$row->proforma_invoice_code.'">

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
                            href="javascript:void(0)" id="close"   data-id="'.$row->id.'" data-name="'.$row->proforma_invoice_code.'">
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
                        href="'.route('proforma-invoice.print', $row->id).'">

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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }


    public function getSalesOrder($customerId)
    {
        $salesOrders = SalesOrder::where('customer_id', $customerId)
            ->where('active', 1)
            ->whereIn('status', [
                'processing',
                'partial',
                'fully_delivered'
            ])
            ->orderByDesc('sales_order_date')
            ->get([
                'id',
                'sales_order_code',
                'sales_order_date',
                'grand_total'
            ]);

        return response()->json($salesOrders);
    }

    public function getSalesOrderTotal($id)
{
    $year = date('Y');

    $salesOrder = DB::table("sales_order_{$year}")
        ->where('id', $id)
        ->first();


    if (!$salesOrder) {
        return response()->json([
            'success' => false
        ]);
    }


    return response()->json([
        'success' => true,
        'grand_total' => $salesOrder->grand_total
    ]);
}
}
