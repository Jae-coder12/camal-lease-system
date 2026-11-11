<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Lease Contract Rental Monitoring System</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>.hero{background:linear-gradient(135deg,#111827,#1d4ed8);color:#fff;padding:4rem 0}</style></head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark"><div class="container">
  <span class="navbar-brand">Lease Contract Rental Monitoring System</span>
  <div>@auth <a href="{{ route('dashboard') }}" class="btn btn-sm btn-light">Dashboard</a>
  @else <a href="{{ route('login') }}" class="btn btn-sm btn-outline-light">Login</a>@endauth</div>
</div></nav>

<section class="hero text-center"><div class="container">
  <h1 class="fw-bold">Rent a stall. Register today.</h1>
  <p class="lead mb-0">Register below, browse available stalls, and apply for your lease online.</p>
</div></section>

<div class="container my-5"><div class="row g-4">
  <div class="col-lg-7">
    <div class="card shadow-sm"><div class="card-body">
      <h5 class="mb-3">Registration Form</h5>
      @include('partials.alerts')
      <form method="POST" action="{{ route('register') }}">@csrf
        @include('partials.registrant-fields', ['registrant' => null])
        <hr>
        <div class="row g-3">
          <div class="col-md-12"><label class="form-label">Email (for login)</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required></div>
          <div class="col-md-6"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
          <div class="col-md-6"><label class="form-label">Confirm Password</label><input type="password" name="password_confirmation" class="form-control" required></div>
        </div>
        <button class="btn btn-primary mt-3 w-100">Register</button>
      </form>
    </div></div>
  </div>

  <div class="col-lg-5">
    <div class="card shadow-sm"><div class="card-body">
      <h5 class="mb-3">Available Stalls <span class="badge bg-success">{{ $stalls->count() }}</span></h5>
      <div class="table-responsive"><table class="table table-sm align-middle">
        <thead><tr><th>Stall</th><th>Location</th><th>Size</th><th class="text-end">Rate/mo</th></tr></thead>
        <tbody>
        @forelse($stalls as $s)
          <tr><td>{{ $s->stall_number }}</td><td>{{ $s->location }}</td><td>{{ $s->size_sqm }} m²</td><td class="text-end">₱{{ number_format($s->monthly_rate,2) }}</td></tr>
        @empty <tr><td colspan="4" class="text-muted text-center">No stalls available right now.</td></tr>
        @endforelse
        </tbody></table></div>
      <p class="small text-muted mb-0">Register, then log in to apply for a stall.</p>
    </div></div>
  </div>
</div></div>
</body></html>
