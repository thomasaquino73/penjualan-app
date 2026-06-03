<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Barang;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesQuotation;
use App\Models\Sales\SalesQuotationDetail;
use App\Models\Setting\SyaratPembayaran;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class SalesQuotationController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $routeName = $request->route()->getName();

            $permissionMap = [
                'sales-quotation.index' => 'sales_quotation-browse',
                'sales-quotation.show' => 'sales_quotation-read',
                'sales-quotation.create' => 'sales_quotation-create',
                'sales-quotation.store' => 'sales_quotation-create',
                'sales-quotation.edit' => 'sales_quotation-edit',
                'sales-quotation.update' => 'sales_quotation-edit',
                'sales-quotation.destroy' => 'sales_quotation-delete',
                'sales-quotation.trash' => 'sales_quotation-trash',
                'sales-quotation.restore' => 'sales_quotation-restore',
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
            $query = SalesQuotation::where('active', '<>', 0)
                ->where(function ($q) use ($userId) {
                    $q->where('status', '<>', 'draft')
                        ->orWhere(function ($subQ) use ($userId) {
                            $subQ->where('status', 'draft')
                                ->where('created_by', $userId);
                        });
                })
                ->orderby('sales_quotation_code', 'desc');
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
                ->addColumn('sales_quotation_date', function ($row) {
                    return $row->sales_quotation_date ? Carbon::parse($row->sales_quotation_date)->format('d M Y') : 'N/A';
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
                            $text = 'Partial PO';
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
                        auth()->user()->can('sales_quotation-delete') &&
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
                    // 1. Hitung total kotor (sum amount) dari detail item PO
                    $subTotal = SalesQuotationDetail::where('sales_quotation_id', $row->id)
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
                    $user = Auth::user();

                    $btn = '<div class="btn-group">
                <button type="button" class="btn btn-primary dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown">
                    <i class="ti ti-menu-2 ti-xs me-1"></i>
                </button>
                <ul class="dropdown-menu">';

                    // ─── OWNER ACTION ─────────────────────────────
                    if ($row->created_by == $currentUserId) {

                        if ($row->status == 'draft') {
                            $btn .= '<a class="dropdown-item btn-submit-pr" href="javascript:void(0)" data-id="'.$row->id.'" data-status="processing">
                        <i class="ti ti-send me-1"></i> Processing Requisition
                     </a>';
                            $btn .= '<hr class="dropdown-divider">';
                        }

                        // ✅ EDIT
                        if ($user->can('sales_quotation-edit') && $row->status == 'draft') {
                            $btn .= '<a class="dropdown-item" href="'.route('sales-quotation.edit', $row->id).'">
                        <i class="far fa-edit me-1"></i> Edit
                     </a>';
                        }

                        // ✅ DELETE
                        if ($user->can('sales_quotation-delete') && $row->status == 'draft') {
                            $btn .= '<a class="dropdown-item" href="javascript:void(0)" id="delete"
                        data-id="'.$row->id.'" data-name="'.$row->code.'">
                        <i class="ti ti-trash me-1"></i> Delete
                     </a>';
                        }
                    }

                    // ─── INFO JIKA SUDAH DIPROSES ─────────────────────────────
                    if ($row->status == 'processing') {
                        $btn .= '<a class="dropdown-item" href="'.route('sales-quotation.edit', $row->id).'">
                        <i class="far fa-edit me-1"></i> Edit
                     </a>';
                    }

                    if ($row->status == 'closed') {
                        $btn .= '<span class="dropdown-item-text text-success small">
                    <i class="ti ti-circle-check me-1"></i> Closed
                 </span>';
                    }
                    $btn .= '<a class="dropdown-item"
                href="'.route('sales-quotation.show', $row->id).'">
                <i class="ti ti-list-details"></i> Detail
             </a>';
                    $btn .= '<a class="dropdown-item" target="_blank"
                href="'.route('sales-quotation.print', $row->id).'">
                <i class="ti ti-printer"></i> Print
             </a>';

                    $btn .= '</ul></div>';

                    return $btn;
                })
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'cekbok', 'sales_quotation_date','total'])
                ->make(true);
        }

        $x = [
            'title' => 'Sales Quotation List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Sales Quotation', 'url' => ''],
            ],
        ];

        return view('sales.salesQuotation.sales_quotation_index', $x);
    }

     private function generateNumberId()
    {
        $last = SalesQuotation::whereNotNull('sales_quotation_code')
            ->orderBy('id', 'desc')
            ->first();

        if (! $last) {
            return 'SQ-0001';
        }

        $lastId = $last->sales_quotation_code;

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
            'title' => 'Sales Quotation New',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Sales Quotation', 'url' => ''],
            ],
            'customer' => Customer::where('status', '<>', 0)->get(),
            'idNumber' => $this->generateNumberId(),
            'product' => Barang::where('status', '<>', 0)->get(),
            'paymentTerm' => SyaratPembayaran::where('status', '<>', 0)->get(),
            'salesman' => User::where('status', '<>', 0)->get(),

        ];

        return view('sales.salesQuotation.sales_quotation_create', $x);
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

    public function getKontakByCustomer($customer_id)
    {
        $kontak = DB::table('customer_kontak')->where('customer_id', $customer_id)->get();
        return response()->json($kontak);
    }
}
