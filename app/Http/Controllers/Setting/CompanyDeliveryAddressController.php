<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyDeliveryAddressRequest;
use App\Models\Setting\CompanyDeliveryAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class CompanyDeliveryAddressController extends Controller
{
    public function index(Request $r)
    {
        if ($r->ajax()) {
            $query = CompanyDeliveryAddress::orderBy('created_at', 'desc')->get();

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

    public function store(CompanyDeliveryAddressRequest $request)
    {
        $id = $request->input('id');

        try {

            $data = $request->all();
            $data['company_id'] = 1;

            if (! empty($id)) {

                // ✅ UPDATE
                $data['updated_at'] = now();
                $data['updated_by'] = Auth::id();

                CompanyDeliveryAddress::where('id', $id)
                    ->update($data);

                return response()->json([
                    'action' => 'update',
                    'message' => 'Data updated successfully',
                ], 200);

            } else {

                // ✅ CREATE
                $data['created_at'] = now();
                $data['created_by'] = Auth::id();

                CompanyDeliveryAddress::create($data);

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

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $data = CompanyDeliveryAddress::find($id);

        if (! $data) {
            return response()->json(['message' => 'Data not found'], 404);
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
        // 1. Cari data currency yang ingin dihapus
        $currency = CompanyDeliveryAddress::findOrFail($id);

        // 2. Cek apakah currency ini sudah terpakai di tabel Company
        // if ($currency->companies()->exists()) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Mata uang tidak dapat dihapus karena sedang digunakan oleh data Perusahaan (Company).',
        //     ], 422); // Status 422 Unprocessable Entity
        // }

        // // 3. Cek apakah currency ini sudah terpakai di tabel Cash Bank
        // if ($currency->cashBanks()->exists()) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Mata uang tidak dapat dihapus karena sedang digunakan oleh data Kas & Bank.',
        //     ], 422);
        // }

        // 4. JIKA LOLOS PENCEKAN DI ATAS, BARU SELEKSI UNTUK DIHAPUS
        $currency->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Mata uang '.$currency->name.' Deleted data successfully.',
        ], 200);
    }
}
