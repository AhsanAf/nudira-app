@extends('layouts.app')
@section('content_title','Production • Data Mixing')

@section('content')
<div class="mixing-scope">
  <div class="py-4 py-md-5">
    <div class="container">

      {{-- data dari server --}}
      <script>
        window.MIX_RAW       = @json($mixJs ?? []);
        window.GR_ORDERS_LAST = @json($ordersLatest ?? []);
      </script>

      <style>
        .mixing-scope{
          --c1:#6a95f7; --c2:#63c3e9; --c3:#6ce1b2;
          --panel:#fff; --panel2:#f7f9ff; --muted:#6b7280;
          --radius:1.25rem; --shadow:0 10px 30px rgba(2,6,23,.10);
        }
        .cardx{background:var(--panel); border:0; border-radius:var(--radius); box-shadow:var(--shadow); overflow:hidden}
        .cardx .card-header{padding:1rem 1.25rem; color:#fff; border:0; background:linear-gradient(90deg,var(--c1),var(--c2),var(--c3))}
        .title{display:flex; align-items:center; gap:.6rem; font-weight:800}

        .table-wrap{border:1px solid #eef1f6; border-radius:1rem; overflow:auto; background:#fff}
        thead th{position:sticky; top:0; z-index:2; background:#f7f9ff; border-bottom:1px solid #e8ecfb!important; color:#3b4271; font-weight:800!important; text-transform:uppercase; letter-spacing:.3px; font-size:.8rem}
        tbody td{vertical-align:middle; border-color:#f0f2fa!important}
        tbody tr:hover{background:#f9fbff}
        .empty{color:#6b7280; text-align:center; padding:1rem .5rem}

        .summary{display:flex; gap:.75rem; flex-wrap:wrap}
        .summary .pill{background:var(--panel2); border:1px solid #e7ebfb; padding:.5rem .8rem; border-radius:999px; font-weight:700}

        /* Modern select (tanpa badge status) */
        .select-group{ display:flex; align-items:center; gap:.6rem; }
        .select-label{ color:#e6f0ff; opacity:.95; font-weight:700; font-size:.9rem; }
        .select-modern{
          position:relative; width:min(360px,100%);
          border:1px solid #dfe7ff; border-radius:12px; background:rgba(255,255,255,.96);
          box-shadow:inset 0 1px 0 rgba(255,255,255,.6), 0 6px 18px rgba(2,6,23,.08);
          transition: box-shadow .18s ease, border-color .18s ease, transform .18s ease;
        }
        .select-modern:focus-within{
          border-color:#a8c7ff;
          box-shadow:0 0 0 4px rgba(99,195,233,.22), 0 10px 24px rgba(2,6,23,.12);
          transform: translateY(-1px);
        }
        .select-modern .icon{
          position:absolute; left:12px; top:50%; transform:translateY(-50%);
          color:#6b87ff; font-size:1rem; pointer-events:none;
        }
        .select-modern .caret{
          position:absolute; right:10px; top:50%; transform:translateY(-50%) rotate(0deg);
          transition: transform .18s ease; color:#8892a6; font-size:.95rem; pointer-events:none;
        }
        .select-modern:focus-within .caret{ transform:translateY(-50%) rotate(180deg); }
        .select-modern .form-select{
          appearance:none; border:0; background:transparent;
          height:40px; padding:8px 36px 8px 40px;  /* ruang icon+caret */
          line-height:1.2; font-weight:600; font-size:.92rem;
        }
        .select-modern .form-select:focus{ outline:0; box-shadow:none; }
      </style>

      <div class="card cardx">
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center">
            <div class="title"><i class="bi bi-bezier2"></i><span>Data Mixing</span></div>
            <div class="select-group">
              <span class="select-label">Order:</span>
              <div class="select-modern">
                <i class="bi bi-receipt icon"></i>
                <select id="orderSelect" class="form-select form-select-sm"></select>
                <i class="bi bi-chevron-down caret"></i>
              </div>
            </div>
          </div>
        </div>

        <div class="card-body">
          {{-- ringkasan total keseluruhan --}}
          <div id="mix-totals" class="summary mb-3"></div>

          <div class="table-wrap">
            <table class="table table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th>Jenis Material</th>
                  <th class="text-end" style="width:160px">Raw Material (kg)</th>
                  <th class="text-end" style="width:140px">Tepung (kg)</th>
                  <th class="text-end" style="width:160px">Water Glass (kg)</th>
                  <th class="text-end" style="width:140px">Total (kg)</th>
                </tr>
              </thead>
              <tbody id="mix-tbody"></tbody>
            </table>
            <div id="mix-empty" class="empty d-none">
              <i class="bi bi-inboxes"></i>
              <div class="mt-1">Belum ada data Mixing untuk order ini.</div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  const nf = new Intl.NumberFormat('id-ID',{maximumFractionDigits:2}).format;

  const $ = (id)=>document.getElementById(id);

  function buildSelect(orders, sel){
    sel.innerHTML = '';
    const list = Array.isArray(orders) ? [...orders].sort((a,b)=>Number(b.id)-Number(a.id)).slice(0,10) : [];
    if(!list.length){
      sel.add(new Option('Tidak ada order', ''));
      sel.disabled = true;
      return '';
    }
    sel.disabled = false;
    list.forEach((o,i)=>{
      const st = (String(o.status).toUpperCase()==='SELESAI') ? 'SELESAI' : 'ON PROGRESS';
      sel.add(new Option(`${o.judul} (${st})`, o.id, i===0, i===0));
    });
    return sel.value;
  }

  // Ambil angka dengan fallback berbagai nama kolom supaya tahan perubahan schema
  function num(...vals){
    for(const v of vals){ const n = Number(v); if(Number.isFinite(n)) return n; }
    return 0;
  }

  // Agregasi per material untuk 1 order
  function aggregate(raw, orderId){
    const map = new Map();
    for(const r of (raw||[])){
      if(String(r.order_id) !== String(orderId)) continue;

      const material = (r.jenis_material || r.material || '-').trim() || '-';
      const rawKg    = num(r.raw_material_kg, r.raw_material, r.raw_kg, r.raw);
      const tepKg    = num(r.tepung_kg, r.tepung);
      const wgKg     = num(r.water_glass_kg, r.water_glass, r.waterglass_kg);

      const cur = map.get(material) || { material, raw:0, tepung:0, wg:0, total:0 };
      cur.raw    += rawKg;
      cur.tepung += tepKg;
      cur.wg     += wgKg;
      cur.total   = cur.raw + cur.tepung + cur.wg;
      map.set(material, cur);
    }
    return [...map.values()].sort((a,b)=>a.material.localeCompare(b.material));
  }

  function renderTable(rows, tbody, emptyBox){
    if(!rows.length){
      tbody.innerHTML = '';
      emptyBox.classList.remove('d-none');
      return;
    }
    emptyBox.classList.add('d-none');
    tbody.innerHTML = rows.map(r=>`
      <tr>
        <td class="fw-semibold">${r.material}</td>
        <td class="text-end">${nf(r.raw)}</td>
        <td class="text-end">${nf(r.tepung)}</td>
        <td class="text-end">${nf(r.wg)}</td>
        <td class="text-end">${nf(r.total)}</td>
      </tr>
    `).join('');
  }

  function renderTotals(rows, box){
    let raw=0, tep=0, wg=0, ttl=0;
    rows.forEach(r=>{ raw+=r.raw||0; tep+=r.tepung||0; wg+=r.wg||0; ttl+=r.total||0; });
    box.innerHTML = `
      <span class="pill">Total Raw Material: <b>${nf(raw)}</b> kg</span>
      <span class="pill">Total Tepung: <b>${nf(tep)}</b> kg</span>
      <span class="pill">Total Water Glass: <b>${nf(wg)}</b> kg</span>
      <span class="pill">Total Keseluruhan: <b>${nf(ttl)}</b> kg</span>
    `;
  }

  function boot(){
    const RAW = Array.isArray(window.MIX_RAW) ? window.MIX_RAW : [];
    const ORD = Array.isArray(window.GR_ORDERS_LAST) ? window.GR_ORDERS_LAST : [];

    const sel    = $('orderSelect');
    const tbody  = $('mix-tbody');
    const empty  = $('mix-empty');
    const totals = $('mix-totals');

    let selected = buildSelect(ORD, sel);

    function draw(){
      const rows = aggregate(RAW, selected);
      renderTable(rows, tbody, empty);
      renderTotals(rows, totals);
    }

    sel.addEventListener('change', ()=>{ selected = sel.value; draw(); });

    draw();
  }

  if(document.readyState!=='loading') boot();
  else document.addEventListener('DOMContentLoaded', boot);
})();
</script>
@endpush
