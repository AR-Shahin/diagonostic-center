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
            <a href="{{ route('admin.manual') }}" @class(['nav-link', 'active' => request()->routeIs('admin.manual')])>
              <i class="nav-icon fas fa-book-open"></i><p>ব্যবহার নির্দেশিকা</p>
            </a>
          </li>
          @php
              $billingMenu = [
                  ['admin.invoices.create', 'admin.invoices.create', [], 'invoice-create', 'fas fa-cash-register', 'New bill'],
                  ['admin.invoices.index', 'admin.invoices.index', [], 'invoice-view', 'fas fa-file-invoice-dollar', 'Invoices'],
                  [null, 'admin.invoices.index', ['status' => 'due'], 'invoice-view', 'fas fa-hand-holding-usd', 'Due list'],
                  ['admin.patients.*', 'admin.patients.index', [], 'patient-view', 'fas fa-user-injured', 'Patients'],
                  ['admin.referrers.*', 'admin.referrers.index', [], 'referrer-view', 'fas fa-user-md', 'Referrers'],
              ];
              $visibleBilling = array_filter($billingMenu, fn ($m) => in_array($m[3], $permissions));
          @endphp
          @if ($visibleBilling)
          <li class="nav-header">BILLING</li>
          @foreach ($visibleBilling as [$pattern, $route, $params, $perm, $icon, $label])
          @php
              $isDueLink = $pattern === null;
              $onDueList = request()->routeIs('admin.invoices.index') && request('status') === 'due';
              $isActive = $isDueLink ? $onDueList : (request()->routeIs($pattern) && ! $onDueList);
          @endphp
          <li class="nav-item">
            <a href="{{ route($route, $params) }}" @class(['nav-link', 'active' => $isActive])>
              <i class="nav-icon {{ $icon }}"></i><p>{{ $label }}</p>
            </a>
          </li>
          @endforeach
          @endif
          @php
              $accountsMenu = [
                  ['admin.commission.*', 'admin.commission.index', 'commission-view', 'fas fa-hand-holding-usd', 'Referral commission'],
                  ['admin.expenses.*', 'admin.expenses.index', 'expense-view', 'fas fa-receipt', 'Expenses'],
                  ['admin.cash-closings.*', 'admin.cash-closings.index', 'cashclosing-create', 'fas fa-cash-register', 'Cash closing'],
                  ['admin.reports.*', 'admin.reports.index', 'report-view', 'fas fa-chart-bar', 'Reports'],
              ];
              $visibleAccounts = array_filter($accountsMenu, fn ($m) => in_array($m[2], $permissions));
          @endphp
          @if ($visibleAccounts)
          <li class="nav-header">ACCOUNTS &amp; REPORTS</li>
          @foreach ($visibleAccounts as [$pattern, $route, $perm, $icon, $label])
          <li class="nav-item">
            <a href="{{ route($route) }}" @class(['nav-link', 'active' => request()->routeIs($pattern)])>
              <i class="nav-icon {{ $icon }}"></i><p>{{ $label }}</p>
            </a>
          </li>
          @endforeach
          @endif
          @php
              $websiteMenu = [
                  ['admin.web-requests.*', 'admin.web-requests.index', 'webrequest-view', 'fas fa-inbox', 'Website requests'],
                  ['admin.doctors.*', 'admin.doctors.index', 'webcontent-edit', 'fas fa-user-md', 'Doctors'],
                  ['admin.articles.*', 'admin.articles.index', 'webcontent-edit', 'fas fa-newspaper', 'Health articles'],
                  ['admin.faqs.*', 'admin.faqs.index', 'webcontent-edit', 'fas fa-question-circle', 'FAQ'],
                  ['admin.testimonials.*', 'admin.testimonials.index', 'webcontent-edit', 'fas fa-comment-dots', 'Patient reviews'],
                  ['admin.website-content.*', 'admin.website-content.index', 'webcontent-edit', 'fas fa-pen-nib', 'Home page text'],
              ];
              $visibleWebsite = array_filter($websiteMenu, fn ($m) => in_array($m[2], $permissions));
              $newWebRequests = in_array('webrequest-view', $permissions) ? \App\Models\WebRequest::where('status', 'new')->count() : 0;
          @endphp
          @if ($visibleWebsite)
          <li class="nav-header">WEBSITE</li>
          @foreach ($visibleWebsite as [$pattern, $route, $perm, $icon, $label])
          <li class="nav-item">
            <a href="{{ route($route) }}" @class(['nav-link', 'active' => request()->routeIs($pattern)])>
              <i class="nav-icon {{ $icon }}"></i><p>{{ $label }}
                @if ($route === 'admin.web-requests.index' && $newWebRequests)<span class="nav-badge badge text-bg-danger me-3">{{ $newWebRequests }}</span>@endif
              </p>
            </a>
          </li>
          @endforeach
          @endif
          @if (in_array('lab-view', $permissions))
          <li class="nav-header">LAB</li>
          <li class="nav-item">
            <a href="{{ route('admin.lab.index') }}" @class(['nav-link', 'active' => request()->routeIs('admin.lab.*')])>
              <i class="nav-icon fas fa-microscope"></i><p>Lab work list</p>
            </a>
          </li>
          @endif
          @php
              $catalogMenu = [
                  ['admin.lab-tests.*', 'admin.lab-tests.index', 'labtest-view', 'Tests'],
                  ['admin.packages.*', 'admin.packages.index', 'package-view', 'Health packages'],
                  ['admin.departments.*', 'admin.departments.index', 'department-view', 'Departments'],
                  ['admin.sample-types.*', 'admin.sample-types.index', 'sampletype-view', 'Sample types'],
                  ['admin.units.*', 'admin.units.index', 'unit-view', 'Units'],
              ];
              $visibleCatalog = array_filter($catalogMenu, fn ($m) => in_array($m[2], $permissions));
              $catalogOpen = collect($visibleCatalog)->contains(fn ($m) => request()->routeIs($m[0]));
          @endphp
          @if ($visibleCatalog)
          <li class="nav-header">DIAGNOSTIC SETUP</li>
          <li @class(['nav-item', 'menu-open' => $catalogOpen])>
            <a href="#" @class(['nav-link', 'active' => $catalogOpen])>
              <i class="nav-icon fas fa-flask"></i>
              <p>Test catalog <i class="nav-arrow fas fa-angle-right"></i></p>
            </a>
            <ul class="nav nav-treeview">
              @foreach ($visibleCatalog as [$pattern, $route, $perm, $label])
              <li class="nav-item">
                <a href="{{ route($route) }}" @class(['nav-link', 'active' => request()->routeIs($pattern)])>
                  <i class="far fa-circle nav-icon"></i><p>{{ $label }}</p>
                </a>
              </li>
              @endforeach
            </ul>
          </li>
          @endif
          @if (in_array('branch-view', $permissions))
          <li class="nav-item">
            <a href="{{ route('admin.branches.index') }}" @class(['nav-link', 'active' => request()->routeIs('admin.branches.*')])>
              <i class="nav-icon fas fa-code-branch"></i><p>Branches</p>
            </a>
          </li>
          @endif
          @if (in_array('businesssetting-view', $permissions))
          <li class="nav-item">
            <a href="{{ route('admin.business-settings.index') }}" @class(['nav-link', 'active' => request()->routeIs('admin.business-settings.*')])>
              <i class="nav-icon fas fa-sliders-h"></i><p>Business rules</p>
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
