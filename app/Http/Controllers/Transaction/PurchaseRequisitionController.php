<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\General\Company;
use App\Models\Master_Data\Barang;
use App\Models\Master_Data\Customer;
use App\Models\Master_Data\DataBarangConversion;
use App\Models\Transaction\PurchaseRequisition;
use App\Models\Transaction\PurchaseRequisitionDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class PurchaseRequisitionController extends Controller
{
    public function index(Request $r)
    {
        if ($r->ajax()) {
            // Ambil ID user yang sedang login
            $userId = auth()->id();

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
                ->addColumn('cekbok', function ($row) {
                    return '<div class="form-check form-check-primary mt-3">
                        <input class="form-check-input checkItem" type="checkbox" value="'.$row->id.'">
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
                        // ✅ TOMBOL EDIT (Hanya jika status draft)
                        if ($user->can('permintaan_pembelian-edit') && $row->status == 'draft') {
                            $btn .= '<a class="dropdown-item" href="'.route('permintaan-pembelian.edit', $row->id).'"><i class="far fa-edit me-1"></i> Edit</a>';
                        }

                        // ✅ TOMBOL SUBMIT (Hanya jika status draft)
                        if ($row->status == 'draft') {
                            $btn .= '<a class="dropdown-item btn-submit-pr" href="javascript:void(0)" data-id="'.$row->id.'"><i class="ti ti-send me-1"></i> Submit to Pending</a>';
                        }

                        // ✅ TOMBOL DELETE (Hanya jika status draft)
                        if ($user->can('permintaan_pembelian-delete') && $row->status == 'draft') {
                            $btn .= '<a class="dropdown-item" href="javascript:void(0)" id="delete" data-id="'.$row->id.'" data-name="'.$row->code.'"><i class="ti ti-trash me-1"></i> Delete</a>';
                        }

                        // 🕒 TEKS JIKA STATUS PENDING (Untuk Pembuat Dokumen)
                        if ($row->status == 'pending') {
                            $btn .= '<span class="dropdown-item-text text-warning small"><i class="ti ti-clock me-1"></i> Awaiting approval</span>';
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
                        $btn .= '<span class="dropdown-item-text text-muted small"><i class="ti ti-alert-circle me-1"></i> Cannot approve your own PR</span>';
                    }

                    // ─── KONDISI GLOBAL: JIKA DATA SUDAH DI-PROCESS LANJUT ───────────────────
                    if ($row->status !== 'draft' && $row->status !== 'pending') {
                        $btn .= '<span class="dropdown-item-text text-muted small"><i class="ti ti-info-circle me-1"></i> Data processed successfully</span>';
                    }
                    if (auth()->user()->can('permintaan_pembelian-read')) {
                        $btn .= '<a class="dropdown-item " href="'.route('permintaan-pembelian.show', $row->id).'"
                            data-id="'.$row->id.'"> <i class="ti ti-list-details"></i> Detail</a>';
                    }
                    $btn .= '<a class="dropdown-item " target="_blank" href="'.route('permintaan-pembelian.print', $row->id).'"
                            data-id="'.$row->id.'"> <i class="ti ti-printer"></i> Print</a>';
                    // 3. Tutup komponen tag HTML dropdown
                    $btn .= '</ul></div>';

                    return $btn;
                })
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'cekbok'])
                ->make(true);
        }

        $x = [
            'title' => 'Purchase Requisition List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Purchase Requisition', 'url' => ''],
            ],
        ];

        return view('transaction.purchase_requisition.purchase_requisition_index', $x);
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

    private function generateNumberId()
    {
        $last = PurchaseRequisition::whereNotNull('code')
            ->orderBy('id', 'desc')
            ->first();

        if (! $last) {
            return 'PR-0001';
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

    public function create()
    {
        $x = [
            'title' => 'Purchase Requisition New',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Purchase Requisition', 'url' => ''],
            ],
            'customer' => Customer::where('status', '<>', 0)->get(),
            'idNumber' => $this->generateNumberId(),
            'product' => Barang::where('status', '<>', 0)->get(),

        ];

        return view('transaction.purchase_requisition.purchase_requisition_create', $x);
    }

    public function store(Request $request)
    {
        // 1. Validasi Input Form Induk / Utama
        $request->validate([
            'code' => 'required|string', // Sesuai blueprint bigInteger
            'date' => 'required|date',
            'description' => 'nullable|string',
            'items_detail' => 'required', // Harus mengirimkan data item dari DataTables lokal
        ]);

        // Mulai Database Transaction demi keamanan integritas relasi data
        DB::beginTransaction();

        try {
            // 2. Simpan Data Master ke tabel `purchase_requisition`
            $prMaster = PurchaseRequisition::create([
                'code' => $request->code,
                'date' => Carbon::parse($request->date)->format('Y-m-d'),
                'description' => $request->description,
                'status' => 'draft', // Default value sesuai skema alur data baru
                'active' => 1,       // 1 = Active sesuai comment di blueprint
                'created_by' => Auth::id(), // ID User yang sedang login
                'updated_by' => null,
            ]);

            // 3. Decode data array string JSON (`items_detail`) yang dikirim dari DataTables lokal
            $items = json_decode($request->items_detail, true);

            if (is_array($items) && count($items) > 0) {
                foreach ($items as $item) {
                    // Simpan setiap baris item ke tabel `purchase_requisition_detail`
                    PurchaseRequisitionDetail::create([
                        'purchase_requisition_id' => $prMaster->id, // Mengambil ID dari master yang baru disimpan
                        'product_id' => $item['product_id'],
                        'qty' => $item['quantity'] ?? $item['qty'],
                        'unit_id' => $item['unit_id'],

                        // Kolom di bawah ini ada di blueprint database, berikan nilai default jika tidak ada di modal
                        'unit_price' => $item['unit_price'] ?? 0,
                        'discount' => $item['discount'] ?? 0,
                        'tax' => $item['tax'] ?? 0,

                        'active' => 1, // 1 = Active
                        'created_by' => Auth::id(),
                        'updated_by' => null,
                    ]);
                }
            } else {
                // Gagalkan proses jika ternyata isi array kosong setelah didecode
                throw new \Exception('There must be at least 1 product item entered.');
            }

            // Jika semua query aman tanpa error, terapkan simpan permanen ke database
            DB::commit();

            // 4. Atur arah redirect URL berdasarkan tombol footer yang diklik user
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
        // 1. Ambil data master sekaligus detailnya di sini (Cukup 1 query utama)
        $purchaseRequisition = PurchaseRequisition::with(['details.produkID', 'details.unitID'])->findOrFail($id);

        $x = [
            'title' => 'Purchase Requisition Show',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Purchase Requisition', 'url' => route('permintaan-pembelian.index')],
                ['label' => 'Show', 'url' => ''],
            ],
            'customer' => Customer::where('status', '<>', 0)->get(),
            'product' => Barang::where('status', '<>', 0)->get(),
            'model' => $purchaseRequisition,
            'company' => Company::first(),

            // REKOMENDASI: Ambil langsung dari object $purchaseRequisition tanpa query ulang
            'modelDetail' => $purchaseRequisition->details,
        ];

        return view('transaction.purchase_requisition.purchase_requisition_show', $x);
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
            'customer' => Customer::where('status', '<>', 0)->get(),
            'product' => Barang::where('status', '<>', 0)->get(),
            'model' => $purchaseRequisition,
        ];

        return view('transaction.purchase_requisition.purchase_requisition_edit', $x);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // 1. Validasi Input Form Induk / Utama (Sesuai dengan struktur store)
        $request->validate([
            'code' => 'required|string|unique:purchase_requisition,code,'.$id, // Menghindari validasi unik bentrok saat update data yang sama
            'date' => 'required|date',
            'description' => 'nullable|string',
            'items_detail' => 'required', // Harus mengirimkan data item dari DataTables lokal
        ]);

        // Cari data induk berdasarkan ID, jika tidak ketemu otomatis melempar error 404
        $prMaster = PurchaseRequisition::findOrFail($id);

        // Mulai Database Transaction demi keamanan integritas relasi data
        DB::beginTransaction();

        try {
            // 2. Update Data Master ke tabel `purchase_requisition`
            $prMaster->update([
                'code' => $request->code,
                'date' => Carbon::parse($request->date)->format('Y-m-d'),
                'description' => $request->description,
                // 'status' tidak diubah di sini karena mengikuti alur status yang sudah ada (misal: tetap draft/approved)
                'updated_by' => Auth::id(), // ID User yang mengubah data
            ]);

            // 3. Decode data array string JSON (`items_detail`) yang dikirim dari DataTables lokal
            $items = json_decode($request->items_detail, true);

            if (is_array($items) && count($items) > 0) {

                // Hapus semua detail lama terlebih dahulu untuk mencegah duplikasi atau data yatim (orphaned data)
                PurchaseRequisitionDetail::where('purchase_requisition_id', $prMaster->id)->delete();

                foreach ($items as $item) {
                    // Masukkan kembali baris item baru/editan ke tabel `purchase_requisition_detail`
                    PurchaseRequisitionDetail::create([
                        'purchase_requisition_id' => $prMaster->id,
                        'product_id' => $item['product_id'],
                        'qty' => $item['quantity'] ?? $item['qty'],
                        'unit_id' => $item['unit_id'],

                        // Kolom default blueprint sesuai skema di fungsi store
                        'unit_price' => $item['unit_price'] ?? 0,
                        'discount' => $item['discount'] ?? 0,
                        'tax' => $item['tax'] ?? 0,

                        'active' => 1, // 1 = Active
                        'created_by' => $prMaster->created_by, // Tetap pertahankan pembuat awal
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

                        case 'completed':
                            $badge = 'bg-success'; // Hijau Solid (Selesai Mutlak)
                            $text = 'Completed';
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

        return view('transaction.purchase_requisition.purchase_requisition_trash', $x);
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

        // Validasi keamanan: Pastikan hanya pembuat draft yang bisa mengajukannya
        if ($pr->status !== 'draft' || $pr->created_by !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki akses untuk mengajukan data ini.'], 403);
        }

        // Ubah status menjadi pending
        $pr->status = 'pending';
        $pr->updated_by = auth()->id(); // Jika Anda mencatat siapa yang melakukan update terakhir
        $pr->save();

        return response()->json(['success' => true, 'message' => 'Purchase Requisition berhasil diajukan!']);
    }

    public function changeStatus(Request $request, $id)
    {
        $pr = PurchaseRequisition::findOrFail($id);

        // Validasi: Pastikan yang mengubah status BUKAN orang yang membuat dokumen
        if ($pr->created_by === auth()->id()) {
            return response()->json(['error' => 'You may not approve/reject documents you create yourself!'], 403);
        }

        $pr->status = $request->status; // Menangkap 'processing' atau 'rejected' dari data AJAX
        $pr->updated_by = Auth::id(); // Menangkap 'processing' atau 'rejected' dari data AJAX
        $pr->save();

        return response()->json(['message' => 'Purchase Requisition status successfully updated!']);
    }

   public function print($id)
{
    // Menggunakan relasi 'creator' sesuai dengan yang ada di model Anda
    $detail = PurchaseRequisition::with(['details.produkID', 'details.unitID', 'creator'])->findOrFail($id);
    $company = Company::first(); 

    // 1. LOGIKA LOGO PERUSAHAAN (Base64)
    $logoBase64 = null;
    if ($company && $company->logo) {
        $path = public_path($company->logo); 
        if (file_exists($path)) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
    }

    // 2. LOGIKA QR CODE TANDA TANGAN DIGITAL
    // DISESUAIKAN: Menggunakan $detail->creator->name
    $approverName = $detail->creator->fullname ?? 'Staff Purchasing';
    
    $qrText = "DOCUMENT VALIDATION\n"
            . "Status: DIGITALLY SIGNED & APPROVED\n"
            . "Doc Number: " . $detail->code . "\n"
            . "Signed By: " . $approverName . "\n"
            . "Date: " . ($detail->date ?? date('Y-m-d'));

    $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qrText);
    $qrContext = stream_context_create(["ssl" => ["verify_peer" => false, "verify_peer_name" => false]]);
    
    try {
        $qrData = file_get_contents($qrUrl, false, $qrContext);
        $qrCodeBase64 = 'data:image/png;base64,' . base64_encode($qrData);
    } catch (\Exception $e) {
        $qrCodeBase64 = null;
    }

    $pdf = Pdf::loadView('pdf.purchase_requisition_pdf', compact('detail', 'company', 'logoBase64', 'qrCodeBase64'))
        ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,
            'chroot'               => [public_path()],
        ]);

    $fileName = str_replace('/', '-', $detail->code) . '.pdf';
    return $pdf->download($fileName);
}
}
