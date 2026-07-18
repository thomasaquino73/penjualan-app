<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSalesRequest;
use App\Models\Inventory\Barang;
use App\Models\Inventory\Warehouse;
use App\Models\Sales\Customer;
use App\Models\Sales\StoreSales;
use App\Models\Sales\StoreSalesDetail;
use App\Models\Setting\Company;
use App\Models\Setting\Shipping;
use App\Models\Setting\SyaratPembayaran;
use App\Models\Setting\Tax;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class KasirController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $routeName = $request->route()->getName();

            $permissionMap = [
                'penjualan-toko.index' => 'penjualan_toko-browse',
                'penjualan-toko.show' => 'penjualan_toko-read',
                'penjualan-toko.create' => 'penjualan_toko-create',
                'penjualan-toko.store' => 'penjualan_toko-create',
                'penjualan-toko.edit' => 'penjualan_toko-edit',
                'penjualan-toko.update' => 'penjualan_toko-edit',
                'penjualan-toko.destroy' => 'penjualan_toko-delete',
                'penjualan-toko.trash' => 'penjualan_toko-trash',
                'penjualan-toko.restore' => 'penjualan_toko-restore',
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

        $userId = Auth::user()->id;
        $data = StoreSales::where(function ($q) use ($userId) {
                $q->where('status', '<>', 'draft')
                    ->orWhere(function ($subQ) use ($userId) {
                        $subQ->where('status', 'draft')
                            ->where('created_by', $userId);
                    });
            })
            ->orderby('store_sales_code', 'desc');;

        if ($r->filled('status')) {
            $data->where('status', $r->status);
        }

        if ($r->ajax()) {
            return DataTables::of($data)
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

                        case 'paid':
                            $badge = 'bg-label-success';
                            $text = 'Paid';
                            break;
                        default:
                            $badge = 'bg-label-secondary';
                            $text = ucfirst(str_replace('_', ' ', $row->status));
                            break;
                    }

                    $html = '
                        <div class="d-flex flex-column">
                            <span class="badge '.$badge.' text-uppercase">
                                '.$text.'
                            </span>
                    ';

                    // OUTSTANDING INFO
                    if (
                        in_array($row->status, ['partially_received']) &&
                        $row->total_outstanding_qty > 0
                    ) {

                        $html .= '
                            <small class="text-warning mt-1">
                                Outstanding : '.number_format($row->total_outstanding_qty).'
                            </small>
                        ';
                    }

                    $html .= '</div>';

                    return $html;
                })
                ->addColumn('total', function ($row) {
                    return format_uang(convert_currency($row->grand_total, $row->currency_id ?? 1));
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">
                      <button type="button" class="btn btn-primary dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-menu-2 ti-xs me-1"></i>

                      </button>
                      <ul class="dropdown-menu" style="">';
                    if (auth()->user()->can('penjualan_toko-edit')) {
                        $btn .= '<a class="dropdown-item " href="'.route('penjualan-toko.edit', $row->id).'"
                            data-id="'.$row->id.'"> <i class="far fa-edit"></i> Edit</a>';
                    }
                    if (auth()->user()->can('penjualan_toko-delete')) {
                        $btn .= '<a class="dropdown-item" href="javascript:void(0)" id="delete"
                                data-id="'.$row->id.'"
                                data-name="'.$row->detail.'"
                                ><i class="ti ti-trash"></i> Delete</a>';
                    }

                    return $btn;
                })

                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'total'])
                ->make(true);
        }
        $x = [
            'title' => 'Store Sales List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Store Sales', 'url' => ''],
            ],
        ];

        return view('sales.kasir.kasir_index', $x);
    }

    public function bulanRomawi($bulan)
    {
        $romawi = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];

        return $romawi[$bulan] ?? 'I';
    }

    private function generateNumberId()
    {
        $tahun = date('Y');
        $bulan = date('n');
        $bulanRomawi = $this->bulanRomawi($bulan);

        $prefix = "TRX/{$tahun}/{$bulanRomawi}/";

        $last = StoreSales::where('store_sales_code', 'like', $prefix.'%')
            ->orderByRaw("
            CAST(
                REGEXP_REPLACE(
                    SUBSTRING_INDEX(store_sales_code,'/',-1),
                    '[^0-9]',
                    ''
                ) AS UNSIGNED
            ) DESC
        ")
            ->first();

        if ($last) {
            preg_match('/(\d+)/', substr($last->store_sales_code, strrpos($last->store_sales_code, '/') + 1), $match);
            $lastNumber = isset($match[1]) ? (int) $match[1] : 0;
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function create()
    {
        $company = Company::with('defaultCurrency')->first();
        $taxes = Tax::where('is_active', true)
            ->whereIn('usage', ['purchase', 'both'])
            ->get();

        return view('sales.kasir.kasir_create', [
            'title' => 'Add Product',
            'idNumber' => $this->generateNumberId(),
            'mataUangDefault' => $company->defaultCurrency,
            'payment' => SyaratPembayaran::where('status', '<>', 0)->get(),
            'shipping' => Shipping::where('status', 1)->get(),
            'customer' => Customer::where('status', '<>', 0)->get(),
            'product' => Barang::where('status', '<>', 0)->get(),
            'warehouse' => Warehouse::where('status', '<>', 0)->get(),
            'bank' => DB::table('bank_list')->get(),
            'taxes' => $taxes,
        ]);
    }
    private function rupiahToNumber($value)
    {
        if (empty($value)) {
            return 0;
        }

        return str_replace(',', '.', str_replace('.', '', $value));
    }

    public function store(StoreSalesRequest $request)
    {
        DB::beginTransaction();

        try {

            $data = $request->validated();
            $itemsDetailRaw = $request->input('items_detail');

            unset($data['items_detail']);

            $data['created_by'] = Auth::id();
            $data['store_sales_date'] = Carbon::parse($request->store_sales_date)->format('Y-m-d');

            $data['sub_total'] = $request->sub_total ?? 0;
            $data['disc_nominal'] = $request->disc_nominal ?? 0;
            $data['tax_id'] = $request->tax_id;
            $data['tax_percent'] = $request->tax_percent ?? 0;
            $data['tax_amount'] = $request->tax_amount ?? 0;
            $data['grand_total'] = $request->total_order ?? 0;
           $data['amount_receive'] = $this->rupiahToNumber($request->amount_receive);
        $data['change_amount'] = $this->rupiahToNumber($request->change_amount);  
            $data['bank_list_id'] = $request->bank_list_id;
            $data['payment_method'] = $request->payment_method;
            $data['shipping_method'] = $request->shipping_method;
            $data['notes'] = $request->notes;
            $data['status'] = $request->save_and_new == 1 ? 'paid' : 'draft';
            /*
            |--------------------------------------------------------------------------
            | Generate kode jika duplicate
            |--------------------------------------------------------------------------
            */

            $storeSales = null;
            $maxRetry = 10;
            $currentCode = $request->store_sales_code;

            for ($attempt = 1; $attempt <= $maxRetry; $attempt++) {

                try {

                    $data['store_sales_code'] = $currentCode;

                    $storeSales = StoreSales::create($data);

                    break;

                } catch (QueryException $e) {

                    if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062) {

                        if (preg_match('/^(.*?)(\d+)$/', $currentCode, $matches)) {

                            $prefix = $matches[1];
                            $lastNumber = (int) $matches[2];
                            $length = strlen($matches[2]);

                            $currentCode = $prefix.
                                str_pad($lastNumber + 1, $length, '0', STR_PAD_LEFT);

                        } else {

                            $currentCode .= '-1';

                        }

                        usleep(50000);

                        continue;
                    }

                    throw $e;
                }
            }

            if (! $storeSales) {
                throw new \Exception('Gagal membuat Store Sales. Nomor transaksi sudah digunakan.');
            }

            /*
            |--------------------------------------------------------------------------
            | Detail
            |--------------------------------------------------------------------------
            */

            if (! empty($itemsDetailRaw)) {

                $items = json_decode($itemsDetailRaw, true);

                if (is_array($items) && count($items)) {

                    foreach ($items as $item) {

                        $qty = (float) ($item['qty'] ?? 0);

                        $unitPrice = (float) ($item['unit_price'] ?? 0);

                        $discount = (float) ($item['discount'] ?? 0);

                        $amount = $item['amount'] ?? (($qty * $unitPrice) - $discount);

                        StoreSalesDetail::create([
                            'store_sales_id' => $storeSales->id,
                            'product_id' => $item['product_id'],
                            'qty' => $qty,
                            'unit_id' => $item['unit_id'],
                            'unit_price' => $unitPrice,
                            'discount' => $discount,
                            'amount' => $amount,
                            'warehouse_id' => ! empty($item['warehouse_id']) ? $item['warehouse_id'] : null,
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Store Sales berhasil disimpan.',
                'redirect' => route('penjualan-toko.index'),
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
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
        $storeSales = StoreSales::with([
            'details.produkID',
            'details.unitID',
        ])->findOrFail($id);
        $company = Company::with('defaultCurrency')->first();
        $taxes = Tax::where('is_active', true)
            ->whereIn('usage', ['purchase', 'both'])
            ->get();
         $detailDataMapped = $storeSales->details->map(function ($detail) use ($storeSales) {
            return [
                'id' => $detail->id,
                'store_sales_id' => $detail->store_sales_id,
                'product_id' => $detail->product_id,
                'data_produk' => $detail->produkID->nama_barang ?? 'Product Not Found',
                'quantity' => (float) $detail->qty,
                'unit_id' => $detail->unit_id,
                'unit' => $detail->unitID->detail ?? '-',
                'warehouse_id' => $detail->warehouse_id,
                'warehouse' => $detail->warehouseID->nama_gudang ?? '-',
                'unit_price' => (float) $detail->unit_price,
                'discount_percent' => $detail->discount_percent,
                'discount' => (float) $detail->discount,
                'amount' => (float) $detail->amount,
                'tax' => (float) ($detail->tax_amount ?? 0),
            ];
        });

        return view('sales.kasir.kasir_edit', [
            'title' => 'Add Product',
            'idNumber' => $this->generateNumberId(),
            'mataUangDefault' => $company->defaultCurrency,
            'payment' => SyaratPembayaran::where('status', '<>', 0)->get(),
            'shipping' => Shipping::where('status', 1)->get(),
            'customer' => Customer::where('status', '<>', 0)->get(),
            'product' => Barang::where('status', '<>', 0)->get(),
            'warehouse' => Warehouse::where('status', '<>', 0)->get(),
            'bank' => DB::table('bank_list')->get(),
            'taxes' => $taxes,
            'model' => $storeSales,
            'jsonDetails' => $detailDataMapped,
        ]);
    }

    public function update(StoreSalesRequest $request, $id)
{
    DB::beginTransaction();

    try {

        $storeSales = StoreSales::find($id);

        if (!$storeSales) {
            throw new \Exception('Store Sales tidak ditemukan.');
        }

        $data = $request->validated();
        $itemsDetailRaw = $request->input('items_detail');

        unset($data['items_detail']);

        $data['store_sales_date'] = Carbon::parse($request->store_sales_date)->format('Y-m-d');

        $data['sub_total'] = $request->sub_total ?? 0;
        $data['disc_nominal'] = $request->disc_nominal ?? 0;
        $data['tax_id'] = $request->tax_id;
        $data['tax_percent'] = $request->tax_percent ?? 0;
        $data['tax_amount'] = $request->tax_amount ?? 0;
        $data['grand_total'] = $request->total_order ?? 0;
        $data['amount_receive'] = $this->rupiahToNumber($request->amount_receive);
        $data['change_amount'] = $this->rupiahToNumber($request->change_amount);    
        $data['bank_list_id'] = $request->bank_list_id;
        $data['payment_method'] = $request->payment_method;
        $data['shipping_method'] = $request->shipping_method;
        $data['notes'] = $request->notes;
        $data['status'] = $request->save_and_new == 1 ? 'paid' : 'draft';
        $data['updated_by'] = Auth::id();

        /*
        |--------------------------------------------------------------------------
        | Update Header
        |--------------------------------------------------------------------------
        */
        $storeSales->update($data);

        /*
        |--------------------------------------------------------------------------
        | Hapus Detail Lama
        |--------------------------------------------------------------------------
        */

        StoreSalesDetail::where('store_sales_id', $storeSales->id)->delete();

        /*
        |--------------------------------------------------------------------------
        | Simpan Detail Baru
        |--------------------------------------------------------------------------
        */

        if (!empty($itemsDetailRaw)) {

            $items = json_decode($itemsDetailRaw, true);

            if (is_array($items) && count($items)) {

                foreach ($items as $item) {

                    $qty = (float) ($item['qty'] ?? 0);

                    $unitPrice = (float) ($item['unit_price'] ?? 0);

                    $discount = (float) ($item['discount'] ?? 0);

                    $amount = $item['amount'] ?? (($qty * $unitPrice) - $discount);

                    StoreSalesDetail::create([
                        'store_sales_id' => $storeSales->id,
                        'product_id'      => $item['product_id'],
                        'qty'             => $qty,
                        'unit_id'         => $item['unit_id'],
                        'unit_price'      => $unitPrice,
                        'discount'        => $discount,
                        'amount'          => $amount,
                        'warehouse_id'    => !empty($item['warehouse_id']) ? $item['warehouse_id'] : null,
                    ]);
                }
            }
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Store Sales berhasil diperbarui.',
            'redirect' => route('penjualan-toko.index'),
        ]);

    } catch (\Throwable $e) {

        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
