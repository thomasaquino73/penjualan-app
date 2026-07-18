<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalesQuotationRequest;
use App\Models\Inventory\Barang;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesQuotation;
use App\Models\Sales\SalesQuotationDetail;
use App\Models\Setting\Company;
use App\Models\Setting\SyaratPembayaran;
use App\Models\Setting\Tax;
use App\Models\User;
use App\Notifications\SalesQuotationNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
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
                ->addColumn('sales_quotation_date', function ($row) {
                    return $row->sales_quotation_date ? Carbon::parse($row->sales_quotation_date)->format('d M Y') : 'N/A';
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
                            $badge = 'bg-dark';
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
                    return format_uang(convert_currency($row->grand_total, $row->currency_id ?? 1));
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
                        <i class="ti ti-send me-1"></i> Processing Quotation
                     </a>';
                            $btn .= '<hr class="dropdown-divider">';
                        }

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
                        data-id="'.$row->id.'" data-name="'.$row->sales_quotation_code.'">
                        <i class="ti ti-trash me-1"></i> Delete
                     </a>';
                    }

                    // ─── INFO JIKA SUDAH DIPROSES ─────────────────────────────
                    if ($row->status == 'processing') {
                        $btn .= '<a class="dropdown-item" href="'.route('sales-quotation.edit', $row->id).'">
                        <i class="far fa-edit me-1"></i> Edit
                     </a>';
                    }

                    if ($row->status != 'closed') {
                        $btn .= '<a class="dropdown-item"
                href="javascript:void(0)" id="close"   data-id="'.$row->id.'" data-name="'.$row->sales_quotation_code.'">
                <i class="ti ti-lock"></i> Close SQ
             </a>';
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
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'cekbok', 'sales_quotation_date', 'total', 'customer'])
                ->make(true);
        }

        $stats = $this->getStatistics($query);
        $x = [
            'title' => 'Sales Quotation List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Sales Quotation', 'url' => ''],
            ],
            'totalPurchase' => $stats['totalPurchase'],
            'partiallyReceived' => $stats['partiallyReceived'],
            'grandTotal' => $stats['grandTotal'],
            'completedReceived' => $stats['completedReceived'],
        ];

        return view('sales.salesQuotation.sales_quotation_index', $x);
    }

    private function getStatistics($query)
    {
        $month = now()->month;
        $year = now()->year;

        return [
            'totalPurchase' => SalesQuotation::where('active', '<>', 0)
                ->whereMonth('sales_quotation_date', $month)
                ->count(),

            'partiallyReceived' => SalesQuotation::where('status', 'partially')
                ->whereMonth('sales_quotation_date', $month)
                ->count(),

            'grandTotal' => SalesQuotation::where('active', '<>', 0)
                ->whereMonth('sales_quotation_date', $month)
                ->whereYear('sales_quotation_date', $year)
                ->whereNotIn('status', ['rejected', 'draft'])
                ->sum('grand_total'),

            'completedReceived' => SalesQuotation::where('status', 'closed')
                ->whereMonth('sales_quotation_date', $month)
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

        $prefix = "SQ/{$tahun}/{$bulanRomawi}/";

        $last = SalesQuotation::where('sales_quotation_code', 'like', $prefix.'%')
            ->orderByRaw("
            CAST(
                REGEXP_REPLACE(
                    SUBSTRING_INDEX(sales_quotation_code,'/',-1),
                    '[^0-9]',
                    ''
                ) AS UNSIGNED
            ) DESC
        ")
            ->first();

        if ($last) {
            preg_match('/(\d+)/', substr($last->sales_quotation_code, strrpos($last->sales_quotation_code, '/') + 1), $match);
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
            'taxes' => $taxes,
            'defaultTax' => $defaultTax,
            'company' => $company->defaultCurrency,

        ];

        return view('sales.salesQuotation.sales_quotation_create', $x);
    }

    public function store(SalesQuotationRequest $request)
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
            $data['sales_quotation_date'] = Carbon::parse($request->sales_quotation_date)->format('Y-m-d');
            $data['salesman_id'] = $request->salesman_id;
            $data['customer_contact_id'] = $request->customer_contact_id;
            $data['sub_total'] = $request->sub_total;
            $data['disc_percent'] = $request->percent;
            $data['disc_nominal'] = $request->discount_all;
            $data['grand_total'] = $request->total_order;
            $data['payment_term_id'] = $request->payment_term_id;
            $data['kena_pajak'] = $request->has('kena_pajak') ? 1 : 0;
            $data['total_termasuk_pajak'] = $request->has('total_termasuk_pajak') ? 1 : 0;
            $data['address'] = $request->address;
            $data['description'] = $request->description;
            $data['taxpayer_data'] = $request->taxpayer_data;
            $data['tax_id'] = $request->tax_id;
            $data['tax_amount'] = $request->tax_amount;

            $salesQuotation = null;
            $maxRetry = 10;
            $currentCode = $request->sales_quotation_code; // Ambil input awal dari user

            for ($attempt = 1; $attempt <= $maxRetry; $attempt++) {
                try {
                    $data['sales_quotation_code'] = $currentCode;
                    $salesQuotation = SalesQuotation::create($data);
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

            if (! $salesQuotation) {
                throw new \Exception('Gagal membuat Sales Quotation: Nomor sudah penuh atau sistem sibuk.');
            }

            if ($itemsDetailRaw) {
                $items = json_decode($itemsDetailRaw, true);
                $involvedPrIds = [];

                if (is_array($items) && count($items) > 0) {
                    foreach ($items as $index => $item) {
                        // $prDetailId = $item['purchase_requisition_detail_id'] ?? $item['pr_detail_id'] ?? $item['detail_id'] ?? null;
                        $qtyInputForm = floatval($item['quantity'] ?? $item['qty'] ?? 0);
                        $unitPrice = floatval($item['unit_price'] ?? 0);
                        $discount = floatval($item['discount'] ?? 0);
                        $amount = ($qtyInputForm * $unitPrice) - $discount;

                        SalesQuotationDetail::create([
                            'sales_quotation_id' => $salesQuotation->id,
                            // 'purchase_requisition_detail_id' => $prDetailId,
                            'product_id' => $item['product_id'],
                            'qty' => $qtyInputForm,
                            'outstanding_qty' => $qtyInputForm,
                            'unit_id' => $item['unit_id'],
                            'unit_price' => $unitPrice,
                            'discount_percent' => $item['discount_percent'],
                            'discount' => $discount,
                            'urutan' => $index,
                            'amount' => $item['amount'] ?? $amount,
                            'active' => 1,
                            'created_by' => Auth::id(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                    }

                    // --- OTOMASI STATUS PR MASTER ---
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
                            ->update(['status' => $newStatus]);
                    }
                }
            }

            DB::commit();

            $redirectUrl = $request->save_and_new == 1
                ? route('sales-quotation.create') // Kembali kosongkan form untuk input data PR baru lagi
                : route('sales-quotation.index');  // Selesai dan kembali ke tabel index utama

            return response()->json([
                'success' => true,
                'message' => 'Sales Quotation saved successfully!',
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

    public function show(string $id)
    {
        $salesQuotation = SalesQuotation::with(['details.produkID', 'details.unitID', 'details.salesOrderDetails.salesOrder'])->findOrFail($id);
        $company = Company::first();
        $logoBase64 = null;
        if ($company && $company->logo) {
            $path = public_path($company->logo);
            if (file_exists($path)) {
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($path);
                $logoBase64 = 'data:image/'.$type.';base64,'.base64_encode($data);
            }
        }
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
            'model' => $salesQuotation,
            'company' => $company,
            'logoBase64' => $logoBase64,
        ];

        return view('sales.salesQuotation.sales_quotation_show', $x);
    }

    public function edit(string $id)
    {
        $salesQuotation = SalesQuotation::with(['details.produkID', 'details.unitID'])->findOrFail($id);
        $taxes = Tax::where('is_active', true)
            ->whereIn('usage', ['purchase', 'both'])
            ->get();

        // 🔥 Ambil default tax (misalnya PPN)
        $defaultTax = Tax::where('is_active', true)
            ->where('is_default', true)
            ->whereIn('usage', ['purchase', 'both'])
            ->first();
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
            'model' => $salesQuotation,
            'taxes' => $taxes,
            'defaultTax' => $defaultTax,
        ];

        return view('sales.salesQuotation.sales_quotation_edit', $x);
    }

    public function update(SalesQuotationRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();

            $salesQuotation = SalesQuotation::findOrFail($id);
            $code = $request->sales_quotation_code;

            while (
                SalesQuotation::where('sales_quotation_code', $code)
                    ->where('id', '!=', $salesQuotation->id)
                    ->exists()
            ) {
                $code = $this->generateNumberId();
            }
            $itemsDetailRaw = $request->input('items_detail');
            unset($data['items_detail']);

            // Update data utama
            $data['updated_by'] = Auth::id();
            $data['sales_quotation_date'] = Carbon::parse($request->sales_quotation_date)->format('Y-m-d');
            $data['sales_quotation_code'] = $request->sales_quotation_code;
            $data['salesman_id'] = $request->salesman_id;
            $data['customer_contact_id'] = $request->customer_contact_id;
            $data['sub_total'] = $request->sub_total;
            $data['disc_percent'] = $request->percent;
            $data['disc_nominal'] = $request->discount_all;
            $data['grand_total'] = $request->total_order;
            $data['payment_term_id'] = $request->payment_term_id;
            $data['address'] = $request->address;
            $data['description'] = $request->description;
            $data['taxpayer_data'] = $request->taxpayer_data;
            $data['kena_pajak'] = $request->has('kena_pajak') ? 1 : 0;
            $data['total_termasuk_pajak'] = $request->has('total_termasuk_pajak') ? 1 : 0;
            $data['taxpayer_data'] = $request->taxpayer_data;
            $data['tax_id'] = $request->tax_id;
            $data['tax_amount'] = $request->tax_amount;
            $salesQuotation->update($data);

            // Update Detail: Hapus yang lama, insert yang baru
            // (Ini cara paling aman jika tidak menggunakan ID unik pada tiap baris detail di form)
            SalesQuotationDetail::where('sales_quotation_id', $salesQuotation->id)->delete();

            if ($itemsDetailRaw) {
                $items = json_decode($itemsDetailRaw, true);

                if (is_array($items) && count($items) > 0) {
                    foreach ($items as $index => $item) {
                        $qtyInputForm = floatval($item['quantity'] ?? $item['qty'] ?? 0);
                        $unitPrice = floatval($item['unit_price'] ?? 0);
                        $discount = floatval($item['discount'] ?? 0);
                        $amount = ($qtyInputForm * $unitPrice) - $discount;
                        $discountPercent = $item['discount_percent'] ?? 0;

                        SalesQuotationDetail::create([
                            'sales_quotation_id' => $salesQuotation->id,
                            'product_id' => $item['product_id'],
                            'qty' => $qtyInputForm,
                            'outstanding_qty' => $qtyInputForm,
                            'unit_id' => $item['unit_id'],
                            'unit_price' => $unitPrice,
                            'urutan' => $index,
                            'discount_percent' => $discountPercent,
                            'discount' => $discount,
                            'amount' => $item['amount'] ?? $amount,
                            'active' => 1,
                            'created_by' => Auth::id(),
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Sales Quotation '.$salesQuotation->sales_quotation_code.' berhasil diperbarui.',
                'redirect' => route('sales-quotation.index'),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui data: '.$e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {

        try {
            $table = SalesQuotation::findOrFail($id);
            $table->active = 0;
            $table->updated_by = Auth::user()->id;
            $table->save();
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function deleteMultiple(Request $request)
    {
        $ids = $request->ids;

        if (! $ids || count($ids) == 0) {
            return response()->json(['success' => false]);
        }

        SalesQuotation::whereIn('id', $ids)->update([
            'active' => '0',
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['success' => true]);
    }

    public function trash(Request $r)
    {
        if ($r->ajax()) {
            // Ambil ID user yang sedang login
            $userId = Auth::user()->id;

            // Query dengan kondisi: Aktif DAN (Status BUKAN draft ATAU Status ADALAH draft kepunyaan sendiri)
            $query = SalesQuotation::where('active', 0)
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
                    $btn = '<div class="btn-group">
                      <button type="button" class="btn btn-primary dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-menu-2 ti-xs me-1"></i>
                      </button>
                      <ul class="dropdown-menu" style="">';

                    if (auth()->user()->can('sales_quotation-restore')) {
                        $btn .= '<a class="dropdown-item restore" href="javascript:void(0)"
                            data-id="'.$row->id.'"> <i class="ti ti-trash-off me-1"></i> Restore</a>';
                    }

                    return $btn;
                })
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'cekbok', 'sales_quotation_date', 'total', 'customer'])
                ->make(true);
        }

        $x = [
            'title' => 'Deleted Sales Quotation List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Deleted Sales Quotation', 'url' => ''],
            ],
        ];

        return view('sales.salesQuotation.sales_quotation_trash', $x);
    }

    public function restore($id)
    {
        DB::beginTransaction();

        try {
            $salesQuotation = SalesQuotation::find($id);
            $salesQuotation->active = 1;
            $salesQuotation->updated_by = Auth::id();
            $salesQuotation->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'redirect' => true,
                'message' => 'Sales quotation successfully restored.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => true,
                'redirect' => true,
                'message' => 'Sales quotation successfully restored.',
            ]);
        }
    }

    public function restoreMultiple(Request $request)
    {
        $ids = $request->ids;

        if (! $ids || count($ids) == 0) {
            return response()->json(['success' => false]);
        }

        SalesQuotation::whereIn('id', $ids)->update([
            'active' => '1',
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['success' => true]);
    }

    public function getKontakByCustomer($customer_id)
    {
        $customer = Customer::find($customer_id);
        if (! $customer_id) {
            return response()->json([
                'success' => false,
                'message' => 'Customer tidak ditemukan.',
            ]);
        }

        $address = collect([
            $customer->alamat_tagihan,
            collect([
                $customer->kota_tagihan,
                $customer->provinsi_tagihan,
                $customer->kodepos_tagihan,
            ])->filter()->implode(', '),
            $customer->negara_tagihan,
        ])->filter()->implode("\n");

        $kontak = DB::table('customer_kontak')
            ->where('customer_id', $customer_id)
            ->get();

        $pajak = DB::table('customer_pajak')
            ->where('customer_id', $customer_id)
            ->first();

        return response()->json([
            'kontak' => $kontak,
            'pajak' => $pajak,
            'address' => $address,
        ]);
    }

    public function getPriceHistory(Request $request)
    {
        $productId = $request->get('product_id');
        $customerId = $request->get('customer_id');

        $year = date('Y');
        $tableDetail = "sales_quotation_detail_{$year}";
        $tableMaster = "sales_quotation_{$year}";

        // Mengambil harga unik langsung dari database
        $history = DB::table($tableDetail)
            ->join($tableMaster, "{$tableDetail}.sales_quotation_id", '=', "{$tableMaster}.id")
            ->where("{$tableDetail}.product_id", $productId)
            ->where("{$tableMaster}.customer_id", $customerId)
            ->select(
                "{$tableDetail}.unit_price as harga", // Pastikan nama kolom benar
                DB::raw("MAX({$tableMaster}.sales_quotation_date) as tanggal")
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

    public function submitToPending($id)
    {
        $sq = SalesQuotation::findOrFail($id);
        $sq->status = 'processing';
        $sq->updated_by = Auth::id(); // Jika Anda mencatat siapa yang melakukan update terakhir
        $sq->save();
        // $users = User::whereHas('roles.permissions', function ($q) {
        //     $q->where('name', 'sales_order-approval');
        // })->get();
        // $users = User::all();
        // Notification::send($users, new SalesQuotationNotification($sq));

        return response()->json(['success' => true, 'message' => 'Sales Quotation berhasil diproses!']);
    }

    public function print($id)
    {
        $SalesQuotation = SalesQuotation::with(['details.produkID', 'details.unitID'])->findOrFail($id);
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
            'model' => $SalesQuotation,
            'company' => $company,
            'modelDetail' => $SalesQuotation->details,
            'logoBase64' => $logoBase64,
        ];

        $pdf = Pdf::loadView('pdf.sales_quotation_pdf', $data)
            ->setPaper('a4', 'portrait');

        // preview di browser
        $filename = $SalesQuotation->sales_quotation_code.'-'.$SalesQuotation->customerID->nama_customer;

        // replace forbidden filename chars
        $filename = preg_replace('/[\/\\\\:*?"<>|]/', '-', $filename);
        $pdf->getDomPDF()->set_option('isPhpEnabled', true);

        return $pdf->stream($filename.'.pdf');

        // kalau mau download:
        // return $pdf->download('sales-quotation.pdf');
    }

    public function CloseDocument(Request $request, $id)
    {

        try {
            $table = SalesQuotation::findOrFail($id);
            $table->status = 'closed';
            $table->updated_by = Auth::user()->id;
            $table->save();
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }
}
