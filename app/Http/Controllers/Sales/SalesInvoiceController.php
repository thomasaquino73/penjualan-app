<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalesInvoiceRequest;
use App\Models\BasicCodeDetail;
use App\Models\Inventory\Barang;
use App\Models\Inventory\DataBarangConversion;
use App\Models\Inventory\Warehouse;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesInvoice;
use App\Models\Sales\SalesInvoiceDetail;
use App\Models\Setting\Company;
use App\Models\Setting\Shipping;
use App\Models\Setting\SyaratPembayaran;
use App\Models\Setting\Tax;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
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

                        // SEND TO APPROVAL
                        if ($row->status == 'draft') {

                            $btn .= '
                                <a class="dropdown-item btn-submit-po"
                                    href="javascript:void(0)"
                                    data-id="'.$row->id.'">

                                    <i class="ti ti-send me-1"></i>
                                    Send To Processing
                                </a>
                            ';
                        }

                        // EDIT
                        if (
                            $user->can('sales_invoice-edit') &&
                            in_array($row->status, ['draft'])
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
                            $row->status == 'draft'
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
        $year = date('Y');
        $month = $this->bulanRomawi(date('n'));

        // 🔥 ambil data terakhir berdasarkan tahun & bulan yg sama
        $last = SalesInvoice::where('sales_invoice_code', 'like', "SI/$year/$month/%")
            ->orderBy('id', 'desc')
            ->first();

        if (! $last) {
            return "SI/$year/$month/0001";
        }

        $lastId = $last->sales_invoice_code;

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

    public function store(SalesInvoiceRequest $request)
    {
        DB::beginTransaction();

        try {
            $currentYear = date('Y');
            $data = $request->validated();
            $itemsDetailRaw = $request->input('items_detail');
            unset($data['items_detail']);

            // Persiapan data header Sales Invoice
            $data['created_by'] = Auth::id();
            $data['sales_invoice_date'] = Carbon::parse($request->sales_invoice_date)->format('Y-m-d');
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
            do {
                $generatedCode = $this->generateNumberId();
                $exists = SalesInvoice::where('sales_invoice_code', $generatedCode)->exists();
            } while ($exists);

            $data['sales_invoice_code'] = $generatedCode;
            $salesInvoice = SalesInvoice::create($data);

            if ($itemsDetailRaw) {
                $items = json_decode($itemsDetailRaw, true);
                $involvedSqIds = [];

                if (is_array($items) && count($items) > 0) {
                    foreach ($items as $item) {
                        // $sqDetailId = $item['sales_quotation_detail_id'] ?? $item['detail_id'] ?? null;
                        $qtyInputForm = floatval($item['quantity'] ?? $item['qty'] ?? 0);
                        $unitPrice = floatval($item['unit_price'] ?? 0);
                        $discount = floatval($item['discount'] ?? 0);
                        $discountPercent = $item['discount'] ?? 0;
                        $amount = ($qtyInputForm * $unitPrice) - $discount;

                        // 1. Simpan ke Sales Invoice Detail
                        $soDetail = SalesInvoiceDetail::create([
                            'sales_invoice_id' => $salesInvoice->id,
                            // 'sales_quotation_detail_id' => $sqDetailId,
                            'product_id' => $item['product_id'],
                            'qty' => $qtyInputForm,
                            'unit_id' => $item['unit_id'],
                            'warehouse_id' => $item['warehouse_id'],
                            'unit_price' => $unitPrice,
                            'discount_percent' => $discountPercent,
                            'discount' => $discount,
                            'amount' => $item['amount'] ?? $amount,
                            'so_qty' => $qtyInputForm, // Sinkronisasi: sq_qty di SO = qty SO
                            'outstanding_qty' => 0, // Karena SO adalah tahap akhir, outstanding di SO biasanya 0
                            'status' => 'open',
                            'active' => 1,
                            'created_by' => Auth::id(),
                        ]);

                        // 2. Sinkronisasi ke Sales Quotation Detail (PR)
                        // if ($sqDetailId) {
                        //     $sqDetail = DB::table("sales_quotation_detail_{$currentYear}")->where('id', $sqDetailId)->first();

                        //     if ($sqDetail) {
                        //         // Hitung total akumulasi qty yang sudah masuk SO untuk item ini
                        //         $totalSoForThisItem = SalesInvoiceDetail::where('sales_quotation_detail_id', $sqDetailId)
                        //             ->where('active', 1)
                        //             ->sum('qty');

                        //         // Update sq_qty dan outstanding_qty di SQ Detail
                        //         $newOutstanding = max(0, ($sqDetail->qty - $totalSoForThisItem));

                        //         DB::table("sales_quotation_detail_{$currentYear}")
                        //             ->where('id', $sqDetailId)
                        //             ->update([
                        //                 'sq_qty' => $totalSoForThisItem,
                        //                 'outstanding_qty' => $newOutstanding,
                        //             ]);

                        //         if (! in_array($sqDetail->sales_quotation_id, $involvedSqIds)) {
                        //             $involvedSqIds[] = $sqDetail->sales_quotation_id;
                        //         }
                        //     }
                        // }
                    }

                    // 3. Otomasi status Sales Quotation Master
                    // foreach ($involvedSqIds as $sqId) {
                    //     $allDetails = DB::table("sales_quotation_detail_{$currentYear}")
                    //         ->where('sales_quotation_id', $sqId)
                    //         ->get();

                    //     $totalRequested = $allDetails->sum('qty');
                    //     $totalOrdered = $allDetails->sum('sq_qty');

                    //     if ($totalOrdered >= $totalRequested) {
                    //         $newStatus = 'closed';
                    //     } elseif ($totalOrdered > 0) {
                    //         $newStatus = 'partial';
                    //     } else {
                    //         $newStatus = 'processing';
                    //     }

                    //     DB::table("sales_quotation_{$currentYear}")
                    //         ->where('id', $sqId)
                    //         ->update(['status' => $newStatus]);
                    // }
                }
            }

            DB::commit();

            $redirectUrl = $request->save_and_new == 1
                ? route('sales-invoice.create') // Kembali kosongkan form untuk input data PR baru lagi
                : route('sales-invoice.index');  // Selesai dan kembali ke tabel index utama

            return response()->json([
                'success' => true,
                'message' => 'Sales Invoice saved successfully!',
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

    public function edit(string $id)
    {
        // Mengambil tahun berjalan untuk tabel dinamis
        $year = date('Y');

        // 1. Load data PO beserta relasinya
        // Pastikan model PurchaseOrder dan Detail sudah mendukung table name dinamis jika diperlukan
        $salesInvoice = SalesInvoice::with([
            // 'salesQuotation',
            'details.produkID',
            'details.unitID',
            // 'details.salesQuotationDetail.quotation',
        ])->findOrFail($id);

        // 2. Cek status PO global: Apakah mengandung minimal satu item hasil serapan PR?
        // $isFromPR = $salesInvoice->details->whereNotNull('sales_quotation_detail_id')->count() > 0;

        // 3. Mapping data detail
        $detailDataMapped = $salesInvoice->details->map(function ($detail) {

            $quotationCode = null;
            $sisaPr = null;
            $kuotaAsliPr = null;
            $totalDiambilLainnya = 0;

            // Cek apakah item detail ini memiliki keterikatan dengan PR
            // if ($detail->sales_quotation_detail_id) {
            //     // Ambil data referensi dari relasi
            //     $prDetail = $detail->salesQuotationDetail;

            //     if ($prDetail) {
            //         $sisaPr = (float) $prDetail->outstanding_qty;
            //         $kuotaAsliPr = (float) $prDetail->qty;

            //         // HITUNG TOTAL YANG SUDAH DIAMBIL DI PO LAIN
            //         // Menggunakan DB::table karena tabel bersifat dinamis per tahun
            //         $totalDiambilLainnya = DB::table("sales_invoice_detail_{$year}")
            //             ->where('sales_quotation_detail_id', $detail->sales_quotation_detail_id)
            //             ->where('sales_invoice_id', '<>', $salesInvoice->id) // Kecuali PO ini sendiri
            //             ->where('active', 1)
            //             ->sum('qty');

            //         if ($prDetail->salesQuotation) {
            //             $quotationCode = $prDetail->salesQuotation->code;
            //         }
            //     }
            // }

            return [
                'id' => $detail->id,
                'sales_invoice_id' => $detail->sales_invoice_id,
                // 'sales_quotation_detail_id' => $detail->sales_quotation_detail_id,
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
        $x = [
            'title' => 'Edit Sales Invoice ',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Edit Sales Invoice', 'url' => ''],
            ],
            'customer' => Customer::where('status', '<>', 0)->get(),
            'idNumber' => $this->generateNumberId(),
            'product' => Barang::where('status', '<>', 0)->get(),
            'warehouse' => Warehouse::where('status', 1)->get(),
            'paymentTerm' => SyaratPembayaran::where('status', '<>', 0)->get(),
            'salesman' => User::where('status', '<>', 0)->get(),
            'shipping' => Shipping::where('status', 1)->get(),
            'fob' => BasicCodeDetail::where('master_id', 7)->get(),
            'model' => $salesInvoice,
            // 'isFromPR' => $isFromPR,
            'jsonDetails' => $detailDataMapped,
            'taxes' => $taxes,
            'defaultTax' => $defaultTax,
        ];

        return view('sales.salesInvoice.sales_invoice_edit', $x);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

     public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            // 1. Cari SO yang akan dihapus
            $po = SalesInvoice::findOrFail($id);

            // 2. Ambil detail SO untuk mendapatkan referensi PR Detail yang terkait
            // $sqDetails = SalesInvoiceDetail::where('sales_invoice_id', $po->id)->get();
            // $involvedPrIds = [];

            // foreach ($sqDetails as $sqDetail) {
            //     if ($sqDetail->sales_quotation_detail_id) {
            //         // Catat ID PR Master-nya
            //         $prDetail = SalesQuotationDetail::where('id', $sqDetail->sales_quotation_detail_id)
            //             ->first();

            //         if ($prDetail && ! in_array($prDetail->sales_quotation_id, $involvedPrIds)) {
            //             $involvedPrIds[] = $prDetail->sales_quotation_id;
            //         }
            //     }
            // }

            // 3. Nonaktifkan SO dan Detail SO
            $po->update(['active' => 0, 'updated_by' => Auth::id()]);
            SalesInvoiceDetail::where('sales_invoice_id', $po->id)->update(['active' => 0]);

            // 4. Update Ulang sq_qty di setiap PR Detail yang terdampak
            // Kita hitung ulang berdasarkan sisa SO yang masih 'active' = 1
            // foreach ($sqDetails as $sqDetail) {
            //     if ($sqDetail->sales_quotation_detail_id) {
            //         $totalRemainingPo = SalesInvoiceDetail::where('sales_quotation_detail_id', $sqDetail->sales_quotation_detail_id)
            //             ->where('active', 1)
            //             ->sum('qty');

            //         DB::table('sales_quotation_detail_'.date('Y'))
            //             ->where('id', $sqDetail->sales_quotation_detail_id)
            //             ->update(['sq_qty' => $totalRemainingPo]);
            //     }
            // }

            // 5. Update Status PR Master
            // foreach ($involvedPrIds as $prId) {
            //     $allDetails = SalesQuotationDetail::where('sales_quotation_id', $prId)
            //         ->get();

            //     $totalRequested = $allDetails->sum('qty');
            //     $totalOrdered = $allDetails->sum('sq_qty');

            //     if ($totalOrdered >= $totalRequested) {
            //         $status = 'closed';
            //     } elseif ($totalOrdered > 0) {
            //         $status = 'partial';
            //     } else {
            //         $status = 'processing';
            //     }

            //     SalesQuotation::where('id', $prId)
            //         ->update(['status' => $status]);
            // }

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

            // 3. Update sq_qty di PR Detail dan kumpulkan ID PR Master
            // foreach ($sqDetails as $sqDetail) {
            //     if ($sqDetail->sales_quotation_detail_id) {
            //         // Hitung total dari SO yang tersisa (yang masih aktif)
            //         $totalRemainingPo = SalesInvoiceDetail::where('sales_quotation_detail_id', $sqDetail->sales_quotation_detail_id)
            //             ->where('active', 1)
            //             ->sum('qty');

            //         // Update ke tabel PR Detail
            //         DB::table('sales_quotation_detail_'.date('Y'))
            //             ->where('id', $sqDetail->sales_quotation_detail_id)
            //             ->update(['sq_qty' => $totalRemainingPo]);

            //         // Simpan ID PR untuk update status nanti
            //         $prDetail = DB::table('sales_quotation_detail_'.date('Y'))
            //             ->where('id', $sqDetail->sales_quotation_detail_id)
            //             ->first();

            //         if ($prDetail && ! in_array($prDetail->sales_quotation_id, $involvedPrIds)) {
            //             $involvedPrIds[] = $prDetail->sales_quotation_id;
            //         }
            //     }
            // }

            // 4. Update Status PR Master berdasarkan akumulasi terbaru
            // foreach ($involvedPrIds as $prId) {
            //     $allDetails = DB::table('sales_quotation_detail_'.date('Y'))
            //         ->where('sales_quotation_id', $prId)
            //         ->get();

            //     $totalRequested = $allDetails->sum('qty');
            //     $totalOrdered = $allDetails->sum('sq_qty');

            //     if ($totalOrdered >= $totalRequested) {
            //         $status = 'closed';
            //     } elseif ($totalOrdered > 0) {
            //         $status = 'partial';
            //     } else {
            //         $status = 'processing';
            //     }

            //     DB::table('sales_quotation_'.date('Y'))
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

            // 4. Update ulang sq_qty di PR Detail
            // foreach ($poDetails as $poDetail) {
            //     if ($poDetail->sales_quotation_detail_id) {
            //         // Hitung total dari semua SO yang aktif
            //         $totalPoForThisItem = SalesInvoiceDetail::where('sales_quotation_detail_id', $poDetail->sales_quotation_detail_id)
            //             ->where('active', 1)
            //             ->sum('qty');

            //         // Update ke tabel PR Detail
            //         DB::table('sales_quotation_detail_'.date('Y'))
            //             ->where('id', $poDetail->sales_quotation_detail_id)
            //             ->update(['sq_qty' => $totalPoForThisItem]);

            //         // Simpan ID PR untuk update status
            //         $prDetail = DB::table('sales_quotation_detail_'.date('Y'))
            //             ->where('id', $poDetail->sales_quotation_detail_id)
            //             ->first();

            //         if ($prDetail && ! in_array($prDetail->sales_quotation_id, $involvedPrIds)) {
            //             $involvedPrIds[] = $prDetail->sales_quotation_id;
            //         }
            //     }
            // }

            // 5. Update Status PR Master
            // foreach ($involvedPrIds as $prId) {
            //     $allDetails = DB::table('sales_quotation_detail_'.date('Y'))
            //         ->where('sales_quotation_id', $prId)
            //         ->get();

            //     $totalRequested = $allDetails->sum('qty');
            //     $totalOrdered = $allDetails->sum('sq_qty');

            //     if ($totalOrdered >= $totalRequested) {
            //         $status = 'closed';
            //     } elseif ($totalOrdered > 0) {
            //         $status = 'partial';
            //     } else {
            //         $status = 'processing';
            //     }

            //     DB::table('sales_quotation_'.date('Y'))
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
            // $poDetails = SalesInvoiceDetail::whereIn('sales_invoice_id', $ids)->get();
            // $involvedPrIds = [];

            // 4. Update sq_qty di PR Detail dan kumpulkan ID PR Master
            // foreach ($poDetails as $poDetail) {
            //     if ($poDetail->sales_quotation_detail_id) {
            //         // Hitung total dari semua SO yang aktif
            //         $totalPoForThisItem = SalesInvoiceDetail::where('sales_quotation_detail_id', $poDetail->sales_quotation_detail_id)
            //             ->where('active', 1)
            //             ->sum('qty');

            //         // Update ke tabel PR Detail
            //         DB::table('sales_quotation_detail_'.date('Y'))
            //             ->where('id', $poDetail->sales_quotation_detail_id)
            //             ->update(['sq_qty' => $totalPoForThisItem]);

            //         // Simpan ID PR untuk update status nanti (hindari duplikat)
            //         $prDetail = DB::table('sales_quotation_detail_'.date('Y'))
            //             ->where('id', $poDetail->sales_quotation_detail_id)
            //             ->first();

            //         if ($prDetail && ! in_array($prDetail->sales_quotation_id, $involvedPrIds)) {
            //             $involvedPrIds[] = $prDetail->sales_quotation_id;
            //         }
            //     }
            // }

            // 5. Update Status PR Master berdasarkan akumulasi terbaru
            // foreach ($involvedPrIds as $prId) {
            //     $allDetails = DB::table('sales_quotation_detail_'.date('Y'))
            //         ->where('sales_quotation_id', $prId)
            //         ->get();

            //     $totalRequested = $allDetails->sum('qty');
            //     $totalOrdered = $allDetails->sum('sq_qty');

            //     if ($totalOrdered >= $totalRequested) {
            //         $status = 'closed';
            //     } elseif ($totalOrdered > 0) {
            //         $status = 'partial';
            //     } else {
            //         $status = 'processing';
            //     }

            //     DB::table('sales_quotation_'.date('Y'))
            //         ->where('id', $prId)
            //         ->update(['status' => $status]);
            // }

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
        $salesInvoice = SalesInvoice::with(['details.produkID', 'details.unitID'])->findOrFail($id);
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
            'model' => $salesInvoice,
            'company' => $company,
            'modelDetail' => $salesInvoice->details,
            'logoBase64' => $logoBase64,
        ];

        $pdf = Pdf::loadView('pdf.sales_invoice_pdf', $data)
            ->setPaper('a4', 'portrait');

        // preview di browser
        $filename = $salesInvoice->sales_invoice_code.'-'.$salesInvoice->customerID->nama_customer;

        // replace forbidden filename chars
        $filename = preg_replace('/[\/\\\\:*?"<>|]/', '-', $filename);

        return $pdf->stream($filename.'.pdf');

        // kalau mau download:
        // return $pdf->download('sales-invoice.pdf');
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
}
