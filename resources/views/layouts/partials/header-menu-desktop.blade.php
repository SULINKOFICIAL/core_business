@php($menuItems = $menuItems ?? header_menu_items())

@foreach ($menuItems as $item)
    @php($isActive = !empty($item['active_routes']) ? Request::routeIs(...$item['active_routes']) : false)
    @if (($item['type'] ?? 'link') === 'link')
        <a href="{{ route($item['route']) }}" class="fw-bold text-hover-warning text-uppercase mx-8 fs-7 {{ $isActive ? 'text-warning' : 'text-white' }}">
            {{ $item['label'] }}
        </a>
    @else
        <div class="menu menu-column menu-gray-600 menu-active-primary menu-hover-light-primary menu-here-light-primary menu-show-light-primary fw-semibold" data-kt-menu="true">
            <div class="menu-item" data-kt-menu-trigger="hover" data-kt-menu-placement="bottom-start">
                <a href="#" class="menu-link">
                    <span class="menu-title text-uppercase fs-7 {{ $isActive ? 'text-warning' : 'text-white' }}">
                        {{ $item['label'] }}
                        <i class="fa-solid fa-angle-down fs-8 ms-2 {{ $isActive ? 'text-warning' : 'text-white' }}"></i>
                    </span>
                </a>
                <div class="menu-sub menu-sub-dropdown w-200px py-2">
                    @foreach ($item['children'] ?? [] as $child)
                        <div class="menu-item">
                            <a href="{{ route($child['route']) }}" class="menu-link">
                                @if (!empty($child['icon']))
                                    <span class="menu-icon">
                                        <i class="{{ $child['icon']['class'] }}">
                                            @for ($i = 1; $i <= ($child['icon']['paths'] ?? 0); $i++)
                                                <span class="path{{ $i }}"></span>
                                            @endfor
                                        </i>
                                    </span>
                                @endif
                                <span class="menu-title">{{ $child['label'] }}</span>
                            </a>
                        </div>
                        @if (! $loop->last)
                            <div class="separator separator-dashed"></div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    @endif
@endforeach
