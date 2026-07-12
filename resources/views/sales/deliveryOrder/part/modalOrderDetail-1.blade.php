<div class="modal fade" id="modalOrderDetail">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Order Processing</h5>
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
                            <th>SO Number</th>
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
