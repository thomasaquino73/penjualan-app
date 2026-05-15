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

                    <button class="btn btn-success btn-sm ">
                        <i class="ti ti-clipboard me-1"></i> REQUISITION
                    </button>

                </div>
            </div>

        </div>
        <div class="card-body table-responsive p-3">
            <form action="{{ route('user.store') }}" method="POST" id="postForm" enctype="multipart/form-data">
                @csrf
                <div class="row mb-5">

                    <div class="col-md-6 mb-3">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Order By</label>
                                    <select name="customer_id" id="customer_id" class="form-select select2"
                                        data-placeholder="Select Customer">
                                        <option></option>
                                        @foreach ($customer as $cust)
                                            <option value="{{ $cust->id }}">{{ $cust->nama }}</option>
                                        @endforeach
                                    </select>
                                    <span class="error text-danger" id="avatarError"></span>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <textarea name="" id="" cols="" rows="5" class="form-control" disabled
                                    placeholder="Address"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Product ID <small class="text-danger">*</small> </label>
                                <input type="text" name="code" id="code" class="form-control"
                                    value="{{ $idNumber }}">
                                <span class="error text-danger" id="codeError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Date<small class="text-danger">*</small> </label>
                                <input type="date" name="date" id="date" class="form-control" value="">
                                <span class="error text-danger" id="dateError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Expected Date<small class="text-danger">*</small> </label>
                                <input type="date" name="date" id="date" class="form-control" value="">
                                <span class="error text-danger" id="dateError"></span>
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
                                <label class="form-label">Vehicle<small class="text-danger">*</small> </label>
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
                <div class="divider divider-dashed">
                    <div class="divider-text">Purchase Requisition Detail</div>
                </div>
                <button class="btn btn-sm btn-primary"><i class="ti ti-plus me-1"></i>Add</button>
                <button class="btn btn-sm btn-primary"><i class="ti ti-edit me-1"></i>Edit</button>
                <button class="btn btn-sm btn-primary"><i class="ti ti-refresh me-1"></i>Refresh</button>
                <div class="row mt-3">
                    <table class="table display responsive nowrap" id="table">
                        <thead class="border-top" style="background-color: #AEDEFC; ">
                            <tr>
                                <th>#</th>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Unit</th>
                                <th>Unit Price</th>
                                <th>Disc %</th>
                                <th>Tax</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
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
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            let table = new DataTable('#table');
        });
    </script>
@endpush
