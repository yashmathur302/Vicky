<div class="sidebar" data-background-color="dark">

  <div class="sidebar-logo">

    <div class="logo-header" data-background-color="dark">

      <a href="{{ route('admin.dashboard') }}" class="logo">

        <img src="{{ asset('admin/img/logo.svg') }}" alt="navbar brand" class="navbar-brand" height="20" />

      </a>

      <div class="nav-toggle">

        <!-- Single optimized button for both toggle and mobile view -->

        <button type="button" class="btn btn-toggle toggle-sidebar" id="mainSidebarToggle" aria-label="Toggle sidebar">

          <i class="gg-menu-right"></i>

        </button>

      </div>

      <button class="topbar-toggler more">

        <i class="gg-more-vertical-alt"></i>

      </button>

    </div>

  </div>

  

  <div class="sidebar-wrapper scrollbar scrollbar-inner">

    <div class="sidebar-content pt-2 pb-4">

      <ul class="nav nav-secondary">

        

        <!-- Dashboard -->

        <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">

          <a href="{{ route('admin.dashboard') }}">

            <i class="fas fa-home"></i>

            <p>Dashboard</p>

          </a>

        </li>



        <li class="nav-section">

          <span class="sidebar-mini-icon">

            <i class="fa fa-ellipsis-h"></i>

          </span>

          <h4 class="text-section">Modules</h4>

        </li>



        <!-- Users -->

        <li class="nav-item {{ request()->routeIs('admin.users.list') ? 'active' : '' }}">

          <a href="{{ route('admin.users.list') }}">

            <i class="fas fa-users"></i>

            <p>Users</p>

          </a>

        </li>

        <!-- Banned Users -->
        <li class="nav-item {{ request()->routeIs('admin.users.banned_user') ? 'active' : '' }}">

          <a href="{{ route('admin.users.banned_user') }}">

            <i class="fas fa-users"></i>

            <p>Banned Users</p>

          </a>

        </li>

        <!-- EVents Module (Dropdown) -->

        <li class="nav-item {{ request()->routeIs('admin.events.*') ? 'active submenu' : '' }}">

          <a data-bs-toggle="collapse" href="#eventsModule" class="{{ request()->routeIs('admin.events.*') ? '' : 'collapsed' }}"

             aria-expanded="{{ request()->routeIs('admin.events.*') ? 'true' : 'false' }}">

            <i class="fas fa-calendar-alt"></i>

            <p>Events</p>

            <span class="caret"></span>

          </a>



          <div class="collapse {{ request()->routeIs('admin.events.*') ? 'show' : '' }}" id="eventsModule">

            <ul class="nav nav-collapse">

              <li class="{{ request()->routeIs('admin.events.list') ? 'active' : '' }}">

                <a href="{{ route('admin.events.list') }}">

                  <span class="sub-item">Events List</span>

                </a>

              </li>



            </ul>

          </div>

        </li>



        <!-- Users -->

        <li class="nav-item {{ request()->routeIs('admin.categories.list') ? 'active' : '' }}">

          <a href="{{ route('admin.categories.list') }}">

            <i class="fas fa-users"></i>

            <p>Categories</p>

          </a>

        </li>



        <!-- Groups -->

        <li class="nav-item {{ request()->routeIs('admin.groups.*') ? 'active' : '' }}">

          <a href="#">

            <i class="fas fa-user-friends"></i>

            <p>Groups</p>

          </a>

        </li>



        <!-- Pages -->

        <li class="nav-item {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}">

          <a href="#">

            <i class="fas fa-file-alt"></i>

            <p>Pages</p>

          </a>

        </li>

        <!-- Blogs -->

        <li class="nav-item {{ request()->routeIs('admin.blogs.*') ? 'active' : '' }}">

          <a href="#">

            <i class="fas fa-pen-nib"></i>

            <p>Blogs</p>

          </a>

        </li>



        <li class="nav-section">

          <span class="sidebar-mini-icon">

            <i class="fa fa-ellipsis-h"></i>

          </span>

          <h4 class="text-section">System</h4>

        </li>



        <!-- Notifications -->

        <li class="nav-item {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">

          <a href="#">

            <i class="fas fa-bell"></i>

            <p>Notifications</p>

          </a>

        </li>



        <!-- Reports -->

        <li class="nav-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">

          <a href="#">

            <i class="fas fa-chart-pie"></i>

            <p>Reports</p>

          </a>

        </li>

        <!-- Settings (Dropdown) -->

        <li class="nav-item {{ request()->routeIs('admin.settings.*') ? 'active submenu' : '' }}">

          <a data-bs-toggle="collapse" href="#settingsModule" class="{{ request()->routeIs('admin.settings.*') ? '' : 'collapsed' }}" aria-expanded="{{ request()->routeIs('admin.settings.*') ? 'true' : 'false' }}">

            <i class="fas fa-cogs"></i>

            <p>Settings</p>

            <span class="caret"></span>

          </a>

          <div class="collapse {{ request()->routeIs('admin.settings.*') ? 'show' : '' }}" id="settingsModule">

            <ul class="nav nav-collapse">

              <li class="{{ request()->routeIs('admin.settings.general') ? 'active' : '' }}">

                <a href="#">

                  <span class="sub-item">General Settings</span>

                </a>

              </li>

            </ul>

          </div>

        </li>



      </ul>

    </div>

  </div>

</div>

