<ul class="nav nav-pills flex-column flex-md-row mb-4">

    @if (auth()->user()->can('general-browse'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('general-setting.*') ? 'active' : '' }}"
                href="{{ route('general-setting.index') }}">
                <i class="ti ti-database ti-xs me-1"></i>General Setting
            </a>
        </li>
    @endif
    @if (auth()->user()->can('company-browse'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('company.*') ? 'active' : '' }}" href="{{ route('company.info') }}">
                <i class="ti ti-building ti-xs me-1"></i>Company Information
            </a>
        </li>
    @endif
</ul>
