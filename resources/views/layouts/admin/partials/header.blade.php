<div class="main-header">
  <div class="main-header-logo">
    <!-- Logo Header -->
    <div class="logo-header" data-background-color="dark">
      <a href="{{ url('/admin/dashboard') }}" class="logo">
        <img
          src="{{ asset('admin/img/logo.svg') }}"
          alt="navbar brand"
          class="navbar-brand"
          height="20"
        />
      </a>
      <div class="nav-toggle">
        <button class="btn btn-toggle toggle-sidebar">
          <i class="gg-menu-right"></i>
        </button>
        <button class="btn btn-toggle sidenav-toggler">
          <i class="gg-menu-left"></i>
        </button>
      </div>
      <button class="topbar-toggler more">
        <i class="gg-more-vertical-alt"></i>
      </button>
    </div>
    <!-- End Logo Header -->
  </div>
  <!-- Navbar Header -->
  <nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom">
    <div class="container-fluid">
      {{-- <nav class="navbar navbar-header-left navbar-expand-lg navbar-form nav-search p-0 d-none d-lg-flex">
        <div class="input-group">
          <div class="input-group-prepend">
            <button type="submit" class="btn btn-search pe-1">
              <i class="fa fa-search search-icon py-2"></i>
            </button>
          </div>
          <input type="text" placeholder="Search ..." class="form-control" />
        </div>
      </nav> --}}

      <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
        <!-- Notifications Dropdown -->
        <li class="nav-item topbar-icon dropdown hidden-caret">
          <a class="nav-link dropdown-toggle" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fa fa-bell"></i>
            <span class="notification">0</span>
          </a>
          {{-- <ul class="dropdown-menu notif-box animated fadeIn" aria-labelledby="notifDropdown"> --}}
          <ul class="dropdown-menu dropdown-menu-end notif-box animated fadeIn" aria-labelledby="notifDropdown">
            <li>
              <div class="dropdown-title">You have 0 new notifications</div>
            </li>
          </ul>
        </li>

        <!-- User Profile Dropdown -->
        <li class="nav-item topbar-user dropdown hidden-caret">
          <a class="dropdown-toggle profile-pic" data-bs-toggle="dropdown" href="#" aria-expanded="false">
            <div class="avatar-sm">
              <!-- STATIC: Avatar placeholder -->
              <span class="avatar-title rounded-circle border border-white bg-primary text-white text-center d-flex align-items-center justify-content-center p-1 px-2">A</span>
            </div>
            <span class="profile-username">
              <span class="op-7">Hi,</span>
              <!-- DYNAMIC: Will be auth()->user()->name -->
              <span class="fw-bold">{{auth()->user()->name}}</span>
            </span>
          </a>
          {{-- <ul class="dropdown-menu dropdown-user animated fadeIn"> --}}
          <ul class="dropdown-menu dropdown-menu-end dropdown-user animated fadeIn">
            <div class="dropdown-user-scroll scrollbar-outer">
              <li>
                <div class="user-box">
                  <div class="u-text">
                    <!-- DYNAMIC: Will be auth()->user()->name -->
                    <h4>{{auth()->user()->name}}</h4>
                    <!-- DYNAMIC: Will be auth()->user()->email -->
                    <p class="text-muted">{{auth()->user()->email}}</p>
                  </div>
                </div>
              </li>
              <li>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">My Profile</a>
                <div class="dropdown-divider"></div>
                {{-- <a class="dropdown-item" href="{{ route('admin.logout') }}">Logout</a> --}}
                <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
              </li>
            </div>
          </ul>
        </li>
      </ul>
    </div>
  </nav>
  <!-- End Navbar -->
</div>