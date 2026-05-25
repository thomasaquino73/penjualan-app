<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExchangeRateRequest;
use App\Models\General\Currency;
use App\Models\General\ExchangeRate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class ExchangeRateController extends Controller
{
    public function index(Request $r)
    {
        if ($r->ajax()) {
            $query = ExchangeRate::orderBy('created_at', 'desc')->get();

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
                ->addColumn('from_currency_id', function ($row) {
                    return $row->from_currency_id ? $row->fromCurrency->code : 'N/A';
                })
                ->addColumn('to_currency_id', function ($row) {
                    return $row->to_currency_id ? $row->toCurrency->code : 'N/A';
                })
                ->addColumn('rate', function ($row) {
                    return '1 '.$row->toCurrency->code.' = '.number_format($row->rate, 2, ',', '.').' '.$row->fromCurrency->code;
                })
                ->addColumn('rate_date', function ($row) {
                    return Carbon::parse($row->rate_date)->format('d M Y'); // Format tanggal
                })
                ->rawColumns(['created_at', 'updated_at', 'from_currency_id', 'to_currency_id', 'rate', 'rate_date'])
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

    public function store(ExchangeRateRequest $request)
    {
        $id = $request->input('id');

        try {

            $data = $request->validated();

            if (! empty($id)) {

                // ✅ UPDATE
                $data['updated_at'] = now();
                $data['updated_by'] = Auth::id();

                ExchangeRate::where('id', $id)
                    ->update($data);

                return response()->json([
                    'action' => 'update',
                    'message' => 'Data updated successfully',
                ], 200);

            } else {

                // ✅ CREATE
                $data['created_at'] = now();
                $data['created_by'] = Auth::id();

                ExchangeRate::create($data);

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
        $data = ExchangeRate::find($id);

        if (! $data) {
            return response()->json(['message' => 'Data Not Found'], 404);
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
        $currency = ExchangeRate::findOrFail($id);

        // // 2. Cek apakah currency ini sudah terpakai di tabel Company
        // if ($currency->companies()->exists()) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Failed to delete exchange rate.',
        //     ], 422); // Status 422 Unprocessable Entity
        // }

        // // 3. Cek apakah currency ini sudah terpakai di tabel Cash Bank
        // if ($currency->cashBanks()->exists()) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Failed to delete exchange rate.',
        //     ], 422);
        // }

        // 4. JIKA LOLOS PENCEKAN DI ATAS, BARU SELEKSI UNTUK DIHAPUS
        $currency->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Exchange rate successfully deleted.',
        ], 200);
    }
}
