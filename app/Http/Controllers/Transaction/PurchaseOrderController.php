<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseOrderRequest;
use App\Models\BasicCodeDetail;
use App\Models\General\Company;
use App\Models\Master_Data\Barang;
use App\Models\Master_Data\Kendaraan;
use App\Models\Master_Data\Supplier;
use App\Models\Transaction\PurchaseOrder;
use App\Models\Transaction\PurchaseOrderDetail;
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
            $query = PurchaseOrder::where('active', '<>', 0)->orderBy('code', 'desc')->get();

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
                        case 'draft': $badge = 'bg-label-secondary';
                            $text = 'Draft';
                            break;
                        case 'pending': $badge = 'bg-label-warning';
                            $text = 'Pending Approval';
                            break;
                        case 'processing': $badge = 'bg-label-info';
                            $text = 'Processing';
                            break;
                        case 'deliver': $badge = 'bg-label-primary';
                            $text = 'In Delivery';
                            break;
                        case 'received': $badge = 'bg-label-success';
                            $text = 'Received';
                            break;
                        case 'completed': $badge = 'bg-success';
                            $text = 'Completed';
                            break;
                        case 'rejected': $badge = 'bg-label-danger';
                            $text = 'Rejected';
                            break;
                        case 'cancelled': $badge = 'bg-danger';
                            $text = 'Cancelled';
                            break;
                        default: $badge = 'bg-label-secondary';
                            $text = ucfirst($row->status);
                            break;
                    }

                    return '<span class="badge '.$badge.' text-uppercase">'.$text.'</span>';
                })
                ->addColumn('date', function ($row) {
                    return Carbon::parse($row->date)->format('d-m-Y');
                })
                ->addColumn('expected_date', function ($row) {
                    return Carbon::parse($row->expected_date)->format('d-m-Y');
                })
                ->addColumn('amount', function ($row) {
                    return $row->amount;
                })
                ->addColumn('supplier', function ($row) {
                    return $row->supplier->nama;
                })
                ->addColumn('cekbok', function ($row) {
                    return '   <div class="form-check form-check-primary mt-3">
                                <input class="form-check-input checkItem" type="checkbox" value="'.$row->id.'"
                                    >
                            </div>';
                })
                ->addColumn('action', function ($row) {
                    // 1. Definisikan variabel ID user yang login
                    $currentUserId = auth()->id();
                    $user = auth()->user(); // Ambil data user login untuk cek permission

                    // 2. Buat pembuka komponen Dropdown Button
                    $btn = '<div class="btn-group">
                                <button type="button" class="btn btn-primary dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ti ti-menu-2 ti-xs me-1"></i>
                                </button>
                                <ul class="dropdown-menu">';

                    // ─── AKSI UNTUK PEMBUAT DOKUMEN (OWNER) ──────────────────────────────────
                    if ($row->created_by == $currentUserId) {
                        // ✅ TOMBOL SUBMIT (Hanya jika status draft)
                        if ($row->status == 'draft') {
                            $btn .= '<a class="dropdown-item btn-submit-pr" href="javascript:void(0)" data-id="'.$row->id.'"><i class="ti ti-send me-1"></i> Submit to Approval</a>';
                            $btn.='<hr class="dropdown-divider">';
                        }
                        // ✅ TOMBOL EDIT (Hanya jika status draft)
                        if ($user->can('permintaan_pembelian-edit') && $row->status == 'draft') {
                            $btn .= '<a class="dropdown-item" href="'.route('purchase-order.edit', $row->id).'"><i class="far fa-edit me-1"></i> Edit</a>';
                        }

                        // ✅ TOMBOL DELETE (Hanya jika status draft)
                        if ($user->can('permintaan_pembelian-delete') && $row->status == 'draft') {
                            $btn .= '<a class="dropdown-item" href="javascript:void(0)" id="delete" data-id="'.$row->id.'" data-name="'.$row->code.'"><i class="ti ti-trash me-1"></i> Delete</a>';
                        }

                        // 🕒 TEKS JIKA STATUS PENDING (Untuk Pembuat Dokumen)
                        if ($row->status == 'pending') {
                            $btn .= '<a class="dropdown-item" href="'.route('purchase-order.edit', $row->id).'"><i class="far fa-edit me-1"></i> Edit</a>';
                            // $btn .= '<span class="dropdown-item-text text-warning small"><i class="ti ti-clock me-1"></i> Awaiting approval</span>';
                        }
                    }

                    // ─── AKSI UNTUK USER LAIN (APPROVER) ──────────────────────────────────────
                    // Syarat: Bukan pembuat dokumen AND Punya permission approval AND Status pending
                    if ($row->created_by !== $currentUserId && $user->can('permintaan_pembelian-approval')) {
                        if ($row->status == 'pending') {
                            $btn .= '<a class="dropdown-item text-success btn-approval-pr" href="javascript:void(0)" data-status="processing" data-id="'.$row->id.'">
                                <i class="ti ti-check me-1"></i> Approve & Process
                            </a>';

                            $btn .= '<a class="dropdown-item text-danger btn-approval-pr" href="javascript:void(0)" data-status="rejected" data-id="'.$row->id.'">
                                <i class="ti ti-x me-1"></i> Reject PR
                            </a>';
                        }
                    }

                    // ─── KONDISI KHUSUS: JIKA DIA APPROVER TAPI DATA PUNYA DIA SENDIRI ─────────
                    if ($row->created_by == $currentUserId && $row->status == 'pending' && $user->can('permintaan_pembelian-approval')) {
                        // Baris ini opsional, untuk memperjelas kenapa dia tidak bisa approve dokumennya sendiri
                        // $btn .= '<span class="dropdown-item-text text-muted small"><i class="ti ti-alert-circle me-1"></i> Cannot approve your own PR</span>';
                    }

                    // ─── KONDISI GLOBAL: JIKA DATA SUDAH DI-PROCESS LANJUT ───────────────────
                    if ($row->status !== 'draft' && $row->status !== 'pending') {
                        $btn .= '<span class="dropdown-item-text text-muted small"><i class="ti ti-info-circle me-1"></i> Data processed successfully</span>';
                    }
                    if (auth()->user()->can('permintaan_pembelian-read')) {
                        $btn .= '<a class="dropdown-item " href="'.route('purchase-order.show', $row->id).'"
                            data-id="'.$row->id.'"> <i class="ti ti-list-details"></i> Detail</a>';
                    }
                    $btn .= '<a class="dropdown-item " target="_blank" href="'.route('purchase-order.print', $row->id).'"
                            data-id="'.$row->id.'"> <i class="ti ti-printer"></i> Print</a>';
                    // 3. Tutup komponen tag HTML dropdown
                    $btn .= '</ul></div>';

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

        return view('transaction.purchase_order.purchase_order_index', $x);
    }

    private function generateNumberId()
    {
        $last = PurchaseOrder::whereNotNull('code')
            ->orderBy('id', 'desc')
            ->first();

        if (! $last) {
            return 'PO-0001';
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
            'kendaraan' => Kendaraan::where('status', 1)->get(),
            'term' => BasicCodeDetail::where('master_id', 5)->get(),
            'product' => Barang::where('status', '<>', 0)->get(),
            'fob' => BasicCodeDetail::where('master_id', 3)->get(),

        ];

        return view('transaction.purchase_order.purchase_order_create', $x);
    }

    public function getProcessingData()
    {
        $requisitions = PurchaseOrder::where('status', 'processing')->get();

        return response()->json($requisitions);
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
            // 1. Ambil semua data yang telah lolos validasi dari Form Request
            $data = $request->validated();

            // 2. Pisahkan data 'items_detail' dari array utama agar tidak masuk ke tabel purchase_order
            $itemsDetailRaw = $request->input('items_detail');
            unset($data['items_detail']); // Menghapus key dari antrean field insert tabel master

            // 3. Lengkapi data audit log untuk tabel master
            $data['created_by'] = Auth::id();
            $data['updated_by'] = null;
            $data['vehicle_id'] = $request->vehicle_id;
            $data['sub_total'] = $request->sub_total;
            $data['disc_percent'] = $request->percent;
            $data['disc_nominal'] = $request->discount_all;
            $data['grand_total'] = $request->grand_total;
            $data['date'] = Carbon::parse($request->date)->format('Y-m-d');
            $data['expected_date'] = $request->expected_date ? Carbon::parse($request->expected_date)->format('Y-m-d') : null;

            // 4. Generate nomor/kode Purchase Order secara unik (Anti-Duplikat beruntun)
            do {
                $generatedCode = $this->generateNumberId();
                // Cek apakah kode PO tersebut sudah terpakai di database
                $exists = PurchaseOrder::where('code', $generatedCode)->exists();
            } while ($exists);

            // Masukkan nomor PO hasil generate ke dalam array data master
            $data['code'] = $generatedCode;

            // 5. Simpan data ke tabel master 'purchase_order' menggunakan Eloquent Create
            // (Otomatis mengisi created_at & updated_at serta mengembalikan objek data utuh)
            $purchaseOrder = PurchaseOrder::create($data);

            // 6. Proses simpan data ke tabel detail 'purchase_order_detail'
            if ($itemsDetailRaw) {
                // Bongkar string JSON data barang dari AJAX menjadi array PHP
                $items = json_decode($itemsDetailRaw, true);

                if (is_array($items) && count($items) > 0) {
                    foreach ($items as $item) {

                        // Hitung jumlah subtotal kotor per item untuk keperluan arsip data jika diperlukan
                        $qty = floatval($item['quantity']) || 0;
                        $unitPrice = floatval($item['unit_price']) || 0;
                        $discount = floatval($item['discount']) || 0;
                        $taxPercent = floatval($item['tax']) || 0;

                        $subTotal = $qty * $unitPrice;
                        $setelahDiskon = $subTotal - $discount;
                        $totalTax = $setelahDiskon * ($taxPercent / 100);
                        $amount = $setelahDiskon + $totalTax;

                        // Insert data baris barang menggunakan Query Builder (Lebih cepat untuk batch insert)
                        DB::table('purchase_order_detail')->insert([
                            'purchase_order_id' => $purchaseOrder->id, // Hubungkan dengan ID tabel master di atas
                            'product_id' => $item['product_id'],
                            'qty' => $item['quantity'], // Format dari prDetailsData JS adalah 'quantity'
                            'unit_id' => $item['unit_id'],
                            'unit_price' => $item['unit_price'] ?? 0,
                            'discount' => $item['discount'] ?? 0,
                            'tax' => $item['tax'] ?? 0,
                            'amount' => $item['amount'] ?? $amount, // Menyimpan total bersih per baris item
                            'active' => 1,
                            'created_by' => Auth::id(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // Jika semua proses insert master dan detail sukses tanpa error, commit ke database
            DB::commit();

            // Kembalikan response JSON sukses ke AJAX untuk penanganan redirect halaman
            return response()->json([
                'status' => 'success',
                'message' => 'Purchase Order '.$generatedCode.' berhasil disimpan.',
                'redirect' => route('purchase-order.index'), // Alihkan ke halaman utama Purchase Order
            ], 200);

        } catch (\Exception $e) {
            // Jika di tengah jalan ada baris kode yang gagal atau crash, batalkan semua perubahan di database
            DB::rollBack();

            // Kembalikan response JSON error dengan status code 500 (Internal Server Error)
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save data: '.$e->getMessage(),
            ], 500);
        }
    }

    public function edit(string $id)
    {
         $purchaseOrder = PurchaseOrder::findOrFail($id);
         $x = [
            'title' => 'Edit Purchase Order ',
            'breadcrumb' => [
                ['label' => 'Purchase Order', 'url' => route('purchase-order.index')],
                ['label' => 'Edit Purchase Order', 'url' => ''],
            ],
            'supplier' => Supplier::where('status', 1)->get(),
            'company' => Company::first(),
            'idNumber' => $this->generateNumberId(),
            'kendaraan' => Kendaraan::where('status', 1)->get(),
            'term' => BasicCodeDetail::where('master_id', 5)->get(),
            'product' => Barang::where('status', '<>', 0)->get(),
            'fob' => BasicCodeDetail::where('master_id', 3)->get(),
            'model' => $purchaseOrder,

        ];

        return view('transaction.purchase_order.purchase_order_edit', $x);
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
                    return Carbon::parse($row->date)->format('d-m-Y');
                })
                ->addColumn('expected_date', function ($row) {
                    return Carbon::parse($row->expected_date)->format('d-m-Y');
                })
                ->addColumn('amount', function ($row) {
                    return $row->amount;
                })
                ->addColumn('supplier', function ($row) {
                    return $row->supplier->nama;
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

        return view('transaction.purchase_order.purchase_order_trash', $x);
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
                'message' => 'Purchase requisition successfully restored.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => true,
                'redirect' => true,
                'message' => 'Purchase requisition successfully restored.',
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
        $pr = PurchaseOrder::findOrFail($id);

        // Validasi keamanan: Pastikan hanya pembuat draft yang bisa mengajukannya
        if ($pr->status !== 'draft' || $pr->created_by !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki akses untuk mengajukan data ini.'], 403);
        }

        // Ubah status menjadi pending
        $pr->status = 'pending';
        $pr->updated_by = auth()->id(); // Jika Anda mencatat siapa yang melakukan update terakhir
        $pr->save();

        return response()->json(['success' => true, 'message' => 'Purchase Order berhasil diajukan!']);
    }

    public function changeStatus(Request $request, $id)
    {
        $pr = PurchaseOrder::findOrFail($id);

        // Validasi: Pastikan yang mengubah status BUKAN orang yang membuat dokumen
        if ($pr->created_by === auth()->id()) {
            return response()->json(['error' => 'You may not approve/reject documents you create yourself!'], 403);
        }

        $pr->status = $request->status; // Menangkap 'processing' atau 'rejected' dari data AJAX
        $pr->updated_by = Auth::id(); // Menangkap 'processing' atau 'rejected' dari data AJAX
        $pr->save();

        return response()->json(['message' => 'Purchase Requisition status successfully updated!']);
    }
}
