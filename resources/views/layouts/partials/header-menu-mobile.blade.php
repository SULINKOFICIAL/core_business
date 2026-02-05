@php($menuItems = $menuItems ?? header_menu_items())

<div class="menu menu-column fw-semibold fs-6 p-6" data-kt-menu="true">
    @foreach ($menuItems as $item)
        @if (($item['type'] ?? 'link') === 'link')
            <div class="menu-item">
                <a href="{{ route($item['route']) }}" class="menu-link">
                    <span class="menu-title">{{ $item['label'] }}</span>
                </a>
            </div>
        @else
            <div class="menu-item" data-kt-menu-trigger="click">
                <span class="menu-link">
                    <span class="menu-title">{{ $item['label'] }}</span>
                    <span class="menu-arrow"></span>
                </span>
                <div class="menu-sub menu-sub-accordion">
                    @foreach ($item['children'] ?? [] as $child)
                        <div class="menu-item">
                            <a href="{{ route($child['route']) }}" class="menu-link">
                                <span class="menu-title">{{ $child['label'] }}</span>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach
</div>
