<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Http\Requests\CashBankRequest;
use App\Models\Setting\CashBank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class CashBankController extends Controller
{
    public function index(Request $r)
    {
        if ($r->ajax()) {
            $query = CashBank::orderBy('created_at', 'desc')->get();

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
                ->rawColumns(['created_at', 'updated_at'])
                ->make(true);
        }

    }

    public function store(CashBankRequest $r)
    {
        $id = $r->input('id');

        try {

            $data = $r->validated();

            if (! empty($id)) {

                // ✅ UPDATE
                $data['updated_at'] = now();
                $data['updated_by'] = Auth::id();

                CashBank::where('id', $id)
                    ->update($data);

                return response()->json([
                    'action' => 'update',
                    'message' => 'Data updated successfully',
                ], 200);

            } else {

                // ✅ CREATE
                $data['created_at'] = now();
                $data['created_by'] = Auth::id();

                CashBank::create($data);

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

}
