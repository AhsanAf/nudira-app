<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Login • Nudira Factory</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap 5 + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="icon" href="{{ asset('img/nudira.png') }}">

  <style>
    :root{
      --brand:#5b7cfa;
      --brand-2:#7ed7c1;
      --ink:#1d2340;
      --ink-soft:#5d668a;
      --surface: rgba(255,255,255,.66);
      --outline: rgba(17,24,39,.08);
      --ring: rgba(91,124,250,.35);
    }
    @media (prefers-color-scheme: dark){
      :root{
        --ink:#e8edff;
        --ink-soft:#a9b4d9;
        --surface: rgba(22,26,39,.72);
        --outline: rgba(255,255,255,.08);
        --ring: rgba(126,215,193,.35);
      }
      body{ color-scheme: dark; }
    }

    /* Background gradien lembut */
    body{
      min-height:100vh;
      background:
        radial-gradient(1200px 600px at 10% 10%, rgba(126,215,193,.22), transparent 60%),
        radial-gradient(1000px 500px at 90% 20%, rgba(91,124,250,.22), transparent 60%),
        linear-gradient(#f7f9fc,#eef2fb);
      display:grid; place-items:center;
      color:var(--ink);
    }
    @media (prefers-color-scheme: dark){
      body{
        background:
          radial-gradient(1200px 600px at 10% 10%, rgba(126,215,193,.22), transparent 60%),
          radial-gradient(1000px 500px at 90% 20%, rgba(91,124,250,.22), transparent 60%),
          linear-gradient(#0f1420,#0b101b);
      }
    }

    /* Kartu glass */
    .auth-card{
      width:min(920px, 92vw);
      border:1px solid var(--outline);
      background: var(--surface);
      box-shadow:
        0 10px 40px rgba(31,40,79,.18),
        inset 0 0 0 9999px rgba(255,255,255,.02);
      backdrop-filter: blur(10px) saturate(1.1);
      border-radius: 22px;
      overflow: hidden;
    }
    .auth-hero{
      background: linear-gradient(135deg, rgba(91,124,250,.85), rgba(126,215,193,.85));
      position: relative;
      color:#fff;
    }
    .auth-hero::after{
      content:"";
      position:absolute; inset:0;
      background:
        radial-gradient(220px 120px at 20% 25%, rgba(255,255,255,.35), transparent 40%),
        radial-gradient(260px 140px at 80% 75%, rgba(255,255,255,.28), transparent 45%);
      opacity:.8;
    }
    .brand-chip{
      display:inline-flex; align-items:center; gap:.6rem;
      padding:.5rem .8rem; border-radius:999px;
      background: rgba(255,255,255,.18);
      border:1px solid rgba(255,255,255,.35);
      font-weight:700; letter-spacing:.2px;
    }

    /* Input + ikon */
    .has-icon{ position:relative; }
    .has-icon > i{
      position:absolute; left:12px; top:50%; transform:translateY(-50%);
      opacity:.6; pointer-events:none;
    }
    .has-icon .form-control{ padding-left:2.2rem; }

    /* Focus ring */
    .form-control:focus{
      box-shadow: 0 0 0 .25rem var(--ring);
      border-color: transparent;
    }

    /* Tombol brand */
    .btn-brand{
      --bs-btn-bg: var(--brand);
      --bs-btn-border-color: var(--brand);
      --bs-btn-hover-bg: #4a6cf1;
      --bs-btn-hover-border-color: #4a6cf1;
      --bs-btn-active-bg: #3c5be4;
      --bs-btn-active-border-color: #3c5be4;
    }
    .btn-ghost{ background: transparent; border:1px solid var(--outline); }

    .mini{ color:var(--ink-soft); font-size:.9rem; }

    @media (max-width: 991.98px){ .auth-hero{ display:none; } }
  </style>
</head>
<body>

  <div class="card auth-card">
    <div class="row g-0">
      <!-- Panel kiri -->
      <div class="col-lg-5 auth-hero p-4 p-md-5 d-flex flex-column justify-content-between">
        <div>
          <span class="brand-chip">Nudira Factory</span>
          <h2 class="mt-4 fw-bold">Welcome back</h2>
          <p class="mb-0" style="max-width:28ch; opacity:.9">
            Silakan masuk untuk mengakses dashboard dan melanjutkan pekerjaanmu.
          </p>
        </div>
      </div>

      <!-- Panel kanan (form) -->
      <div class="col-lg-7">
        <div class="p-4 p-md-5">
          <h1 class="h3 mb-3 fw-bold text-white">Login</h1>
          <p class="mini mb-4">Gunakan email dan password terdaftar.</p>

          {{-- fallback alert (kalau JS mati) --}}
          @if ($errors->any())
            <div class="alert alert-danger py-2">
              <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <form id="loginForm" method="POST" action="{{ route('login') }}" autocomplete="on">
            @csrf

            <div class="mb-3 has-icon">
              <i class="bi bi-envelope"></i>
              <div class="form-floating">
                <input
                  type="email"
                  class="form-control @error('email') is-invalid @enderror"
                  id="email"
                  name="email"
                  value="{{ old('email') }}"
                  placeholder="name@example.com"
                  required
                >
                <label for="email">Email</label>
                <div class="invalid-feedback">Mohon masukkan email yang valid.</div>
              </div>
            </div>

            <div class="mb-3 has-icon">
              <i class="bi bi-shield-lock"></i>
              <div class="form-floating">
                <input
                  type="password"
                  class="form-control @error('password') is-invalid @enderror"
                  id="password"
                  name="password"
                  placeholder="Password"
                  required
                >
                <label for="password">Password</label>

              </div>
              <button type="button" class="btn btn-ghost position-absolute end-0 top-50 translate-middle-y me-1 px-2 py-1" id="togglePass" aria-label="Tampilkan password">
                <i class="bi bi-eye"></i>
              </button>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label text-white" for="remember">Remember me</label>
              </div>
            </div>

            <button id="loginBtn" type="submit" class="btn btn-brand btn-lg w-100">
              <span class="spinner-border spinner-border-sm me-2 d-none" id="btnSpin" role="status" aria-hidden="true"></span>
              Sign In
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    // Toggle show/hide password
    document.getElementById('togglePass').addEventListener('click', function(){
      const input = document.getElementById('password');
      const icon  = this.querySelector('i');
      const isText = input.type === 'text';
      input.type = isText ? 'password' : 'text';
      icon.className = isText ? 'bi bi-eye' : 'bi bi-eye-slash';
      input.focus();
    });
      // Loading ketika submit login
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('loginForm');
    const btn  = document.getElementById('loginBtn');

    if (form && btn) {
      form.addEventListener('submit', function (e) {
        // Tampilkan loading + kunci tombol agar tidak double submit
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses...';

        Swal.fire({
          title: 'Masuk...',
          text: 'Memverifikasi Data',
          allowOutsideClick: false,
          allowEscapeKey: false,
          didOpen: () => { Swal.showLoading(); }
        });

        // Pastikan Swal sempat render sebelum submit beneran
        // (tanpa preventDefault agar tetap native? Lebih stabil: prevent lalu submit sendiri)
        e.preventDefault();
        setTimeout(() => form.submit(), 100);
      });
    }
  });
  
    // Tampilkan SweetAlert error dari Laravel (validasi/credentials)
    @if ($errors->any())
      window.addEventListener('DOMContentLoaded', () => {
        const errs = @json($errors->all());
        Swal.fire({
          icon: 'error',
          title: 'Gagal Masuk',
          html: errs.map(e => `<div style="text-align:left">${e}</div>`).join(''),
          confirmButtonText: 'OK'
        });
        document.getElementById('email')?.classList.add('is-invalid');
        document.getElementById('password')?.classList.add('is-invalid');
      });
    @endif

    // Optional: sukses/info message (mis. setelah reset password)
    @if (session('status') || session('success'))
      window.addEventListener('DOMContentLoaded', () => {
        Swal.fire({
          icon: 'success',
          title: 'Informasi',
          text: @json(session('status') ?? session('success')),
          timer: 2500,
          showConfirmButton: false
        });
      });
    @endif
  </script>
</body>
</html>
