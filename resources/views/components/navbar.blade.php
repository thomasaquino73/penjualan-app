  <!-- Navbar -->

  <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
      id="layout-navbar">
      <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
          <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
              <i class="ti ti-menu-2 ti-sm"></i>
          </a>
      </div>

      <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
          <!-- Search -->
          {{-- <div class="navbar-nav align-items-center">
              <div class="nav-item navbar-search-wrapper mb-0">
                  <a class="nav-item nav-link search-toggler d-flex align-items-center px-0" href="javascript:void(0);">
                      <i class="ti ti-search ti-md me-2"></i>
                      <span class="d-none d-md-inline-block text-muted">Search (Ctrl+/)</span>
                  </a>
              </div>
          </div> --}}
          <!-- /Search -->

          <ul class="navbar-nav flex-row align-items-center ms-auto">
              <!-- Language -->
              {{-- <li class="nav-item dropdown-language dropdown me-2 me-xl-0">
                  <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                      <i class="ti ti-language rounded-circle ti-md"></i>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end">
                      <li>
                          <a class="dropdown-item" href="javascript:void(0);" data-language="en"
                              data-text-direction="ltr">
                              <span class="align-middle">English</span>
                          </a>
                      </li>
                      <li>
                          <a class="dropdown-item" href="javascript:void(0);" data-language="fr"
                              data-text-direction="ltr">
                              <span class="align-middle">French</span>
                          </a>
                      </li>
                      <li>
                          <a class="dropdown-item" href="javascript:void(0);" data-language="ar"
                              data-text-direction="rtl">
                              <span class="align-middle">Arabic</span>
                          </a>
                      </li>
                      <li>
                          <a class="dropdown-item" href="javascript:void(0);" data-language="de"
                              data-text-direction="ltr">
                              <span class="align-middle">German</span>
                          </a>
                      </li>
                  </ul>
              </li> --}}
              <!--/ Language -->

              <a class="nav-link" href="javascript:void(0);" id="wifiIcon">
                  <i class="ti ti-wifi"></i>
              </a>
              <!-- Style Switcher -->
              <li class="nav-item dropdown-style-switcher dropdown me-2 me-xl-0">
                  <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                      <i class="ti ti-md"></i>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end dropdown-styles">
                      <li>
                          <a class="dropdown-item" href="javascript:void(0);" data-theme="light">
                              <span class="align-middle"><i class="ti ti-sun me-2"></i>Light</span>
                          </a>
                      </li>
                      <li>
                          <a class="dropdown-item" href="javascript:void(0);" data-theme="dark">
                              <span class="align-middle"><i class="ti ti-moon me-2"></i>Dark</span>
                          </a>
                      </li>
                      <li>
                          <a class="dropdown-item" href="javascript:void(0);" data-theme="system">
                              <span class="align-middle"><i class="ti ti-device-desktop me-2"></i>System</span>
                          </a>
                      </li>
                  </ul>
              </li>
              <!-- / Style Switcher-->



              <!-- Notification -->
              <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-1">
                  <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown"
                      data-bs-auto-close="outside" aria-expanded="false">
                      <i class="ti ti-bell ti-md"></i>
                      @php
                          $prefix = request()->segment(1); // ambil prefix URL, contoh: 'bjid'
                          $unreadCount = auth()
                              ->user()
                              ->unreadNotifications->filter(function ($notification) use ($prefix) {
                                  return isset($notification->data['module_app']) &&
                                      $notification->data['module_app'] === $prefix;
                              })
                              ->count();
                      @endphp

                      <span class="badge bg-danger rounded-pill badge-notifications">{{ $unreadCount }}</span>

                  </a>
                  <ul class="dropdown-menu dropdown-menu-end py-0">
                      <li class="dropdown-menu-header border-bottom">
                          <div class="dropdown-header d-flex align-items-center py-3">
                              <h5 class="text-body mb-0 me-auto">Notification</h5>
                              <a href="javascript:void(0)" class="dropdown-notifications-all text-body"
                                  data-bs-toggle="tooltip" data-bs-placement="top" title="Mark all as read"><i
                                      class="ti ti-mail-opened fs-4"></i></a>
                          </div>
                      </li>
                      <li class="dropdown-notifications-list scrollable-container">
                          <ul class="list-group list-group-flush">
                              @php
                                  $prefix = request()->segment(1); // Ambil prefix dari URL, contoh: 'bjid'
                                  $filteredNotifications = auth()
                                      ->user()
                                      ->unreadNotifications->filter(function ($notification) use ($prefix) {
                                          return isset($notification->data['module_app']) &&
                                              $notification->data['module_app'] === $prefix;
                                      });
                              @endphp

                              @foreach ($filteredNotifications as $notification)
                                  <li class="list-group-item list-group-item-action dropdown-notifications-item notification-item"
                                      data-id="{{ $notification->id }}"
                                      data-link="{{ $notification->data['link'] ?? '#' }}" style="cursor: pointer;">
                                      <div class="d-flex align-items-center">
                                          <div class="flex-shrink-0 me-3">
                                              <div class="avatar">
                                                  <img src="{{ asset($notification->data['avatar']) ?? asset('image/foto_user/68e99ec6de7e41760140998.avif') }}"
                                                      class="rounded-circle" alt="avatar" />
                                              </div>
                                          </div>
                                          <div class="flex-grow-1">
                                              <h6 class="mb-1">{{ $notification->data['title'] ?? 'No Title' }}</h6>
                                              <p class="mb-0">{{ $notification->data['messages'] ?? '' }}</p>
                                              <small
                                                  class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                          </div>
                                          <div class="flex-shrink-0 ms-3">
                                              <span class="badge badge-dot bg-primary"></span>
                                          </div>
                                      </div>
                                  </li>
                              @endforeach

                              @if ($filteredNotifications->isEmpty())
                                  <li class="list-group-item text-center text-muted">No new notifications</li>
                              @endif

                          </ul>
                      </li>
                      <li class="dropdown-menu-footer border-top">
                          <a href="{{ route('notifications.index') }}"
                              class="dropdown-item d-flex justify-content-center text-primary p-2 h-px-40 mb-1 align-items-center">
                              View all notifications
                          </a>
                      </li>
                  </ul>
              </li>
              <!--/ Notification -->
              <!-- User -->
              <li class="nav-item navbar-dropdown dropdown-user dropdown">
                  <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                      <div class="avatar avatar-online">
                          @php
                              $user = Auth::user();
                          @endphp

                          <div class="avatar avatar-online">
                              <img src="{{ $user?->avatar
                                  ? asset($user->avatar)
                                  : (($user?->gender ?? null) == 'Perempuan'
                                      ? asset('image/foto_user/avatar_women.png')
                                      : asset('image/foto_user/avatar_user_default.png')) }}"
                                  class="rounded-circle" />
                          </div>
                      </div>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end">
                      <li>
                          <a class="dropdown-item" href="{{ route('profile.index') }}">
                              <div class="d-flex">
                                  <div class="flex-shrink-0 me-3">
                                      <div class="avatar avatar-online">
                                          @php
                                              $user = Auth::user();
                                          @endphp

                                          <div class="avatar avatar-online">
                                              <img src="{{ $user?->avatar
                                                  ? asset($user->avatar)
                                                  : (($user?->gender ?? null) == 'Perempuan'
                                                      ? asset('image/foto_user/avatar_women.png')
                                                      : asset('image/foto_user/avatar_user_default.png')) }}"
                                                  class="rounded-circle" />
                                          </div>
                                      </div>
                                  </div>
                                  <div class="flex-grow-1">
                                      <span class="fw-medium d-block">
                                          {{ Auth::user()->fullname ?? 'N/A' }}</span>
                                      <small class="text-muted">
                                          @php
                                              $user = Auth::user();
                                          @endphp

                                          @if ($user)
                                              <span class="fw-medium d-block">{{ $user->nickname ?? 'N/A' }}</span>
                                              <small class="text-muted">
                                                  @foreach ($user->getRoleNames() as $role)
                                                      {{ $role }}@if (!$loop->last)
                                                          |
                                                      @endif
                                                  @endforeach
                                              </small>
                                          @else
                                              <script>
                                                  window.location.href = "{{ route('login') }}";
                                              </script>
                                          @endif
                                      </small>
                                  </div>
                              </div>
                          </a>
                      </li>
                      <li>
                          <div class="dropdown-divider"></div>
                      </li>
                      <li>
                          <a class="dropdown-item" href="{{ route('profile.index') }}">
                              <i class="ti ti-user-check me-2 ti-sm"></i>
                              <span class="align-middle">My Profile</span>
                          </a>
                      </li>
                      <li>
                          <a class="dropdown-item" href="{{ route('profile.changepassword') }}">
                              <i class="ti ti-lock me-2 ti-sm"></i>
                              <span class="align-middle">Change Password</span>
                          </a>
                      </li>
                      <li>
                          <div class="dropdown-divider"></div>
                      </li>
                      <li>
                          <form method="POST" action="{{ route('logout') }}" id="logout-form">
                              @csrf
                              <a href="#" id="logout-button" class="dropdown-item">
                                  <i class="ti ti-logout me-2 ti-sm"></i>
                                  <span class="align-middle">Log Out</span>
                              </a>
                          </form>

                      </li>
                  </ul>
              </li>
              <!--/ User -->
          </ul>
      </div>

      <!-- Search Small Screens -->
      <div class="navbar-search-wrapper search-input-wrapper d-none">
          <input type="text" class="form-control search-input container-xxl border-0" placeholder="Search..."
              aria-label="Search..." />
          <i class="ti ti-x ti-sm search-toggler cursor-pointer"></i>
      </div>
  </nav>

  <!-- / Navbar -->
  @push('scripts')
      <script>
          document.getElementById('logout-button').addEventListener('click', function(e) {
              e.preventDefault(); // hentikan default link behavior

              Swal.fire({
                  title: 'Are you sure?',
                  text: "Want to Logout Now",
                  icon: 'question',
                  showCancelButton: true,
                  confirmButtonText: 'Yes, Logout',
                  cancelButtonText: 'Cancel',
                  customClass: {
                      confirmButton: 'btn btn-primary me-3 waves-effect waves-light',
                      cancelButton: 'btn btn-label-secondary waves-effect waves-light'
                  },
                  buttonsStyling: false
              }).then((result) => {
                  if (result.isConfirmed) {
                      // submit form logout
                      document.getElementById('logout-form').submit();
                  }
              });
          });
      </script>
      <script>
          function updateConnectionStatus() {
              const icon = document.getElementById('wifiIcon');

              if (navigator.onLine) {
                  icon.style.color = 'green'; // online
              } else {
                  icon.style.color = 'black'; // offline
              }
          }

          // cek saat pertama load
          updateConnectionStatus();

          // event ketika berubah
          window.addEventListener('online', updateConnectionStatus);
          window.addEventListener('offline', updateConnectionStatus);
      </script>
  @endpush
