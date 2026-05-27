@extends('layouts.app')
@section('konten')
    <h4>
        <span class="text-muted fw-light">
            @foreach ($breadcrumb as $key => $item)
                @if (!empty($item['url']))
                    <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                @else
                    {{ $item['label'] }}
                @endif
                @if (!$loop->last)
                    /
                @endif
            @endforeach
        </span>
    </h4>

    <div class="card">
        <div
            class="card-header d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center">

            <h5 class="card-title mb-2 mb-lg-0">{{ $title }}</h5>

            <div class="col-12 col-lg-5">
                <div
                    class="d-flex flex-column flex-md-row gap-2
                    justify-content-start justify-content-lg-end">
                    <button class="btn btn-success btn-sm " id="showModalpr">
                        <i class="ti ti-clipboard me-1"></i> REQUISITION
                    </button>

                </div>
            </div>

        </div>
        <div class="card-body table-responsive p-3">
            <form action="{{ route('purchase-order.update', $model->id) }}" method="POST" id="postForm"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row mb-5">

                    <div class="col-md-6 mb-3">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Supplier</label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="ti ti-user"></i></span>
                                        <select name="supplier_id" id="supplier_id" class="form-select select2"
                                            data-placeholder="Select Supplier">
                                            <option></option>
                                            @foreach ($supplier as $supp)
                                                <option value="{{ $supp->id }}" data-alamat="{{ $supp->alamat }}"
                                                    {{ $supp->id == $model->supplier_id ? 'selected' : '' }}>
                                                    [{{ $supp->id_supplier }}] {{ $supp->nama_supplier }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <span class="error text-danger" id="supplier_idError"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">PO Number <small class="text-danger">*</small> </label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="ti ti-barcode"></i></span>
                                    <input type="text" name="code" id="code" class="form-control"
                                        value="{{ $model->code }}" readonly>
                                    <span class="error text-danger" id="codeError"></span>
                                </div>

                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">PO Date<small class="text-danger">*</small> </label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                                    <input type="text" name="datePO" id="datePO" class="form-control"
                                        value="{{ Carbon\Carbon::parse($model->datePO)->format('d-m-Y') }}">
                                    <span class="error text-danger" id="datePOError"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-12">
                    <div class="nav-align-left mb-4">
                        <ul class="nav nav-pills me-3" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                                    data-bs-target="#navs-pills-left-home" aria-controls="navs-pills-left-home"
                                    aria-selected="false" tabindex="-1">
                                    <i class="ti ti-clipboard-text"></i>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" id='tabIndo'
                                    data-bs-target="#navs-pills-left-profile" aria-controls="navs-pills-left-profile"
                                    aria-selected="false" tabindex="-1">
                                    <i class="ti ti-info-circle"></i>
                                </button>
                            </li>

                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade active show" id="navs-pills-left-home" role="tabpanel">
                                <div class="row mt-3">
                                    <table class="table display responsive nowrap" id="table">
                                        <thead class="border-top" style="background-color: #AEDEFC; ">
                                            <tr>
                                                <th>#</th>
                                                <th>Item</th>
                                                <th>Qty</th>
                                                <th>Unit</th>
                                                <th>Unit Price</th>
                                                <th>Disc</th>
                                                <th>Amount</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="navs-pills-left-profile" role="tabpanel">
                                @include('purchase.purchase_order.part.isi_tab.info_pesanan_edit')

                            </div>

                        </div>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-3"></div>
                    <div class="col-md-3">
                        <div class="col-12 mb-3 ">
                            <label class="form-label" for="sub_total">Sub Total</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">{{ $company->currency?->symbol ?? 'Rp' }}</span>
                                <input type="number" id="sub_total" name="sub_total" class="form-control"
                                    placeholder="0" readonly>
                            </div>

                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="col-12 mb-3">
                            <label class="form-label" for="discount_all">Discount</label>
                            <div class="row">
                                <div class="col-4">
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text">%</span>
                                        <input type="number" id="percent" name="percent" min="0"
                                            step="any" class="form-control" placeholder="0">
                                    </div>
                                </div>
                                <div class="col-8">
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text">{{ $company->currency?->symbol ?? 'Rp' }}</span>
                                        <input type="number" id="discount_all" name="discount_all" class="form-control"
                                            placeholder="0" min='0'>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="col-12 mb-3">
                            <label class="form-label" for="total_order"> <strong>Total Order</strong></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">{{ $company->currency?->symbol ?? 'Rp' }}</span>
                                <input type="number" id="total_order" name="total_order" class="form-control"
                                    placeholder="0" readonly>
                            </div>

                        </div>
                    </div>

                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <button type="submit" id="savedata" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i> Update
                    </button>
                    <a href="{{ route('purchase-order.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    @include('purchase.purchase_order.part.modals.modalPrDetail')
    @include('purchase.purchase_order.part.modals.modalRequisitionDetail')
@endsection
@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.0.2/css/buttons.bootstrap5.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/2.0.3/css/select.bootstrap5.css">
@endpush
@push('scripts')
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.bootstrap5.js"></script>

    <script src="https://cdn.datatables.net/select/3.1.3/js/dataTables.select.js"></script>
    <script src="https://cdn.datatables.net/select/2.0.3/js/select.bootstrap5.js"></script>
    <script src="{{ asset('assets/js/thomas/purchase_order/edit-data.js') }}"></script>
    <script>
        let prDetailsData = [
            @if (isset($model))
                @foreach ($model->details as $detail)
                    {
                        'product_id': '{{ $detail->product_id }}',
                        'data_produk': '{{ $detail->produkID ? $detail->produkID->nama_barang : 'Product Not Found' }}',
                        'quantity': '{{ $detail->qty }}',
                        'unit_id': '{{ $detail->unit_id }}',
                        'unit': '{{ $detail->unitID ? $detail->unitID->name ?? ($detail->unitID->detail ?? $detail->unitID->nama) : 'Unit' }}',
                        'unit_price': '{{ $detail->unit_price }}',
                        'discount': '{{ $detail->discount }}',
                        'amount': '{{ $detail->amount }}',
                    }
                    {{ !$loop->last ? ',' : '' }}
                @endforeach
            @endif
        ];
    </script>
@endpush
