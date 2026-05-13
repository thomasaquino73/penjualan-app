<ul class="nav nav-pills flex-column flex-md-row mb-4">

    @if (auth()->user()->can('application_system-browse'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('pengaturan.sistem') ? 'active' : '' }}"
                href="{{ url('pengaturan-sistem') }}">
                <i class="ti ti-database ti-xs me-1"></i>System
            </a>
        </li>
    @endif
    @if (auth()->user()->can('login_background-browse'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('pengaturan.background.*') ? 'active' : '' }}"
                href="{{ route('pengaturan.background.index') }}">
                <i class="ti ti-layout-board ti-xs me-1"></i>Login Background
            </a>
        </li>
    @endif

</ul>
