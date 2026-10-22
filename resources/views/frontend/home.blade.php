@extends('layouts.frontend.main')

@section('title', 'Feed | ' . config('app.name', 'TeachYou'))

@php
    $homeUser   = auth()->user();
    $homeName   = $homeUser->name ?? 'there';
    $firstName  = strtok($homeName, ' ');
    // $homeAvatar = asset('frontend/images/default-avatar.svg');
    $homeAvatar = $homeUser->avatar_url;

    $feelings = [
        'happy'     => '😊 Happy',
        'sad'       => '😢 Sad',
        'laughing'  => '😂 Laughing',
        'loved'     => '😍 Loved',
        'excited'   => '🥳 Excited',
        'thoughtful'=> '🤔 Thoughtful',
        'motivated' => '😤 Motivated',
    ];
@endphp

@section('content')
<div class="tc-page">
    <section class="tc-feed-col" aria-label="Feed">

        @include('frontend.partials.stories')

        {{-- Composer --}}
        <div class="tc-card tc-composer">
            <div class="tc-composer__row">
                <img src="{{ $homeAvatar }}" alt="" class="tc-avatar tc-avatar--sm">
                <button type="button" class="tc-composer__input" data-bs-toggle="modal" data-bs-target="#createPostModal">
                    What do you have in mind, {{ $firstName }}?
                </button>
            </div>
            <div class="tc-composer__actions">
                <button type="button" class="tc-composer__action" data-bs-toggle="modal" data-bs-target="#createPostModal">
                    <i class="fas fa-images tc-ic--green"></i> Photo / Video
                </button>
                <button type="button" class="tc-composer__action" data-bs-toggle="modal" data-bs-target="#createPostModal">
                    <i class="fas fa-file-lines tc-ic--blue"></i> Document
                </button>
                <button type="button" class="tc-composer__action" data-bs-toggle="modal" data-bs-target="#createPostModal">
                    <i class="fas fa-face-smile tc-ic--orange"></i> Feeling
                </button>
            </div>
        </div>

        {{-- Posts: feed.js (layout.js) yahan infinite scroll se load karta hai --}}
        <div id="feedList" data-url="{{ route('feed.load') }}" data-login-url="{{ route('login') }}"></div>
        <div id="feedSkeletons" aria-hidden="true"></div>
        <div class="tc-feed-msg" id="feedMsg" role="status" hidden></div>
        <div id="feedSentinel" aria-hidden="true"></div>
    </section>

    @include('frontend.partials.sidebar-right')
</div>

{{-- Loading placeholder (JS isse clone karta hai) --}}
<template id="feedSkeleton">
    <div class="tc-card tc-post tc-skel">
        <div class="tc-skel__row">
            <span class="tc-skel__circle"></span>
            <span class="tc-skel__lines"><i></i><i></i></span>
        </div>
        <span class="tc-skel__bar"></span>
        <span class="tc-skel__bar tc-skel__bar--short"></span>
        <span class="tc-skel__block"></span>
    </div>
</template>

{{-- Create post modal --}}
<div class="modal fade tc-modal" id="createPostModal" tabindex="-1" aria-labelledby="createPostTitle" aria-hidden="true" data-bs-focus="false">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content" id="createPostForm" method="POST" action="#" enctype="multipart/form-data" novalidate>
            @csrf
            <div class="modal-header justify-content-center position-relative">
                <h2 class="modal-title fs-6 fw-semibold" id="createPostTitle">Create post</h2>
                <button type="button" class="btn-close position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <img src="{{ $homeAvatar }}" alt="" class="tc-avatar tc-avatar--md">
                    <div>
                        <strong class="d-block">{{ $homeName }}</strong>
                        <select name="privacy" class="form-select form-select-sm tc-privacy" aria-label="Who can see this post">
                            <option value="public">Everyone</option>
                            <option value="friends">Friends</option>
                            <option value="private">Only me</option>
                        </select>
                    </div>
                </div>

                <textarea id="cpContent" name="content" class="tc-textarea" maxlength="5000"
                          placeholder="What do you have in mind, {{ $firstName }}?"></textarea>

                <p class="tc-tag" id="cpFeelingTag" hidden>
                    <span id="cpFeelingText"></span>
                    <button type="button" id="cpFeelingClear" aria-label="Remove feeling">&times;</button>
                </p>
                <input type="hidden" name="feeling" id="cpFeelingInput" value="">

                <div class="tc-chips" id="cpFeelingBar" hidden>
                    @foreach ($feelings as $key => $label)
                        <button type="button" class="tc-chip" data-feeling="{{ $key }}" data-label="{{ $label }}">{{ $label }}</button>
                    @endforeach
                </div>

                <div class="tc-location" id="cpLocationRow" hidden>
                    <input type="text" id="cpLocationInput" name="location" class="form-control"
                           maxlength="120" placeholder="Where are you? City, school, venue...">
                </div>

                <div class="tc-thumbs" id="cpThumbs"></div>
                <p class="tc-form-error" id="cpError" role="alert" hidden></p>

                <input type="file" id="cpFiles" name="media[]" multiple hidden
                       accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm">

                <div class="tc-addbar">
                    <span>Add to your post</span>
                    <button type="button" class="tc-icon-btn tc-icon-btn--plain" id="cpPickMedia" aria-label="Add photo or video" title="Photo / Video">
                        <i class="fas fa-images tc-ic--green"></i>
                    </button>
                    <button type="button" class="tc-icon-btn tc-icon-btn--plain" id="cpPickDoc" aria-label="Add document" title="Document (PDF, Word, PowerPoint, Excel)">
                        <i class="fas fa-file-lines tc-ic--blue"></i>
                    </button>
                    <button type="button" class="tc-icon-btn tc-icon-btn--plain" id="cpToggleFeeling" aria-label="Add feeling" title="Feeling">
                        <i class="fas fa-face-smile tc-ic--orange"></i>
                    </button>
                    <button type="button" class="tc-icon-btn tc-icon-btn--plain" id="cpToggleLocation" aria-label="Check in" title="Check in">
                        <i class="fas fa-location-dot tc-ic--rose"></i>
                    </button>
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="tc-btn-primary w-100" id="cpSubmit" disabled>Post</button>
            </div>
        </form>
    </div>
</div>

{{-- Attachment preview (create-post.js se control hota hai) --}}
<div class="tc-lightbox" id="cpLightbox" role="dialog" aria-modal="true" aria-label="Attachment preview" hidden>
    <div class="tc-lightbox__bar">
        <span class="tc-lightbox__title" id="cpLbTitle"></span>
        <span class="tc-lightbox__count" id="cpLbCount"></span>
        <button type="button" class="tc-lightbox__btn" id="cpLbClose" aria-label="Close preview">
            <i class="fas fa-xmark"></i>
        </button>
    </div>
    <button type="button" class="tc-lightbox__btn tc-lightbox__nav tc-lightbox__nav--prev" id="cpLbPrev" aria-label="Previous">
        <i class="fas fa-chevron-left"></i>
    </button>
    <div class="tc-lightbox__stage" id="cpLbStage"></div>
    <button type="button" class="tc-lightbox__btn tc-lightbox__nav tc-lightbox__nav--next" id="cpLbNext" aria-label="Next">
        <i class="fas fa-chevron-right"></i>
    </button>
</div>

{{-- Feed Media Preview Modal --}}
{{-- <div class="modal fade tc-modal" id="feedMediaModal" tabindex="-1" aria-hidden="true" data-bs-theme="dark">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-header border-0 justify-content-end p-2 position-absolute w-100" style="z-index: 999;">
                <button type="button" class="btn-close btn-close-white fs-4 shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 text-center d-flex flex-column align-items-center justify-content-center">
                <div id="feedMediaContainer" class="w-100" style="max-height: 80vh; overflow: hidden;">
                    <!-- JS will inject img or video here -->
                </div>
                <div id="feedMediaCaption" class="text-white mt-3 px-3 py-2 rounded" style="background: rgba(0,0,0,0.6); max-width: 80%;">
                    <!-- JS will inject caption here -->
                </div>
            </div>
        </div>
    </div>
</div> --}}

@endsection

{{-- @push('scripts')
    <script src="{{ asset('frontend/js/stories.js') }}"></script>
    <script src="{{ asset('frontend/js/create-post.js') }}"></script>
@endpush --}}