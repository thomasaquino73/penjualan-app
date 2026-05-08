<?php

namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\Controller;
use App\Http\Requests\SistemRequest;
use App\Models\BasicCodeDetail;
use App\Models\LoginBackground;
use App\Models\PengaturanSistem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class PengaturanSistemController extends Controller
{
    public function index()
    {
        $sistemID = 1;
        $sistem = PengaturanSistem::findOrFail($sistemID);

        $x = [
            'title' => 'Application System',
            'dataSistem' => $sistem,
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Application System', 'url' => ''],
            ],
        ];

        return view('pengaturan.sistem.index', $x);
    }

    public function edit($id)
    {
        $sistem = PengaturanSistem::findOrFail($id);

        $x = [
            'title' => 'Application System',
            'dataSistem' => $sistem,
            'breadcrumb' => [
                ['label' => 'Application System', 'url' => route('pengaturan.sistem')],
                ['label' => 'Application System', 'url' => ''],
            ],
        ];

        return view('pengaturan.sistem.edit', $x);
    }

    private function uploadAvatar($avatar)
    {
        $name = uniqid().time();
        $destination = 'image/logo';
        $filePath = $avatar->move($destination, $name.'.'.$avatar->getClientOriginalExtension());

        return str_replace('\\', '/', $filePath);
    }

    public function store(SistemRequest $r, $id)
    {
        DB::beginTransaction();

        try {
            $sistem = PengaturanSistem::findOrFail($id);

            $data = $r->except('avatar');
            $data['nama_sistem'] = 'Laravel 12';

            if ($r->hasFile('avatar')) {

                $logoPath = $this->uploadAvatar($r->file('avatar'));

                $data['logo'] = $logoPath;
                $data['favicon'] = $logoPath;
            }

            $sistem->update($data);

            DB::commit();

            return response()->json([
                'title' => 'Success',
                'message' => 'System settings updated successfully.',
                'redirect' => route('pengaturan.sistem'),
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function login_background_index(Request $r)
    {
        if ($r->ajax()) {
            $query = LoginBackground::where('status', '<>', 0);

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
                ->addColumn('gambar', function ($row) {
                    $avatarUrl = $row->gambar
                        ? asset('image/login_background/'.$row->gambar)
                        : asset('image/no-images.jpg');

                    return '<img class="avatar avatar-md rounded-circle me-2 avatar-online"
                                src="'.$avatarUrl.'"
                                alt="Login_background">';
                })
                ->addColumn('status', function ($row) {
                    if ($row->status == 1) {
                        return '<span class="badge bg-success">Active</span>';
                    } else {
                        return '<span class="badge bg-warning">Not Active</span>';
                    }
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">
                      <button type="button" class="btn btn-primary dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-menu-2 ti-xs me-1"></i> Action
                      </button>
                      <ul class="dropdown-menu" style="">';
                         if (auth()->user()->can('login_background-edit')) {

                    $btn .= '<a class="dropdown-item editPost" href="javascript:void(0)"
                            data-id="'.$row->id.'"> <i class="far fa-edit me-1"></i>Edit</a>';
                         }
                         if (auth()->user()->can('login_background-read')) {

                    $btn .= '<a class="dropdown-item detail" href="javascript:void(0)"
                                data-gambar="'.asset('image/login_background/'.$row->gambar).'"
                                data-alias="'.$row->alias.'">
                                <i class="far fa-eye me-1"></i>Detail
                            </a>';
                         }
                         if (auth()->user()->can('login_background-edit')) {

                    $btn .= '<a class="dropdown-item" href="javascript:void(0)" id="delete"
                                data-id="'.$row->id.'"
                                data-name="'.$row->alias.'"
                                ><i class="fa fa-trash me-1"></i> Delete</a>';
                         }

                    return $btn;
                })
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'gambar'])
                ->make(true);
        }

        $background = DB::table('login_background')->where('status', 1)->get();

        $x = [
            'title' => 'Application Login Background',
            'background' => $background,
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Application Login Background', 'url' => ''],
            ],
        ];

        return view('pengaturan.login_background.index', $x);
    }

    public function login_background_store(Request $request)
    {

        $id = $request->input('id');

        // Rule validasi
        $rules = [
            'status' => 'required',
        ];

        // Jika create → gambar wajib
        if (empty($id)) {
            $rules['gambar'] = 'required|image|mimes:jpg,jpeg,png';
        } else {
            // jika update → gambar optional
            $rules['gambar'] = 'nullable|image|mimes:jpg,jpeg,png';
        }

        $validator = Validator::make($request->all(), $rules, [
            'gambar.required' => 'Gambar wajib diisi',
            'gambar.image' => 'File harus berupa gambar',
            'gambar.mimes' => 'Format gambar harus jpg, jpeg, png',
            'gambar.max' => 'Ukuran gambar maksimal 2MB',
            'status.required' => 'Status wajib dipilih',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        try {

            $data = [
                'status' => $request->status,
            ];

            // Upload gambar jika ada
            if ($request->hasFile('gambar')) {

                $file = $request->file('gambar');
                $namaFile = time().'_'.$file->getClientOriginalName();
                $file->move(public_path('image/login_background'), $namaFile);

                // buat alias unik
                $alias = 'bg-'.Str::random(8);

                $data['gambar'] = $namaFile;
                $data['alias'] = $alias;
            }

            if (! empty($id)) {

                $data['updated_at'] = now();
                $data['updated_by'] = Auth::id();

                DB::table('login_background')
                    ->where('id', $id)
                    ->update($data);

                return response()->json([
                    'message' => 'Data berhasil diupdate',
                    'title' => 'Updated',
                ], 200);

            } else {

                $data['created_at'] = now();
                $data['created_by'] = Auth::user()->id;

                DB::table('login_background')->insert($data);

                return response()->json([
                    'message' => 'Data berhasil ditambahkan',
                    'title' => 'Created',
                ], 201);
            }

        } catch (\Exception $e) {

            return response()->json([
                'error' => 'Terjadi kesalahan : '.$e->getMessage(),
            ], 500);
        }
    }

    public function login_background_edit(Request $request)
    {

        $where = [
            'id' => $request->id,
        ];
        $data = LoginBackground::where($where)->first();

        return response()->json($data);
    }

    public function login_background_destroy($id)
    {
        DB::beginTransaction();

        try {
            $daftar = LoginBackground::findOrFail($id);

            // ✅ cek apakah ada nama file
            if (! empty($daftar->gambar)) {

                $path = public_path('image/login_background/'.$daftar->gambar);

                // ✅ pastikan file benar-benar ada sebelum dihapus
                if (file_exists($path) && is_file($path)) {
                    unlink($path);
                }
            }

            // ✅ hapus data database
            $daftar->delete();

            DB::commit();

            return response()->json([
                'action' => 'delete',
                'message' => 'Image deleted successfully',
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Failed to delete image',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function mata_uang_index(Request $r)
    {
        if ($r->ajax()) {
            $query = BasicCodeDetail::where('master_id', 4);

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
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">
                      <button type="button" class="btn btn-primary dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-menu-2 ti-xs me-1"></i> Action
                      </button>
                      <ul class="dropdown-menu" style="">';
                         if (auth()->user()->can('mata_uang-edit')) {
                    $btn .= '<a class="dropdown-item editPost" href="javascript:void(0)"
                            data-id="'.$row->id.'"> <i class="far fa-edit me-1"></i>Edit</a>';
                         }
                            if (auth()->user()->can('mata_uang-delete')) {
                    $btn .= '<a class="dropdown-item" href="javascript:void(0)" id="delete"
                                data-id="'.$row->id.'"
                                data-name="'.$row->detail.'"
                                ><i class="fa fa-trash me-1"></i> Delete</a>';
                            }

                    return $btn;
                })
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'gambar'])
                ->make(true);
        }

        $x = [
            'title' => 'Currency',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Currency', 'url' => ''],
            ],
        ];

        return view('pengaturan.mata_uang.index', $x);
    }

    public function mata_uang_edit(Request $request)
    {

        $where = [
            'id' => $request->id,
        ];
        $data = BasicCodeDetail::where($where)->first();

        return response()->json($data);
    }

    public function mata_uang_store(Request $request)
    {
        $id = $request->input('id');

        // ✅ RULE VALIDASI
        $rules = [
            'detail' => 'required|unique:basic_code_detail,detail,'.$id.',id,master_id,4',
            'description' => 'nullable',
        ];

        $validator = Validator::make($request->all(), $rules, [
            'detail.required' => 'Detail is required',
            'detail.unique' => 'Detail has already been taken',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        try {

            // ✅ AMBIL DATA DARI INPUT (INI YANG BENAR)
            $data = [
                'detail' => $request->detail,
                'description' => $request->description,
                'master_id' => 4,
            ];

            if (! empty($id)) {

                // ✅ UPDATE
                $data['updated_at'] = now();
                $data['updated_by'] = Auth::id();

                DB::table('basic_code_detail')
                    ->where('id', $id)
                    ->update($data);

                return response()->json([
                    'action' => 'update',
                    'message' => 'Data updated successfully',
                ], 200);

            } else {

                // ✅ CREATE
                $data['created_at'] = now();
                $data['created_by'] = Auth::id();

                DB::table('basic_code_detail')->insert($data);

                return response()->json([
                    'action' => 'create',
                    'message' => 'Data created successfully',
                ], 201);
            }

        } catch (\Exception $e) {

            return response()->json([
                'error' => 'Error: '.$e->getMessage(),
            ], 500);
        }
    }

    public function mata_uang_destroy($id)
    {
        DB::beginTransaction();

        try {

            $daftar = BasicCodeDetail::findOrFail($id);

            $daftar->delete();

            DB::commit();

            return response()->json([
                'message' => 'Data deleted successfully',
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Failed to delete data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
