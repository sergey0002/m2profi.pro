@php
    $menuService = app(\App\Services\MenuService::class);
    $menuItems = $menuService->getMenuItems();
@endphp

<div class="overlay-page"></div>
<div class="sidenav">
    <div class="sidenav__close"></div>
    <div class="sidenav-wrap">
        
        <a href="{{ config('domains.public_url') }}" class="sidenav__backlink">Вернуться на сайт</a>
        
        <div class="sidenav-nav">
            <a href="{{ config('domains.public_url') }}" class="sidenav__backlink sidenav__backlink_mob">Вернуться на сайт</a>
            <div style="font-size:7px;">{{ $_SERVER['SERVER_ADDR'] ?? 'N/A' }}</div>
            
            <ul class="sidenav-menu">
                @foreach($menuItems as $item)
                    @if(isset($item['hidden']) && $item['hidden'])
                        @continue
                    @endif
                    
                    @if(isset($item['submenu']))
                        {{-- Dropdown Menu --}}
                        <li class="sidenav-dropmenu">
                            <span></span>
                            <a href="#" class="{{ $item['active'] ?? false ? 'active' : '' }}">
                                <i><img src="{{ theme_asset('images/' . $item['icon']) }}" alt=""></i>
                                {!! $item['title'] !!}
                            </a>
                            <ul class="sidenav-submenu {{ $item['active'] ?? false ? 'active' : '' }}" 
                                style="{{ $item['active'] ?? false ? 'display: block;' : '' }}">
                                @foreach($item['submenu'] as $subitem)
                                    @if(isset($subitem['hidden']) && $subitem['hidden'])
                                        @continue
                                    @endif
                                    <li><a href="{{ $subitem['url'] }}">{{ $subitem['title'] }}</a></li>
                                @endforeach
                            </ul>
                        </li>
                    @else
                        {{-- Static Link --}}
                        <li>
                            <a href="{{ $item['url'] }}" class="{{ $item['active'] ?? false ? 'active' : '' }}">
                                <i><img src="{{ theme_asset('images/' . $item['icon']) }}" alt=""></i>
                                {!! $item['title'] !!}
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>
            
        </div>
    </div>
</div>

<style>
.mmenu li{display:inline; padding:10px;}
.iframe_r{position:static; z-index:100;}
section{min-height:100vh;}
</style>
