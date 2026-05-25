<?php

namespace App\Http\Controllers\Setting\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\SyaratPembayaranRequest;
use App\Models\Setting\SyaratPembayaran;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class SyaratPembayaranController extends Controller
{
    public function index(Request $r)
    {

        if ($r->ajax()) {
            $query = SyaratPembayaran::orderBy('created_at', 'desc')->get();

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
                    if ($row->status == 1) {
                        return '<span class="badge bg-info">Active</span>';
                    } else {
                        return '<span class="badge bg-danger">Not Active</span>';
                    }
                })
                ->rawColumns(['created_at', 'updated_at', 'status'])
                ->make(true);
        }
        $x = [
            'title' => 'Payment Term List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Payment Term', 'url' => ''],
            ],
        ];

        return view('pengaturan.syarat_pembayaran.syarat_pembayaran_index', $x);
    }

    public function store(SyaratPembayaranRequest $request)
    {
        try {
            $id = $request->input('id');
            $data = $request->all();

            if (! empty($id)) {
                $data['updated_at'] = now();
                $data['updated_by'] = Auth::id();

                SyaratPembayaran::updateOrCreate(['id' => $id], $data);

                return response()->json([
                    'action' => 'update',
                    'message' => 'Data updated successfully',
                ], 200);

            } else {
                $data['created_at'] = now();
                $data['created_by'] = Auth::id();

                SyaratPembayaran::create($data);

                return response()->json([
                    'action' => 'create',
                    'message' => 'Data created successfully',
                ], 201);
            }

        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $data = SyaratPembayaran::find($id);

        if (! $data) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json($data);
    }

    public function destroy(Request $request, $id)
    {
        try {
            $detail = SyaratPembayaran::findOrFail($id);

            $detail->delete();

            return response()->json([
                'action' => 'delete',
                'status' => 'success',
                'message' => 'Data deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred: '.$e->getMessage(),
            ], 500);
        }
    }
}
