<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#102b46">
  <title>@yield('title', 'Dashboard') | Camalaniugan Public Market</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root{--app-navy:#102b46;--app-green:#198754;--app-ink:#172b3b;--app-muted:#66798b;--app-border:#e4eaf0}
    body{min-height:100vh;background:#f3f6f8;color:var(--app-ink)}
    .app-shell{min-height:100vh}
    .sidebar{position:sticky;top:0;align-self:flex-start;display:flex;flex-direction:column;width:258px;height:100vh;flex:0 0 258px;overflow-y:auto;background:var(--app-navy);color:#e8eff5}
    .sidebar-brand{display:flex;align-items:center;gap:.8rem;padding:1.35rem 1.15rem 1.2rem;color:#fff;text-decoration:none;border-bottom:1px solid rgba(255,255,255,.1)}
    .sidebar-brand-mark{display:grid;place-items:center;width:42px;height:42px;border-radius:13px;background:#dff3e8;color:var(--app-green);font-size:1.2rem}
    .sidebar-brand-title{font-weight:750;line-height:1.15}.sidebar-brand-title small{display:block;margin-top:.25rem;color:#b8c8d6;font-size:.73rem;font-weight:500}
    .sidebar-section{padding:1.2rem 1rem .4rem;color:#91a5b8;font-size:.67rem;font-weight:750;letter-spacing:.1em;text-transform:uppercase}
    .sidebar-nav{padding:0 .65rem}
    .sidebar-link,.sidebar-logout{display:flex;align-items:center;gap:.75rem;width:100%;margin:.15rem 0;padding:.66rem .8rem;border:0;border-radius:9px;background:transparent;color:#d4e0ea;text-align:left;text-decoration:none;font-size:.9rem;transition:background .15s,color .15s}
    .sidebar-link i,.sidebar-logout i{width:1.2rem;color:#a9bdcf;font-size:1rem;text-align:center}
    .sidebar-link:hover,.sidebar-logout:hover{background:rgba(255,255,255,.09);color:#fff}
    .sidebar-link.active{background:#fff;color:var(--app-navy);font-weight:700}
    .sidebar-link.active i{color:var(--app-green)}
    .sidebar-spacer{flex:1}
    .sidebar-account{margin:.75rem .65rem 1rem;padding-top:.7rem;border-top:1px solid rgba(255,255,255,.12)}
    .app-main{min-width:0;flex:1}
    .app-topbar{position:sticky;top:0;z-index:5;display:flex;align-items:center;justify-content:space-between;gap:1rem;min-height:70px;padding:.7rem clamp(1rem,3vw,2rem);border-bottom:1px solid var(--app-border);background:rgba(255,255,255,.94);backdrop-filter:blur(12px)}
    .app-page-title{margin:0;color:var(--app-navy);font-size:1.12rem;font-weight:750}
    .app-user{display:flex;align-items:center;gap:.65rem;color:#53687b;font-size:.86rem}
    .app-user-avatar{display:grid;place-items:center;width:36px;height:36px;border-radius:50%;background:#e5f2eb;color:#167347;font-weight:750}
    .app-content{width:100%;max-width:1600px;margin:auto;padding:clamp(1rem,3vw,2rem)}
    .card{border-color:var(--app-border);border-radius:14px}
    .shadow-sm{box-shadow:0 6px 24px rgba(16,43,70,.055)!important}
    .btn{border-radius:9px;font-weight:600}
    .table thead th{background:#f8fafb;color:#65798b;font-size:.76rem;font-weight:750;letter-spacing:.035em;text-transform:uppercase;white-space:nowrap}
    @media(max-width:767.98px){
      .sidebar{position:relative;top:auto;width:100%;height:auto;max-height:calc(100vh - 65px);flex-basis:auto;overflow-y:auto}
      .sidebar.collapse:not(.show){display:none}
      .sidebar-brand{padding:.8rem 1rem}
      .sidebar-section{padding-top:.8rem}
      .app-topbar{min-height:62px}
      .app-content{padding:1rem}
    }
    @media(prefers-reduced-motion:reduce){*,*::before,*::after{scroll-behavior:auto!important;transition:none!important}}
  </style>
</head>
<body>
  @php
    $u = auth()->user();
    $unreadNotificationCount = $unreadNotifications ?? $u->unreadNotifications()->count();
  @endphp
  <div class="app-shell d-md-flex">
    <aside class="sidebar collapse d-md-flex" id="appSidebar" aria-label="Main navigation">
      <a class="sidebar-brand" href="{{ route('landing') }}">
        <span class="sidebar-brand-mark"><i class="bi bi-shop-window"></i></span>
        <span class="sidebar-brand-title">Camalaniugan<small>Public Market</small></span>
      </a>
      @if($u->isAdmin())
        <div class="sidebar-section">Workspace</div>
        <nav class="sidebar-nav">
          <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i>Dashboard</a>
        </nav>
        <div class="sidebar-section">Market management</div>
        <nav class="sidebar-nav">
          <a href="{{ route('buildings.index') }}" class="sidebar-link {{ request()->routeIs('buildings.*') ? 'active' : '' }}"><i class="bi bi-buildings"></i>Building Management</a>
          <a href="{{ route('stalls.index') }}" class="sidebar-link {{ request()->routeIs('stalls.*') ? 'active' : '' }}"><i class="bi bi-grid-3x3-gap"></i>Stall Management</a>
          <a href="{{ route('stalls.index') }}#occupants" class="sidebar-link"><i class="bi bi-person-lines-fill"></i>Stall Assignments &amp; Occupants</a>
          <a href="{{ route('applications.index') }}" class="sidebar-link {{ request()->routeIs('applications.*') ? 'active' : '' }}"><i class="bi bi-inbox"></i>Applications</a>
          <a href="{{ route('payments.index') }}" class="sidebar-link {{ request()->routeIs('payments.*') ? 'active' : '' }}"><i class="bi bi-cash-stack"></i>Transactions &amp; Payments</a>
          <a href="{{ route('leases.index') }}" class="sidebar-link {{ request()->routeIs('leases.*') ? 'active' : '' }}"><i class="bi bi-file-earmark-text"></i>Lease Contracts</a>
        </nav>
        <div class="sidebar-section">Administration</div>
        <nav class="sidebar-nav">
          <a href="{{ route('admin.dashboard') }}#reports" class="sidebar-link"><i class="bi bi-bar-chart-line"></i>Reports</a>
          <a href="{{ route('registrants.index') }}" class="sidebar-link {{ request()->routeIs('registrants.*') ? 'active' : '' }}"><i class="bi bi-people"></i>User Management</a>
          <a href="{{ route('admin.activity-logs') }}" class="sidebar-link {{ request()->routeIs('admin.activity-logs') ? 'active' : '' }}"><i class="bi bi-clock-history"></i>Activity Log</a>
          <a href="{{ route('admin.settings') }}" class="sidebar-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}"><i class="bi bi-gear"></i>Settings</a>
        </nav>
      @else
        <div class="sidebar-section">Applicant portal</div>
        <nav class="sidebar-nav">
          <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i>Dashboard</a>
          <a href="{{ route('my.stalls') }}" class="sidebar-link {{ request()->routeIs('my.stalls') ? 'active' : '' }}"><i class="bi bi-grid-3x3-gap"></i>Available Stalls</a>
          <a href="{{ route('my.applications') }}" class="sidebar-link {{ request()->routeIs('my.applications') ? 'active' : '' }}"><i class="bi bi-inbox"></i>My Applications</a>
          <a href="{{ route('my.leases') }}" class="sidebar-link {{ request()->routeIs('my.leases') ? 'active' : '' }}"><i class="bi bi-file-earmark-text"></i>My Leases &amp; Payments</a>
        </nav>
      @endif
      <div class="sidebar-spacer"></div>
      <div class="sidebar-account">
        <div class="sidebar-nav">
          <form method="POST" action="{{ route('logout') }}">@csrf<button class="sidebar-logout" type="submit"><i class="bi bi-box-arrow-left"></i>Sign out</button></form>
        </div>
      </div>
    </aside>
    <div class="app-main">
      <header class="app-topbar">
        <div class="d-flex align-items-center gap-2">
          <button class="btn btn-outline-secondary d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#appSidebar" aria-controls="appSidebar" aria-expanded="false" aria-label="Toggle navigation"><i class="bi bi-list"></i></button>
          <h1 class="app-page-title">@yield('title')</h1>
        </div>
        <div class="d-flex align-items-center gap-3">
          <a class="btn btn-sm btn-light position-relative" href="{{ ($u->isAdmin() ? route('admin.dashboard') : route('dashboard')) }}#notifications" aria-label="Notifications">
            <i class="bi bi-bell"></i>
            @if($unreadNotificationCount)<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $unreadNotificationCount }}</span>@endif
          </a>
          <a class="app-user text-decoration-none" href="{{ $u->isAdmin() ? route('admin.settings') : route('dashboard').'#profile' }}">
            <span class="app-user-avatar">{{ mb_substr($u->name, 0, 1) }}</span><span class="d-none d-sm-inline">{{ $u->name }}<small class="d-block text-muted">{{ $u->isAdmin() ? 'Administrator' : 'Applicant' }}</small></span>
          </a>
        </div>
      </header>
      <main class="app-content">
        @include('partials.alerts')
        @yield('content')
      </main>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  @stack('scripts')
</body>
</html>
