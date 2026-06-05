 <div class="row mt-3">
     <table class="table display responsive nowrap" id="table">
         <thead class="border-top" style="background-color: #AEDEFC; ">
             <tr>
                 <th>#</th>
                 <th>Date</th>
                 <th>Qty</th>
                 <th>Unit</th>
                 <th>Unit Price ({{ $mataUangDefault ?? 'Rp' }})</th>
                 <th>Warehouse</th>
             </tr>
         </thead>
     </table>
 </div>

 @push('scripts')
     <script>
         $(document).ready(function() {
             //  const datePicker = flatpickr("#date", {
             //      enableTime: false,
             //      dateFormat: "d-m-Y",
             //      // minDate: "today",
             //      defaultDate: "{{ \Carbon\Carbon::now()->format('d-m-Y') }}"
             //  });


         });
     </script>
 @endpush
