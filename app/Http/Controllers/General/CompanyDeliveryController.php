<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyDeliveryRequest;
use App\Models\General\CompanyDelivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class CompanyDeliveryController extends Controller
{
    public function index(Request $r)
    {
        if ($r->ajax()) {
            $query = CompanyDelivery::orderBy('address_name', 'asc')->get();

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

                ->rawColumns(['created_at', 'updated_at', 'status', 'gambar'])
                ->make(true);
        }

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function store(CompanyDeliveryRequest $request)
    {
        $id = $request->input('id');

        try {

            $data = $request->validated();
            $data['company_id'] = 1;

            if (! empty($id)) {

                // ✅ UPDATE
                $data['updated_at'] = now();
                $data['updated_by'] = Auth::id();

                CompanyDelivery::where('id', $id)
                    ->update($data);

                return response()->json([
                    'action' => 'update',
                    'message' => 'Data updated successfully',
                ], 200);

            } else {

                // ✅ CREATE
                $data['created_at'] = now();
                $data['created_by'] = Auth::id();

                CompanyDelivery::create($data);

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

    public function show(string $id)
    {
        //
    }

    public function edit($id)
    {
        $data = CompanyDelivery::find($id);

        if (! $data) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json($data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy($id)
    {
        // 1. Cari data delivery yang ingin dihapus
        $delivery = CompanyDelivery::findOrFail($id);

        // 2. Cek apakah delivery ini sudah terpakai di tabel Company
        // if ($delivery->companies()->exists()) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Company Delivery tidak dapat dihapus karena sedang digunakan oleh data Perusahaan (Company).',
        //     ], 422); // Status 422 Unprocessable Entity
        // }

        // // 3. Cek apakah delivery ini sudah terpakai di tabel Cash Bank
        // if ($delivery->cashBanks()->exists()) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Company Delivery tidak dapat dihapus karena sedang digunakan oleh data Kas & Bank.',
        //     ], 422);
        // }

        // 4. JIKA LOLOS PENCEKAN DI ATAS, BARU SELEKSI UNTUK DIHAPUS
        $delivery->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Company Delivery '.$delivery->address_name.' berhasil dihapus.',
        ], 200);
    }
}
