@extends('layouts.app')
@section('title', $title)
@section('konten')

    <div class="container-xxl flex-grow-1 container-p-y">
        <h4><span class="text-muted fw-light">
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

        <!-- Header -->
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="user-profile-header-banner">
                        <img src="{{ asset('') }}assets/img/pages/profile-banner.png" alt="Banner image"
                            class="rounded-top" />
                    </div>
                    <div class="user-profile-header d-flex flex-column flex-sm-row text-sm-start text-center mb-4">
                        <div class="flex-shrink-0 mt-n2 mx-sm-0 mx-auto">
                            <img src="{{ Auth::user()->avatar ? asset(Auth::user()->avatar) : asset('image/foto_user/avatar_user_default.png') }}"
                                alt="{{ Auth::user()->fullname ?? 'User Avatar' }}"
                                class="d-block h-auto ms-0 ms-sm-4 rounded user-profile-img" />
                        </div>
                        <div class="flex-grow-1 mt-3 mt-sm-5">
                            <div
                                class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-4 flex-md-row flex-column gap-3">

                                <div class="user-profile-info">
                                    <h4>{{ Auth::user()->fullname }}</h4>

                                    <ul
                                        class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-2">

                                        <li class="list-inline-item d-flex gap-1">
                                            <i class="ti ti-color-swatch"></i>
                                            @foreach (Auth::user()->getRoleNames() as $role)
                                                {{ $role }}
                                                @if (!$loop->last)
                                                    |
                                                @endif
                                            @endforeach
                                        </li>

                                        <li class="list-inline-item d-flex gap-1">
                                            <i class="ti ti-calendar"></i>
                                            {{ Carbon\Carbon::now()->format('d M Y') }}
                                        </li>

                                    </ul>
                                </div>

                                <div class="d-flex gap-2">
                                    <a href="{{ route('profile.changepassword') }}" class="btn btn-primary">
                                        <i class="ti ti-lock me-1"></i>Ubah
                                    </a>

                                    <a href="{{ route('cetak.kartu', Auth::user()->id) }}" target="_blank"
                                        class="btn btn-primary">
                                        <i class="ti ti-id-badge-2 me-1"></i>Cetak Kartu
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/ Header -->

        <!-- Navbar pills -->
        {{-- <div class="row">
            <div class="col-md-12">
                <ul class="nav nav-pills flex-column flex-sm-row mb-4">
                    <li class="nav-item">
                        <a class="nav-link active" href="javascript:void(0);"><i class="ti-xs ti ti-user-check me-1"></i>
                            Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages-profile-projects.html"><i class="ti ti-lock me-1 ti-xs"></i>
                            Account</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages-profile-teams.html"><i class="ti-xs ti ti-users me-1"></i> Teams</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages-profile-projects.html"><i class="ti-xs ti ti-layout-grid me-1"></i>
                            Projects</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages-profile-connections.html"><i class="ti-xs ti ti-link me-1"></i>
                            Connections</a>
                    </li>
                </ul>
            </div>
        </div> --}}
        <!--/ Navbar pills -->

        <!-- User Profile Content -->
        <div class="row">
            <div class="col-xl-12 col-lg-5 col-md-5">
                <!-- About User -->
                <div class="card mb-4">
                    <div class="card-body">
                        <small class="card-text text-uppercase">Data Pribadi</small>
                        <ul class="list-unstyled mb-4 mt-3">
                            <li class="d-flex align-items-center mb-3">
                                <i class="ti ti-user text-heading"></i><span class="fw-medium mx-2 text-heading"><strong>
                                        Fullname:</strong></span> <span>{{ $user->fullname }}</span>
                            </li>
                            <li class="d-flex align-items-center mb-3">
                                <i class="ti ti-user text-heading"></i><span
                                    class="fw-medium mx-2 text-heading"><strong>Nickname:</strong></span>
                                <span>{{ $user->nickname }}</span>
                            </li>
                            <li class="d-flex align-items-center mb-3">
                                <i
                                    class="ti {{ $user->status == 'Active' ? 'ti-check text-success' : 'ti-x text-danger' }}"></i>
                                <span class="fw-medium mx-2 text-heading"><strong>Status:</strong></span>
                                <span>
                                    @if ($user->status == 'Active')
                                        <span class="badge bg-success"> Active</span>
                                    @else
                                        <span class="badge bg-danger">Not Active</span>
                                    @endif
                                </span>
                            </li>
                            <li class="d-flex align-items-center mb-3">
                                <i class="ti ti-crown text-heading"></i><span
                                    class="fw-medium mx-2 text-heading"><strong>Hak Akses:</strong></span> <span>
                                    @foreach (Auth::user()->getRoleNames() as $role)
                                        {{ $role }}
                                        @if (!$loop->last)
                                            |
                                        @endif
                                    @endforeach
                                </span>
                            </li>
                            <li class="d-flex align-items-center mb-3">
                                <i
                                    class="ti {{ $user->gender == 'Pria' ? 'ti-gender-male text-success' : 'ti-gender-male text-danger' }}"></i>
                                <span class="fw-medium mx-2 text-heading"><strong>Jenis Kelamin:</strong></span>
                                <span>
                                    {{ $user->gender }}
                                </span>
                            </li>
                            <li class="d-flex align-items-center mb-3">
                                <i class="ti ti-map-pin text-heading"></i><span
                                    class="fw-medium mx-2 text-heading"><strong>Alamat:</strong></span>
                                <span>{{ $user->address }}
                            </li>
                        </ul>
                        <small class="card-text text-uppercase">Kontak</small>
                        <ul class="list-unstyled mb-4 mt-3">
                            <li class="d-flex align-items-center mb-3">
                                <i class="ti ti-phone-call"></i><span
                                    class="fw-medium mx-2 text-heading"></strong>Kontak:</strong></span>
                                <span>{{ $user->phone_number }}</span>
                            </li>

                            <li class="d-flex align-items-center mb-3">
                                <i class="ti ti-mail"></i><span
                                    class="fw-medium mx-2 text-heading"><strong>Email:</strong></span>
                                <span>{{ $user->email }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
            {{-- <div class="col-xl-8 col-lg-7 col-md-7">
                <!-- Activity Timeline -->
                <div class="card card-action mb-4">
                    <div class="card-header align-items-center">
                        <h5 class="card-action-title mb-0">Activity Timeline</h5>
                        <div class="card-action-element">
                            <div class="dropdown">
                                <button type="button" class="btn dropdown-toggle hide-arrow p-0" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <i class="ti ti-dots-vertical text-muted"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="javascript:void(0);">Share timeline</a></li>
                                    <li><a class="dropdown-item" href="javascript:void(0);">Suggest edits</a></li>
                                    <li>
                                        <hr class="dropdown-divider" />
                                    </li>
                                    <li><a class="dropdown-item" href="javascript:void(0);">Report bug</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pb-0">
                        <ul class="timeline ms-1 mb-0">
                            <li class="timeline-item timeline-item-transparent">
                                <span class="timeline-point timeline-point-primary"></span>
                                <div class="timeline-event">
                                    <div class="timeline-header">
                                        <h6 class="mb-0">Client Meeting</h6>
                                        <small class="text-muted">Today</small>
                                    </div>
                                    <p class="mb-2">Project meeting with john @10:15am</p>
                                    <div class="d-flex flex-wrap">
                                        <div class="avatar me-2">
                                            <img src="../../assets/img/avatars/3.png" alt="Avatar"
                                                class="rounded-circle" />
                                        </div>
                                        <div class="ms-1">
                                            <h6 class="mb-0">Lester McCarthy (Client)</h6>
                                            <span>CEO of Infibeam</span>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="timeline-item timeline-item-transparent">
                                <span class="timeline-point timeline-point-success"></span>
                                <div class="timeline-event">
                                    <div class="timeline-header">
                                        <h6 class="mb-0">Create a new project for client</h6>
                                        <small class="text-muted">2 Day Ago</small>
                                    </div>
                                    <p class="mb-0">Add files to new design folder</p>
                                </div>
                            </li>
                            <li class="timeline-item timeline-item-transparent">
                                <span class="timeline-point timeline-point-danger"></span>
                                <div class="timeline-event">
                                    <div class="timeline-header">
                                        <h6 class="mb-0">Shared 2 New Project Files</h6>
                                        <small class="text-muted">6 Day Ago</small>
                                    </div>
                                    <p class="mb-2">
                                        Sent by Mollie Dixon
                                        <img src="../../assets/img/avatars/4.png" class="rounded-circle me-3"
                                            alt="avatar" height="24" width="24" />
                                    </p>
                                    <div class="d-flex flex-wrap gap-2 pt-1">
                                        <a href="javascript:void(0)" class="me-3">
                                            <img src="../../assets/img/icons/misc/doc.png" alt="Document image"
                                                width="15" class="me-2" />
                                            <span class="fw-medium text-heading">App Guidelines</span>
                                        </a>
                                        <a href="javascript:void(0)">
                                            <img src="../../assets/img/icons/misc/xls.png" alt="Excel image"
                                                width="15" class="me-2" />
                                            <span class="fw-medium text-heading">Testing Results</span>
                                        </a>
                                    </div>
                                </div>
                            </li>
                            <li class="timeline-item timeline-item-transparent border-transparent">
                                <span class="timeline-point timeline-point-info"></span>
                                <div class="timeline-event">
                                    <div class="timeline-header">
                                        <h6 class="mb-0">Project status updated</h6>
                                        <small class="text-muted">10 Day Ago</small>
                                    </div>
                                    <p class="mb-0">Woocommerce iOS App Completed</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <!--/ Activity Timeline -->
            </div> --}}
        </div>
        <!--/ User Profile Content -->
    </div>
    <!-- / Content -->
@endsection
@push('style')
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/css/pages/page-profile.css" />
@endpush
@push('scripts')
    <script src="{{ asset('') }}assets/js/pages-profile.js"></script>
@endpush
