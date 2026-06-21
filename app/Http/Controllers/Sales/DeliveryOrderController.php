<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\BasicCodeDetail;
use App\Models\Inventory\Barang;
use App\Models\Inventory\Warehouse;
use App\Models\Sales\Customer;
use App\Models\Sales\DeliveryOrder;
use App\Models\Sales\DeliveryOrderDetail;
use App\Models\Setting\Shipping;
use App\Models\Setting\SyaratPembayaran;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

            // Query dengan kondisi: Aktif DAN (Status BUKAN draft ATAU Status ADALAH draft kepunyaan sendiri)
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
                    // 1. Hitung total kotor (sum amount) dari detail item SO
                    $subTotal = DeliveryOrderDetail::where('delivery_order_id', $row->id)
                        ->where('active', 1)
                        ->sum('amount');

                    // 2. Hitung grand total: Subtotal dikurangi diskon nominal yang ada di tabel induk ($row)
                    // Gunakan ?? 0 jika kolom disc_nominal di database bisa bernilai null
                    $grandTotal = $subTotal - ($row->disc_nominal ?? 0);

                    // 3. Kembalikan nilai yang sudah dikonversi dan diformat
                    return format_uang(convert_currency($grandTotal, $row->currency_id ?? 1));
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
                                <a class="dropdown-item btn-submit-po"
                                    href="javascript:void(0)"
                                    data-id="'.$row->id.'">

                                    <i class="ti ti-send me-1"></i>
                                    Send To Approval
                                </a>
                            ';
                        }

                        // EDIT
                        if (
                            $user->can('delivery_order-edit') &&
                            in_array($row->status, ['draft', 'rejected'])
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
                            $user->can('delivery_order-delete') &&
                            $row->status == 'draft'
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
                                        Approve SO
                                    </a>
                                ';

                            $btn .= '
                                    <a class="dropdown-item text-danger btn-approval-po"
                                        href="javascript:void(0)"
                                        data-status="rejected"
                                        data-id="'.$row->id.'">

                                        <i class="ti ti-x me-1"></i>
                                        Reject SO
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
                href="'.route('sales-order.receive', $row->id).'">

                <i class="ti ti-package-import me-1"></i>
                Receive Item
            </a>
        ';
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | 5. CANCEL SO
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
                Cancel SO
            </a>
        ';
                    }
                    if ($row->status != 'closed') {
                        $btn .= '<a class="dropdown-item"
                href="javascript:void(0)" id="close"   data-id="'.$row->id.'" data-name="'.$row->delivery_order_code.'">
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
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'cekbok', 'delivery_order_date', 'total', 'customer'])
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
        $year = date('Y');
        $month = $this->bulanRomawi(date('n'));

        // 🔥 ambil data terakhir berdasarkan tahun & bulan yg sama
        $last = DeliveryOrder::where('delivery_order_code', 'like', "DO/$year/$month/%")
            ->orderBy('id', 'desc')
            ->first();

        if (! $last) {
            return "DO/$year/$month/0001";
        }

        $lastId = $last->delivery_order_code;

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

        ];

        return view('sales.deliveryOrder.delivery_order_create', $x);
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
}
