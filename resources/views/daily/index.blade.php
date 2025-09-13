{{-- resources/views/production/daily/index.blade.php --}}
@extends('layouts.app')
@section('content_title','Production • Data Harian')

@section('content')
<div class="daily-scope">
  <div class="py-4 py-md-5">
    <div class="container">

      {{-- data awal dari server --}}
      <script>
        window.initialItems  = @json($itemsJs ?? []);
        window.activeOrders  = @json($orders  ?? []);
      </script>

      {{-- scoped CSS --}}
      <style>
        .daily-scope{
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
        .soft{background:var(--panel2); border-radius:.75rem; padding:.6rem .8rem; font-weight:700; color:#374151}

        .table-wrap{border:1px solid #eef1f6; border-radius:1rem; overflow:auto; background:#fff}
        thead th{position:sticky; top:0; z-index:2; background:#f7f9ff; border-bottom:1px solid #e8ecfb!important; color:#3b4271; font-weight:800!important; text-transform:uppercase; letter-spacing:.3px; font-size:.8rem}
        tbody td{vertical-align:middle; border-color:#f0f2fa!important}
        tbody tr:hover{background:#f9fbff}

        .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem 1.25rem}
        .span-2{grid-column:1/-1}
        .badge{font-weight:700}

        /* pagination */
        .pager{display:flex;justify-content:space-between;align-items:center;gap:.75rem;flex-wrap:wrap;margin-top:1rem}
        .pager .info{color:#556;opacity:.85}
        .pager .btn-group .btn{min-width:42px}
      </style>

      <div class="card cardx">
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center">
            <div class="title"><i class="bi bi-kanban"></i><span>Data Harian</span></div>
            <div class="d-flex gap-2">
              <button class="btn btn-light btn-sm" id="exportCsvBtn"><i class="bi bi-download"></i> Ekspor</button>
              <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#addModal"><i class="bi bi-plus-lg"></i> Tambah Data</button>
            </div>
          </div>
        </div>

        <div class="card-body">
          <div class="soft mb-3">
            Tanggal otomatis hari ini •
            <span class="text-primary">Wajib pilih Order berstatus ON PROGRESS</span> (dibuat oleh Admin ).
          </div>

          <div class="table-wrap">
            <table class="table table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th style="width:130px">Tanggal</th>
                  <th>Order</th>
                  <th style="width:120px">Produksi</th>
                  <th style="width:360px">Rincian</th>
                  <th>Keterangan</th>
                </tr>
              </thead>
              <tbody id="tb"></tbody>
            </table>
          </div>

          <div class="pager">
            <div id="pageInfo" class="info">Menampilkan 0–0 dari 0</div>
            <div class="btn-group">
              <button id="prevBtn" class="btn btn-outline-secondary btn-sm"><i class="bi bi-chevron-left"></i></button>
              <button id="nextBtn" class="btn btn-outline-secondary btn-sm"><i class="bi bi-chevron-right"></i></button>
            </div>
          </div>
        </div>
      </div>

      {{-- Modal Tambah --}}
      <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <form id="addForm" class="modal-content" action="{{ route('production.daily.store') }}" method="POST" novalidate>
            @csrf
            <div class="modal-header">
              <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Tambah Data Harian</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
              <div class="form-grid">

                {{-- Order ke (WAJIB; hanya ON PROGRESS) --}}
                <div class="span-2">
                  <label class="form-label">Order ke <span class="text-danger">*</span></label>
                  <select id="orderSelect" name="production_order_id" class="form-select" required>
                    @forelse($orders as $o)
                      <option value="{{ $o->id }}" data-qtyton="{{ $o->qty_ton }}">
                        {{ $o->judul }} — {{ number_format($o->qty_ton,2) }} ton
                      </option>
                    @empty
                      <option value="">(Tidak ada Order ON PROGRESS)</option>
                    @endforelse
                  </select>
                  <div class="form-text">Data harian hanya dapat dibuat untuk Order berstatus ON PROGRESS.</div>
                </div>

                {{-- Jenis produksi --}}
                <div class="span-2">
                  <label class="form-label">Nama Produksi <span class="text-danger">*</span></label>
                  <select id="jenisSelect" name="jenis" class="form-select" required>
                    <option value="" disabled selected>Pilih</option>
                    <option value="mixing">Mixing</option>
                    <option value="oven">Oven</option>
                    <option value="packing">Packing</option>
                    <option value="grind">Grind</option>
                  </select>
                </div>

                {{-- === MIXING === --}}
                <div data-section="mixing" class="d-none">
                  <label class="form-label">Jenis Material</label>
                  <select name="jenis_material" class="form-select">
                    <option value="" disabled selected>Pilih</option>
                    <option>Batok Kelapa</option>
                    <option>Kayu</option>
                    <option>Residu</option>
                    <option>Batok Regrind</option>
                  </select>
                </div>
                <div data-section="mixing" class="d-none">
                  <label class="form-label">Raw Material (kg)</label>
                  <input type="number" step="0.01" min="0" name="raw_material_kg" class="form-control">
                </div>
                <div data-section="mixing" class="d-none">
                  <label class="form-label">Tepung (kg)</label>
                  <input type="number" step="0.01" min="0" name="tepung_kg" class="form-control">
                </div>
                <div data-section="mixing" class="d-none">
                  <label class="form-label">Water Glass (kg)</label>
                  <input type="number" step="0.01" min="0" name="water_glass_kg" class="form-control">
                </div>

                {{-- === OVEN === --}}
                <div data-section="oven" class="d-none">
                  <label class="form-label">Nomor Oven (1–5)</label>
                  <input type="number" min="1" max="5" name="nomor_oven" class="form-control">
                </div>
                <div data-section="oven" class="d-none">
                  <label class="form-label">Berapa yang keluar (kg)</label>
                  <input type="number" step="0.01" min="0" name="keluar_kg" class="form-control">
                    <div class="form-text text-muted">
                      Tulis 0,jika batch tersebut belum selesai
                    </div>
                </div>
                <div data-section="oven" class="d-none">
                  <label class="form-label">Oli terpakai (liter)</label>
                  <input type="number" step="0.01" min="0" name="oli_liter" class="form-control">

                </div>
                <div data-section="oven" class="d-none">
                  <label class="form-label">Durasi oven (jam)</label>
                  <input type="number" step="0.1" min="0" name="durasi_oven_jam" class="form-control">
                </div>

                {{-- === PACKING === --}}
                <div data-section="packing" class="d-none">
                  <label class="form-label">Packing Order (kg)</label>
                  <input type="number" step="0.01" min="0" name="packing_order_kg" id="packing_order_kg" class="form-control" readonly>
                  <div class="form-text">Otomatis = Quantity (ton) dari Order × 1000.</div>
                </div>
                <div data-section="packing" class="d-none">
                  <label class="form-label">Yang sudah terpacking (kg)</label>
                  <input type="number" step="0.01" min="0" name="packed_kg" class="form-control">
                </div>
                <div data-section="packing" class="d-none">
                  <label class="form-label">Barang reject (kg)</label>
                  <input type="number" step="0.01" min="0" name="reject_kg" class="form-control">
                </div>

                {{-- === GRIND === --}}
                <div data-section="grind" class="d-none">
                  <label class="form-label">Jenis Material</label>
                  <select name="jenis_material" class="form-select">
                    <option value="" disabled selected>Pilih</option>
                    <option>Batok Kelapa</option>
                    <option>Kayu</option>
                    <option>Residu</option>
                    <option>Batok Regrind</option>
                  </select>
                </div>
                <div data-section="grind" class="d-none">
                  <label class="form-label">Bahan baku (kg)</label>
                  <input type="number" step="0.01" min="0" name="bahan_baku_kg" class="form-control">
                </div>
                <div data-section="grind" class="d-none">
                  <label class="form-label">Residu keluar (kg)</label>
                  <input type="number" step="0.01" min="0" name="residu_keluar_kg" class="form-control">
                </div>
                <div data-section="grind" class="d-none">
                  <label class="form-label">Hasil dismill (kg)</label>
                  <input type="number" step="0.01" min="0" name="hasil_dismill_kg" class="form-control">
                </div>

                {{-- Keterangan --}}
                <div class="span-2">
                  <label class="form-label">Keterangan</label>
                  <textarea name="keterangan" rows="3" class="form-control" maxlength="100" placeholder="Opsional"></textarea>
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
  const idID = new Intl.DateTimeFormat('id-ID',{day:'2-digit',month:'2-digit',year:'numeric'});
  const nf2  = new Intl.NumberFormat('id-ID',{minimumFractionDigits:2, maximumFractionDigits:2});

  const PAGE = 10;
  let page = 1;
  let items  = Array.isArray(window.initialItems) ? window.initialItems.slice() : [];
  const orders = Array.isArray(window.activeOrders) ? window.activeOrders.slice() : [];
  const hasActiveOrders = orders.length > 0;

  const tb = document.getElementById('tb');
  const pageInfo = document.getElementById('pageInfo');
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');

  function fmtDate(iso){
    if(!iso) return '';
    const [y,m,d]=String(iso).split('-').map(Number);
    const dt=new Date(y,(m||1)-1,(d||1));
    return isNaN(dt)?'':idID.format(dt);
  }
  function badge(text, cls='secondary'){ return `<span class="badge bg-${cls} me-1">${text}</span>`; }
  function orderLabel(title, status){ return title ? (status==='SELESAI' ? `${title} (SUCSESS)` : title) : '-'; }

  function detailCell(r){
    if(r.jenis==='mixing'){
      return [
        badge(`Material: ${r.jenis_material||'-'}`, 'primary'),
        badge(`Raw: ${nf2.format(r.raw_material_kg||0)} kg`, 'info'),
        badge(`Tepung: ${nf2.format(r.tepung_kg||0)} kg`, 'info'),
        badge(`WG: ${nf2.format(r.water_glass_kg||0)} kg`, 'info'),
      ].join(' ');
    }
    if(r.jenis==='oven'){
      return [
        badge(`Oven #${r.nomor_oven||'-'}`, 'dark'),
        badge(`Keluar: ${nf2.format(r.keluar_kg||0)} kg`, 'success'),
        badge(`Oli: ${nf2.format(r.oli_liter||0)} L`, 'warning'),
        badge(`Durasi: ${nf2.format(r.durasi_oven_jam||0)} jam`, 'secondary'),
      ].join(' ');
    }
    if(r.jenis==='packing'){
      return [
        badge(`Order: ${nf2.format(r.packing_order_kg||0)} kg`, 'primary'),
        badge(`Packed: ${nf2.format(r.packed_kg||0)} kg`, 'success'),
        badge(`Reject: ${nf2.format(r.reject_kg||0)} kg`, 'danger'),
      ].join(' ');
    }
    if(r.jenis==='grind'){
      return [
        badge(`Material: ${r.jenis_material||'-'}`, 'primary'),
        badge(`Bahan: ${nf2.format(r.bahan_baku_kg||0)} kg`, 'info'),
        badge(`Residu: ${nf2.format(r.residu_keluar_kg||0)} kg`, 'warning'),
        badge(`Dismill: ${nf2.format(r.hasil_dismill_kg||0)} kg`, 'success'),
      ].join(' ');
    }
    return '';
  }

  function view(){
    const arr = items.slice().sort((a,b)=>{
      const ad=a.tanggal||'', bd=b.tanggal||'';
      if(ad===bd) return (b.id||0) - (a.id||0);
      return ad<bd?1:-1;
    });
    return arr;
  }

  function render(){
    const data = view();
    const total = data.length;
    const max = Math.max(1, Math.ceil(total / PAGE));
    if(page > max) page = max;

    const start = (page-1)*PAGE, end = Math.min(total, start+PAGE);
    const rows  = data.slice(start, end);

    tb.innerHTML = '';
    const frag = document.createDocumentFragment();
    rows.forEach(r=>{
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${fmtDate(r.tanggal)}</td>
        <td>${orderLabel(r.orderTitle, r.orderStatus)}</td>
        <td class="fw-bold text-capitalize">${r.jenis||''}</td>
        <td>${detailCell(r)}</td>
        <td class="text-secondary">${r.ket||''}</td>
      `;
      frag.appendChild(tr);
    });
    tb.appendChild(frag);

    pageInfo.textContent = `Menampilkan ${total? start+1:0}–${end} dari ${total}`;
    prevBtn.disabled = page<=1; nextBtn.disabled = page>=max;
  }

  prevBtn.addEventListener('click', ()=>{ if(page>1){ page--; render(); }});
  nextBtn.addEventListener('click', ()=>{ page++; render(); });

  // ==== FORM: dynamic section & guard wajib order ====
  const jenisSelect = document.getElementById('jenisSelect');
  const orderSelect = document.getElementById('orderSelect');
  const packingOrderKg = document.getElementById('packing_order_kg');
  const addForm = document.getElementById('addForm');
  const addModal = new bootstrap.Modal(document.getElementById('addModal'));

  function showSection(j){
    document.querySelectorAll('[data-section]').forEach(el=>{
      el.classList.toggle('d-none', el.getAttribute('data-section') !== j);
    });
    if(j==='packing') syncPackingOrderKg();
  }
  function syncPackingOrderKg(){
    const opt = orderSelect.options[orderSelect.selectedIndex];
    const ton = Number(opt?.dataset?.qtyton || 0);
    packingOrderKg.value = ton ? (ton*1000).toFixed(2) : '';
  }
  jenisSelect.addEventListener('change', e=> showSection(e.target.value));
  orderSelect.addEventListener('change', ()=>{ if(jenisSelect.value==='packing') syncPackingOrderKg(); });

  // Jika tidak ada order aktif → kunci form & beritahu user
  if(!hasActiveOrders){
    document.querySelectorAll('#addForm input, #addForm select, #addForm textarea, #addForm button[type="submit"]').forEach(el=> el.disabled = true);
    const addModalEl = document.getElementById('addModal');
    addModalEl.addEventListener('shown.bs.modal', () => {
      Swal.fire({
        icon: 'info',
        title: 'Belum ada Order',
        text: 'Hubungi admin untuk membuat order.',
        confirmButtonText: 'Mengerti'
      });
    }, { once:true });
  }

  // Submit (WAJIB ada order & jenis)
  addForm.addEventListener('submit', async (e)=>{
    e.preventDefault();

    const orderId = (orderSelect.value || '').trim();
    const jenis   = (new FormData(addForm).get('jenis') || '').trim();

    if(!orderId){
      return Swal.fire({icon:'warning', title:'Pilih Order', text:'Data Harian hanya bisa disimpan untuk Order ON PROGRESS.'});
    }
    if(!jenis){
      return Swal.fire({icon:'warning', title:'Pilih jenis produksi', text:'Silakan pilih Mixing/Oven/Packing/Grind.'});
    }

    try{
      const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      const res = await fetch(addForm.getAttribute('action'), {
        method:'POST',
        headers:{'X-CSRF-TOKEN':token,'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},
        body: new FormData(addForm)
      });
      if(!res.ok){
        let msg='Gagal menyimpan';
        try{ const j=await res.json(); if(j?.errors){ msg=Object.values(j.errors).flat().join('\n'); } else if(j?.message){ msg=j.message; } }catch{}
        return Swal.fire({icon:'error', title:'Gagal', text:msg});
      }
      const { data } = await res.json();
      items.unshift(data); page=1; render();

      addModal.hide(); addForm.reset();
      document.querySelectorAll('[data-section]').forEach(el=>el.classList.add('d-none'));
      Swal.fire({icon:'success', title:'Tersimpan', timer:1400, showConfirmButton:false});
    }catch(err){
      console.error(err);
      Swal.fire({icon:'error', title:'Gagal', text:'Tidak dapat terhubung ke server.'});
    }
  });

  // Ekspor CSV
  document.getElementById('exportCsvBtn').addEventListener('click', ()=>{
    const headers = ['Tanggal','Order','Produksi','Rincian','Keterangan'];
    const rows = view().map(r=>[
      r.tanggal || '',
      orderLabel(r.orderTitle, r.orderStatus),
      r.jenis || '',
      (()=>{ // ringkas tanpa HTML
        if(r.jenis==='mixing'){
          return `Material:${r.jenis_material||'-'} | Raw:${r.raw_material_kg||0}kg | Tepung:${r.tepung_kg||0}kg | WG:${r.water_glass_kg||0}kg`;
        }
        if(r.jenis==='oven'){
          return `Oven#${r.nomor_oven||'-'} | Keluar:${r.keluar_kg||0}kg | Oli:${r.oli_liter||0}L | Durasi:${r.durasi_oven_jam||0}j`;
        }
        if(r.jenis==='packing'){
          return `Order:${r.packing_order_kg||0}kg | Packed:${r.packed_kg||0}kg | Reject:${r.reject_kg||0}kg`;
        }
        if(r.jenis==='grind'){
          return `Material:${r.jenis_material||'-'} | Bahan:${r.bahan_baku_kg||0}kg | Residu:${r.residu_keluar_kg||0}kg | Dismill:${r.hasil_dismill_kg||0}kg`;
        }
        return '';
      })(),
      r.ket || ''
    ]);
    const csv = [headers,...rows].map(r=>r.map(s=>`"${String(s).replace(/"/g,'""')}"`).join(',')).join('\r\n');
    const blob=new Blob([csv],{type:'text/csv;charset=utf-8;'});
    const url=URL.createObjectURL(blob);
    const a=Object.assign(document.createElement('a'),{href:url,download:'daily.csv'});
    document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
  });

  render();
});
</script>
@endpush
