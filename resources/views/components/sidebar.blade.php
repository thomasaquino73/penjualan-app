<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

    <div class="app-brand demo">
        <a href="{{ route('dashboard') }}" class="app-brand-link">
            <span><img src="{{ $favicon }}" alt="favicon" width="30px"></span>
            <span class="app-brand-text demo menu-text fw-bold">{{ $aplikasi }}</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="ti menu-toggle-icon d-none d-xl-block ti-sm align-middle"></i>
            <i class="ti ti-x d-block d-xl-none ti-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">

        @php $user = auth()->user(); @endphp

        @foreach ($links as $item)
            @php
                $hasAccess = !isset($item['permissions']) || ($user && $user->canAny($item['permissions']));
            @endphp

            @continue(!$hasAccess)

            {{-- SECTION --}}
            @if ($item['type'] === 'section')
                <li class="menu-header small text-uppercase">
                    <span class="menu-header-text">{{ $item['label'] }}</span>
                </li>

                {{-- DROPDOWN --}}
            @elseif ($item['type'] === 'dropdown')
                @php
                    $isActive = collect($item['children'])->contains(function ($child) use ($user) {
                        $childAccess = !isset($child['permissions']) || ($user && $user->canAny($child['permissions']));

                        $pattern = $child['pattern'] ?? $child['route'] . '*';

                        return $childAccess && request()->routeIs($pattern);
                    });
                @endphp

                <li class="menu-item {{ $isActive ? 'active open' : '' }}">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon ti {{ $item['icon'] }}"></i>
                        <div>{{ $item['name'] }}</div>
                    </a>

                    <ul class="menu-sub">
                        @foreach ($item['children'] as $child)
                            @php
                                $childAccess =
                                    !isset($child['permissions']) || ($user && $user->canAny($child['permissions']));

                                $pattern = $child['pattern'] ?? $child['route'] . '*';

                                $childActive = request()->routeIs($pattern);
                            @endphp

                            @continue(!$childAccess)

                            <li class="menu-item {{ $childActive ? 'active' : '' }}">
                                <a href="{{ route($child['route']) }}" class="menu-link">
                                    <div>{{ $child['name'] }}</div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>

                {{-- SINGLE --}}
            @else
                @php
                    $pattern = $item['pattern'] ?? $item['route'] . '*';
                    $isActive = request()->routeIs($pattern);
                @endphp

                <li class="menu-item {{ $isActive ? 'active' : '' }}">
                    <a href="{{ route($item['route']) }}" class="menu-link">
                        <i class="menu-icon ti {{ $item['icon'] }}"></i>
                        <div>{{ $item['name'] }}</div>
                    </a>
                </li>
            @endif
        @endforeach
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">NEED HELP?</span>
        </li>
        <li class="menu-item">
            <a href="https://wa.me/081299097474" target="_blank" class="menu-link">
                <i class="menu-icon tf-icons ti ti-brand-whatsapp"></i>
                <div>Support</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="https://www.thomasaquino.my.id/" target="_blank" class="menu-link">
                <i class="menu-icon tf-icons ti ti-news"></i>
                <div>Documentation</div>
            </a>
        </li>
        <hr class="mb-3" />

    </ul>

</aside>
