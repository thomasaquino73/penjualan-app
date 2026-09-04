 <div class="modal fade" id="modalsDelivery">
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
                         <div class="col-6 mb-3">
                             <label class="form-label" for="nama_penerima_sub">Name</label>
                             <input type="text" id="nama_penerima_sub" name="nama_penerima_sub" class="form-control"
                                 placeholder="Enter Name">
                             <span class="error text-danger" id="nama_penerima_subError"></span>
                         </div>
                         <div class="col-6 mb-3">
                             <label class="form-label" for="handphone_penerima_sub">Phone Number</label>
                             <input type="number" id="handphone_penerima_sub" name="handphone_penerima_sub"
                                 class="form-control" placeholder="Enter Phone Number" min="0">
                             <span class="error text-danger" id="handphone_penerima_subError"></span>
                         </div>
                         <div class="col-12 mb-3">
                             <label class="form-label" for="alamat_penerima_sub">Delivery Address</label>
                             <textarea id="alamat_penerima_sub" name="alamat_penerima_sub" class="form-control" placeholder="Enter Delivery Address"></textarea>
                             <span class="error text-danger" id="alamat_penerima_subError"></span>
                         </div>
                         <div class="col-12 mb-3">
                             <label class="form-label" for="kota_penerima_sub">City</label>
                             <input type="text" id="kota_penerima_sub" name="kota_penerima_sub" class="form-control"
                                 placeholder="Enter City">
                             <span class="error text-danger" id="kota_penerima_subError"></span>
                         </div>
                         <div class="col-12 mb-3">
                             <label class="form-label" for="provinsi_penerima_sub">Province</label>
                             <input type="text" id="provinsi_penerima_sub" name="provinsi_penerima_sub" class="form-control"
                                 placeholder="Enter Province">
                             <span class="error text-danger" id="provinsi_penerima_subError"></span>
                         </div>
                         <div class="col-12 mb-3">
                             <label class="form-label" for="negara_penerima_sub">Country</label>
                             <input type="text" id="negara_penerima_sub" name="negara_penerima_sub" class="form-control"
                                 placeholder="Enter Country">
                             <span class="error text-danger" id="negara_penerima_subError"></span>
                         </div>
                         <div class="col-12 mb-3">
                             <label class="form-label" for="kodepos_penerima_sub">Postal Code</label>
                             <input type="text" id="kodepos_penerima_sub" name="kodepos_penerima_sub" class="form-control"
                                 placeholder="Enter Postal Code">
                             <span class="error text-danger" id="kodepos_penerima_subError"></span>
                         </div>
                     </div>

                 </div>
                 <div class="modal-footer">
                     <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                     <button type="submit" class="btn btn-primary" id="btnSubmitModal">Update</button>
                 </div>
             </form>
         </div>
     </div>
 </div>
