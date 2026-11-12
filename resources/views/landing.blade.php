<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Browse public market stalls and apply online with the Municipality of Camalaniugan.">
  <title>Camalaniugan Public Market | Stall &amp; Rental Management</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root {
      --navy: #102b46;
      --green: #198754;
      --ink: #172b3b;
      --muted: #607487
    }

    body {
      background: #f5f8fa;
      color: var(--ink)
    }

    .navbar {
      position: absolute;
      inset: 0 0 auto;
      z-index: 10;
      background: linear-gradient(180deg, rgb(8 28 48 / 82%), rgb(8 28 48 / 0%));
      padding-block: 1rem
    }

    .brand-mark {
      display: inline-grid;
      place-items: center;
      width: 40px;
      height: 40px;
      border-radius: 12px;
      background: #dff3e8;
      color: var(--green)
    }

    .hero {
      position: relative;
      display: flex;
      align-items: center;
      min-height: min(740px, 80svh);
      padding: 8rem 0 6rem;
      color: #fff;
      background-color: var(--navy);
      background-image: linear-gradient(90deg, rgb(8 32 54 / 82%) 0%, rgb(8 32 54 / 62%) 38%, rgb(8 32 54 / 18%) 76%, rgb(8 32 54 / 12%) 100%),
        linear-gradient(0deg, rgb(8 32 54 / 26%), transparent 58%),
        url("{{ asset('image/backgroundlogo.jpg') }}");
      background-size: cover;
      background-position: center center;
      background-repeat: no-repeat
    }

    .hero-copy {
      max-width: 650px;
      padding: clamp(1.25rem, 3vw, 2.25rem);
      border: 1px solid rgb(255 255 255 / 18%);
      border-radius: 20px;
      background: rgb(8 32 54 / 30%);
      box-shadow: 0 20px 60px rgb(3 18 32 / 16%);
      backdrop-filter: blur(3px)
    }

    .hero p {
      color: #d9e6ef;
      max-width: 640px
    }

    .hero .btn {
      transition: transform .18s ease, box-shadow .18s ease, background-color .18s ease
    }

    .hero .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 22px rgb(0 0 0 / 22%)
    }

    .stat-card,
    .surface {
      border: 1px solid #e5ecf1;
      border-radius: 16px;
      background: #fff;
      box-shadow: 0 10px 30px #102b460a
    }

    .stat-card {
      padding: 1.1rem;
      height: 100%;
      min-height: 145px
    }

    .stat-card .stat-icon {
      display: grid;
      place-items: center;
      width: 40px;
      height: 40px;
      margin-bottom: .85rem;
      border-radius: 12px;
      background: #e8f5ee;
      font-size: 1.1rem
    }

    .stat-card strong {
      display: block;
      margin-bottom: .25rem;
      font-size: clamp(1.5rem, 2vw, 1.85rem);
      line-height: 1.1;
      color: var(--navy)
    }

    .stall-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(128px, 1fr));
      gap: .65rem
    }

    .stall-tile {
      min-height: 100px;
      border: 0;
      border-radius: 12px;
      padding: .7rem;
      text-align: left;
      color: #fff;
      transition: transform .15s, box-shadow .15s
    }

    .stall-tile:hover {
      transform: translateY(-2px);
      box-shadow: 0 9px 18px #102b4620
    }

    .stall-available {
      background: #198754
    }

    .stall-occupied {
      background: #c94747
    }

    .stall-pending {
      background: #e58a21
    }

    .stall-inactive {
      background: #788594
    }

    .stall-tile .small {
      opacity: .9
    }

    .section-pad {
      padding: 4rem 0
    }

    .form-control,
    .form-select {
      min-height: 44px
    }

    @media (max-width: 991.98px) {
      .navbar .navbar-collapse {
        margin-top: .75rem;
        padding: .75rem 1rem;
        border: 1px solid rgb(255 255 255 / 12%);
        border-radius: 14px;
        background: rgb(8 32 54 / 94%)
      }

      .hero {
        min-height: 650px;
        background-position: 54% center
      }
    }

    @media (max-width: 575.98px) {
      .hero {
        min-height: 690px;
        padding: 7rem 0 5rem;
        background-position: 55% center
      }

      .hero-copy {
        background: rgb(8 32 54 / 56%)
      }
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container"><a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('landing') }}"><span class="brand-mark"><i class="bi bi-shop-window"></i></span><span>Camalaniugan <small class="d-block fw-normal opacity-75">Public Market</small></span></a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#public-nav" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
      <div class="collapse navbar-collapse" id="public-nav">
        <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
          <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
          <li class="nav-item"><a class="nav-link" href="#buildings">Buildings &amp; stalls</a></li>
          <li class="nav-item"><a class="nav-link" href="#how-to-apply">How to apply</a></li>
          @auth<li class="nav-item"><a class="btn btn-light btn-sm" href="{{ route('dashboard') }}">My dashboard</a></li>
          @else<li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Applicant login</a></li>
          <li class="nav-item"><a class="btn btn-outline-light btn-sm" href="{{ route('admin.login') }}"><i class="bi bi-shield-lock me-1"></i>Admin login</a></li>
          <li class="nav-item"><a class="btn btn-success btn-sm" href="#register">Register</a></li>@endauth
        </ul>
      </div>
    </div>
  </nav>

  <header class="hero">
    <div class="container">
      <div class="row align-items-center g-4">
        <div class="col-lg-8">
          <span class="badge bg-success-subtle text-success-emphasis mb-3">MUNICIPALITY OF CAMALANIUGAN</span>
          <h1 class="display-5 fw-bold">A Clearer Way to Find Your Place in the Market.</h1>
          <p class="lead mt-3">Browse official stall records, check individual rental rates, and submit an application through the municipal market office.</p>
          <div class="d-flex flex-wrap gap-2 mt-2">
            <a href="#buildings" class="btn btn-success btn-lg">Explore Market Stalls <i class="bi bi-arrow-down-short"></i></a>
            @guest<a href="{{ route('admin.login') }}" class="btn btn-outline-light btn-lg"><i class="bi bi-shield-lock me-1"></i>Admin Login</a>@endguest
          </div>
        </div>
      </div>
    </div>
  </header>

  <section class="container" style="margin-top:-30px">
    <div class="row g-3">
      @foreach([['Total Buildings',$stats['buildings'],'bi-buildings'],['Total Recorded Stalls',$stats['stalls'],'bi-grid'],['Available Stalls',$stats['available'],'bi-check-circle'],['Occupied Stalls',$stats['occupied'],'bi-shop'],['Pending Applications',$stats['pending'],'bi-hourglass-split']] as [$label,$value,$icon])
      <div class="col-6 col-md-4 col-xl">
        <div class="stat-card">
          <div class="stat-icon text-success"><i class="bi {{ $icon }}"></i></div><strong>{{ $value }}</strong>
          <div class="small text-secondary">{{ $label }}</div>
        </div>
      </div>
      @endforeach
    </div>
  </section>

  <section id="about" class="section-pad">
    <div class="container">
      <div class="row g-4 align-items-center">
        <div class="col-lg-7"><span class="text-success fw-semibold">ABOUT THE PUBLIC MARKET</span>
          <h2 class="fw-bold mt-2">Local trade, supported by transparent records.</h2>
          <p class="text-secondary">The Municipality of Camalaniugan Public Market connects local vendors and residents. Browse building layouts and published stall rates here; applications are reviewed by authorized municipal administrators.</p>
          <div class="d-flex gap-3 flex-wrap text-secondary"><span><i class="bi bi-shield-check text-success"></i> Official records</span><span><i class="bi bi-phone text-success"></i> Mobile friendly</span><span><i class="bi bi-receipt text-success"></i> Trackable rentals</span></div>
        </div>
        <div class="col-lg-5">
          <div class="surface p-4">
            <h5 class="fw-bold">Stall status guide</h5>
            <div class="d-flex gap-2 flex-wrap mt-3"><span class="badge bg-success">Available</span><span class="badge bg-danger">Occupied</span><span class="badge text-bg-warning">Pending</span><span class="badge bg-secondary">Verify / inactive</span></div>
            <p class="small text-secondary mt-3 mb-0">Records marked for verification are not available for application until confirmed by market staff. Reference-photo highlighting is not used to determine occupancy.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section id="buildings" class="section-pad bg-white">
    <div class="container">
      <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
        <div><span class="text-success fw-semibold">MARKET BUILDINGS</span>
          <h2 class="fw-bold mt-2 mb-0">Browse the stall map</h2>
        </div><span class="text-secondary small">Select a stall to see its record and rental rate.</span>
      </div>
      <ul class="nav nav-pills gap-2 mb-4" role="tablist">
        @foreach($buildings as $index => $building)
        <li class="nav-item"><a class="nav-link {{ ($selectedBuilding ? $selectedBuilding === $building->code : $index === 0) ? 'active' : '' }}" href="{{ route('landing', ['building' => $building->code]) }}#buildings">Building {{ $building->code }}</a></li>
        @endforeach
      </ul>
      @foreach($buildings as $index => $building)
      @if($selectedBuilding ? $selectedBuilding === $building->code : $index === 0)
      <div class="surface p-3 p-md-4">
        <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
          <div>
            <h3 class="h5 fw-bold mb-1">{{ $building->name }}</h3><span class="text-secondary small">{{ $building->description }}</span>
          </div><span class="badge text-bg-light align-self-start">{{ $building->stalls->count() }} recorded stalls</span>
        </div>
        <div class="stall-grid">
          @foreach($building->stalls as $stall)
          @php
          $pending = $stall->applications->isNotEmpty();
          $remaining = $stall->remainingCapacity();
          $status = $stall->needs_verification || $stall->status === 'inactive' ? 'inactive' : ($pending && $remaining === 0 ? 'pending' : ($remaining > 0 ? 'available' : 'occupied'));
          $statusLabel = ['available' => 'Available', 'occupied' => 'Occupied', 'pending' => 'Application pending', 'inactive' => 'Verify record'][$status];
          $suffix = $stall->payment_frequency === 'daily' ? '/day' : '/month';
          @endphp
          <button type="button" class="stall-tile stall-{{ $status }}" data-bs-toggle="modal" data-bs-target="#stallModal"
            data-number="{{ $stall->stall_number }}" data-building="{{ $building->name }}" data-type="{{ $stall->stall_type ?? 'Market stall' }}"
            data-occupant="{{ $stall->occupant_name ?? 'Not recorded' }}" data-rate="₱{{ number_format((float) ($stall->rental_rate ?? $stall->monthly_rate), 2) }}{{ $suffix }}"
            data-status="{{ $statusLabel }}" data-notes="{{ $stall->source_notes ?? '' }}" data-apply="{{ $status === 'available' ? 'yes' : 'no' }}">
            <span class="d-block fw-bold">Stall {{ $stall->stall_number }}</span><span class="small d-block text-truncate">{{ $stall->occupant_name ?? $statusLabel }}</span><span class="small">₱{{ number_format((float) ($stall->rental_rate ?? $stall->monthly_rate), 2) }}{{ $suffix }}</span></button>
          @endforeach
        </div>
      </div>
      @endif
      @endforeach
      @if($buildings->isEmpty())<div class="alert alert-info">No active building records are available. Please contact the market office.</div>@endif
    </div>
  </section>

  <section id="how-to-apply" class="section-pad">
    <div class="container">
      <div class="text-center mb-4"><span class="text-success fw-semibold">APPLICATION PROCESS</span>
        <h2 class="fw-bold mt-2">How to apply for a stall</h2>
      </div>
      <div class="row g-3">
        @foreach([['01','Create your account','Register with your personal details. Applicants must be at least 18 years old.'],['02','Choose an available stall','Sign in to browse the current stall map and submit your business purpose.'],['03','Wait for review','The market administrator checks applications and notifies you of the decision.'],['04','Track your lease','If approved, your lease and recorded payments are available from your dashboard.']] as [$number,$title,$text])
        <div class="col-md-6 col-lg-3">
          <div class="surface p-4 h-100"><span class="text-success fw-bold">{{ $number }}</span>
            <h5 class="fw-bold mt-2">{{ $title }}</h5>
            <p class="text-secondary small mb-0">{{ $text }}</p>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </section>

  @guest
  <section id="register" class="section-pad bg-white">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-xl-9">
          <div class="surface p-4 p-md-5">
            <div class="mb-4"><span class="text-success fw-semibold">APPLICANT REGISTRATION</span>
              <h2 class="h3 fw-bold mt-2">Create your account</h2>
              <p class="text-secondary mb-0">Your annual control number is generated after successful registration.</p>
            </div>
            @include('partials.alerts')
            <form method="POST" action="{{ route('register') }}">@csrf
              @include('partials.registrant-fields', ['registrant' => null])
              <div class="row g-3 mt-1">
                <div class="col-md-6"><label class="form-label">Email address</label><input type="email" name="email" class="form-control" value="{{ old('email') }}" required autocomplete="email"></div>
                <div class="col-md-6"><label class="form-label">Username</label><input name="username" class="form-control" value="{{ old('username') }}" required minlength="3" maxlength="40" autocomplete="username"></div>
                <div class="col-md-6"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required minlength="8" autocomplete="new-password"></div>
                <div class="col-md-6"><label class="form-label">Confirm password</label><input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password"></div>
              </div>
              <button class="btn btn-success btn-lg w-100 mt-4">Register as an applicant</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
  @endguest

  <footer class="py-4 text-center text-secondary small">Municipality of Camalaniugan · Public Market Stall &amp; Rental Management</footer>

  <div class="modal fade" id="stallModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header">
          <div><span class="text-success small fw-semibold">STALL DETAILS</span>
            <h5 class="modal-title fw-bold" id="modal-number"></h5>
          </div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <dl class="row mb-0">
            <dt class="col-5">Building</dt>
            <dd class="col-7" id="modal-building"></dd>
            <dt class="col-5">Stall type</dt>
            <dd class="col-7" id="modal-type"></dd>
            <dt class="col-5">Occupant</dt>
            <dd class="col-7" id="modal-occupant"></dd>
            <dt class="col-5">Rental rate</dt>
            <dd class="col-7" id="modal-rate"></dd>
            <dt class="col-5">Availability</dt>
            <dd class="col-7" id="modal-status"></dd>
          </dl>
          <p id="modal-notes" class="small text-secondary mt-3 mb-0"></p>
        </div>
        <div class="modal-footer">@auth @if(auth()->user()->isApplicant())<a href="{{ route('my.stalls') }}" id="modal-apply" class="btn btn-success">Apply for a stall</a>@endif @else<a href="{{ route('login') }}" id="modal-apply" class="btn btn-success">Login to apply</a>@endauth<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button></div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <script>
    document.getElementById('stallModal').addEventListener('show.bs.modal', function(event) {
      const b = event.relatedTarget;
      ['number', 'building', 'type', 'occupant', 'rate', 'status'].forEach(k => document.getElementById('modal-' + k).textContent = b.dataset[k] || '—');
      document.getElementById('modal-notes').textContent = b.dataset.notes || '';
      document.getElementById('modal-apply').classList.toggle('d-none', b.dataset.apply !== 'yes');
    });
  </script>
</body>

</html>