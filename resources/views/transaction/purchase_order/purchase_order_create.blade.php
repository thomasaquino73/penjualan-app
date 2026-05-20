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
            <form action="{{ route('purchase-order.store') }}" method="POST" id="postForm" enctype="multipart/form-data">
                @csrf
                <div class="row mb-5">

                    <div class="col-md-6 mb-3">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Supplier</label>
                                    <select name="supplier_id" id="supplier_id" class="form-select select2"
                                        data-placeholder="Select Supplier">
                                        <option></option>
                                        @foreach ($supplier as $supp)
                                            <option value="{{ $supp->id }}" data-alamat="{{ $supp->alamat }}">
                                                {{ $supp->nama_supplier }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="error text-danger" id="supplier_idError"></span>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Address</label>
                                <textarea name="alamat" id="alamat" cols="" rows="5" class="form-control" disabled
                                    placeholder="Address"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">PO Number <small class="text-danger">*</small> </label>
                                <input type="text" name="code" id="code" class="form-control"
                                    value="{{ $idNumber }}">
                                <span class="error text-danger" id="codeError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">PO Date<small class="text-danger">*</small> </label>
                                <input type="text" name="date" id="date" class="form-control" value="">
                                <span class="error text-danger" id="dateError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Expected Date<small class="text-danger">*</small> </label>
                                <input type="text" name="expected_date" id="expected_date" class="form-control"
                                    placeholder="Select Date">
                                <span class="error text-danger" id="expected_dateError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">FOB<small class="text-danger">*</small> </label>
                                <select name="fob_id" id="fob_id" class="form-select select2"
                                    data-placeholder="Select FOB">
                                    <option></option>
                                    @foreach ($fob as $f)
                                        <option value="{{ $f->detail }}"> {{ $f->detail }}</option>
                                    @endforeach
                                </select>
                                <span class="error text-danger" id="fob_idError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Term<small class="text-danger">*</small> </label>
                                <select name="term" id="term" class="form-select select2"
                                    data-placeholder="Select Term">
                                    <option></option>
                                    @foreach ($term as $term)
                                        <option value="{{ $term->id }}">{{ $term->detail }}</option>
                                    @endforeach
                                </select>
                                <span class="error text-danger" id="termError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Ship via<small class="text-danger">*</small> </label>
                                <select name="vehicle_id" id="vehicle_id" class="form-select select2"
                                    data-placeholder="Select Vehicle">
                                    <option></option>
                                    @foreach ($kendaraan as $kendaraan)
                                        <option value="{{ $kendaraan->id }}"> {{ $kendaraan->merk }} -
                                            {{ $kendaraan->plat_nomor }}</option>
                                    @endforeach
                                </select>
                                <span class="error text-danger" id="vehicle_idError"></span>
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
                                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                    data-bs-target="#navs-pills-left-profile" aria-controls="navs-pills-left-profile"
                                    aria-selected="false" tabindex="-1">
                                    <i class="ti ti-info-circle"></i>
                                </button>
                            </li>

                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade active show" id="navs-pills-left-home" role="tabpanel">
                                @include('transaction.purchase_order.part.tabel_pesanan.tabel_pesanan')
                            </div>
                            <div class="tab-pane fade" id="navs-pills-left-profile" role="tabpanel">
                                <p>
                                    Donut dragée jelly pie halvah. Danish gingerbread bonbon cookie wafer candy oat cake ice
                                    cream. Gummies halvah tootsie roll muffin biscuit icing dessert gingerbread. Pastry ice
                                    cream
                                    cheesecake fruitcake.
                                </p>
                                <p class="mb-0">
                                    Jelly-o jelly beans icing pastry cake cake lemon drops. Muffin muffin pie tiramisu
                                    halvah
                                    cotton candy liquorice caramels.
                                </p>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-lg-6 ">
                        <label class="form-label" for="description">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="8" placeholder="Enter description"></textarea>
                        <span class="error text-danger" id="descriptionError"></span>
                    </div>
                    <div class="col-lg-2 mb-3 ">

                    </div>
                    <div class="col-lg-4">
                        <div class="col-12 mb-3 ">
                            <label class="form-label" for="sub_total">Sub Total</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">{{ $company->currency?->symbol ?? 'Rp' }}</span>
                                <input type="number" id="sub_total" name="sub_total" class="form-control"
                                    placeholder="0" readonly>
                            </div>

                        </div>
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
                    <button type="submit" id="savedata" class="btn btn-primary" data-save-and-new="false">
                        <i class="fa fa-upload me-1"></i> Save and Close
                    </button>

                    <button type="submit" id="savedatamore" class="btn btn-success" data-save-and-new="true">
                        <i class="fa fa-plus-circle me-1"></i> Save and Create New
                    </button>
                    <a href="{{ route('purchase-order.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="modalPrDetail">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Create new entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formPrDetail">
                    @csrf
                    <input type="hidden" name="id" id="detail_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label" for="product_id">Product / Item</label>
                                <select name="product_id" id="product_id" class="form-select select2-modal"
                                    data-placeholder="Select Product">
                                    <option></option>
                                    @foreach ($product as $product)
                                        <option value="{{ $product->id }}">{{ $product->nama_barang }}</option>
                                    @endforeach
                                </select>
                                <span class="error text-danger" id="product_idError"></span>
                            </div>


                            <div class="col-3 mb-3">
                                <label class="form-label" for="quantity">Quantity</label>
                                <input type="number" id="quantity" name="quantity" class="form-control"
                                    placeholder="0" min="0">
                                <span class="error text-danger" id="quantityError"></span>
                            </div>
                            <div class="col-3 mb-3">
                                <label class="form-label" for="unit_id">Unit</label>
                                <select name="unit_id" id="unit_id" class="form-select select2-modal "
                                    data-placeholder="Select Unit">
                                    <option></option>
                                </select>
                                <span class="error text-danger" id="unit_idError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label" for="unit_price">Unit Price</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text">{{ $company->currency?->symbol ?? 'Rp' }}</span>
                                    <input type="number" id="unit_price" name="unit_price" class="form-control"
                                        placeholder="0" min="0">
                                </div>

                                <span class="error text-danger" id="unit_priceError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label" for="discount">Discount</label>
                                <input type="number" id="discount" name="discount" class="form-control"
                                    placeholder="0">
                                <span class="error text-danger" id="discountError"></span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="btnSubmitModal">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalRequisitionDetail">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Requisition Processing</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>
                                    <div class="form-check form-check-primary">
                                        <input class="form-check-input" type="checkbox" id="checkAll">
                                    </div>
                                </th>
                                <th>Invoice Number</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody id="requisitionTableBody">
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="btnSubmitSelected">
                        <i class="ti ti-check me-1"></i> Process Selected
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
@endpush
