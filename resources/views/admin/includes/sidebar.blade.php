<aside class="app-sidebar bg-dark shadow" data-bs-theme="dark">
    <!-- Brand Logo -->
    <div class="sidebar-brand">
    <a href="{{ route('admin.dashboard') }}" class="brand-link">
      <img src="{{ asset("admin/dist/img/AdminLTELogo.png") }}" alt="AdminLTE Logo" class="brand-image rounded-circle shadow" style="opacity: .8">
      <span class="brand-text fw-light">Admin Panel</span>
    </a>
    </div>

    <!-- Sidebar -->
    <div class="sidebar-wrapper">
      <!-- Sidebar user panel -->
      <div class="sidebar-user-panel mt-3 pb-3 mb-3 d-flex border-bottom border-secondary px-3 align-items-center">
        <div class="image">
          <img src="{{ asset("admin/dist/img/user2-160x160.jpg") }}" class="rounded-circle shadow-sm" style="width: 2.1rem;" alt="User Image">
        </div>
        <div class="info ms-3">
          <a href="#" class="d-block text-decoration-none text-light">{{ auth()->user()->name }}</a>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">

          <li class="nav-item">
            <a href="{{ route('admin.dashboard') }}" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>
                Home
              </p>
            </a>
          </li>


          @if (in_array("simple-link",$permissions))
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Simple Link
                <span class="badge text-bg-danger float-end">New</span>
              </p>
            </a>
          </li>
          @endif


          <li class="nav-item">
            <a href="{{ url('log-viewer') }}" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Log View              
              </p>
            </a>
          </li>
          @if (in_array("role-create",$permissions))
          <li class="nav-item">
            <a href="#" class="nav-link ">
              <i class="nav-icon fas fa-solid fa-user-lock"></i>
              <p>
                Security
                <i class="nav-arrow fas fa-angle-right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ route('admin.roles.index') }}" class="nav-link ">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Role</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('admin.permissions.index') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Permission</p>
                </a>
              </li>
            </ul>
          </li>
          @endif
          @if (in_array("admin-create",$permissions))
          <li class="nav-item">
            <a href="#" class="nav-link ">
              <i class="nav-icon fas fa-users"></i>
              <p>
                Users
                <i class="nav-arrow fas fa-angle-right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ route('admin.admins.index') }}" class="nav-link ">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Admin</p>
                </a>
              </li>

            </ul>
          </li>
          @endif
          
          <li class="nav-item">
            <a href="{{ route('admin.settings.index') }}" class="nav-link">
              <i class="nav-icon fas fa-cogs"></i>
              <p>Settings</p>
            </a>
          </li>

          <li class="nav-item">
            <form action="{{ route("admin.logout") }}" method="POST">
                @csrf
                <button type="submit" class="nav-link text-light text-start bg-transparent border-0 w-100"><i class="fas fa-sign-out-alt nav-icon text-danger"></i> <p>Logout</p></button>
            </form>

          </li>
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>
