@extends('layouts.app')
@section('content_title', 'Stok Barang')
@section('content')
<style>
  :root{
    --grad-1:#6a95f7;   /* biru */
    --grad-2:#63c3e9;   /* cyan */
    --grad-3:#6ce1b2;   /* hijau muda */
    --surface:#ffffff;
    --surface-2:#f9fbfd;
    --text:#1f2937;
    --muted:#6b7280;
    --shadow: 0 10px 30px rgba(2,6,23,.08), 0 1px 0 rgba(255,255,255,.6) inset;
    --radius: 1.25rem;
  }

  .panel {
    border: 0; border-radius: var(--radius);
    background: var(--surface);
    box-shadow: var(--shadow);
    overflow: hidden;
    transition: .2s ease;
  }
  .panel:hover { transform: translateY(-2px); box-shadow: 0 14px 40px rgba(2,6,23,.12); }

  .panel-header {
    color:#fff; padding:14px 16px;
    background: linear-gradient(90deg,var(--grad-1),var(--grad-2),var(--grad-3));
  }
  .panel-header .title { font-weight: 600; display:flex; align-items:center; gap:.5rem; }
  .panel-header .badge { background: rgba(255,255,255,.9); color:#111; border-radius:999px; padding:.25rem .7rem; font-weight:600; }

  .soft { background: var(--surface-2); padding:.75rem 1rem; font-weight:600; border-top:1px solid rgba(0,0,0,.05); }
  .table-stok { margin:0; }
  .table-stok tbody tr { border-bottom:1px solid rgba(17,24,39,.06); }
  .table-stok tbody tr:hover { background: rgba(99,102,241,.05); }
  .table-stok td { padding:.85rem 1rem; }

  .pager { display:flex; align-items:center; justify-content:flex-end; gap:.5rem; padding:.75rem 1rem; }
  .pager .page-info{ font-size:.85rem; color:var(--muted); margin-right:auto; }

  @media(min-width:992px){ .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;} }
  @media(max-width:991.98px){ .grid-2{display:grid;gap:1rem;} }
</style>

<div class="container-fluid py-3">

  <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-lg-between mb-3 gap-2">
    <h3 class="fw-semibold mb-0">Stok Barang</h3>
    <form class="d-flex" method="get">
      <input type="search" class="form-control form-control-sm me-2 rounded-pill" name="q" value="{{ $q ?? '' }}" placeholder="Cari nama barang...">
      <button class="btn btn-sm btn-primary rounded-pill px-3">Cari</button>
    </form>
  </div>

  <div class="grid-2">

    <!-- Barang Mentah -->
    <div class="panel">
      <div class="panel-header">
        <div class="d-flex justify-content-between align-items-center">
          <div class="title"><i class="bi bi-box"></i> Barang Mentah</div>
        </div>
      </div>
      <div class="soft">Nama Barang <span class="float-end">Jumlah</span></div>
      <div class="table-responsive">
        <table class="table table-stok mb-0" id="tbl-mentah">
          <tbody>
            @forelse($barangMentah as $b)
              <tr>
                <td class="fw-medium">{{ $b->nama }}</td>
                <td class="text-end fw-semibold">{{ number_format($b->jumlah) }}</td>
              </tr>
            @empty
              <tr><td colspan="2" class="text-center text-muted py-4">Belum ada data.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="pager" data-pager="mentah">
        <span class="page-info"></span>
        <button class="btn btn-sm btn-outline-secondary" data-prev>‹</button>
        <button class="btn btn-sm btn-outline-secondary" data-next>›</button>
      </div>
    </div>

    <!-- Barang Jadi -->
    <div class="panel">
      <div class="panel-header">
        <div class="d-flex justify-content-between align-items-center">
          <div class="title"><i class="bi bi-bag-check"></i> Barang Jadi</div>
        </div>
      </div>
      <div class="soft">Nama Barang <span class="float-end">Jumlah</span></div>
      <div class="table-responsive">
        <table class="table table-stok mb-0" id="tbl-jadi">
          <tbody>
            @forelse($barangJadi as $b)
              <tr>
                <td class="fw-medium">{{ $b->nama }}</td>
                <td class="text-end fw-semibold">{{ number_format($b->jumlah) }}</td>
              </tr>
            @empty
              <tr><td colspan="2" class="text-center text-muted py-4">Belum ada data.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="pager" data-pager="jadi">
        <span class="page-info"></span>
        <button class="btn btn-sm btn-outline-secondary" data-prev>‹</button>
        <button class="btn btn-sm btn-outline-secondary" data-next>›</button>
      </div>
    </div>

  </div>
</div>

<script>
(function(){
  function makePager(tableId,pagerAttr,pageSize=8){
    const tbody=document.querySelector(`#${tableId} tbody`);
    if(!tbody) return;
    const rows=Array.from(tbody.querySelectorAll('tr')).filter(r=>!r.querySelector('.text-muted'));
    const pager=document.querySelector(`.pager[data-pager="${pagerAttr}"]`);
    if(!pager) return;
    let page=1,total=rows.length,totalPages=Math.max(1,Math.ceil(total/pageSize));
    const info=pager.querySelector('.page-info');
    const btnPrev=pager.querySelector('[data-prev]');
    const btnNext=pager.querySelector('[data-next]');

    function render(){
      let start=(page-1)*pageSize,end=start+pageSize;
      rows.forEach((r,i)=>{ r.style.display=(i>=start&&i<end)?'':'none'; });
      info.textContent= total ? `Menampilkan ${start+1}-${Math.min(end,total)} dari ${total}` : 'Tidak ada data';
      btnPrev.disabled=(page<=1); btnNext.disabled=(page>=totalPages);
    }
    btnPrev.onclick=e=>{e.preventDefault(); if(page>1){page--; render();}};
    btnNext.onclick=e=>{e.preventDefault(); if(page<totalPages){page++; render();}};
    render();
  }
  makePager('tbl-mentah','mentah');
  makePager('tbl-jadi','jadi');
})();
</script>
@endsection
