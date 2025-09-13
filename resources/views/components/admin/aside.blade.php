{{-- resources/views/layouts/partials/aside.blade.php --}}
<aside id="nudira-aside" class="app-aside">
  <!-- Brand -->
  <a href="{{ route('dashboard') }}" class="brand">
    <img src="{{ asset('img/nudira.png') }}" alt="Logo">
    <h6>Nudira Factory</h6>
  </a>

  <div class="nav-section">

    <a href="{{ route('dashboard') }}"
       class="side-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
      <i class="bi bi-speedometer2 me-2"></i><span>Dashboard</span>
    </a>

    {{-- Inventory --}}
    <a href="#"
       class="side-link caret"
       data-toggle="submenu"
       aria-expanded="{{ request()->routeIs('inventory.*') ? 'true' : 'false' }}"
       data-target="#invMenu">
      <i class="bi bi-basket2 me-2"></i><span>Inventory</span>
      <i class="bi bi-caret-down-fill chev ms-auto"></i>
    </a>
    <ul id="invMenu" class="submenu {{ request()->routeIs('inventory.*') ? 'show' : '' }}">
      <li class="nav-item">
        <a href="{{ route('inventory.logistik.index') }}"
           class="side-link {{ request()->routeIs('inventory.logistik.*') ? 'active' : '' }}">
          <span class="dot"></span>Logistik
        </a>
      </li>
      <li class="nav-item">
        <a href="{{ route('inventory.stokbarang.index') }}"
           class="side-link {{ request()->routeIs('inventory.stokbarang.*') ? 'active' : '' }}">
          <span class="dot"></span>Stok Barang
        </a>
      </li>
    </ul>

    {{-- Production --}}
    <a href="#"
       class="side-link caret"
       data-toggle="submenu"
       aria-expanded="{{ request()->routeIs('production.*') ? 'true' : 'false' }}"
       data-target="#prodMenu">
      <i class="bi bi-building-gear me-2"></i><span>Production</span>
      <i class="bi bi-caret-down-fill chev ms-auto"></i>
    </a>
    <ul id="prodMenu" class="submenu {{ request()->routeIs('production.*') ? 'show' : '' }}">
      <li><a href="{{ route('production.daily.index')   }}" class="side-link {{ request()->routeIs('production.daily.*')   ? 'active' : '' }}"><span class="dot"></span>Data Harian</a></li>
      <li><a href="{{ route('production.grinder.index') }}" class="side-link {{ request()->routeIs('production.grinder.*') ? 'active' : '' }}"><span class="dot"></span>Data Grinder</a></li>
      <li><a href="{{ route('production.mixing.index')  }}" class="side-link {{ request()->routeIs('production.mixing.*')  ? 'active' : '' }}"><span class="dot"></span>Data Mixing</a></li>
      <li><a href="{{ route('production.oven.index')    }}" class="side-link {{ request()->routeIs('production.oven.*')    ? 'active' : '' }}"><span class="dot"></span>Data Oven</a></li>
      <li><a href="{{ route('production.packing.index') }}" class="side-link {{ request()->routeIs('production.packing.*') ? 'active' : '' }}"><span class="dot"></span>Data Packing</a></li>
      <li><a href="{{ route('production.lab.index')     }}" class="side-link {{ request()->routeIs('production.lab.*')     ? 'active' : '' }}"><span class="dot"></span>Data Uji Lab</a></li>
      <li><a href="{{ route('production.oil-log.index') }}" class="side-link {{ request()->routeIs('production.oil-log.*') ? 'active' : '' }}"><span class="dot"></span>Stok Oli</a></li>
    </ul>

    {{-- Admin --}}
    @role('admin')
      <div class="label">Admin</div>
      <a href="{{ route('admin.dashboard') }}" class="side-link" id="admin-dashboard-link">
        <i class="bi bi-speedometer me-2"></i><span>Admin Dashboard</span>
      </a>
      <a href="{{ route('admin.users.index') }}" class="side-link" id="admin-dashboard-link">
        <i class="bi bi-gear me-2"></i><span>Admin Setting</span>
      </a>
    @endrole
  </div>

  <!-- Close button (mobile) -->
  <button type="button" class="aside-close d-lg-none" data-aside-close aria-label="Tutup">
    <i class="bi bi-x-lg"></i>
  </button>
</aside>

{{-- Assets --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
/* ====== Look & feel ====== */
#nudira-aside{
  --g1:#5fa8ff; --g2:#64d1dc; --g3:#72e0b6;
  --bg:#0b1220; --fg:#cbd5e1; --fg2:#97a6c0; --bd:rgba(255,255,255,.06);
  background: radial-gradient(160% 120% at -10% -10%, #0e1a33 0%, var(--bg) 40%, #0a1222 100%);
  color:var(--fg);
  border-radius:1.25rem; padding:18px;
  box-shadow:0 16px 40px rgba(18,27,53,.25), inset 0 0 0 1px var(--bd);
  min-width:260px; max-width:300px;
  position:sticky; top:24px; align-self:start; z-index:1040;
}
#nudira-aside *{box-sizing:border-box}
#nudira-aside a{color:inherit; text-decoration:none}

#nudira-aside .brand{
  display:flex; align-items:center; gap:12px; padding:10px 12px; border-radius:16px;
  background:linear-gradient(135deg,rgba(255,255,255,.06),rgba(255,255,255,.02));
  box-shadow: inset 0 0 0 1px var(--bd);
}
#nudira-aside .brand img{width:28px; height:28px; border-radius:8px; object-fit:contain}
#nudira-aside .brand h6{margin:0; font-weight:800; letter-spacing:.2px; color:#fff}

#nudira-aside .nav-section{margin-top:12px}
#nudira-aside .label{font-size:.72rem; letter-spacing:.4px; text-transform:uppercase; color:var(--fg2); padding:8px 12px}

#nudira-aside .side-link{
  display:flex; align-items:center; gap:12px; padding:10px 12px; border-radius:16px;
  transition:.18s ease; color:var(--fg);
}
#nudira-aside .side-link:hover{ background:rgba(255,255,255,.06); color:#fff }
#nudira-aside .side-link.active{
  color:#0b1a2c; font-weight:700;
  background:linear-gradient(90deg,var(--g1) 0%, var(--g2) 55%, var(--g3) 100%);
  box-shadow:0 6px 18px rgba(100,209,220,.35);
}

/* Chevron */
#nudira-aside .caret .chev{margin-left:auto; transition:transform .22s cubic-bezier(.4,0,.2,1); transform:rotate(-90deg); opacity:.9}
#nudira-aside .caret[aria-expanded="true"] .chev{transform:rotate(0)}

/* ==== Smooth dropdown (height + opacity) ==== */
#nudira-aside .submenu{
  list-style:none; margin:6px 0 10px 34px; padding-left:10px;
  border-left:1px dashed rgba(255,255,255,.14);
  overflow:hidden; display:block;           /* selalu block: animasi berdasarkan height */
  height:0; opacity:0;
  transition: height .30s cubic-bezier(.4,0,.2,1), opacity .20s linear;
  will-change: height, opacity;
}
#nudira-aside .submenu.show{
  height: auto;     /* <- penting supaya tetap terbuka setelah reload */
  opacity: 1;
}   /* height dikelola via inline style JS */
#nudira-aside .submenu .side-link{padding:8px 10px; border-radius:12px; font-size:.95rem; color:#b8c5de}
#nudira-aside .submenu .side-link:hover{color:#fff}
#nudira-aside .submenu .side-link.active{color:#0b1a2c}
#nudira-aside .dot{width:8px; height:8px; border-radius:999px; display:inline-block; margin-right:8px;
  background:linear-gradient(90deg,var(--g2),var(--g3)); box-shadow:0 0 0 2px rgba(255,255,255,.05) inset}

/* Mobile off-canvas */
@media (max-width: 991.98px){
  #nudira-aside{
    position:fixed; inset:14px auto 14px 14px; width:280px;
    transform: translateX(-115%); transition:transform .28s cubic-bezier(.4,0,.2,1);
    border-radius:16px;
  }
  html.aside-open #nudira-aside{ transform: translateX(0) }
  #nudira-aside .aside-close{
    position:absolute; top:10px; right:10px;
    background:rgba(255,255,255,.12); color:#fff; border:0; border-radius:10px; padding:6px 8px;
  }
}

/* Backdrop */
.aside-backdrop{
  position:fixed; inset:0; background:rgba(12,18,32,.45); backdrop-filter:saturate(120%) blur(2px);
  z-index:1035; display:none;
}
html.aside-open .aside-backdrop{ display:block }

/* Aksesibilitas: nonaktifkan animasi jika user minta reduced motion */
@media (prefers-reduced-motion: reduce){
  #nudira-aside .submenu{ transition:none }
  #nudira-aside{ transition:none }
}
/* === OFFSET SUPAYA TIDAK KETIMPA TOPBAR === */
/* Atur default tinggi topbar di sini (ubah kalau perlu) */
:root { --topbar-h: 64px; }

@media (max-width: 991.98px){
  /* Geser aside ke bawah setinggi topbar + 14px margin */
  #nudira-aside{
    position: fixed;
    inset: calc(var(--topbar-h) + 14px) auto 14px 14px; /* TOP | RIGHT | BOTTOM | LEFT */
    width: 280px;
    transform: translateX(-115%);
    transition: transform .28s cubic-bezier(.4,0,.2,1);
    z-index: 1080; /* pastikan di atas konten, tapi di bawah modal bootstrap */
    border-radius: 16px;
  }
  html.aside-open #nudira-aside{ transform: translateX(0) }
}

/* Backdrop dimulai di bawah topbar, biar topbar tetap terlihat */
.aside-backdrop{
  position: fixed;
  inset: var(--topbar-h) 0 0 0; /* top mengikuti tinggi topbar */
  background: rgba(12,18,32,.45);
  backdrop-filter: saturate(120%) blur(2px);
  z-index: 1070;
  display: none;
}
html.aside-open .aside-backdrop{ display:block }
/* === Tighten gap antar item utama (Inventory ↔ Production) === */
#sidebar{ --item-gap: 4px; }             /* ubah angka ini kalau mau lebih rapat/longgar */
#sidebar .nav-item{ 
  margin: var(--item-gap) 0 !important;  /* default kita 6px → jadi 4px */
}

/* Kalau ingin hanya 2 item bertetangga yg sama-sama parent (has-children) yang dirapatkan: */
#sidebar .nav-item.has-children + .nav-item.has-children{
  margin-top: 2px !important;            /* spesifik Inventory → Production */
}

/* Kurangi juga margin bawaan submenu agar tidak menambah jarak visual */
#sidebar .submenu{
  margin: 4px 0 6px 34px !important;     /* sebelumnya 6px 0 10px 34px */
}

/* Jarak sebelum label section (MAIN/ADMIN) tetap nyaman */
#sidebar .label{
  margin-top: 12px !important;
  margin-bottom: 6px !important;
}

</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
/* ===== Smooth submenu toggle (tanpa ubah HTML) ===== */
document.querySelectorAll('#nudira-aside [data-toggle="submenu"]').forEach(link=>{
  const panel = document.querySelector(link.getAttribute('data-target'));
  if(!panel) return;

  document.querySelectorAll('#nudira-aside .submenu.show').forEach(panel => {
  const id = panel.id ? `#${panel.id}` : null;
  if(!id) return;
  const link = document.querySelector(`#nudira-aside [data-toggle="submenu"][data-target="${id}"]`);
  if(link) link.setAttribute('aria-expanded','true');
});

  // Inisialisasi: jika sudah "show" dari server, biarkan terbuka (tanpa height inline)
  if(panel.classList.contains('show')){
    panel.style.height = ''; // biar pakai auto
  }

  link.addEventListener('click', e=>{
    e.preventDefault();
    const isOpen = link.getAttribute('aria-expanded') === 'true';

    if(isOpen){
      // CLOSE: dari height sekarang ke 0
      const start = panel.scrollHeight;
      panel.style.height = start + 'px';    // set current height
      panel.getBoundingClientRect();        // force reflow
      link.setAttribute('aria-expanded','false');
      panel.classList.remove('show');       // opacity -> 0
      panel.style.height = '0px';           // animate height -> 0
      panel.addEventListener('transitionend', function te(ev){
        if(ev.propertyName !== 'height') return;
        panel.style.height = '';            // cleanup
        panel.removeEventListener('transitionend', te);
      }, {once:true});
    }else{
      // OPEN: dari 0 ke target height
      link.setAttribute('aria-expanded','true');
      panel.classList.add('show');          // opacity -> 1 (tetap height:0 sementara)
      const target = panel.scrollHeight;    // ukur konten
      panel.style.height = '0px';
      panel.getBoundingClientRect();        // reflow
      panel.style.height = target + 'px';   // animate ke tinggi konten
      panel.addEventListener('transitionend', function te(ev){
        if(ev.propertyName !== 'height') return;
        panel.style.height = '';            // biar auto setelah animasi
        panel.removeEventListener('transitionend', te);
      }, {once:true});
    }
  });
});

/* ===== Mobile off-canvas ===== */
(function(){
  const html = document.documentElement;
  const aside = document.getElementById('nudira-aside');
  let backdrop = document.querySelector('.aside-backdrop');
  if(!backdrop){
    backdrop = document.createElement('div');
    backdrop.className = 'aside-backdrop d-lg-none';
    document.body.appendChild(backdrop);
  }
  aside.querySelector('[data-aside-close]')?.addEventListener('click', ()=> html.classList.remove('aside-open'));
  backdrop.addEventListener('click', ()=> html.classList.remove('aside-open'));
  document.addEventListener('click', (e)=>{
    const t = e.target.closest('[data-aside-toggle]');
    if(!t) return;
    e.preventDefault();
    html.classList.toggle('aside-open');
  });
  const mq = window.matchMedia('(max-width: 991.98px)');
  const apply = ()=> html.classList.toggle('aside-open', !mq.matches);
  mq.addEventListener?.('change', apply);
  apply();
})();
</script>
@endpush
