@php
    $chatAvatar = asset('frontend/images/default-avatar.svg');

    // TEMP SAMPLE DATA (design phase)
    $chatFriends = [
        ['name' => 'Chris',  'online' => true],
        ['name' => 'Robert', 'online' => true],
        ['name' => 'Martin', 'online' => false],
        ['name' => 'Bruce',  'online' => true],
        ['name' => 'Tom',    'online' => false],
        ['name' => 'Kat',    'online' => true],
        ['name' => 'Ryan',   'online' => false],
        ['name' => 'Alen',   'online' => false],
    ];
    $chatGroups = ['Chemistry Study Group', 'Class 10 Maths', 'Physics Lab Team', 'Coding Club'];
@endphp

<div class="tc-chat dropup">
    <button type="button" class="tc-chat__btn" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" aria-label="Open chat">
        <i class="fas fa-comments"></i><span class="d-none d-sm-inline">Chat</span>
    </button>

    <div class="dropdown-menu dropdown-menu-end tc-menu tc-chat__panel">
        <div class="tc-menu__head">Chats</div>

        <ul class="nav nav-pills tc-chat__tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link active" data-bs-toggle="pill" data-bs-target="#chatFriends" role="tab" aria-selected="true">Friends</button>
            </li>
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link" data-bs-toggle="pill" data-bs-target="#chatGroups" role="tab" aria-selected="false">Groups</button>
            </li>
        </ul>

        <div class="tab-content tc-menu__list tc-chat__list">
            <div class="tab-pane fade show active" id="chatFriends" role="tabpanel">
                @foreach ($chatFriends as $friend)
                    <a href="#" class="tc-chat__row">
                        <span class="tc-av-wrap">
                            <img src="{{ $chatAvatar }}" alt="" class="tc-avatar tc-avatar--sm">
                            @if ($friend['online'])<span class="tc-online" aria-label="Online"></span>@endif
                        </span>
                        <span>{{ $friend['name'] }}</span>
                    </a>
                @endforeach
            </div>
            <div class="tab-pane fade" id="chatGroups" role="tabpanel">
                @foreach ($chatGroups as $group)
                    <a href="#" class="tc-chat__row">
                        <img src="{{ $chatAvatar }}" alt="" class="tc-avatar tc-avatar--sm">
                        <span>{{ $group }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>