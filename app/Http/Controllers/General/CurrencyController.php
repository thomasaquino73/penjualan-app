<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Http\Requests\CurrencyRequest;
use App\Models\General\Currency;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class CurrencyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $r)
    {
        if ($r->ajax()) {
            $query = Currency::orderBy('created_at', 'desc')->get();

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

    /**
     * Store a newly created resource in storage.
     */
    public function store(CurrencyRequest $request)
    {
        $id = $request->input('id');

        try {

            $data = $request->validated();

            if (! empty($id)) {

                // ✅ UPDATE
                $data['updated_at'] = now();
                $data['updated_by'] = Auth::id();

                DB::table('currency')
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

                DB::table('currency')->insert($data);

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

    public function edit($id)
    {
        $data = Currency::find($id);

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
        // 1. Cari data currency yang ingin dihapus
        $currency = Currency::findOrFail($id);

        // 2. Cek apakah currency ini sudah terpakai di tabel Company
        if ($currency->companies()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mata uang tidak dapat dihapus karena sedang digunakan oleh data Perusahaan (Company).',
            ], 422); // Status 422 Unprocessable Entity
        }

        // 3. Cek apakah currency ini sudah terpakai di tabel Cash Bank
        if ($currency->cashBanks()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mata uang tidak dapat dihapus karena sedang digunakan oleh data Kas & Bank.',
            ], 422);
        }

        // 4. JIKA LOLOS PENCEKAN DI ATAS, BARU SELEKSI UNTUK DIHAPUS
        $currency->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Mata uang '.$currency->name.' berhasil dihapus.',
        ], 200);
    }
    // public function destroy($id)
    // {
    //     try {
    //         $currency = Currency::findOrFail($id);
    //         $currency->delete();

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Data mata uang berhasil dihapus.',
    //         ], 200);

    //     } catch (QueryException $e) {
    //         // Cek apakah error disebabkan oleh pelanggaran Foreign Key (Error Code 23000 atau 1451)
    //         if ($e->getCode() === '23000' || str_contains($e->getMessage(), '1451')) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Currency failed to delete because this data is already used in another table transaction!',
    //             ], 422); // Gunakan HTTP status 422 (Unprocessable Entity)
    //         }

    //         // Antisipasi jika ada error database lainnya
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Terjadi kesalahan pada database: '.$e->getMessage(),
    //         ], 500);
    //     }
    // }
}
