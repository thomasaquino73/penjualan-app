<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Master_Data\Barang;
use App\Models\Master_Data\Customer;
use App\Models\Master_Data\DataBarangConversion;
use App\Models\Transaction\PurchaseRequisition;
use App\Models\Transaction\PurchaseRequisitionDetail;
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
                            // Sesuaikan 'created_by' dengan nama kolom foreign key user di tabel Anda
                            $subQ->where('status', 'draft')
                                ->where('created_by', $userId);
                        });
                })
                ->orderby('code', 'desc'); // Jangan gunakan ->get() di sini agar server-side processing DataTables berjalan optimal

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
                        case 'processing':
                            $badge = 'bg-label-info';
                            $text = 'Processing';
                            break;
                        case 'deliver':
                            $badge = 'bg-label-primary';
                            $text = 'In Delivery';
                            break;
                        case 'received':
                            $badge = 'bg-label-success';
                            $text = 'Received';
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
                    $btn = '<div class="btn-group">
                  <button type="button" class="btn btn-primary dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ti ti-menu-2 ti-xs me-1"></i>
                  </button>
                  <ul class="dropdown-menu">';

                    // Tombol EDIT (Hanya muncul jika user punya izin)
                    if (auth()->user()->can('permintaan_pembelian-edit')) {
                        $btn .= '<a class="dropdown-item" href="'.route('permintaan-pembelian.edit', $row->id).'"><i class="far fa-edit"></i> Edit</a>';
                    }

                    // Tambahan: Tombol SUBMIT / AJUKAN jika statusnya masih draft
                    if ($row->status == 'draft' && $row->created_by == auth()->id()) {
                        $btn .= '<a class="dropdown-item btn-submit-pr" href="javascript:void(0)" data-id="'.$row->id.'"><i class="ti ti-send"></i> Submit to Pending</a>';
                    }

                    // Tombol DELETE
                    if (auth()->user()->can('permintaan_pembelian-delete')) {
                        $btn .= '<a class="dropdown-item" href="javascript:void(0)" id="delete"
                                data-id="'.$row->id.'"
                                data-name="'.$row->code.'"
                                ><i class="ti ti-trash"></i> Delete</a>';
                    }

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

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
}
