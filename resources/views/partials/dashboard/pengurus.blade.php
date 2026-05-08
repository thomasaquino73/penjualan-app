 <div class="row g-4  mb-4">

     @forelse($pengurus as $user)
         <div class="col-lg-3 col-md-4 col-sm-6">
             <div class="card">
                 <div class="card-body">

                     <div class="d-flex align-items-center flex-column">

                         <img class="img-fluid rounded-circle mb-3 mt-3"
                             src="{{ $user->avatar ? asset($user->avatar) : asset('image/foto_user/avatar_user_default.png') }}"
                             width="100" height="100">

                         <div class="user-info text-center">
                             <h5 class="mb-1">{{ $user->nama_lengkap }}</h5>

                             @foreach ($user->getRoleNames() as $role)
                                 @if ($role == 'Ketua')
                                     <span class="badge bg-label-primary">{{ $role }}</span>
                                 @else
                                     <span class="badge bg-label-success">{{ $role }}</span>
                                 @endif
                             @endforeach
                         </div>

                     </div>

                 </div>
             </div>
         </div>
     @empty
         {{-- <div class="m-auto text-center">Belum Ada Data</div> --}}
     @endforelse

 </div>
