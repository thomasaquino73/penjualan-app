<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseOrderRequest;
use App\Models\BasicCodeDetail;
use App\Models\Inventory\Barang;
use App\Models\Purchase\PurchaseOrder;
use App\Models\Purchase\PurchaseOrderDetail;
use App\Models\Purchase\PurchaseRequisition;
use App\Models\Purchase\PurchaseRequisitionDetail;
use App\Models\Purchase\Supplier;
use App\Models\Setting\Company;
use App\Models\Setting\CompanyDeliveryAddress;
use App\Models\Setting\Shipping;
use App\Models\Setting\SyaratPembayaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class PurchaseOrderController extends Controller
{
    public function index(Request $r)
    {
        if ($r->ajax()) {
            $userId = auth()->id();

            // Query dengan kondisi: Aktif DAN (Status BUKAN draft ATAU Status ADALAH draft kepunyaan sendiri)
            $query = PurchaseOrder::where('active', '<>', 0)
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
                ->addColumn('date', function ($row) {
                    return Carbon::parse($row->datePO)->format('d-m-Y');
                })
                ->addColumn('tanggal_kirim', function ($row) {
                    return Carbon::parse($row->tanggal_kirim)->format('d-m-Y');
                })
                ->addColumn('amount', function ($row) {
                    // 1. Hitung total kotor (sum amount) dari detail item PO
                    $subTotal = PurchaseOrderDetail::where('purchase_order_id', $row->id)
                        ->where('active', 1)
                        ->sum('amount');

                    // 2. Hitung grand total: Subtotal dikurangi diskon nominal yang ada di tabel induk ($row)
                    // Gunakan ?? 0 jika kolom disc_nominal di database bisa bernilai null
                    $grandTotal = $subTotal - ($row->disc_nominal ?? 0);

                    // 3. Kembalikan nilai yang sudah dikonversi dan diformat
                    return format_uang(convert_currency($grandTotal, $row->currency_id ?? 1));
                })
                ->addColumn('supplier', function ($row) {
                    return $row->supplier->nama_supplier;
                })
                ->addColumn('cekbok', function ($row) {
                    return '   <div class="form-check form-check-primary mt-3">
                                <input class="form-check-input checkItem" type="checkbox" value="'.$row->id.'"
                                    >
                            </div>';
                })
                ->addColumn('action', function ($row) {

                    $currentUserId = auth()->id();
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
                            $user->can('purchase_order-edit') &&
                            in_array($row->status, ['draft', 'rejected'])
                        ) {

                            $btn .= '
                <a class="dropdown-item"
                    href="'.route('purchase-order.edit', $row->id).'">

                    <i class="far fa-edit me-1"></i>
                    Edit PO
                </a>
            ';
                        }

                        // DELETE
                        if (
                            $user->can('purchase_order-delete') &&
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
                        $user->can('purchase_order-approval')
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
                        // $user->can('purchase_order-send-supplier')
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
                        $user->can('purchase_order-receive')
                    ) {

                        $btn .= '
            <a class="dropdown-item text-primary"
                href="'.route('purchase-order.receive', $row->id).'">

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
                        $user->can('purchase_order-cancel')
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

                    /*
                    |--------------------------------------------------------------------------
                    | 7. PRINT
                    |--------------------------------------------------------------------------
                    */

                    $btn .= '
        <a class="dropdown-item"
            target="_blank"
            href="'.route('purchase-order.print', $row->id).'">

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
            'title' => 'Purchase Order List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Purchase Order', 'url' => ''],
            ],
        ];

        return view('purchase.purchase_order.purchase_order_index', $x);
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
        $last = PurchaseOrder::where('code', 'like', "PO/$year/$month/%")
            ->orderBy('id', 'desc')
            ->first();

        if (! $last) {
            return "PO/$year/$month/0001";
        }

        $lastId = $last->code;

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

    public function table_pr(Request $r)
    {
        if ($r->ajax()) {
            $query = PurchaseOrderDetail::with('produkID')
                ->where('active', '<>', 0)
                ->get();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('data_produk', function ($row) {
                    return $row->produkID->nama_barang;
                })

                ->rawColumns(['data_produk'])
                ->make(true);
        }
    }

    public function create()
    {
        $x = [
            'title' => 'Purchase Order New',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Purchase Order', 'url' => ''],
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

        return view('purchase.purchase_order.purchase_order_create', $x);
    }

    public function getProcessingData()
    {
        // Mengambil data PR beserta item detailnya yang belum lunas di-PO
        $orders = PurchaseRequisition::with(['details' => function ($query) {
            $query->whereRaw('po_qty < qty'); // Hanya ambil item yang belum terpenuhi
        }])
            ->whereNotIn('status', ['draft', 'closed', 'done'])
            ->get();

        return response()->json($orders);
    }

    public function getPrice($id)
    {
        // Mencari data barang berdasarkan ID
        $product = Barang::find($id);

        if ($product) {
            return response()->json([
                'success' => true,
                'price' => $product->price,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Product tidak ditemukan',
        ], 404);
    }

    public function store(PurchaseOrderRequest $request)
    {
        // Mulai Database Transaction untuk memastikan keamanan data relasi
        DB::beginTransaction();

        try {
            // Ambil tahun berjalan secara dinamis sesuai struktur tabel tahunan Anda
            $currentYear = date('Y');

            // 1. Ambil semua data yang telah lolos validasi dari Form Request
            $data = $request->validated();

            // 2. Pisahkan data 'items_detail' dari array utama agar tidak masuk ke tabel purchase_order
            $itemsDetailRaw = $request->input('items_detail');
            unset($data['items_detail']); // Menghapus key dari antrean field insert tabel master

            $syaratPembayaran = SyaratPembayaran::find($request->payment_term);

            // 3. Lengkapi data audit log untuk tabel master
            $data['created_by'] = Auth::id();
            $data['updated_by'] = null;
            $data['vehicle_id'] = $request->vehicle_id;
            $data['sub_total'] = $request->sub_total;
            $data['disc_percent'] = $request->percent;
            $data['disc_nominal'] = $request->discount_all;
            $data['grand_total'] = $request->total_order;
            $data['payment_term'] = $request->payment_term;
            $data['kena_pajak'] = 0; // Di-set 0 karena tidak menggunakan sistem tax
            $data['total_termasuk_pajak'] = 0; // Di-set 0 karena tidak menggunakan sistem tax
            $data['shipping_address'] = $request->shipping_address;
            $data['description'] = $request->description;
            $data['datePO'] = Carbon::parse($request->datePO)->format('Y-m-d');
            $data['tanggal_kirim'] = $request->tanggal_kirim ? Carbon::parse($request->tanggal_kirim)->format('Y-m-d') : null;
            $data['total_hari'] = $syaratPembayaran->total_hari;
            $data['total_diskon'] = $syaratPembayaran->total_diskon;
            $data['masa_jatuh_tempo'] = $syaratPembayaran->masa_jatuh_tempo;

            // 4. Generate nomor/kode Purchase Order secara unik (Anti-Duplikat beruntun)
            do {
                $generatedCode = $this->generateNumberId();
                // Cek apakah kode PO tersebut sudah terpakai di database
                $exists = PurchaseOrder::where('code', $generatedCode)->exists();
            } while ($exists);

            // Masukkan nomor PO hasil generate ke dalam array data master
            $data['code'] = $generatedCode;

            // 5. Simpan data ke tabel master 'purchase_order' menggunakan Eloquent Create
            $purchaseOrder = PurchaseOrder::create($data);

            // 6. Proses simpan data ke tabel detail 'purchase_order_detail'
            if ($itemsDetailRaw) {
                // Bongkar string JSON data barang dari AJAX menjadi array PHP
                $items = json_decode($itemsDetailRaw, true);

                if (is_array($items) && count($items) > 0) {
                    // Array untuk menampung ID PR master yang terlibat (untuk kalkulasi status akhir dokumen)
                    $involvedPrIds = [];

                    foreach ($items as $item) {

                        // --- PROTEKSI & DETEKSI ALUR (Ambil PR vs Isi Sendiri) ---
                        $prDetailId = null;

                        // Pemetaan alternatif penamaan properti ID detail PR dari Javascript AJAX
                        if (! empty($item['purchase_requisition_detail_id'])) {
                            $prDetailId = $item['purchase_requisition_detail_id'];
                        } elseif (! empty($item['pr_detail_id'])) {
                            $prDetailId = $item['pr_detail_id'];
                        } elseif (! empty($item['detail_id'])) {
                            $prDetailId = $item['detail_id'];
                        }

                        // Pemetaan alternatif penamaan kuantitas (quantity atau qty)
                        $qtyInputForm = floatval($item['quantity'] ?? $item['qty'] ?? 0);

                        // JIKA ITEM DIKATEGORIKAN AMBIL DARI PR
                        if ($prDetailId) {
                            // Ambil data detail PR menggunakan Query Builder agar dinamis membaca tabel tahunan
                            $prDetail = DB::table("purchase_requisition_detail_{$currentYear}")
                                ->where('id', $prDetailId)
                                ->first();

                            if ($prDetail) {
                                // Ambil master dokumen PR untuk memeriksa status alurnya
                                $prMaster = DB::table("purchase_requisition_{$currentYear}")
                                    ->where('id', $prDetail->purchase_requisition_id)
                                    ->first();

                                // Proteksi status: Mengizinkan data jika PR berstatus 'processing' atau 'partial'
                                if ($prMaster && ! in_array($prMaster->status, ['processing', 'partial'])) {
                                    throw new \Exception("Barang '{$item['data_produk']}' tidak dapat diproses karena dokumen PR ({$prMaster->code}) berstatus: ".$prMaster->status);
                                }

                                // HITUNG LIMIT PARSIAL: Ambil sisa batas toleransi pengisian PO
                                $sisaBolehPO = floatval($prDetail->qty) - floatval($prDetail->po_qty);

                                // Batalkan transaksi jika user menginput kuantitas melebihi sisa permintaan PR asli
                                if ($qtyInputForm > $sisaBolehPO) {
                                    throw new \Exception("Kuantitas item '{$item['data_produk']}' melebihi sisa PR yang tersedia. Sisa kuantitas saat ini: {$sisaBolehPO}");
                                }

                                // UPDATE COUNTER PO_QTY: Tambahkan nominal kuantitas baru ke kolom penampung po_qty
                                DB::table("purchase_requisition_detail_{$currentYear}")
                                    ->where('id', $prDetailId)
                                    ->increment('po_qty', $qtyInputForm);

                                // Catat ID PR Master ke dalam array agar tidak terduplikasi saat dicek di langkah ke-7
                                if (! in_array($prDetail->purchase_requisition_id, $involvedPrIds)) {
                                    $involvedPrIds[] = $prDetail->purchase_requisition_id;
                                }
                            }
                        }
                        // -----------------------------------------------------------

                        // Hitung Matematika Murni Nilai Rupiah Per Baris (Murni Tanpa Pajak)
                        $unitPrice = floatval($item['unit_price'] ?? 0);
                        $discount = floatval($item['discount'] ?? 0);

                        $subTotal = $qtyInputForm * $unitPrice;
                        $amount = $subTotal - $discount; // Total bersih per baris barang

                        // Insert data baris barang menggunakan Model Eloquent
                        PurchaseOrderDetail::create([
                            'purchase_order_id' => $purchaseOrder->id,
                            'purchase_requisition_detail_id' => $prDetailId, // NULL jika isi manual sendiri
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

                    // =================================================================
                    // 7. AUTOMATION STATUS MASTER PR (Closed vs Partial vs Processing)
                    // =================================================================
                    foreach ($involvedPrIds as $prId) {
                        // Ambil seluruh baris item aktif yang terdaftar di PR tersebut
                        $allPrItems = DB::table("purchase_requisition_detail_{$currentYear}")
                            ->where('purchase_requisition_id', $prId)
                            ->where('active', 1)
                            ->get();

                        $isPrFullyCompleted = true; // Flag untuk indikasi cicilan habis total
                        $isAnyItemOrdered = false;   // Flag untuk indikasi adanya barang yang mulai dicicil

                        foreach ($allPrItems as $prItem) {
                            $orderedQty = floatval($prItem->po_qty);
                            $requestedQty = floatval($prItem->qty);

                            // Jika ada minimal satu item yang sudah di-PO kan
                            if ($orderedQty > 0) {
                                $isAnyItemOrdered = true;
                            }

                            // Jika didapati ada kuantitas PO yang masih kurang dari kuantitas PR
                            if ($orderedQty < $requestedQty) {
                                $isPrFullyCompleted = false;
                            }
                        }

                        // Tentukan perubahan status final dokumen PR berdasarkan hasil kalkulasi item
                        if ($isPrFullyCompleted) {
                            // Kasus A: Kuantitas semua barang telah sukses terpenuhi 100% tanpa sisa
                            DB::table("purchase_requisition_{$currentYear}")
                                ->where('id', $prId)
                                ->update(['status' => 'closed']);
                        } elseif ($isAnyItemOrdered) {
                            // Kasus B: Baru sebagian kuantitas atau sebagian barang yang dibuatkan PO
                            DB::table("purchase_requisition_{$currentYear}")
                                ->where('id', $prId)
                                ->update(['status' => 'partial']);
                        } else {
                            // Kasus C: Kondisi default jika belum tersentuh PO sama sekali
                            DB::table("purchase_requisition_{$currentYear}")
                                ->where('id', $prId)
                                ->update(['status' => 'processing']);
                        }
                    }
                    // =================================================================
                }
            }

            // Jika semua proses insert master dan detail sukses tanpa error, commit ke database
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Purchase Order '.$generatedCode.' berhasil disimpan.',
                'redirect' => route('purchase-order.index'),
            ], 200);

        } catch (\Exception $e) {
            // Jika di tengah jalan ada kode yang gagal atau crash, batalkan semua perubahan di database
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data: '.$e->getMessage(),
            ], 500);
        }
    }

    public function edit(string $id)
    {
        $purchaseOrder = PurchaseOrder::with(['details.produkID', 'details.unitID'])->findOrFail($id);
        $x = [
            'title' => 'Edit Purchase Order ',
            'breadcrumb' => [
                ['label' => 'Purchase Order', 'url' => route('purchase-order.index')],
                ['label' => 'Edit Purchase Order', 'url' => ''],
            ],
            'supplier' => Supplier::where('status', 1)->get(),
            'company' => Company::first(),
            'idNumber' => $this->generateNumberId(),
            'shipping' => Shipping::where('status', 1)->get(),
            'paymentTerm' => SyaratPembayaran::where('status', 1)->get(),
            'product' => Barang::where('status', '<>', 0)->get(),
            'fob' => BasicCodeDetail::where('master_id', 7)->get(),
            'taxes' => BasicCodeDetail::where('master_id', 6)->get(),
            'model' => $purchaseOrder,

        ];

        return view('purchase.purchase_order.purchase_order_edit', $x);
    }

    public function update(PurchaseOrderRequest $request, string $id)
    {
        // Cari data induk berdasarkan ID, jika tidak ketemu otomatis melempar error 404
        $prMaster = PurchaseOrder::findOrFail($id);

        // Mulai Database Transaction demi keamanan integritas relasi data
        DB::beginTransaction();

        try {
            // Ambil data syarat pembayaran untuk melengkapi data master seperti di proses create
            $syaratPembayaran = SyaratPembayaran::find($request->payment_term);

            // 1. Update Data Master Purchase Order
            $prMaster->update([
                'supplier_id' => $request->supplier_id,
                'code' => $request->code,
                'datePO' => Carbon::parse($request->datePO)->format('Y-m-d'),
                'tanggal_kirim' => $request->tanggal_kirim
                                            ? Carbon::parse($request->tanggal_kirim)->format('Y-m-d')
                                            : null,
                'kena_pajak' => $request->has('kena_pajak') ? 1 : 0,
                'total_termasuk_pajak' => $request->has('total_termasuk_pajak') ? 1 : 0,
                'fob_id' => $request->fob_id,
                'vehicle_id' => $request->vehicle_id,
                'payment_term' => $request->payment_term,
                'shipping_address' => $request->shipping_address,
                'description' => $request->description,

                'sub_total' => $request->sub_total,
                'disc_percent' => $request->percent,
                'disc_nominal' => $request->discount_all,
                'grand_total' => $request->total_order,

                // 🔥 TAMBAHAN sinkronisasi dari data Syarat Pembayaran (Sama seperti Create)
                'total_hari' => $syaratPembayaran->total_hari ?? 0,
                'total_diskon' => $syaratPembayaran->total_diskon ?? 0,
                'masa_jatuh_tempo' => $syaratPembayaran->masa_jatuh_tempo ?? 0,

                'updated_by' => Auth::id(),
            ]);

            // 2. Decode data array string JSON (`items_detail`) dari DataTables / Form
            $items = json_decode($request->items_detail, true);

            if (is_array($items) && count($items) > 0) {

                // Gunakan relasi hasMany jika ada (misal: $prMaster->details()), atau panggil Model Detail langsung
                // Hapus semua detail lama terlebih dahulu untuk mencegah data gantung / duplikasi
                PurchaseOrderDetail::where('purchase_order_id', $prMaster->id)->delete();

                // Loop untuk insert ulang item baru (Alur disamakan dengan simpan baru pada Create)
                foreach ($items as $item) {
                    PurchaseOrderDetail::create([
                        'purchase_order_id' => $prMaster->id,
                        'product_id' => $item['product_id'],
                        'qty' => $item['quantity'] ?? $item['qty'], // Fallback antisipasi perbedaan penamaan key
                        'unit_id' => $item['unit_id'],
                        'unit_price' => $item['unit_price'],
                        'discount' => $item['discount'] ?? 0,
                        'amount' => $item['amount'],
                        'created_by' => $prMaster->created_by, // Tetap jaga pembuat asli (jika ada kolomnya di detail)
                        'updated_by' => Auth::id(),
                    ]);
                }

            } else {
                // Validasi fail-safe jika item kosong
                throw new \Exception('Minimal harus ada 1 item produk yang dimasukkan.');
            }

            // Jika semua langkah aman tanpa error, lakukan commit data ke database
            DB::commit();

            // Target URL setelah sukses update
            $redirectUrl = route('purchase-order.index');

            return response()->json([
                'success' => true,
                'title' => 'Data Updated Successfully',
                'message' => 'Purchase Order successfully updated!',
                'redirect' => $redirectUrl,
            ], 200);

        } catch (\Exception $e) {
            // Batalkan semua query jika terjadi kegagalan di tengah jalan
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update data: '.$e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {

        try {
            $table = PurchaseOrder::findOrFail($id);
            $table->active = 0;
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
            $query = PurchaseOrder::where('active', 0)->get();

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
                    return '<span class="badge bg-info">Processing Queue</span>';
                })
                ->addColumn('date', function ($row) {
                    return Carbon::parse($row->datePO)->format('d-m-Y');
                })
                ->addColumn('tanggal_kirim', function ($row) {
                    return Carbon::parse($row->tanggal_kirim)->format('d-m-Y');
                })
                ->addColumn('amount', function ($row) {
                    return $row->grand_total;
                })
                ->addColumn('supplier', function ($row) {
                    return $row->supplier->nama_supplier;
                })
                ->addColumn('cekbok', function ($row) {
                    return '   <div class="form-check form-check-primary mt-3">
                                <input class="form-check-input checkItem" type="checkbox" value="'.$row->id.'"
                                    >
                            </div>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">
                      <button type="button" class="btn btn-primary dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-menu-2 ti-xs me-1"></i>
                      </button>
                      <ul class="dropdown-menu" style="">';

                    if (auth()->user()->can('purchase_order-restore')) {
                        $btn .= '<a class="dropdown-item restore" href="javascript:void(0)"
                            data-id="'.$row->id.'"> <i class="ti ti-trash-off me-1"></i> Restore</a>';
                    }

                    return $btn;
                })
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'cekbok', 'supplier', 'date', 'amount'])
                ->make(true);
        }

        $x = [
            'title' => 'Deleted Purchase Order List',
            'breadcrumb' => [
                ['label' => 'Purchase Order', 'url' => route('purchase-order.index')],
                ['label' => 'Deleted Purchase Order', 'url' => ''],
            ],

        ];

        return view('purchase.purchase_order.purchase_order_trash', $x);
    }

    public function deleteMultiple(Request $request)
    {
        $ids = $request->ids;

        if (! $ids || count($ids) == 0) {
            return response()->json(['success' => false]);
        }

        PurchaseOrder::whereIn('id', $ids)->update([
            'active' => '0',
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['success' => true]);
    }

    public function restore($id)
    {
        DB::beginTransaction();

        try {
            $permintaanpembelian = PurchaseOrder::find($id);
            $permintaanpembelian->active = 1;
            $permintaanpembelian->updated_by = Auth::id();
            $permintaanpembelian->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'redirect' => true,
                'message' => 'Purchase order successfully restored.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => true,
                'redirect' => true,
                'message' => 'Purchase order successfully restored.',
            ]);
        }
    }

    public function restoreMultiple(Request $request)
    {
        $ids = $request->ids;

        if (! $ids || count($ids) == 0) {
            return response()->json(['success' => false]);
        }

        PurchaseOrder::whereIn('id', $ids)->update([
            'active' => '1',
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['success' => true]);
    }

    public function submitToPending($id)
    {
        // 1. Ambil tahun berjalan secara dinamis
        $year = date('Y');
        $tableName = "purchase_order_{$year}";

        // 2. Gunakan Query Builder dengan nama tabel dinamis agar pencarian ID aman
        $poData = DB::table($tableName)->where('id', $id)->first();

        // Jika data memang benar-benar tidak ditemukan di database
        if (! $poData) {
            return response()->json(['success' => false, 'message' => 'Data Purchase Order tidak ditemukan.'], 404);
        }

        // 3. Validasi Keamanan: Pastikan hanya pembuat draft yang bisa mengajukannya
        if ($poData->status !== 'draft' || $poData->created_by !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki akses untuk mengajukan data ini.'], 403);
        }

        // 4. Lakukan pembaruan status menggunakan Query Builder demi stabilitas tabel dinamis
        DB::table($tableName)->where('id', $id)->update([
            'status' => 'pending',
            'updated_by' => auth()->id(),
            'updated_at' => now(), // Mengisi timestamp bawaan laravel secara manual karena menggunakan Query Builder
        ]);

        return response()->json(['success' => true, 'message' => 'Purchase Order berhasil diajukan!']);
    }

    public function changeStatus(Request $request, $id)
    {
        // 1. Ambil tahun berjalan secara dinamis untuk dynamic table
        $year = date('Y');
        $tableName = "purchase_order_{$year}";

        // 2. Cari data PO berdasarkan ID menggunakan Query Builder
        $poData = DB::table($tableName)->where('id', $id)->first();

        // Validasi jika data tidak ditemukan
        if (! $poData) {
            return response()->json(['error' => 'Data Purchase Order tidak ditemukan.'], 404);
        }

        // 3. Validasi Keamanan: Pastikan yang mengubah status BUKAN orang yang membuat dokumen (Anti Self-Approval)
        if ($poData->created_by === Auth::user()->id) {
            return response()->json(['error' => 'You may not approve/reject documents you create yourself!'], 403);
        }

        // 4. Validasi Input Status (Memastikan hanya menerima 'approved' atau 'rejected')
        $statusTarget = $request->input('status');
        if (! in_array($statusTarget, ['approved', 'rejected'])) {
            return response()->json(['error' => 'Status target tidak valid.'], 400);
        }

        // 5. Eksekusi Update ke Database
        DB::table($tableName)->where('id', $id)->update([
            'status' => $statusTarget,
            'pic_by' => Auth::id(),
            'pic_at' => now(), // Isi timestamp manual karena menggunakan Query Builder
        ]);

        // ==========================================
        // 5b. OTOMATISASI: Kirim dokumen hanya jika statusnya 'approved'
        // ==========================================
        if ($statusTarget === 'approved') {
            try {
                // Panggil fungsi atau service pengiriman dokumen Anda di sini.
                // Contoh jika menggunakan Mail Laravel:
                // Mail::to($poData->vendor_email)->send(new PurchaseOrderMail($poData));

                // Atau jika menggunakan job queue (Sangat disarankan agar performa aplikasi tetap cepat):
                // SendPurchaseOrderJob::dispatch($poData);

            } catch (\Exception $e) {
            }
        }

        // 6. Return response dengan pesan dinamis sesuai aksi (Approve / Reject)
        $messageText = $statusTarget === 'approved' ? 'approved' : 'rejected';

        return response()->json([
            'success' => true,
            'message' => "Purchase Order status successfully {$messageText}!",
        ], 200);
    }

    public function show(string $id) {}

    public function print($id)
    {
        $purchaseOrder = PurchaseOrder::with(['details.produkID', 'details.unitID'])->findOrFail($id);
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
            'model' => $purchaseOrder,
            'company' => $company,
            'modelDetail' => $purchaseOrder->details,
            'logoBase64' => $logoBase64,
        ];

        $pdf = Pdf::loadView('pdf.purchase_order_pdf', $data)
            ->setPaper('a4', 'portrait');

        // preview di browser
        $filename = $purchaseOrder->code.'-'.$purchaseOrder->supplier->nama_supplier;

        // replace forbidden filename chars
        $filename = preg_replace('/[\/\\\\:*?"<>|]/', '-', $filename);

        return $pdf->stream($filename.'.pdf');

        // kalau mau download:
        // return $pdf->download('purchase-order.pdf');
    }

    public function getPriceHistory(Request $request)
    {
        $productId = $request->get('product_id');
        $supplierId = $request->get('supplier_id');

        $year = date('Y');
        $tableDetail = "purchase_order_detail_{$year}";
        $tableMaster = "purchase_order_{$year}";

        // Mengambil harga unik langsung dari database
        $history = DB::table($tableDetail)
            ->join($tableMaster, "{$tableDetail}.purchase_order_id", '=', "{$tableMaster}.id")
            ->where("{$tableDetail}.product_id", $productId)
            ->where("{$tableMaster}.supplier_id", $supplierId)
            // Kuncinya di sini: kelompokkan berdasarkan harga, lalu ambil tanggal terbaru dengan MAX()
            ->select(
                "{$tableDetail}.unit_price as harga",
                DB::raw("MAX({$tableMaster}.datePO) as tanggal")
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

    public function getCompanyAddresses($companyId)
    {

        $addresses = CompanyDeliveryAddress::where('company_id', 1)->where('active', 1)->get();

        return response()->json([
            'success' => true,
            'data' => $addresses,
        ]);
    }

    public function sendSupplier($id)
    {
        $po = PurchaseOrder::findOrFail($id);

        // VALIDASI STATUS
        if ($po->status != 'approved') {

            return response()->json([
                'message' => 'Only approved PO can be sent.',
            ], 422);
        }

        // UPDATE STATUS
        $po->update([
            'status' => 'sent',
            'updated_by' => Auth::user()->id,
        ]);

        return response()->json([
            'message' => 'PO successfully sent to supplier.',
        ]);
    }

    public function getRequisitionDetail(Request $request)
    {
        // 1. Ambil array ID master PR dari request AJAX
        $ids = $request->ids;

        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data PR yang dipilih.',
                'data' => [],
            ]);
        }

        // 2. Ambil data detail menggunakan Eloquent Model agar dinamis membaca tabel tahunan
        // dan panggil semua relasi yang sudah kamu buat di model
        $details = PurchaseRequisitionDetail::with(['produkID', 'unitID', 'requisition'])
            ->whereIn('purchase_requisition_id', $ids)
            ->where('active', 1) // Pastikan hanya item aktif yang ditarik
            ->get();

        // 3. Transformasikan data Eloquent ke dalam format flat array yang dinanti oleh Javascript AJAX kamu
        $formattedData = $details->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,

                // Mengambil nama produk dari relasi produkID (Model Barang)
                'product_name' => $item->produkID->nama_barang ?? $item->produkID->product_name ?? 'Unknown Product',

                'qty' => floatval($item->qty),     // Kuantitas awal PR
                'po_qty' => floatval($item->po_qty),  // Jumlah kuantitas yang sudah pernah di-PO

                'unit_id' => $item->unit_id,

                // Mengambil nama satuan dari relasi unitID (Model BasicCodeDetail)
                // Sesuaikan kolom namanya, biasanya 'name', 'nama', atau 'detail_name'
                'unit_name' => $item->unitID->name ?? $item->unitID->nama ?? $item->unitID->detail_name ?? 'PCS',

                // Mengambil nomor kode dan tanggal dari relasi induk master requisition
                'requisition_code' => $item->requisition->code ?? 'N/A',
                'required_date' => $item->requisition->date ? Carbon::parse($item->requisition->date)->format('Y-m-d') : null,

                'notes' => $item->notes ?? '',
            ];
        });

        // 4. Kembalikan response JSON yang rapi dan siap pakai
        return response()->json([
            'success' => true,
            'data' => $formattedData,
        ]);
    }
}
