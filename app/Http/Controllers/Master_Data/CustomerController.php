<?php

namespace App\Http\Controllers\Master_Data;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRequest;
use App\Models\Master_Data\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\DataTables;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $routeName = $request->route()->getName();

            $permissionMap = [
                'customer.index' => 'customer-browse',
                'customer.show' => 'customer-read',
                'customer.create' => 'customer-create',
                'customer.store' => 'customer-create',
                'customer.edit' => 'customer-edit',
                'customer.update' => 'customer-edit',
                'customer.destroy' => 'customer-delete',
                'customer.trash' => 'customer-trash',
                'customer.restore' => 'customer-restore',
            ];

            if (isset($permissionMap[$routeName])) {
                if (! $request->user()->can($permissionMap[$routeName])) {
                    abort(403, 'Unauthorized action');
                }
            }

            return $next($request);
        });
    }

    public function index(Request $r)
    {
        if ($r->ajax()) {
            $query = Customer::where('status', '<>', 0)->get();

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

                    if (auth()->user()->can('customer-edit')) {
                        $btn .= '<a class="dropdown-item " href="'.route('customer.edit',$row->id).'"
                            data-id="'.$row->id.'"> <i class="far fa-edit"></i> Edit</a>';
                    }

                    if (auth()->user()->can('customer-delete')) {
                        $btn .= '<a class="dropdown-item" href="javascript:void(0)" id="delete"
                                data-id="'.$row->id.'"
                                data-name="'.$row->nama.'"
                                ><i class="ti ti-trash"></i> Delete</a>';
                    }

                    return $btn;
                })
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'cekbok'])
                ->make(true);
        }

        $x = [
            'title' => 'Customer List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Customer', 'url' => ''],
            ],
        ];

        return view('master_data.customer.customer_index', $x);
    }

    private function generateNumberId()
    {
        $last = Customer::whereNotNull('id_customer')
            ->orderBy('id', 'desc')
            ->first();

        if (! $last) {
            return 'C-0001';
        }

        $lastId = $last->id_customer;

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

    public function generateId()
    {
        return response()->json([
            'id_customer' => $this->generateCustomerId(),
        ]);
    }

    public function store(CustomerRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $itemsDetailRaw = $request->input('items_detail');
            $data['created_by'] = Auth::id();
            if (empty($data['id_customer'])) {
                do {
                    $data['id_customer'] = $this->generateCustomerId();
                    $exists = Customer::where('id_customer', $data['id_customer'])->lockForUpdate()->exists();
                } while ($exists);
            } else {
                $exists = Customer::where('id_customer', $data['id_customer'])->lockForUpdate()->exists();
                if ($exists) {
                    DB::rollBack();

                    return response()->json([
                        'error' => 'ID Customer sudah digunakan',
                    ], 422);
                }
            }
            $customer = Customer::create($data);
            DB::table('customer_kontak')->insert([
                'customer_id' => $customer->id,
                'sapaan' => $request->sapaan,
                'contact_person' => $request->contact_person,
                'posisi_jabatan' => $request->posisi_jabatan,
                'email_kontak' => $request->email_kontak,
                'handphone_kontak' => $request->handphone_kontak,
                'notel_bisnis_kontak' => $request->notel_bisnis_kontak,
                'faximili_kontak' => $request->faximili_kontak,
                'no_whatsapp_kontak' => $request->no_whatsapp_kontak,
                'website_kontak' => $request->website_kontak,
                'catatan' => $request->catatan,
            ]);
            DB::table('customer_pengiriman')->insert([
                'customer_id' => $customer->id,
                'default_pengiriman' => $request->default_pengiriman,
                'nama_penerima' => $request->nama_penerima,
                'handphone_penerima' => $request->handphone_penerima,
                'alamat_pengiriman' => $request->alamat_pengiriman,
                'kota_pengiriman' => $request->kota_pengiriman,
                'kodepos_pengiriman' => $request->kodepos_pengiriman,
                'provinsi_pengiriman' => $request->provinsi_pengiriman,
                'negara_pengiriman' => $request->negara_pengiriman,
            ]);
            DB::table('customer_pajak')->insert([
                'customer_id' => $customer->id,
                'default_pajak' => $request->default_pajak,
                'check_address' => $request->check_address,
                'tipe_id_pajak' => $request->tipe_id_pajak,
                'nomor_wajib_pajak' => $request->nomor_wajib_pajak,
                'nama_wajib_pajak' => $request->nama_wajib_pajak,
                'id_tku' => $request->id_tku,
                'alamat_pajak' => $request->alamat_pajak,
                'kota_pajak' => $request->kota_pajak,
                'kodepos_pajak' => $request->kodepos_pajak,
                'provinsi_pajak' => $request->provinsi_pajak,
                'negara_pajak' => $request->negara_pajak,
            ]);

            DB::commit();

            return response()->json([
                'action' => 'create',
                'redirect' => route('customer.index'),
                'message' => 'Data created successfully',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

    }

    public function create()
    {
        $x = [
            'title' => 'Customer List New',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Customer New', 'url' => ''],
            ],
            'idNumber' => $this->generateNumberId(),

        ];

        return view('master_data.customer.customer_create', $x);
    }

    public function show(string $id)
    {
        //
    }

    public function edit($id)
    {
        // customr utama
        $customer = DB::table('customer')->where('id', $id)->first();
        $kontak = DB::table('customer_kontak')->where('customer_id', $id)->first();
        $pajak = DB::table('customer_pajak')->where('customer_id', $id)->first();
        $pengiriman = DB::table('customer_pengiriman')->where('customer_id', $id)->first();
      
        $x = [
            'title' => 'Customer List New',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Customer New', 'url' => ''],
            ],
            'idNumber' => $this->generateNumberId(),

            // kirim data
            'customer' => $customer,
            'kontak' => $kontak,
            'pajak' => $pajak,
            'pengiriman' => $pengiriman,
        ];

        return view('master_data.customer.customer_edit', $x);
    }

    /**
     * Update the specified resource in storage.
     */
   public function update(CustomerRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $customer = Customer::findOrFail($id);

            $data = $request->validated();
            $data['updated_by'] = Auth::id();
            unset($data['id_customer']);
            $customer->update($data);
            DB::table('customer_kontak')
                ->updateOrInsert(
                    ['customer_id' => $customer->id],
                    [
                        'sapaan' => $request->sapaan,
                        'contact_person' => $request->contact_person,
                        'posisi_jabatan' => $request->posisi_jabatan,
                        'email_kontak' => $request->email_kontak,
                        'handphone_kontak' => $request->handphone_kontak,
                        'notel_bisnis_kontak' => $request->notel_bisnis_kontak,
                        'faximili_kontak' => $request->faximili_kontak,
                        'no_whatsapp_kontak' => $request->no_whatsapp_kontak,
                        'website_kontak' => $request->website_kontak,
                        'catatan' => $request->catatan,
                        'updated_at' => now(),
                    ]
                );

            DB::table('customer_pengiriman')
                ->updateOrInsert(
                    ['customer_id' => $customer->id],
                    [
                        'default_pengiriman' => $request->default_pengiriman,
                        'nama_penerima' => $request->nama_penerima,
                        'handphone_penerima' => $request->default_pengiriman,
                        'alamat_pengiriman' => $request->alamat_pengiriman,
                        'kota_pengiriman' => $request->kota_pengiriman,
                        'kodepos_pengiriman' => $request->kodepos_pengiriman,
                        'provinsi_pengiriman' => $request->provinsi_pengiriman,
                        'negara_pengiriman' => $request->negara_pengiriman,
                        'updated_at' => now(),
                    ]
                );

            DB::table('customer_pajak')
                ->updateOrInsert(
                    ['customer_id' => $customer->id],
                    [
                        'tipe_id_pajak' => $request->tipe_id_pajak,
                'default_pajak' => $request->default_pajak,
                'check_address' => $request->check_address,
                        'nomor_wajib_pajak' => $request->nomor_wajib_pajak,
                        'nama_wajib_pajak' => $request->nama_wajib_pajak,
                        'id_tku' => $request->id_tku,
                        'alamat_pajak' => $request->alamat_pajak,
                        'kota_pajak' => $request->kota_pajak,
                        'kodepos_pajak' => $request->kodepos_pajak,
                        'provinsi_pajak' => $request->provinsi_pajak,
                        'negara_pajak' => $request->negara_pajak,
                        'updated_at' => now(),
                    ]
                );

            DB::commit();

            return response()->json([
                'action' => 'update',
                'redirect' => route('customer.index'),
                'message' => 'Data updated successfully',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy(Request $request, $id)
    {

        try {
            $table = Customer::findOrFail($id);
            $table->status = '0';
            $table->updated_by = Auth::user()->id;
            $table->save();
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }

    // public function destroy(Request $request, $id)
    // {
    //     try {
    //         $customer = Customer::findOrFail($id);

    //         // 1. Check Relations: Is this Customer ID used in other tables?
    //         // Note: Ensure 'items' and 'purchases' are defined as relationships in the Customer Model
    //         $hasRelation = $customer->items()->exists() || $customer->purchases()->exists();

    //         if ($hasRelation) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Customer cannot be deleted or deactivated because it is linked to existing transaction records.'
    //             ], 422);
    //         }

    //         // 2. If no relations exist, proceed with deactivation
    //         $customer->status = '0';
    //         $customer->updated_by = Auth::user()->id;
    //         $customer->save();

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Customer has been successfully deactivated.'
    //         ]);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'An error occurred: ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function deleteMultiple(Request $request)
    {
        $ids = $request->ids;

        if (! $ids || count($ids) == 0) {
            return response()->json(['success' => false]);
        }

        Customer::whereIn('id', $ids)->update([
            'status' => '0',
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['success' => true]);
    }

    public function trash(Request $r)
    {
        if ($r->ajax()) {
            $query = Customer::where('status', 0)->get();

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

                    if (auth()->user()->can('customer-restore')) {
                        $btn .= '<a class="dropdown-item restore" href="javascript:void(0)"
                            data-id="'.$row->id.'"> <i class="ti ti-trash-off me-1"></i> Restore</a>';
                    }

                    return $btn;
                })
                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'cekbok'])
                ->make(true);
        }

        $x = [
            'title' => 'Deleted Customer List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Deleted Customer', 'url' => ''],
            ],
        ];

        return view('master_data.customer.customer_trash', $x);
    }

    public function restore($id)
    {
        DB::beginTransaction();

        try {
            $album = Customer::find($id);
            $album->status = 1;
            $album->updated_by = Auth::id();
            $album->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'redirect' => true,
                'message' => 'Customer successfully restored.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => true,
                'redirect' => true,
                'message' => 'Customer successfully restored.',
            ]);
        }
    }

    public function restoreMultiple(Request $request)
    {
        $ids = $request->ids;

        if (! $ids || count($ids) == 0) {
            return response()->json(['success' => false]);
        }

        Customer::whereIn('id', $ids)->update([
            'status' => '1',
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['success' => true]);
    }
}
