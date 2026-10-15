@extends('layouts.frontend.main')

@section('title', 'Feed | ' . config('app.name', 'EduSocial'))

@section('content')
<!-- Custom Styles for Feed (In production, move this to your main CSS file) -->
<style>
    body { background-color: #f3f4f6; }
    .sticky-sidebar { position: sticky; top: 80px; height: calc(100vh - 80px); overflow-y: auto; }
    /* Hide scrollbar for sidebars but allow scrolling */
    .sticky-sidebar::-webkit-scrollbar { width: 0px; background: transparent; }
    .story-avatar { width: 60px; height: 60px; border-radius: 50%; border: 3px solid #0d6efd; padding: 2px; object-fit: cover; }
    .post-avatar { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; }
    .hover-bg-light:hover { background-color: #f8f9fa; transition: 0.2s; border-radius: 8px; }
    .feed-image { max-height: 500px; width: 100%; object-fit: cover; border-radius: 8px; }
    .action-btn { cursor: pointer; color: #6c757d; font-weight: 500; }
    .action-btn:hover { background-color: #f1f3f5; color: #0d6efd; border-radius: 5px; }
    .stories-wrapper { display: flex; overflow-x: auto; gap: 15px; padding-bottom: 10px; scrollbar-width: none; }
    .stories-wrapper::-webkit-scrollbar { display: none; }
</style>

<div class="container py-4">
    <div class="row g-4">
        
        <!-- LEFT SIDEBAR: Navigation -->
        <div class="col-lg-3 d-none d-lg-block">
            <div class="sticky-sidebar pe-2">
                <div class="d-flex align-items-center mb-4 p-2 hover-bg-light cursor-pointer">
                    <img src="{{ asset('frontend/images/default-avatar.png') }}" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name ?? 'User') }}&background=random'" alt="User" class="post-avatar me-3 shadow-sm">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">{{ $user->name ?? 'Student Name' }}</h6>
                        <small class="text-muted">{{ '@' . ($user->username ?? 'username') }}</small>
                    </div>
                </div>

                <nav class="nav flex-column gap-1">
                    <a href="#" class="nav-link text-dark hover-bg-light rounded px-3 py-2 fw-semibold d-flex align-items-center">
                        <i class="fas fa-newspaper text-primary fs-5 me-3 w-20px"></i> News Feed
                    </a>
                    <a href="#" class="nav-link text-dark hover-bg-light rounded px-3 py-2 fw-semibold d-flex align-items-center">
                        <i class="fas fa-book-open text-success fs-5 me-3 w-20px"></i> My Courses
                    </a>
                    <a href="#" class="nav-link text-dark hover-bg-light rounded px-3 py-2 fw-semibold d-flex align-items-center">
                        <i class="fas fa-users text-info fs-5 me-3 w-20px"></i> Study Groups
                    </a>
                    <a href="#" class="nav-link text-dark hover-bg-light rounded px-3 py-2 fw-semibold d-flex align-items-center">
                        <i class="fas fa-calendar-alt text-danger fs-5 me-3 w-20px"></i> Events & Webinars
                    </a>
                    <a href="#" class="nav-link text-dark hover-bg-light rounded px-3 py-2 fw-semibold d-flex align-items-center">
                        <i class="fas fa-bookmark text-warning fs-5 me-3 w-20px"></i> Saved Materials
                    </a>
                </nav>
                
                <hr class="my-4">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-light w-100 text-start text-danger fw-semibold hover-bg-light">
                        <i class="fas fa-sign-out-alt me-2"></i> Log Out
                    </button>
                </form>
            </div>
        </div>

        <!-- MAIN CONTENT: Feed -->
        <div class="col-lg-6 col-md-12">
            
            <!-- Stories Section -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3">
                    <div class="stories-wrapper">
                        <!-- Add Story -->
                        <div class="text-center cursor-pointer flex-shrink-0" style="width: 70px;">
                            <div class="position-relative d-inline-block mb-1">
                                <img src="{{ asset('frontend/images/default-avatar.png') }}" onerror="this.src='https://ui-avatars.com/api/?name=Add&background=e9ecef&color=0d6efd'" class="story-avatar border-secondary">
                                <span class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 22px; height: 22px; right: -5px; bottom: 0; border: 2px solid white;">
                                    <i class="fas fa-plus fs-7"></i>
                                </span>
                            </div>
                            <small class="d-block text-truncate fw-semibold text-muted" style="font-size: 0.75rem;">Create</small>
                        </div>
                        
                        <!-- Dummy Stories (Backend Ready: Foreach loop here) -->
                        @for($i = 1; $i <= 5; $i++)
                        <div class="text-center cursor-pointer flex-shrink-0" style="width: 70px;">
                            <img src="https://i.pravatar.cc/150?img={{ $i+10 }}" class="story-avatar mb-1" onerror="this.src='https://ui-avatars.com/api/?name=User&background=random'">
                            <small class="d-block text-truncate fw-semibold text-dark" style="font-size: 0.75rem;">Student {{ $i }}</small>
                        </div>
                        @endfor
                    </div>
                </div>
            </div>

            <!-- Create Post Box -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <img src="{{ asset('frontend/images/default-avatar.png') }}" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name ?? 'U') }}&background=random'" class="post-avatar me-3">
                        <button class="btn btn-light rounded-pill text-start flex-grow-1 text-muted border border-1 py-2" data-bs-toggle="modal" data-bs-target="#createPostModal">
                            Share an update, ask a question, or post a resource...
                        </button>
                    </div>
                    <hr class="text-muted my-2">
                    <div class="d-flex justify-content-between px-2 pt-2">
                        <div class="action-btn p-2 d-flex align-items-center w-100 justify-content-center">
                            <i class="fas fa-image text-success me-2 fs-5"></i> Media
                        </div>
                        <div class="action-btn p-2 d-flex align-items-center w-100 justify-content-center">
                            <i class="fas fa-file-alt text-primary me-2 fs-5"></i> Document
                        </div>
                        <div class="action-btn p-2 d-flex align-items-center w-100 justify-content-center d-none d-sm-flex">
                            <i class="fas fa-poll text-warning me-2 fs-5"></i> Poll
                        </div>
                    </div>
                </div>
            </div>

            <!-- Feed Posts Area -->
            <div id="social-feed">
                <!-- Post Item 1 (With Image) -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 post-card">
                    <div class="card-body p-0">
                        <!-- Post Header -->
                        <div class="d-flex align-items-center justify-content-between p-3 pb-2">
                            <div class="d-flex align-items-center">
                                <img src="https://i.pravatar.cc/150?img=33" onerror="this.src='https://ui-avatars.com/api/?name=John+Doe'" class="post-avatar me-3">
                                <div>
                                    <h6 class="mb-0 fw-bold"><a href="#" class="text-dark text-decoration-none hover-underline">Prof. Alan Smith</a></h6>
                                    <small class="text-muted"><i class="fas fa-globe-americas me-1"></i> 2 hours ago • <span class="badge bg-light text-secondary border">Computer Science</span></small>
                                </div>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-link text-muted text-decoration-none" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-h"></i></button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-bookmark me-2 text-muted"></i> Save Post</a></li>
                                    <li><a class="dropdown-item text-danger" href="#"><i class="fas fa-flag me-2"></i> Report</a></li>
                                </ul>
                            </div>
                        </div>
                        
                        <!-- Post Body -->
                        <div class="p-3 pt-1">
                            <p class="card-text text-dark">Just uploaded the new notes for Data Structures and Algorithms. Make sure to review the Binary Trees section before tomorrow's quiz! 📚💻</p>
                        </div>
                        <img src="https://images.unsplash.com/photo-1516116216624-53e697fedbea?w=800&q=80" onerror="this.style.display='none'" class="img-fluid feed-image" alt="Post attachment">
                        
                        <!-- Post Footer/Interactions -->
                        <div class="p-3">
                            <div class="d-flex justify-content-between text-muted small mb-2 px-1">
                                <span><i class="fas fa-thumbs-up text-primary me-1"></i> 124</span>
                                <span>12 Comments • 3 Shares</span>
                            </div>
                            <hr class="m-0 mb-2">
                            <div class="d-flex justify-content-between">
                                <div class="action-btn p-2 d-flex align-items-center w-100 justify-content-center">
                                    <i class="far fa-thumbs-up me-2 fs-6"></i> Like
                                </div>
                                <div class="action-btn p-2 d-flex align-items-center w-100 justify-content-center">
                                    <i class="far fa-comment-alt me-2 fs-6"></i> Comment
                                </div>
                                <div class="action-btn p-2 d-flex align-items-center w-100 justify-content-center">
                                    <i class="far fa-share-square me-2 fs-6"></i> Share
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State (Hidden by default, show when no posts) -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 text-center py-5 d-none" id="empty-state">
                    <div class="card-body">
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-users-slash fs-1 text-muted"></i>
                        </div>
                        <h5 class="fw-bold text-dark">No posts yet</h5>
                        <p class="text-muted mb-0">Follow more peers and teachers to see their updates here.</p>
                    </div>
                </div>

                <!-- Loading Spinner for Infinite Scroll -->
                {{-- <div class="text-center py-3 text-muted">
                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div> Loading more posts...
                </div> --}}
            </div>

        </div>

        <!-- RIGHT SIDEBAR: Widgets -->
        <div class="col-lg-3 d-none d-lg-block">
            <div class="sticky-sidebar ps-2">
                
                <!-- Upcoming Events -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0">
                        <h6 class="fw-bold text-dark mb-0">Upcoming Events</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-start mb-3">
                            <div class="bg-light rounded text-center me-3 px-2 py-1 border border-danger">
                                <span class="d-block text-danger fw-bold fs-6">12</span>
                                <small class="d-block text-dark fw-semibold" style="font-size: 0.7rem;">OCT</small>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-bold fs-7">Laravel Workshop</h6>
                                <small class="text-muted d-block"><i class="fas fa-video me-1"></i> Virtual Meeting</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-start">
                            <div class="bg-light rounded text-center me-3 px-2 py-1 border border-primary">
                                <span class="d-block text-primary fw-bold fs-6">15</span>
                                <small class="d-block text-dark fw-semibold" style="font-size: 0.7rem;">OCT</small>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-bold fs-7">Web Dev Hackathon</h6>
                                <small class="text-muted d-block"><i class="fas fa-map-marker-alt me-1"></i> Main Campus Area</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Suggested Peers -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0">
                        <h6 class="fw-bold text-dark mb-0">Suggested to Follow</h6>
                    </div>
                    <div class="card-body">
                        @for($i = 1; $i <= 3; $i++)
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center overflow-hidden me-2">
                                <img src="https://i.pravatar.cc/150?img={{ $i+40 }}" onerror="this.src='https://ui-avatars.com/api/?name=User'" class="rounded-circle me-2 object-fit-cover" width="40" height="40">
                                <div class="text-truncate">
                                    <h6 class="mb-0 fw-bold text-dark fs-7">Jane Student</h6>
                                    <small class="text-muted">B.Tech CS</small>
                                </div>
                            </div>
                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3"><i class="fas fa-plus"></i></button>
                        </div>
                        @endfor
                        <a href="#" class="text-decoration-none d-block text-center fw-semibold mt-2">View More</a>
                    </div>
                </div>
                
                <!-- Footer Links -->
                <div class="text-muted small px-2">
                    <a href="#" class="text-muted text-decoration-none hover-underline">Privacy Policy</a> • 
                    <a href="#" class="text-muted text-decoration-none hover-underline">Terms</a> • 
                    <a href="#" class="text-muted text-decoration-none hover-underline">Support</a>
                    <p class="mt-2">© {{ date('Y') }} EduSocial Platform</p>
                </div>
            </div>
        </div>
        
    </div>
</div>
@endsection