{{-- resources/views/dashboard/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container py-3 py-md-4">

  {{-- Inject data dari controller (biarkan apa adanya; notifs boleh tetap dikirim walau tidak dipakai) --}}
  <script>
    window.initialOrders = @json($ordersJs ?? []);
    window.initialNotifs = @json($notifJs ?? []);   // tidak dipakai lagi
    window.initialMsgs   = @json($msgJs ?? []);
  </script>

  <style>
    :root{
      --c1:#5fa8ff; --c2:#64d1dc; --c3:#72e0b6;
      --panel:#fff; --panel2:#f7f9ff;
      --ink:#2b3653; --muted:#6b7280;
      --radius:1rem; --shadow:0 8px 22px rgba(2,6,23,.08);
      --fz-base:.95rem; --fz-small:.84rem; --fz-title:1.05rem;
    }
    body{ background:#f6f8fb; color:var(--ink); font-size:var(--fz-base); }

    /* Header pita kecil */
    .app-header{
      background:linear-gradient(90deg,var(--c1) 0%,var(--c2) 50%,var(--c3) 100%);
      color:#fff; border-radius:1.1rem; padding:.6rem .9rem;
      display:flex; align-items:center; justify-content:space-between; gap:.75rem;
    }
    .app-header .title{ font-size:var(--fz-title); font-weight:700 }
    .app-header .meta{ opacity:.95; font-size:var(--fz-small) }

    /* Kartu umum */
    .cardx{ background:var(--panel); border:0; border-radius:var(--radius); box-shadow:var(--shadow); }
    .soft{ background:var(--panel2); border-radius:.75rem; padding:.55rem .7rem; font-weight:600; color:#3a4657; font-size:.88rem }

    /* Toolbar orders */
    .toolbar{ display:flex; gap:.5rem; flex-wrap:wrap; align-items:center }
    .toolbar .input-group-text{ padding:.4rem .55rem }
    .toolbar .form-control{ padding:.4rem .55rem }
    .toolbar .btn{ padding:.35rem .6rem }

    /* Tabel orders + scroll mobile */
    .orders-scroll{ overflow-x:auto; border-radius:.95rem; border:1px solid #eef1f6; background:#fff; }
    .table-modern{ width:100%; }
    .table-modern thead th{
      position:sticky; top:0; z-index:2; background:#f7f9ff!important;
      border-bottom:1px solid #e8ecfb!important; color:#3b4271;
      text-transform:uppercase; letter-spacing:.3px; font-size:.78rem; font-weight:800;
    }
    .table-modern td, .table-modern th{ padding:.7rem .9rem }
    .table-modern tbody tr + tr td{ border-top:1px dashed rgba(0,0,0,.06) }
    #orderTable{ min-width: 720px; }

    .small-muted{ font-size:.86rem; color:#6e7591 }

    /* List pesan */
    .list-clean .item{ display:flex; gap:.6rem; align-items:flex-start; padding:.6rem .75rem; border-radius:.7rem; border:1px solid #eef1f6; background:#fff }
    .list-clean .item + .item{ margin-top:.55rem }
    .list-clean .icon{ width:32px; height:32px; border-radius:.55rem; display:inline-flex; align-items:center; justify-content:center; font-size:.95rem }
    .icon-blue{ background:#e9f2ff; color:#1769ff; border:1px solid #cfe2ff }
    .icon-green{ background:#e8f8ee; color:#0b8455; border:1px solid #cfeedd }
    .badge-soft{ background:#f2f4ff; color:#4251a3; border:1px solid #e1e6ff; border-radius:999px; padding:.15rem .5rem; font-weight:700; font-size:.72rem }

    /* Grid: kiri (orders) / kanan (pesan) */
    @media (min-width: 992px){
      .dashboard-grid{ display:grid; grid-template-columns: 2fr 1fr; gap: .75rem 1rem; }
      .grid-left{ order:1 }
      .grid-right{ order:2 }
    }

    .px-compact{ padding-left:.85rem!important; padding-right:.85rem!important }
    .pt-compact{ padding-top:.7rem!important }
    .pb-compact{ padding-bottom:.7rem!important }

    .mini-pager{ display:flex; justify-content:space-between; align-items:center; gap:.5rem; flex-wrap:wrap; }
  </style>

  {{-- Header pita --}}
  <div class="app-header mb-3">
    <div class="d-flex align-items-center gap-2">
      <i class="bi bi-speedometer2"></i>
      <span class="title">Dashboard</span>
    </div>
    <div class="meta"><span id="nowId">-</span></div>
  </div>

  {{-- GRID --}}
  <div class="dashboard-grid">
    {{-- LEFT: Orders --}}
    <div class="grid-left">
      <div class="cardx pt-compact pb-compact">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-compact">
          <div class="fw-semibold"><i class="bi bi-list-check me-1"></i>Order Terbaru</div>

          <div class="toolbar" style="max-width:760px">
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-white border-end-0"><i class="bi bi-calendar"></i></span>
              <input id="dateFrom" type="date" class="form-control border-start-0">
              <span class="input-group-text">s.d.</span>
              <input id="dateTo" type="date" class="form-control">
            </div>
            <div class="input-group input-group-sm flex-grow-1" style="min-width:230px">
              <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
              <input id="orderSearch" type="search" class="form-control border-start-0" placeholder="Cari order / status…">
            </div>
            <button id="resetOrder" class="btn btn-outline-secondary btn-sm">
              <i class="bi bi-arrow-counterclockwise"></i> Reset
            </button>
          </div>
        </div>

        <div class="orders-scroll mt-2">
          <table class="table table-modern table-hover align-middle mb-0" id="orderTable">
            <thead>
              <tr>
                <th style="min-width:130px">Tanggal</th>
                <th style="min-width:180px">Order</th>
                <th class="text-center" style="min-width:130px">Quantity (Tons)</th>
                <th style="min-width:140px">Status</th>
              </tr>
            </thead>
            <tbody id="orderBody"></tbody>
          </table>
        </div>

        <div class="d-flex justify-content-between align-items-center px-compact pt-2 flex-wrap gap-2">
          <div class="small-muted" id="orderInfo">Menampilkan 0–0 dari 0</div>
          <div class="btn-group">
            <button class="btn btn-outline-secondary btn-sm" id="prevOrder"><i class="bi bi-chevron-left"></i></button>
            <button class="btn btn-outline-secondary btn-sm" id="nextOrder"><i class="bi bi-chevron-right"></i></button>
          </div>
        </div>
      </div>
    </div>

    {{-- RIGHT: Pesan saja --}}
    <div class="grid-right">
      <div class="cardx p-2">
        <div class="d-flex align-items-center justify-content-between px-compact mb-2">
          <div class="fw-semibold"><i class="bi bi-envelope-fill me-1"></i>Pesan dari Admin</div>
          <div class="d-flex align-items-center gap-2">
            <button id="markAllMsg" class="btn btn-light btn-sm"><i class="bi bi-check2-all"></i> Tandai semua</button>
          </div>
        </div>

        <div id="msgList" class="list-clean px-compact pb-2"></div>

        <div class="mini-pager px-compact pb-2 pt-1">
          <div class="small-muted" id="msgInfo">Menampilkan 0–0 dari 0</div>
          <div class="btn-group btn-group-sm">
            <button id="prevMsg" class="btn btn-outline-secondary"><i class="bi bi-chevron-left"></i></button>
            <button id="nextMsg" class="btn btn-outline-secondary"><i class="bi bi-chevron-right"></i></button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function(){
  /* Waktu realtime di header kecil */
  const idNow = ()=> new Date().toLocaleString('id-ID',{weekday:'long',day:'2-digit',month:'long',year:'numeric',hour:'2-digit',minute:'2-digit'});
  const $now = document.getElementById('nowId'); if($now){ $now.textContent = idNow(); setInterval(()=>{ $now.textContent = idNow(); }, 60000); }

  const CSRF = document.querySelector('meta[name="csrf-token"]').content;

  /* ===== State dari controller ===== */
  const orders = (window.initialOrders || []).slice();
  const msgs   = (window.initialMsgs   || []).slice();

  /* ===== PESAN (kanan) ===== */
  const PER_PAGE = 5;
  window.msgPagination = window.msgPagination || { perPage: PER_PAGE, current: 1, total: msgs.length };

  const $msgList = document.getElementById('msgList');
  const $msgInfo = document.getElementById('msgInfo');
  const $prevMsg = document.getElementById('prevMsg');
  const $nextMsg = document.getElementById('nextMsg');

  function sortByTimeDesc(a,b){
    const ta = (a.created_at || a.time || '').toString();
    const tb = (b.created_at || b.time || '').toString();
    return ta===tb ? 0 : (ta < tb ? 1 : -1);
  }
  function getMsgsSorted(){ const c = msgs.slice(); c.sort(sortByTimeDesc); return c; }

  function renderMsgs(){
    const p = window.msgPagination;
    const all = getMsgsSorted();
    const total = all.length;
    const maxPage = Math.max(1, Math.ceil(total / p.perPage));
    if (p.current > maxPage) p.current = maxPage;

    const start = (p.current - 1) * p.perPage;
    const rows  = all.slice(start, start + p.perPage);

    $msgList.innerHTML = '';
    if (rows.length === 0){
      $msgList.innerHTML = `<div class="text-center text-secondary small p-2">Belum ada pesan.</div>`;
    } else {
      rows.forEach(m=>{
        const unread = !!m.unread;
        const when   = m.created_at || m.time || '';
        const from   = m.from || 'Admin';
        const el = document.createElement('div');
        el.className = 'item';
        el.innerHTML = `
          <div class="icon ${unread?'icon-blue':'icon-green'}"><i class="bi ${unread?'bi-envelope-fill':'bi-envelope-open'}"></i></div>
          <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-start gap-2">
              <div>
                <div class="fw-semibold">${(m.subject||'(Tanpa subjek)')}</div>
                <div class="small text-secondary">${from} • <span>${when}</span></div>
              </div>
              <div class="d-flex align-items-center gap-1">
                ${unread && m.id ? `<button class="btn btn-outline-success btn-sm btnMarkRead" data-id="${m.id}"><i class="bi bi-check2"></i></button>` : ''}
              </div>
            </div>
            <div class="small text-muted mt-1" style="white-space:pre-wrap">${(m.body||'').toString()}</div>
          </div>`;
        $msgList.appendChild(el);
      });
    }

    $msgInfo.textContent = total ? `Menampilkan ${start+1}–${Math.min(total, start+p.perPage)} dari ${total}` : 'Menampilkan 0–0 dari 0';
    if ($prevMsg) $prevMsg.disabled = (p.current <= 1);
    if ($nextMsg) $nextMsg.disabled = (p.current >= maxPage);
  }

  if ($prevMsg) $prevMsg.addEventListener('click', ()=>{ window.msgPagination.current = Math.max(1, window.msgPagination.current - 1); renderMsgs(); });
  if ($nextMsg) $nextMsg.addEventListener('click', ()=>{ window.msgPagination.current = window.msgPagination.current + 1; renderMsgs(); });

  // Tandai satu pesan terbaca (AJAX)
  if ($msgList) $msgList.addEventListener('click', async (ev)=>{
    const btn = ev.target.closest('.btnMarkRead');
    if(!btn) return;
    const id = Number(btn.dataset.id || 0);
    if(!id) return;

    try{
      const res = await fetch(`{{ route('messages.markRead') }}`, {
        method:'POST',
        headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept':'application/json' },
        body: JSON.stringify({ id })
      });
      const j = await res.json();
      if(!res.ok || !j?.success) throw new Error(j?.message || 'Gagal menandai baca.');

      const i = msgs.findIndex(x=>Number(x.id)===id);
      if (i > -1) msgs[i].unread = false;

      renderMsgs();
      window.dispatchEvent(new CustomEvent('messages:unread-changed', { detail:{ unread: j.unread ?? undefined }}));
    }catch(e){
      Swal.fire({icon:'error', title:'Gagal', text: e.message || 'Tidak dapat menghubungi server.'});
    }
  });

  // Tandai semua pesan terbaca
  const $markAllMsg = document.getElementById('markAllMsg');
  if ($markAllMsg) $markAllMsg.addEventListener('click', async ()=>{
    const ask = await Swal.fire({icon:'question', title:'Tandai semua pesan terbaca?', showCancelButton:true, confirmButtonText:'Ya', cancelButtonText:'Batal'});
    if(!ask.isConfirmed) return;

    try{
      const res = await fetch(`{{ route('messages.markAllRead') }}`, {
        method:'POST',
        headers:{ 'X-CSRF-TOKEN': CSRF, 'Accept':'application/json' }
      });
      const j = await res.json();
      if(!res.ok || !j?.success) throw new Error(j?.message || 'Gagal menandai semua.');

      msgs.forEach(m => { m.unread = false; });
      window.msgPagination.current = 1;
      renderMsgs();

      window.dispatchEvent(new CustomEvent('messages:unread-changed', { detail:{ unread: j.unread ?? 0 }}));
      Swal.fire({icon:'success', title:'Selesai', timer:1100, showConfirmButton:false});
    }catch(e){
      Swal.fire({icon:'error', title:'Gagal', text:e.message || 'Tidak dapat menghubungi server.'});
    }
  });

  // Event pesan baru (dari header) — tanpa reload
  window.addEventListener('message:created', (ev)=>{
    const d = ev.detail || {};
    if (d.id){
      const exist = msgs.findIndex(x => Number(x.id) === Number(d.id));
      if (exist > -1) msgs.splice(exist, 1);
    }
    msgs.unshift({
      id: d.id,
      subject: d.subject || '(Tanpa subjek)',
      body: d.body || '',
      from: d.from || 'Admin',
      created_at: d.created_at || new Date().toLocaleString('id-ID'),
      unread: true
    });
    window.msgPagination.current = 1;
    renderMsgs();
  });

  /* ===== ORDERS kiri ===== */
  const PAGE=6; let page=1;
  const $from = document.getElementById('dateFrom');
  const $to   = document.getElementById('dateTo');
  const $q    = document.getElementById('orderSearch');
  const $body = document.getElementById('orderBody');
  const $info = document.getElementById('orderInfo');

  const idFmt = new Intl.DateTimeFormat('id-ID',{ day:'2-digit', month:'2-digit', year:'numeric' });
  const nf    = new Intl.NumberFormat('id-ID');
  const within=(iso,from,to)=>{ if(!from && !to) return true; if(from && iso<from) return false; if(to && iso>to) return false; return true; };

  function getOrdersView(){
    const from=$from.value, to=$to.value, q=($q.value||'').toLowerCase();
    return orders
      .filter(o=>{
        const okDate = within(o.t,from,to);
        const okText = !q || String(o.order).toLowerCase().includes(q) || String(o.st).toLowerCase().includes(q);
        return okDate && okText;
      })
      .sort((a,b)=> (a.t===b.t)?0:(a.t<b.t?1:-1));
  }

  function renderOrders(){
    const view = getOrdersView();
    const total = view.length;
    const maxPage = Math.max(1, Math.ceil(total/PAGE));
    if(page>maxPage) page=maxPage;
    const start=(page-1)*PAGE, end=Math.min(total,start+PAGE);

    $body.innerHTML='';
    view.slice(start,end).forEach(o=>{
      const tr=document.createElement('tr');
      tr.innerHTML=`
        <td>${idFmt.format(new Date(o.t))}</td>
        <td class="fw-semibold">${o.order}</td>
        <td class="text-center">${nf.format(Number(o.qty||0))}</td>
        <td>${o.st}</td>`;
      $body.appendChild(tr);
    });

    $info.textContent = total?`Menampilkan ${start+1}–${end} dari ${total}`:'Menampilkan 0–0 dari 0';
    document.getElementById('prevOrder').disabled = page<=1;
    document.getElementById('nextOrder').disabled = page>=maxPage;
  }

  ['input','change'].forEach(evt=>{
    $from.addEventListener(evt, ()=>{ page=1; renderOrders(); });
    $to  .addEventListener(evt, ()=>{ page=1; renderOrders(); });
    $q   .addEventListener(evt, ()=>{ page=1; renderOrders(); });
  });
  document.getElementById('resetOrder').addEventListener('click', ()=>{ $from.value=''; $to.value=''; $q.value=''; page=1; renderOrders(); });
  document.getElementById('prevOrder').addEventListener('click', ()=>{ page=Math.max(1,page-1); renderOrders(); });
  document.getElementById('nextOrder').addEventListener('click', ()=>{ page=page+1; renderOrders(); });

  renderOrders();
  renderMsgs();
})();
</script>
@endpush
@endsection
