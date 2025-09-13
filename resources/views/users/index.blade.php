@extends('layouts.app')

@section('content')
<div class="container py-4 py-md-5">

  {{-- ====== STYLE ====== --}}
  <style>
    :root{ --c1:#5fa8ff; --c2:#64d1dc; --c3:#72e0b6; --panel:#fff; --panel2:#f7f9ff; --radius:1.25rem; --shadow:0 10px 26px rgba(2,6,23,.08); }
    body{ background:#f6f8fb; }
    .app-section-header{ background:linear-gradient(90deg,var(--c1) 0%,var(--c2) 50%,var(--c3) 100%); color:#fff; border-radius:1.5rem; }

    .cardx,.table-card{ background:var(--panel); border:0; border-radius:var(--radius); box-shadow:var(--shadow); max-width:100%; }
    .table-card{ overflow:hidden; }
    .soft{ background:var(--panel2); border-radius:.75rem; padding:.6rem .8rem; font-weight:600; color:#3a4657 }

    .cards-wrap{ --gap:1rem; display:flex; flex-wrap:wrap; gap:var(--gap); }
    .cards-wrap .col-card{ flex:1 1 calc(25% - var(--gap)); min-width:210px; }
    .stat-card{ border:0; border-radius:1rem; box-shadow:0 6px 16px rgba(28,39,60,.08); }
    .stat-card .label{ color:#6b7280; font-size:.85rem }
    .stat-card .value{ font-size:1.6rem; font-weight:800 }

    .form-label{ font-size:.9rem }
    .form-control{ padding:.48rem .6rem }
    .input-group .form-control{ min-width:0; } /* anti overflow mobile */

    .role-badge{ font-weight:700; border-radius:999px; padding:.2rem .6rem; font-size:.8rem }
    .role-admin{ background:#e9f2ff; color:#1769ff }
    .role-staff{ background:#e8f8ee; color:#118c4f }

    .table-modern thead th{
      position:sticky; top:0; z-index:2; background:#f7f9ff!important;
      border-bottom:1px solid #e8ecfb!important; color:#3b4271;
      text-transform:uppercase; letter-spacing:.3px; font-size:.78rem; font-weight:800;
    }
    .table-modern td, .table-modern th{ padding:.8rem 1rem }
    .table-modern tbody tr + tr td{ border-top:1px dashed rgba(0,0,0,.06) }

    /* Tabel: scroll horizontal di layar sempit */
    .table-wrap{ border:1px solid #eef1f6; border-radius:1rem; background:#fff;
      overflow-x:auto; overflow-y:hidden; -webkit-overflow-scrolling:touch; }
    .table-modern{ min-width:720px; }

    /* Toolbar (filter + search) */
    .tools{ display:flex; align-items:center; gap:.5rem; flex-wrap:wrap; }
    .tools .role{ flex:0 0 auto; min-width:140px; }
    .tools .search{ flex:1 1 260px; max-width:420px; min-width:160px; }

    /* Grid 2 kolom (1 kolom di mobile) */
    .admin-grid{ display:grid; grid-template-columns:1fr; gap:1rem; }
    .admin-grid > *{ min-width:0; }
    @media (min-width:992px){
      .admin-grid{ grid-template-columns:1fr 2fr; align-items:start; }
    }
  </style>

  {{-- ===== HEADER ===== --}}
  <div class="app-section-header d-flex align-items-center justify-content-between px-4 py-3 mb-4">
    <div class="d-flex align-items-center gap-2">
      <i class="bi bi-gear" style="font-size:1.1rem"></i>
      <span class="fw-semibold">Admin Setting</span>
    </div>
    <div class="d-flex align-items-center gap-2">
      <span class="small text-white-50">Hanya Admin/High Admin</span>
    </div>
  </div>

  {{-- ===== STATS ===== --}}
  <div class="cards-wrap mb-4">
    <div class="col-card"><div class="card stat-card h-100"><div class="card-body">
      <div class="label mb-1"><i class="bi bi-people me-1"></i>Total User</div>
      <div class="value" id="statTotal">{{ number_format($stats['total'] ?? 0) }}</div>
    </div></div></div>
    <div class="col-card"><div class="card stat-card h-100"><div class="card-body">
      <div class="label mb-1"><i class="bi bi-shield-check me-1"></i>Admin</div>
      <div class="value" id="statAdmin">{{ number_format($stats['admin'] ?? 0) }}</div>
    </div></div></div>
    <div class="col-card"><div class="card stat-card h-100"><div class="card-body">
      <div class="label mb-1"><i class="bi bi-person-badge me-1"></i>Staff</div>
      <div class="value" id="statStaff">{{ number_format($stats['staff'] ?? 0) }}</div>
    </div></div></div>
    <div class="col-card"><div class="card stat-card h-100"><div class="card-body">
      <div class="label mb-1"><i class="bi bi-clock-history me-1"></i>Terakhir Update</div>
      <div class="value" id="statUpdated">
        {{ optional($stats['lastUpdate'] ?? null)->timezone('Asia/Jakarta')->format('d/m/Y, H.i') ?? '-' }}
      </div>
    </div></div></div>
  </div>

  {{-- ===== GRID: FORM + TABEL ===== --}}
  <div class="admin-grid">
    {{-- FORM BUAT AKUN --}}
    <section>
      <div class="cardx p-3 h-100">
        <div class="d-flex align-items-center gap-2 mb-2">
          <i class="bi bi-person-plus text-primary"></i><div class="fw-bold">Buat Akun</div>
        </div>

        @if ($errors->any())
          <div class="alert alert-danger py-2">
            <ul class="mb-0 small">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form id="createForm" method="POST" action="{{ route('admin.users.store') }}" novalidate>
          @csrf
          <div class="mb-3">
            <label class="form-label">Nama</label>
            <input type="text" name="name" id="name" class="form-control" placeholder="Nama lengkap" required value="{{ old('name') }}">
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-control" placeholder="nama@email.com" required value="{{ old('email') }}">
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <div class="input-group">
              <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
              <button class="btn btn-outline-secondary" type="button" id="togglePass"><i class="bi bi-eye"></i></button>
            </div>
            <div class="form-text">Minimal 8 karakter.</div>
          </div>
          <div class="mb-3">
            <label class="form-label d-block">Role</label>
            <div class="btn-group" role="group">
              <input type="radio" class="btn-check" name="role" id="roleStaff" value="staff" {{ old('role','staff')==='staff'?'checked':'' }}>
              <label class="btn btn-outline-success" for="roleStaff"><i class="bi bi-person-badge me-1"></i>Staff</label>
              <input type="radio" class="btn-check" name="role" id="roleAdmin" value="admin" {{ old('role')==='admin'?'checked':'' }}>
              <label class="btn btn-outline-primary" for="roleAdmin"><i class="bi bi-shield-check me-1"></i>Admin</label>
            </div>
            <div class="form-text">Staff tidak bisa akses Admin Dashboard & Admin Setting.</div>
          </div>

          {{-- bawa filter/query saat kembali (untuk UX) --}}
          <input type="hidden" name="q" value="{{ $q }}">
          <input type="hidden" name="role_filter" value="{{ $role }}">

          <div class="d-grid">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-save2 me-1"></i>Simpan Akun
            </button>
          </div>
        </form>
      </div>
    </section>

    {{-- TABEL USER --}}
    <section>
      <div class="card table-card h-100">
        <div class="card-body p-0">
          <div class="d-flex align-items-center gap-2 p-3 pb-0 flex-wrap">
            <div class="fw-semibold me-auto">Data User</div>

            {{-- TOOLBAR: role (kiri) + search (kanan), tanpa tombol --}}
            <form id="searchTools" action="{{ route('admin.users.index') }}" method="GET"
                  class="tools ms-auto">
              <!-- <select class="form-select form-select-sm role" name="role" aria-label="Filter role">
                <option value="">Semua Role</option>
                <option value="admin" @selected($role==='admin')>Admin</option>
                <option value="staff" @selected($role==='staff')>Staff</option>
              </select> -->

              <div class="input-group input-group-sm search">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="search" class="form-control" name="q" value="{{ $q }}"
                       placeholder="Cari nama/email…" autocomplete="off">
              </div>
            </form>
          </div>

          <div class="table-wrap mt-3">
            <table class="table table-modern table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th style="min-width:160px">Nama</th>
                  <th style="min-width:200px">Email</th>
                  <th style="width:110px">Role</th>
                  <th style="width:130px">Dibuat</th>
                  <th style="width:170px" class="text-end">Aksi</th>
                </tr>
              </thead>
              <tbody>
                @forelse($users as $u)
                  <tr>
                    <td>{{ $u->name }}</td>
                    <td>{{ $u->email }}</td>
                    <td>
                      <span class="role-badge {{ ($u->role ?? '')==='admin' ? 'role-admin' : 'role-staff' }}">
                        {{ ucfirst($u->role ?? 'staff') }}
                      </span>
                    </td>
                    <td>{{ $u->created_at?->timezone('Asia/Jakarta')->format('d-m-Y') }}</td>
                    <td class="text-end">
                      <div class="d-inline-flex gap-2">
                        {{-- Toggle role (admin<->staff) --}}
                        <form method="POST" action="{{ route('admin.users.update',$u) }}" id="formRole-{{ $u->id }}">
                          @csrf @method('PATCH')
                          <input type="hidden" name="name" value="{{ $u->name }}">
                          <input type="hidden" name="email" value="{{ $u->email }}">
                          <input type="hidden" name="role" value="{{ ($u->role ?? '')==='admin' ? 'staff' : 'admin' }}">
                          <input type="hidden" name="q" value="{{ $q }}">
                          <input type="hidden" name="role_filter" value="{{ $role }}">
                        </form>
                        <button type="button"
                                class="btn btn-sm btn-outline-secondary me-1 btnToggleRole"
                                data-id="{{ $u->id }}"
                                data-name="{{ $u->name }}"
                                data-newrole="{{ ($u->role ?? '')==='admin' ? 'STAFF' : 'ADMIN' }}"
                                title="Ubah role">
                          <i class="bi bi-person-gear"></i>
                        </button>

                        {{-- Reset password (modal) --}}
                        <button type="button"
                                class="btn btn-sm btn-outline-primary me-1 btnResetPw"
                                data-name="{{ $u->name }}"
                                data-url="{{ route('admin.users.reset',$u) }}"
                                title="Ubah password">
                          <i class="bi bi-key"></i>
                        </button>

                        {{-- Hapus user --}}
                        <form method="POST" action="{{ route('admin.users.destroy',$u) }}" id="formDelete-{{ $u->id }}">
                          @csrf @method('DELETE')
                          <input type="hidden" name="q" value="{{ $q }}">
                          <input type="hidden" name="role_filter" value="{{ $role }}">
                        </form>
                        <button type="button"
                                class="btn btn-sm btn-outline-danger btnDelete"
                                data-id="{{ $u->id }}"
                                data-name="{{ $u->name }}"
                                title="Hapus">
                          <i class="bi bi-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada data.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <div class="d-flex justify-content-between align-items-center p-3 border-top flex-wrap gap-2">
            <div class="small text-muted">
              Menampilkan {{ $users->firstItem() }}–{{ $users->lastItem() }} dari {{ $users->total() }} user
            </div>
            <nav>{{ $users->onEachSide(1)->links() }}</nav>
          </div>
        </div>
      </div>
    </section>
  </div>
</div>

{{-- ===== MODAL RESET PASSWORD ===== --}}
<div class="modal fade" id="modalPass" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content rounded-4 border-0">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-key me-1"></i>Ubah Password</h5>
        <button class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2">
          <label class="form-label">User</label>
          <input class="form-control" id="passUser" value="-" disabled>
        </div>
        <form id="formResetPw" method="POST" action="#">
          @csrf @method('PATCH')
          <div class="mb-2">
            <label class="form-label">Password Baru</label>
            <input type="password" class="form-control" id="newPass" name="new_password" placeholder="••••••••">
          </div>
          <div class="mb-2">
            <label class="form-label">Konfirmasi Password</label>
            <input type="password" class="form-control" id="newPass2" placeholder="••••••••">
          </div>
          <div class="form-text">Minimal 8 karakter.</div>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-primary" id="savePassBtn">Simpan</button>
      </div>
    </div>
  </div>
</div>

{{-- ===== JS + SweetAlert ===== --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  // Toggle password input
  document.getElementById('togglePass')?.addEventListener('click', function(){
    const i = document.getElementById('password');
    const show = i.type === 'password';
    i.type = show ? 'text' : 'password';
    this.innerHTML = show ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
  });

  // Flash dari server
  @if (session('success')) Swal.fire({icon:'success', title:@json(session('success')), timer:1300, showConfirmButton:false}); @endif
  @if (session('error'))   Swal.fire({icon:'error',   title:@json(session('error'))}); @endif
  @if ($errors->any())
    Swal.fire({icon:'error', title:'Validasi gagal', html:`<ul style="text-align:left;margin:0;padding-left:18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>`});
  @endif

  // Konfirmasi buat akun
  const createForm = document.getElementById('createForm');
  createForm?.addEventListener('submit', async (e)=>{
    e.preventDefault();
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const role  = (document.getElementById('roleAdmin').checked ? 'ADMIN' : 'STAFF');
    if(!name || !email) return Swal.fire({icon:'warning', title:'Lengkapi form terlebih dahulu'});
    const ok = await Swal.fire({
      title:'Simpan akun baru?',
      html:`Nama: <b>${name}</b><br>Email: <b>${email}</b><br>Role: <b>${role}</b>`,
      icon:'question', showCancelButton:true, confirmButtonText:'Ya, simpan', cancelButtonText:'Batal', reverseButtons:true
    });
    if(ok.isConfirmed) createForm.submit();
  });

  // Toggle Role
  document.addEventListener('click', async (e)=>{
    const b = e.target.closest('.btnToggleRole');
    if(!b) return;
    const ok = await Swal.fire({
      title:'Ubah Role',
      html:`Ganti role <b>${b.dataset.name}</b> menjadi <b>${b.dataset.newrole}</b>?`,
      icon:'question', showCancelButton:true, confirmButtonText:'Ya, ubah', cancelButtonText:'Batal', reverseButtons:true
    });
    if(ok.isConfirmed) document.getElementById('formRole-'+b.dataset.id).submit();
  });

  // Reset Password (modal)
  const modalPass = new bootstrap.Modal(document.getElementById('modalPass'));
  const formResetPw = document.getElementById('formResetPw');
  document.addEventListener('click', (e)=>{
    const b = e.target.closest('.btnResetPw');
    if(!b) return;
    document.getElementById('passUser').value = b.dataset.name || '-';
    formResetPw.action = b.dataset.url;
    document.getElementById('newPass').value = ''; document.getElementById('newPass2').value = '';
    modalPass.show();
  });
  document.getElementById('savePassBtn')?.addEventListener('click', async ()=>{
    const p1 = document.getElementById('newPass').value.trim();
    const p2 = document.getElementById('newPass2').value.trim();
    if(p1.length < 8) return Swal.fire({icon:'warning', title:'Password minimal 8 karakter'});
    if(p1 !== p2)     return Swal.fire({icon:'warning', title:'Konfirmasi tidak sama'});
    const ok = await Swal.fire({icon:'question', title:'Simpan password baru?', showCancelButton:true, confirmButtonText:'Simpan', cancelButtonText:'Batal', reverseButtons:true});
    if(ok.isConfirmed) formResetPw.submit();
  });

  // Hapus user
  document.addEventListener('click', async (e)=>{
    const b = e.target.closest('.btnDelete');
    if(!b) return;
    const ok = await Swal.fire({
      title:'Hapus akun?', html:`Akun <b>${b.dataset.name}</b> akan dihapus permanen.`,
      icon:'warning', showCancelButton:true, confirmButtonText:'Ya, hapus', cancelButtonText:'Batal', reverseButtons:true
    });
    if(ok.isConfirmed) document.getElementById('formDelete-'+b.dataset.id).submit();
  });

  // ====== LIVE SEARCH (tanpa tombol) ======
  (function () {
    const form = document.getElementById('searchTools');
    if (!form) return;
    const roleSel = form.querySelector('select[name="role"]');
    const qInput  = form.querySelector('input[name="q"]');

    // Debounce
    const debounce = (fn, ms=350) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; };

    // Pakai URL navigation agar selalu fresh dan kompatibel
    const navigate = () => {
      const url = new URL(window.location.href);
      const q = (qInput.value || '').trim();
      const r = roleSel.value || '';
      if (q) url.searchParams.set('q', q); else url.searchParams.delete('q');
      if (r) url.searchParams.set('role', r); else url.searchParams.delete('role');
      url.searchParams.delete('page'); // reset pagination
      window.location.assign(url.toString());
    };

    roleSel.addEventListener('change', navigate);
    qInput.addEventListener('input', debounce(navigate, 350));
  })();
</script>
@endsection

