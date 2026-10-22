@php
    $sideAvatar = asset('frontend/images/default-avatar.svg');

    // TEMP SAMPLE DATA (design phase)
    $upcomingEvents = [
        ['day' => '18', 'mon' => 'OCT', 'title' => 'Physics Lab Webinar',   'where' => 'Online',          'icon' => 'fa-video'],
        ['day' => '22', 'mon' => 'OCT', 'title' => 'Inter-College Quiz',    'where' => 'Main Auditorium', 'icon' => 'fa-location-dot'],
    ];
    $trends = [
        ['tag' => 'Board Exam Prep',  'posts' => '1,248 posts'],
        ['tag' => 'Python for Beginners', 'posts' => '864 posts'],
        ['tag' => 'Scholarships 2026', 'posts' => '512 posts'],
        ['tag' => 'Study Tips',       'posts' => '307 posts'],
    ];
    $onlineNow = ['Chris', 'Robert', 'Martin', 'Bruce', 'Tom', 'Kat'];
@endphp

<aside class="tc-aside d-none d-lg-block" aria-label="Suggestions">

    {{-- People you may know (DB se) --}}
    <section class="tc-card tc-widget">
        <div class="tc-widget__head">
            <h2>People you may know</h2>
            <a href="#">See all</a>
        </div>

        @forelse ($suggestions as $person)
            <div class="tc-person">
                {{-- <img src="{{ $sideAvatar }}" alt="" class="tc-avatar tc-avatar--md" loading="lazy"> --}}
                <img src="{{ $person->avatar_url }}" alt="" class="tc-avatar tc-avatar--md" loading="lazy">
                <div class="tc-person__info">
                    <strong>{{ $person->name }}</strong>
                    <small>{{ '@' . $person->username }}</small>
                </div>
                <button type="button" class="tc-follow" data-follow="{{ $person->id }}">Follow</button>
            </div>
        @empty
            <p class="tc-widget__empty">No suggestions right now.</p>
        @endforelse
    </section>

    {{-- Upcoming events --}}
    {{-- <section class="tc-card tc-widget">
        <div class="tc-widget__head">
            <h2>Upcoming events</h2>
            <a href="#">See all</a>
        </div>
        @foreach ($upcomingEvents as $event)
            <a href="#" class="tc-event">
                <span class="tc-event__date"><strong>{{ $event['day'] }}</strong><small>{{ $event['mon'] }}</small></span>
                <span class="tc-event__info">
                    <strong>{{ $event['title'] }}</strong>
                    <small><i class="fas {{ $event['icon'] }}"></i> {{ $event['where'] }}</small>
                </span>
            </a>
        @endforeach
    </section> --}}

    {{-- Online now (design only) --}}
    <section class="tc-card tc-widget">
        <div class="tc-widget__head"><h2>Online now</h2></div>
        <div class="tc-online-grid">
            @foreach ($onlineNow as $name)
                <a href="#" class="tc-av-wrap" title="{{ $name }}">
                    <img src="{{ $sideAvatar }}" alt="{{ $name }}" class="tc-avatar tc-avatar--md" loading="lazy">
                    <span class="tc-online" aria-label="Online"></span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Trending --}}
    <section class="tc-card tc-widget">
        <div class="tc-widget__head"><h2>Trending topics</h2></div>
        @foreach ($trends as $trend)
            <a href="#" class="tc-trend">
                <i class="fas fa-hashtag"></i>
                <span><strong>{{ $trend['tag'] }}</strong><small>{{ $trend['posts'] }}</small></span>
            </a>
        @endforeach
    </section>
</aside>