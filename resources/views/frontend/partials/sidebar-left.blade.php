@php
    // href '#' wale links ke routes functionality phase me aayenge
    $sidebarNav = [
        ['label' => 'Feed',     'icon' => 'fa-house',         'tone' => 'blue',   'href' => route('home'), 'active' => request()->routeIs('home')],
        ['label' => 'Messages', 'icon' => 'fa-comments',      'tone' => 'green',  'href' => '#', 'active' => false],
        ['label' => 'Friends',  'icon' => 'fa-user-plus',     'tone' => 'rose',   'href' => '#', 'active' => false],
        ['label' => 'Events',   'icon' => 'fa-calendar-days', 'tone' => 'orange', 'href' => '#', 'active' => false],
        ['label' => 'Pages',    'icon' => 'fa-flag',          'tone' => 'purple', 'href' => '#', 'active' => false],
        ['label' => 'Groups',   'icon' => 'fa-users',         'tone' => 'sky',    'href' => '#', 'active' => false],
        ['label' => 'Blog',     'icon' => 'fa-newspaper',     'tone' => 'indigo', 'href' => '#', 'active' => false],
    ];
@endphp

<aside class="offcanvas offcanvas-start tc-sidebar" tabindex="-1" id="siteSidebar" aria-label="Main menu">
    <div class="offcanvas-header d-xl-none">
        <span class="tc-brand">{{ config('app.name', 'TeachYou') }}</span>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body">
        <nav class="tc-nav" aria-label="Primary">
            @foreach ($sidebarNav as $item)
                <a href="{{ $item['href'] }}"
                   class="tc-nav__link {{ $item['active'] ? 'is-active' : '' }}"
                   @if ($item['active']) aria-current="page" @endif>
                    <i class="fas {{ $item['icon'] }} tc-ic--{{ $item['tone'] }}"></i>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="tc-nav__section">More</div>
        <nav class="tc-nav tc-nav--small" aria-label="Secondary">
            <a href="#" class="tc-nav__link"><i class="fas fa-gear"></i><span>Settings</span></a>
            <a href="#" class="tc-nav__link"><i class="fas fa-circle-question"></i><span>Support</span></a>
        </nav>

        <div class="tc-sidebar__foot">
            <a href="#">About</a>
            <a href="#">Privacy</a>
            <a href="#">Terms</a>
            <a href="#">Contact</a>
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'TeachYou') }}</p>
        </div>
    </div>
</aside>