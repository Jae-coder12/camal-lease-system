<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'Dashboard') | Lease Monitoring</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
  body{background:#f3f4f6}
  .sidebar{width:250px;flex-shrink:0;background:#111827;min-height:100vh}
  .sidebar .brand{color:#fff;font-weight:700;padding:1.25rem}
  .sidebar .section{font-size:.7rem;text-transform:uppercase;color:#64748b;padding:1rem 1.25rem .25rem}
  .sidebar a,.sidebar button.link{color:#cbd5e1;text-decoration:none;display:block;width:100%;text-align:left;background:none;border:0;padding:.65rem 1.25rem}
  .sidebar a:hover,.sidebar a.active,.sidebar button.link:hover{background:#1f2937;color:#fff}
  .sidebar i{margin-right:.5rem}
  @media(max-width:768px){.wrap{flex-direction:column}.sidebar{width:100%;min-height:auto}}
</style>
</head>
<body>
<div class="d-flex wrap">
  <aside class="sidebar">
    <div class="brand"><i class="bi bi-shop"></i> Lease Monitoring</div>
    @php $u = auth()->user(); @endphp
    <div class="section">{{ $u->isAdmin() ? 'Administrator' : 'Stall Owner' }}</div>
    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i>Dashboard</a>
    @if($u->isAdmin())
      <a href="{{ route('registrants.index') }}" class="{{ request()->routeIs('registrants.*') ? 'active' : '' }}"><i class="bi bi-people"></i>Registrants</a>
      <a href="{{ route('stalls.index') }}" class="{{ request()->routeIs('stalls.*') ? 'active' : '' }}"><i class="bi bi-grid-3x3-gap"></i>Stalls</a>
      <a href="{{ route('applications.index') }}" class="{{ request()->routeIs('applications.*') ? 'active' : '' }}"><i class="bi bi-inbox"></i>Applications</a>
      <a href="{{ route('leases.index') }}" class="{{ request()->routeIs('leases.index') ? 'active' : '' }}"><i class="bi bi-file-earmark-text"></i>Leases &amp; Documents</a>
    @else
      <a href="{{ route('my.stalls') }}" class="{{ request()->routeIs('my.stalls') ? 'active' : '' }}"><i class="bi bi-grid-3x3-gap"></i>Available Stalls</a>
      <a href="{{ route('my.applications') }}" class="{{ request()->routeIs('my.applications') ? 'active' : '' }}"><i class="bi bi-inbox"></i>My Applications</a>
      <a href="{{ route('my.leases') }}" class="{{ request()->routeIs('my.leases') ? 'active' : '' }}"><i class="bi bi-file-earmark-text"></i>My Leases &amp; Documents</a>
    @endif
    <div class="section">Account</div>
    <form method="POST" action="{{ route('logout') }}">@csrf<button class="link"><i class="bi bi-box-arrow-left"></i>Logout</button></form>
  </aside>
  <main class="flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="mb-0">@yield('title')</h4>
      <span class="text-muted small"><i class="bi bi-person-circle"></i> {{ $u->name }}</span>
    </div>
    @include('partials.alerts')
    @yield('content')
  </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body></html>
