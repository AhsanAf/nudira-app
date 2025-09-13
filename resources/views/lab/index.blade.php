{{-- resources/views/production/lab/index.blade.php --}}
@extends('layouts.app')
@section('content_title','Production • Data Uji Lab')

@section('content')
<div class="lab-scope">
  <div class="py-4 py-md-5">
    <div class="container page-compact">

      {{-- Header gradient (rounded) --}}
      <div class="app-section-header d-flex align-items-center justify-content-between px-4 py-3 mb-3">
        <div class="d-flex align-items-center gap-2 text-white">
          <i class="bi bi-bezier2"></i>
          <span class="fw-semibold">Data Uji Lab</span>
        </div>
        <button class="btn btn-light btn-sm rounded-3" onclick="window.print()">
          <i class="bi bi-printer me-1"></i> Cetak
        </button>
      </div>

      {{-- Styles --}}
      <style>
        .lab-scope{
          --c1:#5fa8ff; --c2:#64d1dc; --c3:#72e0b6;
          --panel:#fff; --panel2:#f7f9ff;
          --radius:.9rem; --shadow:0 10px 26px rgba(2,6,23,.08);
          --chart-col: 380px;   /* lebar kolom kiri untuk DONUT */
          --chart-max: 360px;   /* ukuran maksimum kanvas donut */
          --fs-s: .875rem;      /* 14px */
          --fs-xs: .8rem;       /* 13px */
        }
        .page-compact{ max-width: 1180px; }
        .app-section-header{
          background: linear-gradient(90deg, #5fa8ff 0%, #64d1dc 50%, #72e0b6 100%);
          color:#fff; border-radius: 1.5rem;
        }
        .cardx{ background:var(--panel); border:0; border-radius:var(--radius); box-shadow:var(--shadow); }
        .soft{ background:var(--panel2); border-radius:.6rem; padding:.5rem .7rem; font-weight:700; color:#374151; font-size:var(--fs-s) }

        /* GRID:
           | input input |
           | chart table | */
        .grid-lab{
          display:grid; gap:1rem;
          grid-template-columns: var(--chart-col) 1fr;
          grid-template-rows: auto auto;
          grid-template-areas:
            "input input"
            "chart table";
          align-items:start;
        }
        .area-input { grid-area: input; }
        .area-chart { grid-area: chart; }
        .area-table { grid-area: table; }
        @media (max-width: 991.98px){
          .grid-lab{
            grid-template-columns: 1fr;
            grid-template-rows: auto auto auto;
            grid-template-areas: "input" "chart" "table";
          }
        }

        .form-label{ font-size:var(--fs-xs); margin-bottom:.25rem }
        .form-text{ font-size:var(--fs-xs) }
        .form-control{ padding:.38rem .55rem; font-size:var(--fs-s) }
        .btn{ font-size:var(--fs-s) }

        /* kiri (field) – kanan (hasil) */
        .split{ display:grid; gap:.8rem; grid-template-columns: minmax(0, 320px) 1fr; }
        @media (max-width:1199.98px){ .split{ grid-template-columns:1fr; } }

        .result-card{ border:1px solid #eef2ff; border-radius:.7rem; padding:.6rem .7rem; background:#fff; }
        .result-card .value{ font-size:1.1rem; font-weight:800; }
        .result-grid{ display:grid; gap:.6rem; grid-template-columns:1fr 1fr; }

        .num-grid{ display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.5rem .75rem; }
        .num-grid .f .form-label{ font-size:.75rem; margin-bottom:.15rem; }
        .num-grid .f .form-control{ padding:.3rem .5rem; font-size:.85rem; }

        .chart-card{ border:1px solid #e8f0ff; border-radius:.8rem; background:#fff; box-shadow:0 6px 16px rgba(2,6,23,.06); padding:.75rem; }
        .chart-card.compact{ max-width:var(--chart-max); }
        .donut-wrap{ width:100%; aspect-ratio:1/1; position:relative }
        .donut-wrap canvas{ width:100%!important; height:100%!important; display:block }

        .table-wrap{ border:1px solid #eef1f6; border-radius:.8rem; background:#fff }
        .table-wrap thead th{
          position:sticky; top:0; z-index:2; background:#f7f9ff;
          border-bottom:1px solid #e8ecfb!important; color:#3b4271;
          font-weight:800!important; text-transform:uppercase; letter-spacing:.3px; font-size:.78rem
        }
        .table-sm td,.table-sm th{ padding:.45rem .6rem; font-size:var(--fs-s) }
        .table-wrap tbody tr:hover{ background:#f9fbff; cursor:pointer }
        @media (max-width:991.98px){ .table-wrap{ overflow:auto; } .table-wrap table{ min-width:780px; } }

        .chip{ display:inline-block; padding:.25rem .55rem; border-radius:999px; font-weight:700;
               background:#f7f9ff; border:1px solid #e8eefc; color:#1f2937; font-size:var(--fs-xs) }
      </style>

      <div class="soft mb-3">
        Masukkan <span class="chip">A</span>, <span class="chip">B</span>, <span class="chip">C</span>, <span class="chip">D</span> lalu <b>Submit</b> untuk menyimpan ke tabel. Grafik menampilkan komposisi sampel aktif.
      </div>

      <div class="grid-lab">

        {{-- INPUT --}}
        <div class="area-input">
          <div class="cardx p-3">
            <div class="d-flex align-items-center gap-2 mb-2">
              <i class="bi bi-pencil-square text-primary"></i>
              <div class="fw-bold" style="font-size:.95rem">Input Sampel</div>
            </div>

            <form id="labForm" method="POST" action="{{ route('production.lab.store') }}" novalidate>
              @csrf

              <div class="split">
                <div>
                  <div class="mb-2">
                    <label class="form-label">Nama Sampel</label>
                    <input type="text" class="form-control" id="sampleName" name="sample_name" placeholder="cth: Briket Batch 12" required>
                  </div>

                  <div class="num-grid">
                    <div class="f">
                      <label class="form-label">A</label>
                      <input type="number" step="0.0001" class="form-control" id="A" name="a" placeholder="0" required>
                      <div class="form-text">berat awal (g)</div>
                    </div>
                    <div class="f">
                      <label class="form-label">B</label>
                      <input type="number" step="0.0001" class="form-control" id="B" name="b" placeholder="0" required>
                      <div class="form-text">kering (g)</div>
                    </div>
                    <div class="f">
                      <label class="form-label">C</label>
                      <input type="number" step="0.0001" class="form-control" id="C" name="c" placeholder="0" required>
                      <div class="form-text">abu (g)</div>
                    </div>
                    <div class="f">
                      <label class="form-label">D</label>
                      <input type="number" step="0.0001" class="form-control" id="D" name="d" placeholder="0" required>
                      <div class="form-text">setelah VM (g)</div>
                    </div>
                  </div>

                  <div class="d-grid mt-2">
                    <button type="submit" class="btn btn-primary">
                      <i class="bi bi-check2-circle me-1"></i> Submit ke Tabel
                    </button>
                  </div>
                </div>

                <div class="result-grid">
                  <div class="result-card">
                    <div class="d-flex justify-content-between align-items-center">
                      <div class="fw-bold">Moisture</div><span class="chip">MC%</span>
                    </div>
                    <div class="value mt-1" id="mcVal">0.00%</div>
                  </div>
                  <div class="result-card">
                    <div class="d-flex justify-content-between align-items-center">
                      <div class="fw-bold">Ash</div><span class="chip">Ash%</span>
                    </div>
                    <div class="value mt-1" id="ashVal">0.00%</div>
                  </div>
                  <div class="result-card">
                    <div class="d-flex justify-content-between align-items-center">
                      <div class="fw-bold">Volatile</div><span class="chip">VM%</span>
                    </div>
                    <div class="value mt-1" id="vmVal">0.00%</div>
                  </div>
                  <div class="result-card">
                    <div class="d-flex justify-content-between align-items-center">
                      <div class="fw-bold">Fixed Carbon</div><span class="chip">FC%</span>
                    </div>
                    <div class="value mt-1" id="fcVal">100.00%</div>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>

        {{-- CHART --}}
        <div class="area-chart">
          <div class="chart-card compact">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <div class="fw-bold" style="font-size:.95rem">Komposisi Sampel (%)</div>
              <div class="text-muted" style="font-size:var(--fs-s)">Sampel: <span id="sampleTitle">-</span></div>
            </div>
            <div class="donut-wrap"><canvas id="donut"></canvas></div>
          </div>
        </div>

        {{-- TABLE --}}
        <div class="area-table">
          <div class="table-wrap">
            <table class="table table-sm table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th style="width:120px">Tanggal Uji</th>
                  <th>Nama Sampel</th>
                  <th class="text-end">MC%</th>
                  <th class="text-end">Ash%</th>
                  <th class="text-end">VM%</th>
                  <th class="text-end">FC%</th>
                  <th class="text-end">A/B/C/D</th>
                </tr>
              </thead>
              <tbody id="tb">
                @forelse($tests as $t)
                  <tr data-mc="{{ (float)$t->mc_pct }}" data-ash="{{ (float)$t->ash_pct }}"
                      data-vm="{{ (float)$t->vm_pct }}" data-fc="{{ (float)$t->fc_pct }}"
                      data-name="{{ $t->sample_name }}">
                    <td>{{ \Illuminate\Support\Carbon::parse($t->tanggal)->translatedFormat('d M Y') }}</td>
                    <td>{{ $t->sample_name }}</td>
                    <td class="text-end {{ $t->mc_pct < 0 ? 'text-danger' : '' }}">{{ number_format((float)$t->mc_pct,2) }}</td>
                    <td class="text-end {{ $t->ash_pct < 0 ? 'text-danger' : '' }}">{{ number_format((float)$t->ash_pct,2) }}</td>
                    <td class="text-end {{ $t->vm_pct < 0 ? 'text-danger' : '' }}">{{ number_format((float)$t->vm_pct,2) }}</td>
                    <td class="text-end {{ $t->fc_pct < 0 ? 'text-danger' : '' }}">{{ number_format((float)$t->fc_pct,2) }}</td>
                    <td class="text-end text-muted">
                      {{ number_format((float)$t->a,2) }}/{{ number_format((float)$t->b,2) }}/{{ number_format((float)$t->c,2) }}/{{ number_format((float)$t->d,2) }}
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>

          @php
            $from = $tests->firstItem() ?? 0;
            $to   = $tests->lastItem() ?? 0;
            $tot  = $tests->total() ?? 0;
          @endphp
          <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2">
            <div class="text-muted" style="font-size:var(--fs-s)">Menampilkan {{ $from }}–{{ $to }} dari {{ $tot }} data</div>
            {{ $tests->onEachSide(1)->withQueryString()->links('pagination::bootstrap-5') }}
          </div>
        </div>

      </div>

      @if(session('success'))
        <script>window.__labSaved = @json(session('success'));</script>
      @endif

    </div>
  </div>
</div>

@php
  $donutData = $donut ?? [
    'labels' => ['Moisture','Ash','Volatile','Fixed C'],
    'data'   => [0,0,0,100],
  ];
@endphp

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', ()=>{
  const nf2 = (n)=> (isFinite(n)? Math.round(n*100)/100 : 0).toFixed(2);
  const $ = (s)=>document.querySelector(s);
  const sampleTitle = $('#sampleTitle');

  // Donut
  const donutData = @json($donutData);
  const donut = new Chart(document.getElementById('donut'), {
    type:'doughnut',
    data:{ labels: donutData.labels, datasets:[{ data: donutData.data, backgroundColor:['#7ea6ff','#f5c84b','#55d59c','#a6b0c6'], borderWidth:0 }] },
    options:{ responsive:true, maintainAspectRatio:true, aspectRatio:1, cutout:'64%',
      plugins:{ legend:{ position:'bottom', labels:{ usePointStyle:true, boxWidth:10, font:{size:12} } } } }
  });

  // Realtime compute
  const ids=['A','B','C','D','sampleName'];
  const get=id=>parseFloat(document.getElementById(id).value)||0;
  const elMc=$('#mcVal'), elAsh=$('#ashVal'), elVm=$('#vmVal'), elFc=$('#fcVal');

  function recompute(){
    const A=get('A'), B=get('B'), C=get('C'), D=get('D');
    const mc=(A>0)?((A-B)/A)*100:0;
    const ash=(B>0)?(C/B)*100:0;
    const vm=(B>0)?((B-D)/B)*100:0;
    let fc=100-(mc+ash+vm); if(!isFinite(fc)) fc=0;

    elMc.textContent=nf2(mc)+'%';
    elAsh.textContent=nf2(ash)+'%';
    elVm.textContent=nf2(vm)+'%';
    elFc.textContent=nf2(fc)+'%';

    sampleTitle.textContent=document.getElementById('sampleName').value||'-';

    donut.data.datasets[0].data=[
      Math.max(mc,0), Math.max(ash,0), Math.max(vm,0), Math.max(fc,0)
    ];
    donut.update('none');
  }
  ids.forEach(id=>document.getElementById(id)?.addEventListener('input',recompute));
  recompute();

  // Klik row => set donut
  document.querySelectorAll('#tb tr[data-mc]').forEach(tr=>{
    tr.addEventListener('click', ()=>{
      const mc=parseFloat(tr.dataset.mc)||0;
      const ash=parseFloat(tr.dataset.ash)||0;
      const vm=parseFloat(tr.dataset.vm)||0;
      const fc=parseFloat(tr.dataset.fc)||0;
      donut.data.datasets[0].data=[Math.max(mc,0),Math.max(ash,0),Math.max(vm,0),Math.max(fc,0)];
      donut.update();
      sampleTitle.textContent=tr.dataset.name||'-';
    });
  });

  // SweetAlert: VALIDASI nama & konfirmasi submit
  const form = document.getElementById('labForm');
  if(form){
    form.addEventListener('submit', async (e)=>{
      e.preventDefault();

      const name = (document.getElementById('sampleName').value || '').trim();
      const A = document.getElementById('A').value || '0';
      const B = document.getElementById('B').value || '0';
      const C = document.getElementById('C').value || '0';
      const D = document.getElementById('D').value || '0';

      // Wajib nama sampel
      if (!name){
        if (window.Swal){
          await Swal.fire({
            icon: 'warning',
            title: 'Nama sampel wajib diisi',
            text: 'Mohon isi Nama Sampel terlebih dahulu.',
            confirmButtonText: 'Mengerti'
          });
        }else{
          alert('Nama sampel wajib diisi');
        }
        document.getElementById('sampleName').focus();
        return;
      }

      // Konfirmasi
      if (window.Swal){
        const res = await Swal.fire({
          title: 'Yakin ingin menambahkan data?',
          html: `
            <div class="text-start">
              Nama Sampel: <b>${name}</b><br>
              A: <b>${A}</b> &nbsp; B: <b>${B}</b><br>
              C: <b>${C}</b> &nbsp; D: <b>${D}</b>
            </div>
          `,
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Ya, simpan',
          cancelButtonText: 'Batal',
          reverseButtons: true,
          focusCancel: true
        });
        if(!res.isConfirmed) return;
      }
      form.submit();
    });
  }

  if (window.__labSaved && window.Swal){
    Swal.fire({icon:'success', title:'Berhasil', text:window.__labSaved, timer:1400, showConfirmButton:false});
  }
});
</script>
@endpush
@endsection
