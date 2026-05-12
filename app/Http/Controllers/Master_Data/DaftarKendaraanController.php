<?php

namespace App\Http\Controllers\Master_Data;

use App\Http\Controllers\Controller;
use App\Http\Requests\KendaraanRequest;
use App\Models\Master_Data\Kendaraan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\DataTables;

class DaftarKendaraanController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $routeName = $request->route()->getName();

            $permissionMap = [
                'daftar-kendaraan.index' => 'kendaraan-browse',
                'daftar-kendaraan.show' => 'kendaraan-read',
                'daftar-kendaraan.create' => 'kendaraan-create',
                'daftar-kendaraan.store' => 'kendaraan-create',
                'daftar-kendaraan.edit' => 'kendaraan-edit',
                'daftar-kendaraan.update' => 'kendaraan-edit',
                'daftar-kendaraan.destroy' => 'kendaraan-delete',
                'daftar-kendaraan.trash' => 'kendaraan-trash',
                'daftar-kendaraan.restore' => 'kendaraan-restore',
            ];

            if (isset($permissionMap[$routeName])) {
                if (! $request->user()->can($permissionMap[$routeName])) {
                    abort(403, 'Unauthorized action');
                }
            }

            return $next($request);
        });
    }

    public function index()
    {
        $x = [
            'title' => 'Vehicle List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Vehicle', 'url' => ''],
            ],
        ];

        return view('master_data.kendaraan.index', $x);
    }

    public function data(Request $r)
    {
        if ($r->ajax()) {
            $query = Kendaraan::where('status', '<>', 0);

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
                ->addColumn('foto', function ($row) {
                    $avatarUrl = $row->foto
                        ? asset($row->foto)
                        : asset('image/no-images.jpg');

                    return '<img class="avatar avatar-md rounded-circle me-2 avatar-online detail"
                                src="'.$avatarUrl.'"
                                alt="Foto Kendaraan"  data-gambar="'.asset($row->foto).'"
                                data-alias="'.$row->plat_nomor.'">';
                })
                ->addColumn('status', function ($row) {
                    if ($row->status == 1) {
                        return '<span class="badge bg-success">Active</span>';
                    } else {
                        return '<span class="badge bg-warning">Not Active</span>';
                    }
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
                        
                      </button>
                      <ul class="dropdown-menu" style="">';
                    if (auth()->user()->can('kendaraan-edit')) {
                        $btn .= '<a class="dropdown-item editPost" href="javascript:void(0)"
                            data-id="'.$row->id.'"> <i class="far fa-edit me-1"></i>Edit</a>';
                    }
                    if (auth()->user()->can('kendaraan-read')) {
                        $btn .= '<a class="dropdown-item detail" href="javascript:void(0)"
                                data-gambar="'.asset($row->foto).'"
                                data-alias="'.$row->plat_nomor.'">
                                <i class="ti ti-list-details me-1"></i>Detail
                            </a>';
                    }
                    if (auth()->user()->can('kendaraan-delete')) {
                        $btn .= '<a class="dropdown-item" href="javascript:void(0)" id="delete"
                                data-id="'.$row->id.'"
                                data-name="'.$row->plat_nomor.'"
                                ><i class="fa fa-trash me-1"></i> Delete</a>';
                    }

                    return $btn;
                })
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'foto', 'cekbok'])
                ->make(true);
        }
    }

    private function uploadAvatar($avatar)
    {
        $name = uniqid().time();
        $destination = 'image/foto_kendaraan';
        $filePath = $avatar->move($destination, $name.'.'.$avatar->getClientOriginalExtension());

        return str_replace('\\', '/', $filePath);
    }

    public function store(KendaraanRequest $request)
    {
        try {
            $id = $request->input('id');

            // 1️⃣ Gabungkan plat nomor terpisah menjadi satu kolom
            $plat_nomor = strtoupper(
                trim($request->plat_depan).' '.
                trim($request->plat_tengah).' '.
                trim($request->plat_belakang)
            );

            // 2️⃣ Siapkan data untuk simpan/update
            $data = [
                'merk' => $request->merk,
                'tipe' => $request->tipe,
                'warna' => $request->warna,
                'pemilik' => $request->pemilik,
                'status' => $request->status,
                'plat_nomor' => $plat_nomor,
            ];
            if ($request->hasFile('foto')) {
                $data['foto'] = $this->uploadAvatar($request->file('foto'));
            }
            if (! empty($id)) {
                // Update existing record
                $data['updated_at'] = Carbon::now();
                $data['updated_by'] = Auth::id();

                Kendaraan::updateOrCreate(
                    ['id' => $id],
                    $data
                );

                return response()->json([
                    'message' => 'Data Updated Successfully',
                    'title' => 'Updated',
                    'updated_at' => now()->toDateTimeString(),
                ], 200);
            } else {
                // Create new record
                $data['created_at'] = Carbon::now();
                $data['created_by'] = Auth::id();

                Kendaraan::create($data);

                return response()->json([
                    'message' => 'Data Added Successfully',
                    'title' => 'Created',
                    'created_at' => now()->toDateTimeString(),
                ], 201);
            }

        } catch (ValidationException $e) {
            // Jika validasi gagal
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Error umum
            return response()->json([
                'error' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $kendaraan = Kendaraan::findOrFail($id);
        // explode plat nomor
        $platParts = explode(' ', $kendaraan->plat_nomor);

        return response()->json([
            'id' => $kendaraan->id,
            'merk' => $kendaraan->merk,
            'tipe' => $kendaraan->tipe,
            'warna' => $kendaraan->warna,
            'pemilik' => $kendaraan->pemilik,
            'status' => $kendaraan->status,
            'deskripsi' => $kendaraan->deskripsi,
            'plat_depan' => $platParts[0] ?? '',
            'plat_tengah' => $platParts[1] ?? '',
            'plat_belakang' => $platParts[2] ?? '',
        ]);
    }

    public function show($id)
    {
        $kendaraan = Kendaraan::findOrFail($id);

        $x = [
            'title' => 'Detail Kendaraan',
            'kendaraan' => $kendaraan,
        ];

        return view('kendaraan.detail', $x);

    }

    public function destroy(Request $request, $id)
    {

        try {
            $table = Kendaraan::findOrFail($id);
            $table->status = 0;
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

        Kendaraan::whereIn('id', $ids)->update([
            'status' => '0',
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['success' => true]);
    }

    public function trash(Request $r)
    {
        if ($r->ajax()) {
            $query = Kendaraan::where('status', 0);

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
                ->addColumn('foto', function ($row) {
                    $avatarUrl = $row->foto
                        ? asset($row->foto)
                        : asset('image/no-images.jpg');

                    return '<img class="avatar avatar-md rounded-circle me-2 avatar-online"
                                src="'.$avatarUrl.'"
                                alt="Foto Kendaraan">';
                })
                ->addColumn('status', function ($row) {
                    if ($row->status == 1) {
                        return '<span class="badge bg-success">Active</span>';
                    } else {
                        return '<span class="badge bg-warning">Not Active</span>';
                    }
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
                        
                      </button>
                      <ul class="dropdown-menu" style="">';
                    if (auth()->user()->can('kendaraan-restore')) {
                        $btn .= ' <button class="dropdown-item restore "data-id="'.$row->id.'"
                                                data-name="'.$row->plat_nomor.'">
                                               <i class="ti ti-trash-off me-1"></i>Restore
                                            </button>';
                    }

                    return $btn;
                })
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'foto', 'cekbok'])
                ->make(true);
        }

        return view('master_data.kendaraan.trash', [
            'title' => 'Deleted Vehicle List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Deleted Vehicles', 'url' => ''],
            ],
        ]);
    }

    public function restore($id)
    {
        DB::beginTransaction();

        try {
            $album = Kendaraan::find($id);

            $album->status = 1;
            $album->updated_by = Auth::id();
            $album->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'redirect' => true,
                'message' => 'Vehicle successfully restored.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'redirect' => false,
                'message' => 'Failed to restore vehicle.',
            ]);
        }
    }

    public function restoreMultiple(Request $request)
    {
        $ids = $request->ids;

        if (! $ids || count($ids) == 0) {
            return response()->json(['success' => false]);
        }

        Kendaraan::whereIn('id', $ids)->update([
            'status' => '1',
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['success' => true]);
    }
}
