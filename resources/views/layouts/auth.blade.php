<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#102b46">
  <title>@yield('title') | Camalaniugan Public Market</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root{--portal-navy:#102b46;--portal-green:#198754;--portal-ink:#172b3b;--portal-muted:#63778a}
    *{box-sizing:border-box}
    body{min-height:100vh;margin:0;background:#f3f6f8;color:var(--portal-ink);font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif}
    .auth-shell{min-height:100vh;display:grid;grid-template-columns:minmax(0,1.05fr) minmax(420px,.95fr)}
    .auth-visual{position:relative;display:flex;align-items:flex-end;min-height:100vh;padding:clamp(2rem,6vw,5.5rem);color:#fff;background-image:linear-gradient(180deg,rgba(8,28,48,.24),rgba(8,28,48,.87)),url("{{ asset('image/backgroundlogo.jpg') }}");background-size:cover;background-position:center}
    .auth-visual-content{position:relative;max-width:620px}
    .auth-brand{display:flex;align-items:center;gap:.8rem;color:inherit;text-decoration:none}
    .auth-brand-mark{display:grid;place-items:center;width:48px;height:48px;border-radius:15px;background:#dff3e8;color:var(--portal-green);font-size:1.45rem}
    .auth-brand-name{font-weight:750;line-height:1.15;letter-spacing:.01em}
    .auth-brand-name small{display:block;margin-top:.25rem;font-size:.76rem;font-weight:500;opacity:.8}
    .auth-visual h1{max-width:580px;margin:2.5rem 0 1rem;font-size:clamp(2.2rem,4.2vw,4rem);font-weight:750;line-height:1.08;letter-spacing:-.04em}
    .auth-visual p{max-width:540px;color:rgba(255,255,255,.82);font-size:1.05rem;line-height:1.75}
    .auth-panel{display:flex;align-items:center;justify-content:center;padding:clamp(1.25rem,5vw,4.5rem);background:#f8fafb}
    .auth-card{width:min(100%,460px);padding:clamp(1.5rem,4vw,2.6rem);border:1px solid #e3eaf0;border-radius:22px;background:#fff;box-shadow:0 24px 70px rgba(16,43,70,.10)}
    .auth-eyebrow{display:inline-flex;align-items:center;gap:.45rem;margin-bottom:.8rem;color:var(--portal-green);font-size:.76rem;font-weight:750;letter-spacing:.09em;text-transform:uppercase}
    .auth-card h2{margin-bottom:.55rem;color:var(--portal-navy);font-size:1.75rem;font-weight:750;letter-spacing:-.025em}
    .auth-description{margin-bottom:1.7rem;color:var(--portal-muted);line-height:1.6}
    .auth-card .form-label{margin-bottom:.45rem;color:#30465a;font-size:.9rem;font-weight:650}
    .auth-card .form-control{min-height:48px;border-color:#dbe4eb;border-radius:10px;padding:.7rem .85rem}
    .auth-card .form-control:focus{border-color:#5ca481;box-shadow:0 0 0 .22rem rgba(25,135,84,.12)}
    .auth-input-icon{position:relative}
    .auth-input-icon>.bi{position:absolute;top:50%;left:.9rem;z-index:2;transform:translateY(-50%);color:#718397}
    .auth-input-icon .form-control{padding-left:2.65rem}
    .auth-password-toggle{position:absolute;top:50%;right:.65rem;z-index:3;transform:translateY(-50%);border:0;background:transparent;color:#607487}
    .auth-password-toggle:hover{color:var(--portal-navy)}
    .auth-primary{min-height:49px;border:0;border-radius:10px;background:var(--portal-green);font-weight:700;transition:background .16s,transform .16s}
    .auth-primary:hover{background:#157347;transform:translateY(-1px)}
    .auth-links{display:flex;flex-wrap:wrap;justify-content:space-between;gap:.75rem;margin-top:1.35rem;font-size:.9rem}
    .auth-links a{color:#315c7d;text-decoration:none;font-weight:600}
    .auth-links a:hover{color:var(--portal-green);text-decoration:underline}
    .auth-alert{border-radius:10px;font-size:.9rem}
    .auth-footer{margin-top:1.5rem;color:#8493a1;font-size:.78rem;text-align:center}
    @media(max-width:900px){.auth-shell{grid-template-columns:1fr}.auth-visual{min-height:260px;align-items:flex-start;padding:1.5rem 1.25rem;background-position:center 45%}.auth-visual h1{margin:2rem 0 .55rem;font-size:clamp(1.75rem,6vw,2.55rem)}.auth-visual p{margin-bottom:0;font-size:.92rem}.auth-panel{padding:1.5rem 1rem 2rem}.auth-card{max-width:520px}}
    @media(max-width:480px){.auth-visual{min-height:225px}.auth-visual h1{margin-top:1.35rem}.auth-visual p{font-size:.85rem}.auth-panel{align-items:flex-start}.auth-card{padding:1.35rem;border-radius:17px}}
    @media(prefers-reduced-motion:reduce){*,*::before,*::after{scroll-behavior:auto!important;transition:none!important}}
  </style>
</head>
<body>
  <div class="auth-shell">
    <aside class="auth-visual">
      <div class="auth-visual-content">
        <a href="{{ route('landing') }}" class="auth-brand" aria-label="Camalaniugan Public Market home">
          <span class="auth-brand-mark"><i class="bi bi-shop-window"></i></span>
          <span class="auth-brand-name">Municipality of Camalaniugan<small>Public Market Management</small></span>
        </a>
        <h1>Public service, made easier.</h1>
        <p>Access market services, review stall information, and manage your municipal market account securely.</p>
      </div>
    </aside>
    <main class="auth-panel">
      <section class="auth-card" aria-labelledby="auth-title">
        <div class="auth-eyebrow"><i class="bi @yield('icon','bi-shield-check')"></i>@yield('eyebrow','Secure portal')</div>
        <h2 id="auth-title">@yield('heading')</h2>
        <p class="auth-description">@yield('description')</p>
        @if(session('status'))
          <div class="alert alert-success auth-alert" role="status">{{ session('status') }}</div>
        @endif
        @if($errors->any())
          <div class="alert alert-danger auth-alert" role="alert">
            <ul class="mb-0 ps-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
          </div>
        @endif
        @yield('form')
        @yield('links')
        <div class="auth-footer">Municipality of Camalaniugan · Public Market</div>
      </section>
    </main>
  </div>
  <script>
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
      button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.passwordToggle);
        const showing = input.type === 'password';
        input.type = showing ? 'text' : 'password';
        button.setAttribute('aria-label', showing ? 'Hide password' : 'Show password');
        button.innerHTML = showing ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
      });
    });
  </script>
</body>
</html>
