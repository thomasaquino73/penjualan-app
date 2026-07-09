<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use App\Models\BasicCodeDetail;
use App\Models\Inventory\Barang;
use App\Models\Purchase\PurchaseInvoice;
use App\Models\Purchase\PurchaseInvoiceDetail;
use App\Models\Purchase\Supplier;
use App\Models\Setting\Company;
use App\Models\Setting\Shipping;
use App\Models\Setting\SyaratPembayaran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

                        case 'unpaid':
                            $badge = 'bg-label-secondary';
                            $text = 'Draft';
                            break;
                        case 'partially_paid':
                            $badge = 'bg-label-warning';
                            $text = 'Draft';
                            break;
                        case 'paid':
                            $badge = 'bg-label-success';
                            $text = 'Draft';
                            break;
                        case 'overdue':
                            $badge = 'bg-label-danger';
                            $text = 'Draft';
                            break;
                        default:
                            $badge = 'bg-label-dark';
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
                ->addColumn('cekbok', function ($row) {

                    if (
                        auth()->user()->can('purchase_invoice-delete') &&
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
                            $user->can('purchase_invoice-edit') &&
                            in_array($row->status, ['draft', 'rejected'])
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
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | 2. APPROVAL ACTION
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $row->created_by !== $currentUserId &&
                        $user->can('purchase_invoice-approval')
                    ) {

                        if ($row->status == 'pending') {

                            $btn .= '
                                    <a class="dropdown-item text-success btn-approval-po"
                                        href="javascript:void(0)"
                                        data-status="approved"
                                        data-id="'.$row->id.'">

                                        <i class="ti ti-check me-1"></i>
                                        Approve PO
                                    </a>
                                ';

                            $btn .= '
                                    <a class="dropdown-item text-danger btn-approval-po"
                                        href="javascript:void(0)"
                                        data-status="rejected"
                                        data-id="'.$row->id.'">

                                        <i class="ti ti-x me-1"></i>
                                        Reject PO
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
                    if ($row->status != 'closed') {
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
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'cekbok', 'supplier', 'date', 'amount'])
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

        // Prefix yang akan dicari
        $prefix = "PI/{$tahun}/{$bulanRomawi}/";

        // Ambil nomor terakhir pada bulan & tahun yang sama
        $last = PurchaseInvoice::where('code', 'like', $prefix.'%')
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
            'title' => 'Purchase Invoice New',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Purchase Invoice', 'url' => ''],
            ],
            'supplier' => Supplier::where('status', 1)->get(),
            'company' => Company::first(),
            'idNumber' => $this->generateNumberId(),
            'shipping' => Shipping::where('status', 1)->get(),
            'paymentTerm' => SyaratPembayaran::where('status', 1)->get(),
            'product' => Barang::where('status', '<>', 0)->get(),
            'fob' => BasicCodeDetail::where('master_id', 7)->get(),
            'taxes' => BasicCodeDetail::where('master_id', 6)->get(),

        ];

        return view('purchase.purchase_invoice.purchase_invoice_create', $x);
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
