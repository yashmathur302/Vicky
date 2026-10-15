@php
    // TEMP SAMPLE DATA (design phase). Functionality phase me DB se aayega.
    $stories = [
        ['name' => 'Chris',  'seen' => false],
        ['name' => 'Robert',  'seen' => false],
        ['name' => 'Martin', 'seen' => false],
        ['name' => 'Bruce', 'seen' => true],
        ['name' => 'Tom',  'seen' => true],
        ['name' => 'Kat',  'seen' => true],
        ['name' => 'Ryan',  'seen' => true],
        ['name' => 'Alen', 'seen' => true],
    ];
    $storyAvatar = asset('frontend/images/default-avatar.svg');
@endphp

<section class="tc-card tc-stories" aria-label="Stories">
    <button type="button" class="tc-stories__arrow tc-stories__arrow--prev" id="storiesPrev" aria-label="Previous stories" hidden>
        <i class="fas fa-chevron-left"></i>
    </button>

    <ul class="tc-stories__track" id="storiesTrack">
        <li>
            <button type="button" class="tc-story tc-story--add">
                <span class="tc-story__ring"><i class="fas fa-camera"></i></span>
                <span class="tc-story__name">Create</span>
            </button>
        </li>
        @foreach ($stories as $story)
            <li>
                <a href="#" class="tc-story">
                    <span class="tc-story__ring {{ $story['seen'] ? 'is-seen' : '' }}">
                        <img src="{{ $storyAvatar }}" alt="">
                    </span>
                    <span class="tc-story__name">{{ $story['name'] }}</span>
                </a>
            </li>
        @endforeach
    </ul>

    <button type="button" class="tc-stories__arrow tc-stories__arrow--next" id="storiesNext" aria-label="Next stories" hidden>
        <i class="fas fa-chevron-right"></i>
    </button>
</section>