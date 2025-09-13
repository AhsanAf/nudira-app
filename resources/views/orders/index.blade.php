@extends('layouts.app')
@section('content_title','Admin Dashboard • Orders')

@section('content')
<div class="orders-scope">
  <div class="py-4 py-md-5">
    <div class="container">

      <script>window.initialOrders = @json($ordersJs ?? []);</script>

      <style>
        .orders-scope{
          --c1:#6a95f7; --c2:#63c3e9; --c3:#6ce1b2;
          --panel:#fff; --panel2:#f7f9ff; --muted:#6b7280;
          --radius:1.25rem; --shadow:0 10px 30px rgba(2,6,23,.10);
        }
        .cardx{background:var(--panel); border:0; border-radius:var(--radius); box-shadow:var(--shadow); overflow:hidden}
        .cardx .card-header{
          padding:1rem 1.25rem; color:#fff; border:0;
          background:linear-gradient(90deg,var(--c1),var(--c2),var(--c3));
          position:sticky; top:0; z-index:3;
        }
        .title{display:flex; align-items:center; gap:.6rem; font-weight:800}

        .table-wrap{border:1px solid #eef1f6; border-radius:1rem; overflow:auto; background:#fff}
        thead th{position:sticky; top:0; z-index:2; background:#f7f9ff; border-bottom:1px solid #e8ecfb!important; color:#3b4271; font-weight:800!important; text-transform:uppercase; letter-spacing:.3px; font-size:.8rem}
        tbody td{vertical-align:middle; border-color:#f0f2fa!important}
        tbody tr:hover{background:#f9fbff}

        /* badge status */
        .badge-state{
          display:inline-block;
          padding:.38rem .9rem;
          border-radius:999px;
          font-weight:800;
          font-size:.78rem;
          line-height:1;
          white-space:nowrap;
        }
        .state-on  { background:rgba(59,130,246,.15); color:#1d4ed8; border:1px solid rgba(59,130,246,.30) }
        .state-done{ background:rgba(16,185,129,.15); color:#047857; border:1px solid rgba(16,185,129,.30) }

        .soft{background:var(--panel2); border-radius:.75rem; padding:.6rem .8rem; font-weight:700; color:#374151}
        .hint{font-size:.85rem; color:var(--muted)}
        .toolbar{display:flex; gap:.6rem .75rem; align-items:center; flex-wrap:wrap}
        .toolbar .search{flex:1 1 320px; min-width:260px}

        /* pusatkan kolom status */
        td.status-cell, th.status-cell { text-align:center; }

        /* pagination */
        .pager{display:flex;justify-content:space-between;align-items:center;gap:.75rem;flex-wrap:wrap;margin-top:1rem}
        .pager .info{color:#556;opacity:.85}
        .pager .btn-group .btn{min-width:42px}
      </style>

      <div class="card cardx mb-3">
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center">
            <div class="title"><i class="bi bi-clipboard-check"></i><span>Order Management</span></div>
            <div class="d-flex gap-2">
              <button class="btn btn-light btn-sm" id="exportCsvBtn"><i class="bi bi-download"></i> Ekspor</button>
              <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#addOrderModal"><i class="bi bi-plus-lg"></i> Tambah Order</button>
            </div>
          </div>
        </div>

        <div class="card-body">
          <div class="toolbar mb-3">
            <div class="search input-group">
              <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
              <input id="searchInput" type="search" class="form-control border-start-0" placeholder="Cari judul order…">
            </div>
            <div class="soft">Tanggal dibuat otomatis (hari ini) • Status awal: <b>ON PROGRESS</b></div>
          </div>

          <div class="table-wrap">
            <table class="table table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th style="width:130px">Dibuat</th>
                  <th style="width:130px">Selesai</th>
                  <th>Order</th>
                  <th class="text-end" style="width:140px">Quantity (ton)</th>
                  <th class="status-cell" style="width:140px">Status</th>
                  <th class="text-center" style="width:160px">Aksi</th>
                </tr>
              </thead>
              <tbody id="ordersBody"></tbody>
            </table>
          </div>

          <div id="emptyState" class="text-center py-4 text-secondary d-none">
            <i class="bi bi-inboxes"></i><div class="mt-2">Belum ada order.</div>
          </div>

          {{-- Pagination --}}
          <div class="pager">
            <div id="pageInfo" class="info">Menampilkan 0–0 dari 0</div>
            <div class="btn-group">
              <button id="prevBtn" class="btn btn-outline-secondary btn-sm"><i class="bi bi-chevron-left"></i></button>
              <button id="nextBtn" class="btn btn-outline-secondary btn-sm"><i class="bi bi-chevron-right"></i></button>
            </div>
          </div>
        </div>
      </div>

      {{-- Modal Tambah Order --}}
      <div class="modal fade" id="addOrderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <form id="orderForm" class="modal-content" action="{{ route('admin.orders.store') }}" method="POST" novalidate>
            @csrf
            <div class="modal-header">
              <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Tambah Order</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
              <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem 1.25rem">
                <div class="span-2" style="grid-column:1/-1">
                  <label class="form-label">Order</label>
                  <input type="text" name="judul" class="form-control" placeholder="Contoh: Order ke Bandung" required>
                  <div class="hint mt-1">Tanggal dibuat & status diisi otomatis.</div>
                </div>
                <div>
                  <label class="form-label">Quantity (ton)</label>
                  <input type="number" name="qty_ton" min="0.01" step="0.01" class="form-control" placeholder="cth: 12.50" required>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Batal</button>
              <button class="btn btn-dark" type="submit"><i class="bi bi-save2 me-1"></i>Simpan</button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', ()=>{
  const PAGE_SIZE = 10;     // << ukuran per halaman
  let page = 1;

  const idID = new Intl.DateTimeFormat('id-ID',{day:'2-digit',month:'2-digit',year:'numeric'});
  const nf2  = new Intl.NumberFormat('id-ID',{minimumFractionDigits:2, maximumFractionDigits:2});

  let items = Array.isArray(window.initialOrders) ? window.initialOrders.slice() : [];

  const body   = document.getElementById('ordersBody');
  const empty  = document.getElementById('emptyState');
  const pageInfo = document.getElementById('pageInfo');
  const prevBtn  = document.getElementById('prevBtn');
  const nextBtn  = document.getElementById('nextBtn');

  function fmtDate(iso){
    if(!iso) return '';
    const [y,m,d]=String(iso).split('-').map(Number);
    const dt=new Date(y,(m||1)-1,(d||1));
    return isNaN(dt)?'':idID.format(dt);
  }

  function getView(){
    const q = document.getElementById('searchInput').value.trim().toLowerCase();
    let data = items.filter(o => !q || (o.judul||'').toLowerCase().includes(q));
    // urutkan terbaru dulu (berdasarkan tanggal_dibuat + id)
    data.sort((a,b)=>{
      const ad = a.tanggal_dibuat || '';
      const bd = b.tanggal_dibuat || '';
      if (ad === bd) return (b.id||0) - (a.id||0);
      return ad < bd ? 1 : -1;
    });
    return data;
  }

  function render(){
    const data = getView();
    const total = data.length;
    const maxPage = Math.max(1, Math.ceil(total / PAGE_SIZE));
    if(page > maxPage) page = maxPage;

    const start = (page-1)*PAGE_SIZE;
    const end   = Math.min(total, start+PAGE_SIZE);
    const rows  = data.slice(start, end);

    body.innerHTML = '';
    if(rows.length === 0){
      empty.classList.remove('d-none');
      pageInfo.textContent = 'Menampilkan 0–0 dari 0';
      prevBtn.disabled = nextBtn.disabled = true;
      return;
    }
    empty.classList.add('d-none');

    const frag = document.createDocumentFragment();
    rows.forEach(o=>{
      const tr = document.createElement('tr');
      const badge = o.status==='SELESAI'
        ? '<span class="badge-state state-done">SELESAI</span>'
        : '<span class="badge-state state-on">ON PROGRESS</span>';

      tr.innerHTML = `
        <td>${fmtDate(o.tanggal_dibuat)}</td>
        <td>${fmtDate(o.tanggal_selesai)}</td>
        <td class="fw-semibold">${o.judul||''}</td>
        <td class="text-end">${o.qty_ton!=null? nf2.format(o.qty_ton):''}</td>
        <td class="status-cell">${badge}</td>
        <td class="text-center">
          ${o.status==='SELESAI'
            ? '<button class="btn btn-outline-success btn-sm" disabled><i class="bi bi-check2-circle"></i> Selesai</button>'
            : `<button class="btn btn-success btn-sm btn-finish" data-id="${o.id}"><i class="bi bi-flag"></i> Selesaikan</button>`}
        </td>
      `;
      frag.appendChild(tr);
    });
    body.appendChild(frag);

    pageInfo.textContent = `Menampilkan ${start+1}–${end} dari ${total}`;
    prevBtn.disabled = page<=1;
    nextBtn.disabled = page>=maxPage;
  }

  document.getElementById('searchInput').addEventListener('input', ()=>{ page=1; render(); });

  // Pagination buttons
  prevBtn.addEventListener('click', ()=>{ if(page>1){ page--; render(); }});
  nextBtn.addEventListener('click', ()=>{ page++; render(); });

  // Tambah order (AJAX)
  const form = document.getElementById('orderForm');
  form.addEventListener('submit', async (e)=>{
    e.preventDefault();
    const judul = form.judul.value.trim();
    const qty   = Number(form.qty_ton.value);

    if(!judul || !(qty>0)){
      Swal.fire({icon:'warning', title:'Lengkapi data', text:'Order & Quantity wajib diisi.'});
      return;
    }

    const ask = await Swal.fire({icon:'question', title:'Simpan order?', showCancelButton:true, confirmButtonText:'Simpan'});
    if(!ask.isConfirmed) return;

    try{
      const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      const res = await fetch(`{{ route('admin.orders.store') }}`, {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':token,'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},
        body: JSON.stringify({ judul, qty_ton: qty })
      });
      if(!res.ok){
        let msg='Gagal menyimpan';
        try{ const j=await res.json(); if(j?.errors){ msg = Object.values(j.errors).flat().join('\n'); } else if(j?.message){ msg=j.message; } }catch{}
        return Swal.fire({icon:'error', title:'Gagal', text:msg});
      }
      const { data } = await res.json();
      items.unshift(data);      // taruh paling atas
      page = 1;                 // kembali ke halaman pertama
      render();

      bootstrap.Modal.getInstance(document.getElementById('addOrderModal'))?.hide();
      form.reset();
      Swal.fire({icon:'success', title:'Order dibuat', timer:1400, showConfirmButton:false});
    }catch(err){
      console.error(err);
      Swal.fire({icon:'error', title:'Gagal', text:'Tidak dapat terhubung ke server.'});
    }
  });

  // Selesaikan order
  document.addEventListener('click', async (ev)=>{
    const btn = ev.target.closest('.btn-finish');
    if(!btn) return;

    const id = btn.dataset.id;
    const row = items.find(x=>String(x.id)===String(id));
    if(!row) return;

    const ask = await Swal.fire({
      icon:'question',
      title:'Tandai selesai?',
      html:`<div class="text-start">Order: <b>${row.judul}</b><br>Qty: <b>${nf2.format(row.qty_ton)} ton</b></div>`,
      showCancelButton:true, confirmButtonText:'Selesai'
    });
    if(!ask.isConfirmed) return;

    try{
      const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      const res = await fetch(`{{ url('/admin/orders') }}/${id}/finish`, {
        method:'POST',
        headers:{'X-CSRF-TOKEN':token,'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}
      });
      if(!res.ok){
        let msg='Gagal menyelesaikan';
        try{ const j=await res.json(); if(j?.message) msg=j.message; }catch{}
        return Swal.fire({icon:'error', title:'Gagal', text:msg});
      }
      const { data } = await res.json();
      const idx = items.findIndex(x=>x.id===data.id);
      if(idx>=0) items[idx] = data;
      render();

      Swal.fire({icon:'success', title:'Berhasil diselesaikan', timer:1400, showConfirmButton:false});
    }catch(err){
      console.error(err);
      Swal.fire({icon:'error', title:'Gagal', text:'Tidak dapat terhubung ke server.'});
    }
  });

  // Export CSV
  document.getElementById('exportCsvBtn').addEventListener('click', ()=>{
    const view = getView(); // pakai view setelah filter & sort
    const headers = ['Tgl Dibuat','Tgl Selesai','Order','Qty (ton)','Status'];
    const rows = view.map(o=>[
      o.tanggal_dibuat || '', o.tanggal_selesai || '', o.judul || '', o.qty_ton || '', o.status || ''
    ]);
    const csv = [headers,...rows].map(r=>r.map(s=>`"${String(s).replace(/"/g,'""')}"`).join(',')).join('\r\n');
    const blob=new Blob([csv],{type:'text/csv;charset=utf-8;'});
    const url=URL.createObjectURL(blob);
    const a=Object.assign(document.createElement('a'),{href:url,download:'orders.csv'});
    document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
  });

  render();
});
</script>
@endpush
