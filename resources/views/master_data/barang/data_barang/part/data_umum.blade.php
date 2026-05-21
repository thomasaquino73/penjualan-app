   <div class="row">
       <div class="col-lg-12">
           <div class="row g-3">
               <div class="col-md-3  ">
                   <label class="form-label">Product Type<small class="text-danger">*</small></label>
                   <div class="d-flex gap-2">
                       <div class="form-check form-check-success me-4">
                           <input name="product_type" class="form-check-input" type="radio" value="supply"
                               id="radioSupply" checked>
                           <label class="form-check-label" for="radioSupply"> Supply </label>
                       </div>

                       <div class="form-check form-check-success">
                           <input name="product_type" class="form-check-input" type="radio" value="non_supply"
                               id="radioNonSupply">
                           <label class="form-check-label" for="radioNonSupply"> Non Supply </label>
                       </div>
                   </div>
                   <span class="error text-danger" id="product_typeError"></span>
               </div>
               <div class="col-md-3  ">
                   <label class="form-label">Status</label>
                   <div class="form-check form-check-primary">
                       <input class="form-check-input" type="checkbox" value="1" id="status" name="status">
                       <label class="form-check-label" for="status">
                           Set as Not Active
                       </label>
                   </div>
                   <span class="error text-danger" id="statusError"></span>
               </div>

               <div class="row mt-3">
                   <div class="col-lg-6">
                       <div class="col-12 mb-3">
                           <label class="form-label">Picture</label>
                           <input type="file" name="photo_filename" id="photo_filename" class="form-control">
                           <span class="error text-danger" id="photo_filenameError"></span>
                       </div>
                       <div class="col-12 mb-3">
                           <label class="form-label">Product ID <small class="text-danger">*</small> </label>
                           <input type="text" name="id_barang" id="id_barang" class="form-control"
                               value="{{ $idNumber }}">
                           <span class="error text-danger" id="id_barangError"></span>
                       </div>
                       <div class="col-12 mb-3">
                           <label class="form-label">Name <small class="text-danger">*</small> </label>
                           <input type="text" name="nama_barang" id="nama_barang" class="form-control">
                           <span class="error text-danger" id="nama_barangError"></span>
                       </div>
                   </div>
                   <div class="col-lg-6">
                       <div class="col-md-12  mb-3">
                           <label class="form-label">Category<small class="text-danger">*</small></label>
                           <select name="kategori_id" id="kategori_id" class="form-select select2"
                               data-placeholder="Select category">
                               <option></option>
                               @foreach ($categories as $category)
                                   <option value="{{ $category->id }}">{{ $category->detail }}</option>
                               @endforeach
                           </select>
                           <span class="error text-danger" id="kategori_idError"></span>
                       </div>
                       <div class="col-md-12 mb-3">
                           <label class="form-label">Warehouse<small class="text-danger">*</small></label>
                           <select name="gudang_id" id="gudang_id" class="form-select select2"
                               data-placeholder="Select warehouse">
                               <option></option>
                               @foreach ($warehouses as $warehouse)
                                   <option value="{{ $warehouse->id }}">
                                       {{ $warehouse->nama_gudang }}</option>
                               @endforeach
                           </select>
                           <span class="error text-danger" id="gudang_idError"></span>
                       </div>
                       <div class="col-md-12 mb-3">
                           <label class="form-label">Unit<small class="text-danger">*</small></label>
                           <div class="input-group">
                               <select name="unit_id" id="unit_id" class="form-select select2 "
                                   data-placeholder="Select unit">
                                   <option></option>
                                   @foreach ($unit as $units)
                                       <option value="{{ $units->id }}">{{ $units->detail }}</option>
                                   @endforeach
                               </select>
                               {{-- <button type="button" id="showSubUnit"
                                        class="btn btn-sm btn-primary waves-effect waves-light">...</button> --}}
                           </div>
                           <span class="error text-danger" id="unit_idError"></span>
                       </div>
                       <div class="col-md-12">
                           <label class="form-label">Inventory Type<small class="text-danger">*</small></label>
                           <div class="input-group">
                               <select name="tipe_persediaan_id" id="tipe_persediaan_id" class="form-select select2 "
                                   data-placeholder="Select inventory type">
                                   <option></option>
                                   @foreach ($inventoryTypes as $type)
                                       <option value="{{ $type->id }}">{{ $type->detail }}</option>
                                   @endforeach
                               </select>
                               {{-- <button type="button" id="showSubUnit"
                                        class="btn btn-sm btn-primary waves-effect waves-light">...</button> --}}
                           </div>
                           <span class="error text-danger" id="tipe_persediaan_idError"></span>
                       </div>
                   </div>
               </div>
           </div>
       </div>
   </div>
   <div class="divider my-7 ">
       <div class="divider-text">Additional Information</div>
   </div>

   <div class="row">
       <div class="col-lg-6" id="supplyForm">
           <h6><strong>Purchase Information</strong></h6>
           <div class="mb-3 row">
               <label class="col-md-4 col-form-label">Primary Supplier</label>
               <div class="col-md-8">
                   <select name="supplier_id" id="supplier_id" class="form-select select2 "
                       data-placeholder="Select Unit">
                       <option></option>
                       @foreach ($supplier as $sup)
                           <option value="{{ $sup->id }}">{{ $sup->nama_supplier }}</option>
                       @endforeach
                   </select>
                   <span class="error text-danger" id="supplier_idError"></span>
               </div>
           </div>
           <div class="mb-3 row">
               <label class="col-md-4 col-form-label">Unit</label>
               <div class="col-md-8">
                   <select name="unit_id_supplier" id="unit_id_supplier" class="form-select select2 "
                       data-placeholder="Select Unit">
                       <option></option>
                       @foreach ($unit as $sat)
                           <option value="{{ $sat->id }}">{{ $sat->detail }}</option>
                       @endforeach
                   </select>
                   <span class="error text-danger" id="unit_id_supplierError"></span>
               </div>
           </div>
           <div class="mb-3 row">
               <label class="col-md-4 col-form-label">Price/Unit</label>
               <div class="col-md-8">
                   <div class="input-group input-group-merge">
                       <span class="input-group-text"> {{ $mataUangDefault ?? 'Rp' }}
                       </span>
                       <input type="number" id="price" name="price" class="form-control" placeholder="0"
                           min="0">
                   </div>
                   <span class="error text-danger" id="priceError"></span>
               </div>
           </div>

           <div class="mb-3 row">
               <label class="col-md-4 col-form-label">Minimum Order Quantity</label>
               <div class="col-md-8">
                   <input class="form-control" type="number" id="minimum_order" name="minimum_order"
                       min="0">
               </div>
           </div>
           <div class="mb-3 row">
               <label for="html5-text-input" class="col-md-4 col-form-label">
                   Minimum Stock Threshold</label>
               <div class="col-md-8">
                   <input type="number" name="minimum_stock" id="minimum_stock" class="form-control"
                       min="0">
                   <span class="error text-danger" id="minimum_stockError"></span>
               </div>
           </div>
       </div>
       <div class="col-lg-6">
           <h6><strong>Unit Conversion</strong></h6>
           <div class="conversion-item border p-3 mb-2 rounded ">
               <div class="d-flex justify-content-between mb-2">

               </div>
               <div class="row g-2">
                   <div class="col-md-4">
                       <input type="text" class="form-control from_unit_text" disabled>
                       <input type="hidden" name="conversion[0][from_unit]" class="from_unit_id">
                   </div>
                   <div class="col-md-2 text-center">
                       <div class="fw-bold">=</div>
                   </div>

                   <div class="col-md-3">
                       <input type="number" name="conversion[0][qty]" class="form-control qty" placeholder="Qty"
                           disabled>
                   </div>

                   <div class="col-md-3">
                       <select name="conversion[0][to_unit]" class="form-select to_unit" disabled>
                           <option value="">Select</option>
                           @foreach ($unit as $u)
                               <option value="{{ $u->id }}">{{ $u->detail }}</option>
                           @endforeach
                       </select>
                   </div>

               </div>
           </div>

           <div class="conversion-item border p-3 mb-2 rounded">
               <div class="d-flex justify-content-between mb-2">

               </div>
               <div class="row g-2">
                   <div class="col-md-4">
                       <label>From Unit</label>
                       <input type="text" class="form-control from_unit_text" disabled>
                       <input type="hidden" name="conversion[1][from_unit]" class="from_unit_id">

                   </div>
                   <div class="col-md-2 text-center">
                       <label>&nbsp;</label>
                       <div class="fw-bold">=</div>
                   </div>

                   <div class="col-md-3">
                       <label>Quantity</label>
                       <input type="number" name="conversion[1][qty]" class="form-control qty" placeholder="Qty"
                           disabled>
                   </div>

                   <div class="col-md-3">
                       <label>To Unit</label>
                       <select name="conversion[1][to_unit]" class="form-select to_unit" disabled>
                           <option value="">Select</option>
                           @foreach ($unit as $u)
                               <option value="{{ $u->id }}">{{ $u->detail }}</option>
                           @endforeach
                       </select>
                   </div>
               </div>
           </div>
       </div>
   </div>
