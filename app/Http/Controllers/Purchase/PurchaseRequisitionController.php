<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Barang;
use App\Models\Inventory\DataBarangConversion;
use App\Models\Purchase\PurchaseRequisition;
use App\Models\Purchase\PurchaseRequisitionDetail;
use App\Models\Sales\Customer;
use App\Models\Setting\Company;
use App\Models\User;
use App\Notifications\PurchaseRequisitionNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Dotenv\Exception\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Yajra\DataTables\DataTables;

class PurchaseRequisitionController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $routeName = $request->route()->getName();

            $permissionMap = [
                'purchase-requisition.index' => 'purchase_requisition-browse',
                'purchase-requisition.show' => 'purchase_requisition-read',
                'purchase-requisition.create' => 'purchase_requisition-create',
                'purchase-requisition.store' => 'purchase_requisition-create',
                'purchase-requisition.edit' => 'purchase_requisition-edit',
                'purchase-requisition.update' => 'purchase_requisition-edit',
                'purchase-requisition.destroy' => 'purchase_requisition-delete',
                'purchase-requisition.trash' => 'purchase_requisition-trash',
                'purchase-requisition.restore' => 'purchase_requisition-restore',
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
            $query = PurchaseRequisition::where('active', '<>', 0)
                ->where(function ($q) use ($userId) {
                    $q->where('status', '<>', 'draft')
                        ->orWhere(function ($subQ) use ($userId) {
                            $subQ->where('status', 'draft')
                                ->where('created_by', $userId);
                        });
                })
                ->orderby('code', 'desc');
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
                ->addColumn('date', function ($row) {
                    return $row->date ? Carbon::parse($row->date)->format('d M Y') : 'N/A';
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
                        auth()->user()->can('purchase_requisition-delete') &&
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
                        if ($user->can('permintaan_pembelian-edit') && $row->status == 'draft') {
                            $btn .= '<a class="dropdown-item" href="'.route('permintaan-pembelian.edit', $row->id).'">
                        <i class="far fa-edit me-1"></i> Edit
                     </a>';
                        }

                        // ✅ DELETE
                        if ($user->can('permintaan_pembelian-delete') && $row->status == 'draft') {
                            $btn .= '<a class="dropdown-item" href="javascript:void(0)" id="delete"
                        data-id="'.$row->id.'" data-name="'.$row->code.'">
                        <i class="ti ti-trash me-1"></i> Delete
                     </a>';
                        }
                    }

                    // ─── INFO JIKA SUDAH DIPROSES ─────────────────────────────
                    if ($row->status == 'processing') {
                        $btn .= '<a class="dropdown-item" href="'.route('permintaan-pembelian.edit', $row->id).'">
                        <i class="far fa-edit me-1"></i> Edit
                     </a>';
                    }

                    if ($row->status != 'closed') {
                        $btn .= '<a class="dropdown-item"
                href="javascript:void(0)" id="close"   data-id="'.$row->id.'" data-name="'.$row->code.'">
                <i class="ti ti-lock"></i> Close PR
             </a>';
                    }

                    $btn .= '<a class="dropdown-item"
                href="'.route('permintaan-pembelian.show', $row->id).'">
                <i class="ti ti-list-details"></i> Detail
             </a>';
                    $btn .= '<a class="dropdown-item" target="_blank"
                href="'.route('permintaan-pembelian.print', $row->id).'">
                <i class="ti ti-printer"></i> Print
             </a>';

                    $btn .= '</ul></div>';

                    return $btn;
                })
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'cekbok', 'date'])
                ->make(true);
        }

        $x = [
            'title' => 'Purchase Requisition List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Purchase Requisition', 'url' => ''],
            ],
        ];

        return view('purchase.purchase_requisition.purchase_requisition_index', $x);
    }

    public function table_pr(Request $r)
    {
        if ($r->ajax()) {
            $query = PurchaseRequisitionDetail::with('produkID')
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
        // Ambil record terakhir berdasarkan ID (urutkan dari yang terbaru)
        $last = PurchaseRequisition::orderBy('id', 'desc')->lockForUpdate()->first();

        if (! $last) {
            // Jika database benar-benar kosong, gunakan format default
            return 'PR/2026/VII/0001';
        }

        $lastCode = $last->code;

        // Regex untuk memisahkan prefix (semua karakter) dan angka (diakhiri digit)
        if (preg_match('/^(.*?)(\d+)$/', $lastCode, $matches)) {
            $prefix = $matches[1];      // Contoh: "PR/2026/VII/"
            $lastNumber = $matches[2];  // Contoh: "0001"

            $length = strlen($lastNumber);
            $nextNumber = (int) $lastNumber + 1;

            // Gabungkan kembali dengan format padding yang sama
            return $prefix.str_pad($nextNumber, $length, '0', STR_PAD_LEFT);
        }

        // Jika tidak ada pola angka, tambahkan -0001
        return $lastCode.'-0001';
    }

    public function create()
    {
        $x = [
            'title' => 'Purchase Requisition New',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Purchase Requisition', 'url' => ''],
            ],
            'idNumber' => $this->generateNumberId(),
            'product' => Barang::where('status', '<>', 0)->get(),

        ];

        return view('purchase.purchase_requisition.purchase_requisition_create', $x);
    }

    public function store(Request $request)
    {
        // 1. Validasi Input Form Induk / Utama
        $request->validate([
            'date' => 'required|date',
            'description' => 'nullable|string',
            'items_detail' => 'required', // Harus mengirimkan data item dari DataTables lokal
        ]);

        // Mulai Database Transaction demi keamanan integritas relasi data
        DB::beginTransaction();

        try {
            // 🔥 GENERATE CODE OTOMATIS & AMAN DARI RACE CONDITION
            // Melakukan loop otomatis jika nomor kode keduluan diambil user lain

            // 2. Simpan Data Master ke tabel `purchase_requisition`
            $data = [
                'date' => Carbon::parse($request->date)->format('Y-m-d'),
                'description' => $request->description,
                'status' => 'draft',
                'active' => 1,
                'created_by' => Auth::id(),
                'updated_by' => null,
            ];

            $purchaseRequisition = null;
            $maxRetry = 10;
            $currentCode = $request->code; // Ambil input awal dari user

            for ($attempt = 1; $attempt <= $maxRetry; $attempt++) {
                try {
                    $data['code'] = $currentCode;
                    $purchaseRequisition = PurchaseRequisition::create($data);
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

            if (! $purchaseRequisition) {
                throw new \Exception('Gagal membuat Purchase Requisition: Nomor sudah penuh atau sistem sibuk.');
            }
            // $prMaster = PurchaseRequisition::create([
            //     // 'code' => $generatedCode, // Gunakan code yang sudah di-generate secara aman
            //     'date' => Carbon::parse($request->date)->format('Y-m-d'),
            //     'description' => $request->description,
            //     'status' => 'draft', // Default value sesuai skema alur data baru
            //     'active' => 1,       // 1 = Active sesuai comment di blueprint
            //     'created_by' => Auth::id(), // ID User yang sedang login
            //     'updated_by' => null,
            // ]);

            // 3. Decode data array string JSON (`items_detail`) yang dikirim dari DataTables lokal
            $items = json_decode($request->items_detail, true);

            if (is_array($items) && count($items) > 0) {
                foreach ($items as $item) {
                    // Cek apakah required_date diisi dan tidak kosong
                    $requiredDate = ! empty($item['required_date'])
                        ? Carbon::parse($item['required_date'])->format('Y-m-d')
                        : null; // Jika kosong, langsung set ke null secara mutlak

                    // Simpan setiap baris item ke tabel `purchase_requisition_detail`
                    PurchaseRequisitionDetail::create([
                        'purchase_requisition_id' => $purchaseRequisition->id,
                        'product_id' => $item['product_id'],
                        'qty' => $item['quantity'] ?? $item['qty'],
                        'unit_id' => $item['unit_id'],
                        'outstanding_qty' => $item['quantity'] ?? $item['qty'],
                        'required_date' => $requiredDate,
                        'notes' => ! empty($item['notes']) ? $item['notes'] : null,
                        'active' => 1,
                        'created_by' => Auth::id(),
                        'updated_by' => null,
                    ]);
                }
            } else {
                // Gagalkan proses jika ternyata isi array kosong setelah didecode
                throw new \Exception('There must be at least 1 product item entered.');
            }

            DB::commit();

            $redirectUrl = $request->save_and_new == 1
                ? route('permintaan-pembelian.create') // Kembali kosongkan form untuk input data PR baru lagi
                : route('permintaan-pembelian.index');  // Selesai dan kembali ke tabel index utama

            return response()->json([
                'success' => true,
                'message' => 'Purchase Requisition saved successfully!',
                'redirect' => $redirectUrl,
            ], 200);

        } catch (\Exception $e) {
            // Batalkan semua query yang sempat berjalan jika ada error di tengah jalan (Rollback)
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to save data: '.$e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id)
    {
        // Load master PR beserta detail, produk, dan relasi unitID (BasicCodeDetail)
        $purchaseRequisition = PurchaseRequisition::with(['details.produkID', 'details.unitID', 'details.purchaseRequisitionDetails.purchaseRequisition'])->findOrFail($id);
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
            'title' => 'Purchase Requisition Show',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Purchase Requisition', 'url' => route('permintaan-pembelian.index')],
                ['label' => 'Detail', 'url' => ''],
            ],
            'customer' => Customer::where('status', '<>', 0)->get(),
            'product' => Customer::where('status', '<>', 0)->get(),
            'model' => $purchaseRequisition,
            'company' => $company,
            'logoBase64' => $logoBase64,
        ];

        return view('purchase.purchase_requisition.purchase_requisition_show', $x);
    }

    public function edit(string $id)
    {
        // Load master PR beserta detail, produk, dan relasi unitID (BasicCodeDetail)
        $purchaseRequisition = PurchaseRequisition::with(['details.produkID', 'details.unitID'])->findOrFail($id);

        $x = [
            'title' => 'Purchase Requisition Edit',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Purchase Requisition', 'url' => route('permintaan-pembelian.index')],
                ['label' => 'Edit', 'url' => ''],
            ],
            'product' => Barang::where('status', '<>', 0)->get(),
            'model' => $purchaseRequisition,
        ];

        return view('purchase.purchase_requisition.purchase_requisition_edit', $x);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // 1. Validasi Input Form Induk / Utama (Sesuai dengan struktur store)
        $request->validate([
            'code' => 'required|string|unique:purchase_requisition_'.date('Y').',code,'.$id, // Menghindari validasi unik bentrok saat update data yang sama
            'date' => 'required|date',
            'description' => 'nullable|string',
            'items_detail' => 'required', // Harus mengirimkan data item dari DataTables lokal
        ]);

        // Cari data induk berdasarkan ID, jika tidak ketemu otomatis melempar error 404
        $prMaster = PurchaseRequisition::findOrFail($id);

        // Mulai Database Transaction demi keamanan integritas relasi data
        DB::beginTransaction();

        try {
            $code = $request->code;

            while (
                PurchaseRequisition::where('code', $code)
                    ->where('id', '!=', $prMaster->id)
                    ->exists()
            ) {
                $code = $this->generateNumberId();
            }
            // 2. Update Data Master ke tabel `purchase_requisition`
            $prMaster->update([
                'code' => $code,
                'date' => Carbon::parse($request->date)->format('Y-m-d'),
                'description' => $request->description,
                'status' => $request->has('status') ? 'closed' : 'processing',
                'updated_by' => Auth::id(),
            ]);

            // 3. Decode data array string JSON (`items_detail`) yang dikirim dari DataTables lokal
            $items = json_decode($request->items_detail, true);

            if (is_array($items) && count($items) > 0) {

                // Hapus semua detail lama terlebih dahulu untuk mencegah duplikasi atau data yatim (orphaned data)
                PurchaseRequisitionDetail::where('purchase_requisition_id', $prMaster->id)->delete();

                foreach ($items as $item) {
                    $requiredDate = ! empty($item['required_date'])
                        ? Carbon::parse($item['required_date'])->format('Y-m-d')
                        : null;
                    PurchaseRequisitionDetail::create([
                        'purchase_requisition_id' => $prMaster->id,
                        'product_id' => $item['product_id'],
                        'qty' => $item['quantity'] ?? $item['qty'],
                        'outstanding_qty' => $item['quantity'] ?? $item['qty'],
                        'unit_id' => $item['unit_id'],
                        'required_date' => $requiredDate,
                        'notes' => $item['notes'] ?? null,
                        'active' => 1,
                        'updated_by' => Auth::id(),
                    ]);
                }
            } else {
                // Gagalkan proses jika ternyata isi array kosong setelah didecode
                throw new \Exception('Minimal harus ada 1 item produk yang dimasukkan.');
            }

            // Jika semua query aman tanpa error, terapkan simpan permanen ke database
            DB::commit();

            // 4. Atur arah redirect URL (Aksi update biasanya langsung kembali ke halaman index utama)
            $redirectUrl = route('permintaan-pembelian.index');

            return response()->json([
                'success' => true,
                'message' => 'Purchase Requisition successfully updated!',
                'redirect' => $redirectUrl,
            ], 200);

        } catch (\Exception $e) {
            // Batalkan semua query yang sempat berjalan jika ada error di tengah jalan (Rollback)
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
            $table = PurchaseRequisition::findOrFail($id);
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
            $query = PurchaseRequisition::where('active', 0)->orderby('code', 'desc')->get();

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
                    // Tentukan warna dan teks berdasarkan nilai status di database
                    switch ($row->status) {
                        case 'draft':
                            $badge = 'bg-label-secondary'; // Abu-abu
                            $text = 'Draft';
                            break;

                        case 'pending':
                            $badge = 'bg-label-warning'; // Kuning
                            $text = 'Pending Approval';
                            break;

                        case 'processing':
                            $badge = 'bg-label-info'; // Biru Muda
                            $text = 'Processing';
                            break;

                        case 'deliver':
                            $badge = 'bg-label-primary'; // Biru Tua / Ungu
                            $text = 'In Delivery';
                            break;

                        case 'received':
                            $badge = 'bg-label-success'; // Hijau
                            $text = 'Received';
                            break;

                        case 'closed':
                            $badge = 'bg-success'; // Hijau Solid (Selesai Mutlak)
                            $text = 'Closed';
                            break;

                        case 'rejected':
                            $badge = 'bg-label-danger'; // Merah
                            $text = 'Rejected';
                            break;

                        case 'cancelled':
                            $badge = 'bg-danger'; // Merah Solid
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

                    if (auth()->user()->can('permintaan_pembelian-restore')) {
                        $btn .= '<a class="dropdown-item restore" href="javascript:void(0)"
                            data-id="'.$row->id.'"> <i class="ti ti-trash-off me-1"></i> Restore</a>';
                    }

                    return $btn;
                })
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'cekbok'])
                ->make(true);
        }

        $x = [
            'title' => 'Deleted Purchase Requisition List',
            'breadcrumb' => [
                ['label' => 'Purchase Requisition', 'url' => route('permintaan-pembelian.index')],
                ['label' => 'Deleted Purchase Requisition', 'url' => ''],
            ],

        ];

        return view('purchase.purchase_requisition.purchase_requisition_trash', $x);
    }

    public function getUnitsByProduct($id)
    {
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
        return response()->json($result);
    }

    public function deleteMultiple(Request $request)
    {
        $ids = $request->ids;

        if (! $ids || count($ids) == 0) {
            return response()->json(['success' => false]);
        }

        PurchaseRequisition::whereIn('id', $ids)->update([
            'active' => '0',
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['success' => true]);
    }

    public function restore($id)
    {
        DB::beginTransaction();

        try {
            $permintaanpembelian = PurchaseRequisition::find($id);
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

        PurchaseRequisition::whereIn('id', $ids)->update([
            'active' => '1',
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['success' => true]);
    }

    public function submitToPending($id)
    {
        $pr = PurchaseRequisition::findOrFail($id);
        $pr->status = 'processing';
        $pr->updated_by = auth()->id(); // Jika Anda mencatat siapa yang melakukan update terakhir
        $pr->save();
        $users = User::whereHas('roles.permissions', function ($q) {
            $q->where('name', 'purchase_order-approval');
        })->get();
        // $users = User::all();
        Notification::send($users, new PurchaseRequisitionNotification($pr));

        return response()->json(['success' => true, 'message' => 'Purchase Requisition berhasil diproses!']);
    }

     public function print($id)
    {
        $purchaseRequisition = PurchaseRequisition::with(['details.produkID', 'details.unitID'])->findOrFail($id);
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
            'detail' => $purchaseRequisition,
            'company' => $company,
            'modelDetail' => $purchaseRequisition->details,
            'logoBase64' => $logoBase64,
        ];

        $pdf = Pdf::loadView('pdf.purchase_requisition_pdf', $data)
            ->setPaper('a4', 'portrait');

        // preview di browser
        $filename = $purchaseRequisition->code;

        // replace forbidden filename chars
        $filename = preg_replace('/[\/\\\\:*?"<>|]/', '-', $filename);
        $pdf->getDomPDF()->set_option('isPhpEnabled', true);

        return $pdf->stream($filename.'.pdf');

        // kalau mau download:
        // return $pdf->download('purchase-order.pdf');
    }

    public function CloseDocument(Request $request, $id)
    {

        try {
            $table = PurchaseRequisition::findOrFail($id);
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
