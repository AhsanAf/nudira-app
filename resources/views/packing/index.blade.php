{{-- resources/views/production/packing/index.blade.php --}}
@extends('layouts.app')
@section('content_title','Production • Data Packing')

@section('content')
<div class="packing-scope">
  <div class="py-4 py-md-5">
    <div class="container">

      {{-- ====== SCOPED CSS (seragam + donut compact bulat) ====== --}}
      <style>
        .packing-scope{
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

        /* tabel & pager ala daily */
        .table-wrap{border:1px solid #eef1f6; border-radius:1rem; overflow:auto; background:#fff}
        .table-wrap thead th{
          position:sticky; top:0; z-index:2; background:#f7f9ff;
          border-bottom:1px solid #e8ecfb!important; color:#3b4271;
          font-weight:800!important; text-transform:uppercase; letter-spacing:.3px; font-size:.8rem
        }
        .table-wrap tbody td{vertical-align:middle; border-color:#f0f2fa!important}
        .table-wrap tbody tr:hover{background:#f9fbff}
        .pager{display:flex;justify-content:space-between;align-items:center;gap:.75rem;flex-wrap:wrap;margin-top:1rem}
        .pager .info{color:#556;opacity:.85}

        /* donut compact – BULAT SEMPURNA */
        .chart-card{
          background:#fff; border:1px solid #e8f0ff; border-radius:1rem;
          box-shadow:0 6px 16px rgba(2,6,23,.06); padding:1rem 1rem .75rem;
        }
        .chart-card.chart-compact{
          max-width: 420px;     /* lebar kartu donut */
          margin: 1rem auto 0;  /* center */
        }
        /* wrapper menjaga rasio 1:1 agar donut bulat */
        .donut-wrap{
          width: 100%;
          aspect-ratio: 1 / 1;      /* <— kunci bulat */
          position: relative;
        }
        .donut-wrap canvas{
          width: 100% !important;
          height: 100% !important;  /* isi penuh wrapper square */
          display: block;
        }
        .chart-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:.25rem}
        .chart-head .title{color:#111; font-weight:800; font-size:1.05rem;}
        .legend-note{font-size:.9rem;color:#6b7280}

        /* chips kecil di bawah donut */
        .mini-chips{display:flex;gap:.75rem;flex-wrap:wrap;justify-content:center;margin:.75rem 0 0}
        .mini-chip{
          background:#f7f9ff;border:1px solid #e8eefc;color:#1f2937;
          padding:.5rem .9rem;border-radius:999px;font-weight:700;box-shadow:0 3px 8px rgba(2,6,23,.05);
        }
      </style>

      <div class="card cardx">
        {{-- ====== HEADER + ORDER SELECTOR ====== --}}
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="title"><i class="bi bi-box-seam"></i><span>Data Packing</span></div>
            <form method="GET" action="{{ route('production.packing.index') }}" class="d-flex align-items-center gap-2">
              <label class="fw-semibold me-1">Order:</label>
              <select name="order_id" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                @if(($ordersOn ?? collect())->isEmpty() && ($ordersDone ?? collect())->isEmpty())
                  <option value="">(Tidak ada order)</option>
                @else
                  @if(($ordersOn ?? collect())->isNotEmpty())
                    <optgroup label="ON PROGRESS">
                      @foreach($ordersOn as $o)
                        <option value="{{ $o->id }}" {{ (int)$selectedOrderId === (int)$o->id ? 'selected' : '' }}>
                          {{ $o->judul }} (ON PROGRESS)
                        </option>
                      @endforeach
                    </optgroup>
                  @endif
                  @if(($ordersDone ?? collect())->isNotEmpty())
                    <optgroup label="SELESAI (10 terakhir)">
                      @foreach($ordersDone as $o)
                        <option value="{{ $o->id }}" {{ (int)$selectedOrderId === (int)$o->id ? 'selected' : '' }}>
                          {{ $o->judul }} (SELESAI)
                        </option>
                      @endforeach
                    </optgroup>
                  @endif
                @endif
              </select>
            </form>
          </div>
        </div>

        <div class="card-body">
          {{-- ====== KARTU INFO ====== --}}
          <div class="soft mb-3">
            <div class="d-flex flex-wrap align-items-center gap-3">
              <div><b>Sisa yang harus dibuat</b></div>
              <div class="fs-4 fw-bold">{{ number_format((float)$sisaHarusDibuat, 2) }} kg</div>
              @if($order)
                <div class="text-muted small">
                  (Target: {{ number_format((float)$orderQtyKg, 2) }} kg • Terpacking: {{ number_format((float)$totalPacked, 2) }} kg)
                </div>
              @endif
            </div>
          </div>

          {{-- ====== TABEL HARIAN ====== --}}
          <div class="table-wrap">
            <table class="table table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th style="width:130px">Tanggal</th>
                  <th>Yang keluar dari oven</th>
                  <th>Yang terpacking</th>
                  <th>Barang Reject</th>
                  <th>Selisih</th>
                </tr>
              </thead>
              <tbody>
              @forelse($rows as $r)
                <tr>
                  <td>{{ \Illuminate\Support\Carbon::parse($r->tanggal)->translatedFormat('d M Y') }}</td>
                  <td>{{ number_format((float)$r->oven_out, 2) }}</td>
                  <td>{{ number_format((float)$r->packed, 2) }}</td>
                  <td class="text-danger">{{ number_format((float)$r->reject, 2) }}</td>
                  <td class="{{ $r->selisih < 0 ? 'text-danger' : 'text-success' }}">{{ number_format((float)$r->selisih, 2) }}</td>
                </tr>
              @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada data.</td></tr>
              @endforelse
              </tbody>
            </table>
          </div>

          {{-- Pager --}}
          @php
            $from = $rows->firstItem() ?? 0;
            $to   = $rows->lastItem() ?? 0;
            $tot  = $rows->total() ?? 0;
          @endphp
          <div class="pager">
            <div class="info">Menampilkan {{ $from }}–{{ $to }} dari {{ $tot }} hari</div>
            {{ $rows->onEachSide(1)->withQueryString()->links('pagination::bootstrap-5') }}
          </div>

          {{-- ====== DONUT COMPACT (BULAT) ====== --}}
          <div class="chart-card chart-compact mt-4">
            <div class="chart-head">
              <div class="title">Komposisi (%)</div>
              <div class="legend-note">Order: {{ $order->judul ?? '-' }}</div>
            </div>
            <div class="donut-wrap">
              <canvas id="packingDonut"></canvas>
            </div>

            @php
              $d = $donut['data'] ?? [0,0,0];
              [$totPacked,$totReject,$totSelisih] = [$d[0] ?? 0, $d[1] ?? 0, $d[2] ?? 0];
            @endphp
            <div class="mini-chips">
              <div class="mini-chip">Terpacking: {{ number_format($totPacked,2) }} kg</div>
              <div class="mini-chip">Reject: {{ number_format($totReject,2) }} kg</div>
              <div class="mini-chip">Selisih: {{ number_format($totSelisih,2) }} kg</div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const donut = @json($donut ?? ['labels'=>[], 'data'=>[]]);

  const ctx = document.getElementById('packingDonut');
  if (!ctx) return;

  const COLORS = ['#7ea6ff','#f5c84b','#55d59c']; // biru, kuning, hijau

  new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: donut.labels,
      datasets: [{
        data: donut.data,
        backgroundColor: COLORS,
        borderWidth: 0
      }]
    },
    options: {
      // biarkan Chart.js menjaga rasio, wrapper 1:1 memastikan bulat sempurna
      responsive: true,
      maintainAspectRatio: true,
      aspectRatio: 1,                 // persegi
      cutout: '62%',
      plugins: {
        legend: {
          position: 'bottom',
          labels: { usePointStyle: true, boxWidth: 10, font: { size: 12 } }
        },
        tooltip: {
          callbacks: {
            label: (ctx) => {
              const total = (donut.data || []).reduce((a,b)=>a+b,0) || 1;
              const val   = ctx.parsed ?? 0;
              const pct   = (val/total*100).toFixed(1);
              return `${ctx.label}: ${val.toLocaleString('id-ID',{minimumFractionDigits:2,maximumFractionDigits:2})} (${pct}%)`;
            }
          }
        }
      }
    }
  });
});
</script>
@endpush
@endsection
