@extends('layouts.app')
@section('content_title','Production • Data Grinder')

@section('content')
<div class="grinder-scope">
  <div class="py-4 py-md-5">
    <div class="container">

      {{-- data dari controller --}}
      <script>
        window.GR_RAW         = @json($itemsJs ?? []);
        window.GR_ORDERS_LAST = @json($ordersLatest ?? []);
      </script>

      <style>
        .grinder-scope{
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
        .summary .pill{background:var(--panel2); border:1px solid #e7ebfb; padding:.45rem .7rem; border-radius:999px; font-weight:700}

        .pager{display:flex;justify-content:space-between;align-items:center;gap:.75rem;flex-wrap:wrap;margin-top:1rem}
        .pager .info{color:#556;opacity:.85}
        .pager .btn-group .btn{min-width:42px}

        /* donut kecil */
        .donut-wrap{width:min(280px, 100%); margin:.25rem auto 0}
        #gr-donut{ display:block; width:100% !important; height:150px !important; }

        /* ===== Modern Select (tanpa badge status) ===== */
        .select-group{ display:flex; align-items:center; gap:.6rem; }
        .select-label{ color:#e6f0ff; opacity:.95; font-weight:700; font-size:.9rem; }

        .select-modern{
          position:relative;
          width:min(360px, 100%);
          border:1px solid #dfe7ff;
          border-radius:12px;
          background:rgba(255,255,255,.96);
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
          height:40px; padding:8px 36px 8px 40px;  /* kanan hanya untuk caret */
          line-height:1.2; font-weight:600; font-size:.92rem;
        }
        .select-modern .form-select:focus{ outline:0; box-shadow:none; }
      </style>

      <div class="card cardx">
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center">
            <div class="title"><i class="bi bi-cpu"></i><span>Data Grinder</span></div>
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
          <div class="row g-3">
            <div class="col-lg-7">
              <div class="table-wrap">
                <table class="table table-hover align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Jenis Material</th>
                      <th class="text-end" style="width:140px">Bahan Baku (kg)</th>
                      <th class="text-end" style="width:140px">Hasil Dismill (kg)</th>
                      <th class="text-end" style="width:120px">Residu (kg)</th>
                      <th class="text-end" style="width:120px">Selisih (kg)</th>
                    </tr>
                  </thead>
                  <tbody id="gr-tbody"></tbody>
                </table>
                <div id="gr-empty" class="empty d-none">
                  <i class="bi bi-inboxes"></i>
                  <div class="mt-1">Belum ada data Grinder untuk order ini.</div>
                </div>
              </div>

              <div class="pager">
                <div id="gr-pageinfo" class="info">Menampilkan 0–0 dari 0</div>
                <div class="btn-group">
                  <button id="gr-prev" class="btn btn-outline-secondary btn-sm"><i class="bi bi-chevron-left"></i></button>
                  <button id="gr-next" class="btn btn-outline-secondary btn-sm"><i class="bi bi-chevron-right"></i></button>
                </div>
              </div>
            </div>

            <div class="col-lg-5">
              <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0 fw-bold">Komposisi (%)</h6>
                    <small id="gr-caption" class="text-muted">Order: -</small>
                  </div>
                  <div class="donut-wrap">
                    <canvas id="gr-donut"></canvas>
                  </div>
                  <div class="summary mt-3" id="gr-summary"></div>
                </div>
              </div>
            </div>
          </div> <!-- /row -->
        </div>
      </div>

    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function (){
  const PAGE = 10;
  let page = 1, selectedOrderId = '', donut = null;

  const nf  = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format;
  const nf0 = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format;

  const $ = (id)=> document.getElementById(id);

  function renderSelect(ORD, sel){
    sel.innerHTML = '';
    const list = Array.isArray(ORD) ? [...ORD].sort((a,b)=>Number(b.id)-Number(a.id)).slice(0,10) : [];
    if(list.length===0){
      sel.add(new Option('Tidak ada order',''));
      sel.disabled = true;
      selectedOrderId = '';
      return;
    }
    sel.disabled = false;
    list.forEach((o,i)=>{
      const status = (String(o.status).toUpperCase()==='SELESAI') ? 'SELESAI' : 'ON PROGRESS';
      sel.add(new Option(`${o.judul} (${status})`, o.id, i===0, i===0));
    });
    selectedOrderId = sel.value;
  }

  function aggregate(RAW){
    if(!selectedOrderId) return [];
    const m = new Map();
    for(const r of (RAW||[])){
      if(String(r.order_id)!==String(selectedOrderId)) continue;
      const bahan = Number(r.bahan ?? r.bahan_baku ?? r.bahan_baku_kg) || 0;
      const dis   = Number(r.dismill)||0;
      const res   = Number(r.residu)||0;
      const key   = (r.material||'-').trim() || '-';

      const cur = m.get(key) || {material:key, bahan:0, dismill:0, residu:0, selisih:0};
      cur.bahan   += bahan;
      cur.dismill += dis;
      cur.residu  += res;
      cur.selisih  = cur.bahan - (cur.dismill + cur.residu);
      m.set(key, cur);
    }
    return [...m.values()].sort((a,b)=>a.material.localeCompare(b.material));
  }

  function totals(rows){
    let bahan=0,d=0,r=0;
    rows.forEach(x=>{ bahan+=x.bahan||0; d+=x.dismill||0; r+=x.residu||0; });
    const s = Math.max(0, bahan-(d+r));
    return {bahan,d,r,s};
  }

  function drawChart(canvas, caption, pills, orderTitle, rows){
    const {bahan,d,r,s} = totals(rows);
    caption.textContent = 'Order: ' + (orderTitle || '-');
    pills.innerHTML = `
      <span class="pill">Bahan Baku: <b>${nf(bahan)}</b> kg</span>
      <span class="pill">Dismill: <b>${nf(d)}</b> kg</span>
      <span class="pill">Residu: <b>${nf(r)}</b> kg</span>
      <span class="pill">Selisih: <b>${nf(s)}</b> kg</span>`;

    if(typeof Chart==='undefined' || !canvas) return;
    const data = bahan>0 ? [d/bahan*100, r/bahan*100, s/bahan*100] : [0,0,0];

    if(donut) donut.destroy();
    donut = new Chart(canvas, {
      type:'doughnut',
      data:{
        labels:['Dismill','Residu','Selisih'],
        datasets:[{
          data,
          backgroundColor:['rgba(59,130,246,.7)','rgba(234,179,8,.7)','rgba(34,197,94,.7)'],
          hoverBackgroundColor:['rgba(59,130,246,.9)','rgba(234,179,8,.9)','rgba(34,197,94,.9)'],
          borderWidth:0
        }]
      },
      options:{
        responsive:true,
        maintainAspectRatio:false,   // pakai tinggi CSS
        cutout:'62%',
        animation:{duration:380,easing:'easeOutQuart'},
        plugins:{
          legend:{position:'bottom'},
          tooltip:{callbacks:{label:(c)=>`${c.label}: ${nf0(c.parsed||0)}%`}}
        }
      }
    });
  }

  function render(RAW, ORD){
    const sel   = $('orderSelect');
    const tbody = $('gr-tbody');
    const empty = $('gr-empty');
    const info  = $('gr-pageinfo');
    const prev  = $('gr-prev');
    const next  = $('gr-next');
    const canv  = $('gr-donut');
    const cap   = $('gr-caption');
    const pills = $('gr-summary');

    const rows = aggregate(RAW);
    const ord  = (ORD||[]).find(o=>String(o.id)===String(selectedOrderId));
    const title= ord ? ord.judul : '-';

    if(rows.length===0){
      tbody.innerHTML=''; empty.classList.remove('d-none');
      info.textContent='Menampilkan 0–0 dari 0';
      prev.disabled = next.disabled = true;
      drawChart(canv, cap, pills, title, []);
      return;
    }
    empty.classList.add('d-none');

    const total = rows.length;
    const maxPg = Math.max(1, Math.ceil(total/PAGE));
    if(page>maxPg) page=maxPg;
    const start=(page-1)*PAGE, end=Math.min(total,start+PAGE);
    const view = rows.slice(start,end);

    tbody.innerHTML = view.map(r=>`
      <tr>
        <td class="fw-semibold">${r.material}</td>
        <td class="text-end">${nf(r.bahan)}</td>
        <td class="text-end">${nf(r.dismill)}</td>
        <td class="text-end">${nf(r.residu)}</td>
        <td class="text-end">${nf(r.selisih)}</td>
      </tr>`).join('');

    info.textContent = `Menampilkan ${start+1}–${end} dari ${total}`;
    prev.disabled = page<=1; next.disabled = page>=maxPg;

    drawChart(canv, cap, pills, title, rows);
  }

  function boot(){
    const RAW = Array.isArray(window.GR_RAW) ? window.GR_RAW : [];
    const ORD = Array.isArray(window.GR_ORDERS_LAST) ? window.GR_ORDERS_LAST : [];
    const sel = $('orderSelect');

    renderSelect(ORD, sel);

    sel.addEventListener('change', ()=>{ selectedOrderId = sel.value||''; page=1; render(RAW,ORD); });
    $('gr-prev').addEventListener('click', ()=>{ if(page>1){ page--; render(RAW,ORD); }});
    $('gr-next').addEventListener('click', ()=>{ page++; render(RAW,ORD); });

    render(RAW, ORD);
  }

  if(document.readyState!=='loading') boot();
  else document.addEventListener('DOMContentLoaded', boot);
})();
</script>
@endpush
