@php
    $homeUser   = auth()->user();
    $media = $post['media'];
    $shown = array_slice($media, 0, 4);
    $extra = count($media) - 4;
    $audienceIcon = ['public' => 'fa-earth-asia', 'friends' => 'fa-user-group', 'private' => 'fa-lock'][$post['audience']] ?? 'fa-earth-asia';
    $profilePicture = $homeUser->avatar_url;
@endphp

<article class="tc-card tc-post" id="post-{{ $post['id'] }}" data-post-id="{{ $post['id'] }}">

    <header class="tc-post__head">
        <img src="{{ $profilePicture }}" alt="" class="tc-avatar tc-avatar--md" loading="lazy">
        <div class="tc-post__who">
            <a href="#" class="tc-post__name">{{ $post['author'] }}</a>
            @if (!empty($post['feeling']))
                <span class="tc-post__tag">is {{ $post['feeling'] }}</span>
            @endif
            <div class="tc-post__meta">
                <span>{{ $post['time'] }}</span>
                <i class="fas {{ $audienceIcon }}" aria-label="{{ ucfirst($post['audience']) }}"></i>
                @if (!empty($post['location']))
                    <span><i class="fas fa-location-dot"></i> {{ $post['location'] }}</span>
                @endif
            </div>
        </div>
        <div class="dropdown">
            <button type="button" class="tc-icon-btn tc-icon-btn--plain" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Post options">
                <i class="fas fa-ellipsis"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end tc-menu tc-menu--post">
                <button type="button" class="dropdown-item" data-action="edit"><i class="far fa-edit"></i> Edit Post</button>
                <button type="button" class="dropdown-item" data-action="save"><i class="far fa-bookmark"></i> Save post</button>
                <button type="button" class="dropdown-item" data-action="report"><i class="far fa-flag"></i> Report post</button>
            </div>
        </div>
    </header>

    @if (!empty($post['text']))
        <div class="tc-post__body">
            <div class="tc-post__text">{{ $post['text'] }}</div>
            <button type="button" class="tc-post__more" data-action="more" hidden>See more</button>
        </div>
    @endif

    @if (count($media))
        <div class="tc-media tc-media--{{ count($shown) }}">
            @foreach ($shown as $item)
                <div class="tc-media__item">
                    @if ($item['type'] === 'video')
                        <video src="{{ $item['url'] }}" controls preload="metadata"></video>
                    @else
                        <img src="{{ $item['url'] }}" alt="" loading="lazy">
                    @endif
                    @if ($loop->last && $extra > 0)
                        <span class="tc-media__more">+{{ $extra }}</span>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if (!empty($post['document']))
        <a href="{{ $post['document']['url'] }}" target="_blank" class="tc-doc">
            <span class="tc-doc__icon"><i class="fas fa-file-pdf text-danger"></i></span>
            <span class="tc-doc__info">
                <strong>{{ $post['document']['name'] }}</strong>
                <small>PDF &middot; Click to view</small>
            </span>
            {{-- <i class="fas fa-download tc-doc__dl"></i> --}}
        </a>
    @endif

    <div class="tc-post__stats">
        <span><i class="fas fa-heart"></i> <span class="js-like-count" data-count="{{ $post['likes'] }}">{{ $post['likes'] }}</span></span>
        <span>{{ $post['comments_count'] }} comments &middot; {{ $post['shares'] }} shares</span>
    </div>

    <div class="tc-post__actions">
        <button type="button" class="tc-act {{ $post['liked'] ? 'is-liked' : '' }}" data-action="like" aria-pressed="{{ $post['liked'] ? 'true' : 'false' }}">
            <i class="{{ $post['liked'] ? 'fas' : 'far' }} fa-heart"></i> Like
        </button>
        <button type="button" class="tc-act" data-action="comment"><i class="far fa-comment"></i> Comment</button>
        <button type="button" class="tc-act" data-action="share"><i class="far fa-share-from-square"></i> Share</button>
    </div>

    <div class="tc-post__comments" hidden>
        @foreach ($post['comments'] as $comment)
            <div class="tc-comment">
                <img src="{{ $comment['avatar'] }}" alt="" class="tc-avatar tc-avatar--xs" loading="lazy">
                <div>
                    <div class="tc-comment__bubble">
                        <strong>{{ $comment['author'] }}</strong>
                        <p>{{ $comment['text'] }}</p>
                    </div>
                    <small class="tc-comment__time">{{ $comment['time'] }}</small>
                </div>
            </div>
        @endforeach

        <form class="tc-comment-form" data-comment-form>
            <input type="text" name="content" maxlength="1000" placeholder="Write a comment..." aria-label="Write a comment" autocomplete="off">
            <button type="submit" aria-label="Send comment"><i class="fas fa-paper-plane"></i></button>
        </form>
    </div>
</article>