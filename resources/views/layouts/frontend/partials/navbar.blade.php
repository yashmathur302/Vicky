@php
    $authUser   = auth()->user();
    $authName   = $authUser->name ?? 'User';
    $authHandle = '@' . ($authUser->username ?? 'username');
    $avatar     = asset('frontend/images/default-avatar.svg');

    // TEMP SAMPLE DATA (design phase). Functionality phase me DB se aayega.
    $navNotifications = [
        ['name' => 'Chris',  'text' => 'shared your post in a group.',               'time' => '2 hours ago', 'unread' => true],
        ['name' => 'Robert', 'text' => 'replied to your comment.',                   'time' => '5 hours ago', 'unread' => true],
        ['name' => 'Kat',    'text' => 'invited you to the Chemistry Study Group.',  'time' => 'Yesterday',   'unread' => false],
        ['name' => 'Tom',    'text' => 'is hosting a webinar tomorrow.',             'time' => '2 days ago',  'unread' => false],
    ];
    $navMessages = [
        ['name' => 'Bruce', 'text' => 'Did you finish the assignment?',   'time' => '10:15 AM', 'unread' => true],
        ['name' => 'Ryan',  'text' => 'Sharing the lecture notes now.',   'time' => '08:30 AM', 'unread' => false],
        ['name' => 'Alen',  'text' => 'Let\'s revise together tonight.',  'time' => 'Mon',      'unread' => true],
    ];
    $unreadNotifications = collect($navNotifications)->where('unread', true)->count();
@endphp

<header class="tc-header">
    <div class="tc-header__inner">

        {{-- Left: sidebar toggle (below 1200px) + brand --}}
        <div class="tc-header__start">
            <button type="button" class="tc-icon-btn tc-icon-btn--plain d-xl-none"
                    data-bs-toggle="offcanvas" data-bs-target="#siteSidebar"
                    aria-controls="siteSidebar" aria-label="Open menu">
                <i class="fas fa-bars"></i>
            </button>
            <a href="{{ route('home') }}" class="tc-brand">{{ config('app.name', 'TeachYou') }}</a>
        </div>

        {{-- Center: search (hidden on mobile) --}}
        <div class="tc-search d-none d-md-block">
            <i class="fas fa-search"></i>
            <input type="search" placeholder="Search people, groups, events..." aria-label="Search" autocomplete="off">
        </div>

        {{-- Right: create / notifications / messages / profile --}}
        <div class="tc-header__end">

            {{-- Create --}}
            <div class="dropdown">
                <button type="button" class="tc-icon-btn" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Create">
                    <i class="fas fa-plus"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end tc-menu tc-menu--create">
                    <div class="tc-menu__head">Create</div>
                    <button type="button" class="dropdown-item tc-create-item" data-create="group">
                        <span class="tc-tile tc-tile--blue"><i class="fas fa-users"></i></span>
                        <span><strong>Group</strong><small>Meet people with similar interests.</small></span>
                    </button>
                    <button type="button" class="dropdown-item tc-create-item" data-create="page">
                        <span class="tc-tile tc-tile--purple"><i class="fas fa-flag"></i></span>
                        <span><strong>Page</strong><small>Build a presence for your class or club.</small></span>
                    </button>
                    <button type="button" class="dropdown-item tc-create-item" data-create="event">
                        <span class="tc-tile tc-tile--rose"><i class="fas fa-calendar-alt"></i></span>
                        <span><strong>Event</strong><small>Plan webinars, workshops and meetups.</small></span>
                    </button>
                    <button type="button" class="dropdown-item tc-create-item" data-create="blog">
                        <span class="tc-tile tc-tile--teal"><i class="fas fa-newspaper"></i></span>
                        <span><strong>Blog</strong><small>Share articles and knowledge.</small></span>
                    </button>
                </div>
            </div>

            {{-- Notifications --}}
            <div class="dropdown">
                <button type="button" class="tc-icon-btn" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                    <i class="fas fa-bell"></i>
                    @if ($unreadNotifications > 0)
                        <span class="tc-badge">{{ $unreadNotifications }}</span>
                    @endif
                </button>
                <div class="dropdown-menu dropdown-menu-end tc-menu tc-menu--list">
                    <div class="tc-menu__head">Notifications</div>
                    <div class="tc-menu__list">
                        @foreach ($navNotifications as $note)
                            <a href="#" class="dropdown-item {{ $note['unread'] ? 'tc-item--unread' : '' }}">
                                <img src="{{ $avatar }}" alt="" class="tc-avatar tc-avatar--md">
                                <span class="tc-item__body">
                                    <strong>{{ $note['name'] }}</strong> {{ $note['text'] }}
                                    <span class="tc-item__time">{{ $note['time'] }}</span>
                                </span>
                                @if ($note['unread'])
                                    <span class="tc-dot" aria-label="Unread"></span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                    <a href="#" class="tc-menu__foot">View all notifications</a>
                </div>
            </div>

            {{-- Messages --}}
            <div class="dropdown">
                <button type="button" class="tc-icon-btn" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Messages">
                    <i class="fas fa-comment-dots"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end tc-menu tc-menu--list">
                    <div class="tc-menu__head">Chats</div>
                    <div class="tc-menu__list">
                        @foreach ($navMessages as $msg)
                            <a href="#" class="dropdown-item">
                                <img src="{{ $avatar }}" alt="" class="tc-avatar tc-avatar--md">
                                <span class="tc-item__body">
                                    <strong>{{ $msg['name'] }}</strong>
                                    <span class="tc-item__text">{{ $msg['text'] }}</span>
                                </span>
                                <span class="tc-item__time">{{ $msg['time'] }}</span>
                                @if ($msg['unread'])
                                    <span class="tc-dot" aria-label="Unread"></span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                    <a href="#" class="tc-menu__foot">See all messages</a>
                </div>
            </div>

            {{-- Profile --}}
            <div class="dropdown">
                <button type="button" class="tc-avatar-btn" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                        aria-expanded="false" aria-label="Account menu">
                    <img src="{{ $avatar }}" alt="" class="tc-avatar tc-avatar--sm">
                </button>
                <div class="dropdown-menu dropdown-menu-end tc-menu tc-menu--profile">
                    <a href="#" class="tc-profile-link">
                        <img src="{{ $avatar }}" alt="" class="tc-avatar tc-avatar--md">
                        <span>
                            <strong>{{ $authName }}</strong>
                            <small>{{ $authHandle }}</small>
                        </span>
                    </a>
                    <hr class="dropdown-divider">
                    <a href="#" class="dropdown-item"><i class="fas fa-cog"></i> My Account</a>
                    <button type="button" class="dropdown-item tc-theme-toggle" id="themeToggle" role="switch" aria-checked="false">
                        <i class="fas fa-moon"></i> Night mode
                        <span class="tc-switch" aria-hidden="true"></span>
                    </button>
                    <hr class="dropdown-divider">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="fas fa-sign-out-alt"></i> Log Out
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</header>