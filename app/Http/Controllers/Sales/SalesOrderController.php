<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalesOrderRequest;
use App\Models\BasicCodeDetail;
use App\Models\Inventory\Barang;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderDetail;
use App\Models\Sales\SalesQuotation;
use App\Models\Setting\Shipping;
use App\Models\Setting\SyaratPembayaran;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        if ($r->ajax()) {
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
                    // 1. Hitung total kotor (sum amount) dari detail item PO
                    $subTotal = SalesOrderDetail::where('sales_order_id', $row->id)
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
                        <i class="ti ti-send me-1"></i> Processing Order
                     </a>';
                            $btn .= '<hr class="dropdown-divider">';
                        }

                        // ✅ EDIT
                        if ($user->can('sales_order-edit') && $row->status == 'draft') {
                            $btn .= '<a class="dropdown-item" href="'.route('sales-order.edit', $row->id).'">
                        <i class="far fa-edit me-1"></i> Edit
                     </a>';
                        }

                        // ✅ DELETE
                        if ($user->can('sales_order-delete') && $row->status == 'draft') {
                            $btn .= '<a class="dropdown-item" href="javascript:void(0)" id="delete"
                        data-id="'.$row->id.'" data-name="'.$row->sales_order_code.'">
                        <i class="ti ti-trash me-1"></i> Delete
                     </a>';
                        }
                    }

                    // ─── INFO JIKA SUDAH DIPROSES ─────────────────────────────
                    if ($row->status == 'processing') {
                        $btn .= '<a class="dropdown-item" href="'.route('sales-order.edit', $row->id).'">
                        <i class="far fa-edit me-1"></i> Edit
                     </a>';
                    }

                    if ($row->status == 'closed') {
                        $btn .= '<span class="dropdown-item-text text-success small">
                    <i class="ti ti-circle-check me-1"></i> Closed
                 </span>';
                    }
                    $btn .= '<a class="dropdown-item"
                            href="'.route('sales-order.show', $row->id).'">
                            <i class="ti ti-list-details"></i> Detail
                        </a>';
                    $btn .= '<a class="dropdown-item" target="_blank"
                            href="'.route('sales-order.print', $row->id).'">
                            <i class="ti ti-printer"></i> Print
                        </a>';

                    $btn .= '</ul></div>';

                    return $btn;
                })
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'cekbok', 'sales_order_date', 'total', 'customer'])
                ->make(true);
        }

        $x = [
            'title' => 'Sales Order List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Sales Order', 'url' => ''],
            ],
        ];

        return view('sales.salesOrder.sales_order_index', $x);
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
        $last = SalesOrder::where('sales_order_code', 'like', "SO/$year/$month/%")
            ->orderBy('id', 'desc')
            ->first();

        if (! $last) {
            return "SO/$year/$month/0001";
        }

        $lastId = $last->sales_order_code;

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
            'title' => 'Sales Order New',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Sales Order', 'url' => ''],
            ],
            'customer' => Customer::where('status', '<>', 0)->get(),
            'idNumber' => $this->generateNumberId(),
            'product' => Barang::where('status', '<>', 0)->get(),
            'paymentTerm' => SyaratPembayaran::where('status', '<>', 0)->get(),
            'salesman' => User::where('status', '<>', 0)->get(),
            'shipping' => Shipping::where('status', 1)->get(),
            'fob' => BasicCodeDetail::where('master_id', 7)->get(),

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

            $syaratPembayaran = SyaratPembayaran::find($request->payment_term);

            $data['created_by'] = Auth::id();
            $data['updated_by'] = null;
            $data['sales_order_date'] = Carbon::parse($request->sales_order_date)->format('Y-m-d');
            $data['salesman_id'] = $request->salesman_id;
            $data['customer_contact_id'] = $request->customer_contact_id;
            $data['sub_total'] = $request->sub_total;
            $data['disc_percent'] = $request->percent;
            $data['disc_nominal'] = $request->discount_all;
            $data['grand_total'] = $request->total_order;
            $data['payment_term_id'] = $request->payment_term_id;
            $data['kena_pajak'] = 0;
            $data['total_termasuk_pajak'] = 0;
            $data['address'] = $request->address;
            $data['description'] = $request->description;
            $data['tanggal_pengiriman'] = Carbon::parse($request->shipping_date)->format('Y-m-d');
            $data['jenis_pengiriman'] = $request->jenis_pengiriman;
            $data['fob_id'] = $request->fob_id;

            do {
                $generatedCode = $this->generateNumberId();
                $exists = SalesOrder::where('sales_order_code', $generatedCode)->exists();
            } while ($exists);

            $data['sales_order_code'] = $generatedCode;
            $salesOrder = SalesOrder::create($data);

            if ($itemsDetailRaw) {
                $items = json_decode($itemsDetailRaw, true);
                $involvedPrIds = [];

                if (is_array($items) && count($items) > 0) {
                    foreach ($items as $item) {
                        // $prDetailId = $item['sales_quotation_detail_id'] ?? $item['pr_detail_id'] ?? $item['detail_id'] ?? null;
                        $qtyInputForm = floatval($item['quantity'] ?? $item['qty'] ?? 0);
                        $unitPrice = floatval($item['unit_price'] ?? 0);
                        $discount = floatval($item['discount'] ?? 0);
                        $amount = ($qtyInputForm * $unitPrice) - $discount;

                        SalesOrderDetail::create([
                            'sales_order_id' => $salesOrder->id,
                            // 'sales_quotation_detail_id' => $prDetailId,
                            'product_id' => $item['product_id'],
                            'qty' => $qtyInputForm,
                            'unit_id' => $item['unit_id'],
                            'unit_price' => $unitPrice,
                            'discount' => $discount,
                            'amount' => $item['amount'] ?? $amount,
                            'active' => 1,
                            'created_by' => Auth::id(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                    }

                    // --- OTOMASI STATUS PR MASTER ---
                    foreach ($involvedPrIds as $prId) {
                        $allDetails = DB::table("sales_quotation_detail_{$currentYear}")
                            ->where('sales_quotation_id', $prId)
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

                        DB::table("sales_quotation_{$currentYear}")
                            ->where('id', $prId)
                            ->update(['status' => $newStatus]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Sales Order '.$generatedCode.' berhasil disimpan.',
                'redirect' => route('sales-order.index'),
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

    public function getProcessingData()
    {
        // Mengambil data PR beserta item detailnya yang belum lunas di-PO
        $orders = SalesQuotation::with(['details' => function ($query) {
            $query->whereRaw('sq_qty < qty'); // Hanya ambil item yang belum terpenuhi
        }])
            ->whereNotIn('status', ['draft', 'closed', 'done'])
            ->get();

        return response()->json($orders);
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
            // Kuncinya di sini: kelompokkan berdasarkan harga, lalu ambil tanggal terbaru dengan MAX()
            ->select(
                "{$tableDetail}.unit_price as harga",
                DB::raw("MAX({$tableMaster}.sales_order_date) as tanggal")
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
}
