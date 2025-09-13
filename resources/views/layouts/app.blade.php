<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>@yield('title','Nudira Factory')</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="{{ asset('img/nudira.png') }}">

  <!-- Bootstrap 5 + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Style global proyek -->
  <link rel="stylesheet" href="{{ asset('css/app-modern.css') }}">

  <style>
    :root{
      --nudira-font: system-ui, -apple-system, "Segoe UI", Roboto, Inter, Ubuntu, "Helvetica Neue", Arial, sans-serif;
      --c1:#5fa8ff; --c2:#64d1dc; --c3:#72e0b6;
      --ink:#1f2a44;
    }
    :root, body, .card, .table, .btn, .form-control, .form-select, .modal,
    .dropdown-menu, .navbar, .pagination, .badge { font-family: var(--nudira-font) !important; }

    .app { display:flex; min-height:100dvh; background:#f6f8fb; color:var(--ink); }
    .main{ flex:1; display:flex; flex-direction:column; min-width:0; }

    .topbar{
      position: sticky; top: 0; z-index: 1050;
      backdrop-filter: blur(10px);
      background: rgba(255,255,255,.85);
      border-bottom: 1px solid rgba(28,39,60,.06);
      box-shadow: 0 6px 18px rgba(28,39,60,.06);
    }
    .topbar .inner{
      display:flex; align-items:center; width:100%;
      padding:.55rem .9rem; gap:.75rem; position:relative;
    }
    .topbar .inner::after{
      content:""; position:absolute; left:.6rem; right:.6rem; bottom:-4px; height:3px;
      background: linear-gradient(90deg, var(--c1), var(--c2) 50%, var(--c3));
      border-radius: 999px; opacity:.25;
    }

    .brand{ display:flex; align-items:center; gap:.5rem; font-weight:800; letter-spacing:.2px }
    .brand .welcome{ white-space:nowrap; }

    .iconbtn{
      display:inline-flex; align-items:center; justify-content:center;
      width:38px; height:38px; border-radius:10px; border:1px solid #e7ebf4;
      background:#fff; color:#415276; transition: all .15s ease;
    }
    .iconbtn:hover{ transform: translateY(-1px); box-shadow:0 6px 16px rgba(28,39,60,.12); }

    .header-actions{ display:flex; align-items:center; gap:.45rem; margin-left:auto; }
    .header-actions .text{ display:none; }
    @media (min-width:768px){ .header-actions .text{ display:inline; } }

    .dot{
      position:absolute; right:6px; top:6px; width:9px; height:9px; border-radius:999px;
      background:linear-gradient(90deg,var(--c1),var(--c3));
      box-shadow:0 0 0 3px #fff;
    }

    .content-wrap{ padding: 1rem .8rem 1.25rem; }
    @media (min-width:768px){ .content-wrap{ padding: 1.25rem 1.25rem 1.6rem; } }

    .modal-message .modal-content{ border:0; border-radius:1rem; box-shadow:0 16px 40px rgba(28,39,60,.18) }
    .modal-message .modal-header{
      border:0; background:linear-gradient(90deg,var(--c1),var(--c2) 50%,var(--c3));
      color:#fff; border-radius:1rem 1rem 0 0; padding:.85rem 1rem;
    }
  </style>

  @stack('styles')
</head>
<body>
  <div class="app">
    @include('components.admin.aside')
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <div class="main">
      <div class="topbar">
        <div class="inner">
          <div class="d-flex align-items-center gap-2">
            <button class="btn btn-light d-lg-none" data-aside-toggle>
  <i class="bi bi-list"></i>
</button>
            <div class="brand">
              <span class="welcome">Welcome, {{ Auth::user()->name ?? 'User' }}</span>
            </div>
          </div>

          <div class="header-actions">
            @role('admin')
              <div class="position-relative">
                <button class="iconbtn" data-bs-toggle="modal" data-bs-target="#messageModal" title="Kirim Pesan">
                  <i class="bi bi-envelope-plus"></i>
                </button>
                <span class="dot" id="msgDot" style="display:none"></span>
              </div>
            @endrole

            <form id="logoutForm" action="{{ route('logout') }}" method="POST" class="d-inline">
              @csrf
              <button type="button" id="logoutBtn" class="btn btn-outline-danger">
                <i class="bi bi-box-arrow-right"></i> <span class="text">Sign out</span>
              </button>
            </form>
          </div>
        </div>
      </div>

      <div class="content-wrap">
        @yield('content')
      </div>
    </div>
  </div>

  @role('admin')
  <div class="modal fade modal-message" id="messageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form id="messageForm" class="modal-content" novalidate>
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-chat-right-dots me-2"></i>Kirim Pesan</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Subject</label>
            <input id="msgSubject" type="text" class="form-control" placeholder="Subjek pesan…" required>
            <div class="invalid-feedback">Subject wajib diisi.</div>
          </div>
          <div class="mb-2">
            <label class="form-label">Pesan</label>
            <textarea id="msgBody" class="form-control" rows="5" placeholder="Tulis pesan untuk tim…" required></textarea>
            <div class="invalid-feedback">Pesan tidak boleh kosong.</div>
          </div>
          <input type="hidden" id="msgTarget" value="all">
        </div>
        <div class="modal-footer">
          <button class="btn btn-light" type="button" data-bs-dismiss="modal">Batal</button>
          <button id="sendMsgBtn" class="btn btn-primary" type="submit">
            <i class="bi bi-send-fill me-1"></i>Kirim Pesan
          </button>
        </div>
      </form>
    </div>
  </div>
  @endrole

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('js/app-modern.js') }}"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
  document.addEventListener('DOMContentLoaded', () => {
    // ===== Logout
    const logoutBtn  = document.getElementById('logoutBtn');
    const logoutForm = document.getElementById('logoutForm');
    if (logoutBtn && logoutForm) {
      logoutBtn.addEventListener('click', () => {
        Swal.fire({
          title: 'Keluar sekarang?',
          text: 'Sesi Anda akan diakhiri.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Ya, logout',
          cancelButtonText: 'Batal',
          reverseButtons: true,
          focusCancel: true
        }).then(res => {
          if(res.isConfirmed){
            Swal.fire({ title:'Sedang keluar…', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });
            logoutForm.submit();
          }
        });
      });
    }

    // ===== Kirim Pesan (Admin)
    const form = document.getElementById('messageForm');
    if (form){
      form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const subjectEl = document.getElementById('msgSubject');
        const bodyEl    = document.getElementById('msgBody');
        const targetEl  = document.getElementById('msgTarget');

        const subject = subjectEl.value.trim();
        const body    = bodyEl.value.trim();
        const target  = (targetEl?.value || 'all');

        subjectEl.classList.toggle('is-invalid', !subject);
        bodyEl.classList.toggle('is-invalid', !body);
        if(!subject || !body) return;

        const esc = s => String(s).replace(/[<>&]/g, c => ({'<':'&lt;','>':'&gt;','&':'&amp;'}[c]));
        const ask = await Swal.fire({
          icon:'question',
          title:'Kirim pesan?',
          html:`<div class="text-start" style="line-height:1.6">
                  <div><b>Subject</b>: ${esc(subject)}</div>
                  <div class="mt-1"><b>Pesan</b>:</div>
                  <div style="white-space:pre-wrap;border:1px dashed #e5e7f3;border-radius:.5rem;padding:.5rem;margin-top:.25rem">${esc(body)}</div>
                </div>`,
          showCancelButton:true,
          confirmButtonText:'Kirim',
          cancelButtonText:'Batal',
          reverseButtons:true
        });
        if(!ask.isConfirmed) return;

        try{
          const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
          const res = await fetch(`{{ route('admin.messages.store') }}`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': token,
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ subject, body, target })
          });

          let j = null;
          try { j = await res.json(); } catch { j = null; }

          if(!res.ok || !(j && j.success)){
            let msg = 'Gagal mengirim pesan.';
            if(j?.errors){
              msg = Object.values(j.errors).flat().join('<br>');
            }else if(j?.message){
              msg = j.message;
            }
            Swal.fire({icon:'error', title:'Gagal', html:msg});
            return;
          }

          // tutup modal + reset
          const modalEl = document.getElementById('messageModal');
          bootstrap.Modal.getInstance(modalEl)?.hide();
          form.reset();

          Swal.fire({ icon:'success', title:'Terkirim', text:'Pesan berhasil dikirim.', timer:1500, showConfirmButton:false });

          // indikator titik
          const dot = document.getElementById('msgDot');
          if(dot) dot.style.display = 'inline-block';

          // ==== FIX PENTING: fallback tidak pakai ?? agar string "" ikut fallback ====
          const valOr = (v, fb) => (v !== undefined && v !== null && String(v).trim() !== '') ? v : fb;

          // broadcast ke Dashboard
          const fromName = @json(Auth::user()->name ?? 'Admin');
          const payload = {
            id:        valOr(j?.id, undefined),
            subject:   valOr(j?.subject, subject),   // <= tidak akan kosong
            body:      valOr(j?.body, body),
            from:      valOr(j?.from, fromName),
            created_at:valOr(j?.created_at, new Date().toLocaleString('id-ID')),
            unread:    true
          };
          window.dispatchEvent(new CustomEvent('message:created', { detail: payload }));
        }catch(err){
          console.error(err);
          Swal.fire({icon:'error', title:'Gagal', text:'Tidak dapat terhubung ke server.'});
        }
      });
    }

    // badge dot dari dashboard
    window.addEventListener('messages:unread-changed', (ev)=>{
      const unread = Number(ev.detail?.unread ?? 0);
      const dot = document.getElementById('msgDot');
      if (!dot) return;
      dot.style.display = unread > 0 ? 'inline-block' : 'none';
    });
  });
  </script>

  @stack('scripts')
</body>
</html>
