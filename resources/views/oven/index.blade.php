{{-- resources/views/production/oven/index.blade.php --}}
@extends('layouts.app')
@section('content_title','Production • Data Oven')

@section('content')
<div class="oven-scope">
  <div class="py-4 py-md-5">
    <div class="container">

      {{-- ====== SCOPED CSS: selaras dengan daily.index ====== --}}
      <style>
        .oven-scope{
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

        /* filter cards (5 kolom di desktop, responsif) */
        .ovens{display:grid; grid-template-columns:repeat(5,1fr); gap:1rem}
        @media (max-width:1199.98px){ .ovens{ grid-template-columns:repeat(3,1fr);} }
        @media (max-width:767.98px){  .ovens{ grid-template-columns:repeat(2,1fr);} }
        @media (max-width:575.98px){  .ovens{ grid-template-columns:1fr;} }

        .ov-card{
          display:block; text-decoration:none; color:inherit;
          background:#fff; border-radius:1rem;
          border:1px solid #e8f0ff; box-shadow:0 6px 16px rgba(2,6,23,.06);
          padding:22px 18px; text-align:center;
          transition:.12s ease; user-select:none;
        }
        .ov-card:hover{ transform:translateY(-2px); box-shadow:0 10px 24px rgba(2,6,23,.10); }
        .ov-card .label{ text-transform:uppercase; font-size:.8rem; color:#6b7280; letter-spacing:.3px; }
        .ov-card .num{ font-weight:800; font-size:1.6rem; }
        .ov-card.active{ outline:3px solid rgba(99,195,233,.35); }

        /* tabel & pager: samakan dengan daily.index */
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
        .pager .btn-group .btn{min-width:42px}

        /* chip batch (seragam) */
        .chip{ display:inline-block; padding:.25rem .6rem; border-radius:999px; font-weight:700; font-size:.9rem; }
        .chip-green{ background:#e8f8ee; color:#118c4f; }
      </style>

      <div class="card cardx">
        {{-- HEADER gradient + selector Order --}}
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="title">
              <i class="bi bi-fire"></i><span>Data Oven</span>
            </div>
            <form method="GET" action="{{ route('production.oven.index') }}" class="d-flex align-items-center gap-2">
              <label class="fw-semibold me-1">Order:</label>
              <input type="hidden" name="oven" value="{{ $selectedOven }}">
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
          {{-- banner info ala daily --}}
          <div class="soft mb-3">
            Klik kartu untuk memilih <b>Oven</b>. Tabel di bawah menampilkan batch untuk <b>Order</b> & <b>Oven</b> yang dipilih.
          </div>

          {{-- filter cards --}}
          <div class="ovens mb-3">
            @for ($i = 1; $i <= 5; $i++)
              <a class="ov-card {{ $selectedOven === $i ? 'active':'' }}"
                 href="{{ route('production.oven.index', ['oven'=>$i, 'order_id'=>$selectedOrderId]) }}">
                <div class="label">Oven</div>
                <div class="num">#{{ $i }}</div>
              </a>
            @endfor
          </div>

          {{-- tabel batch --}}
          <div class="table-wrap">
            <table class="table table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th>Nomor Oven</th>
                  <th>Batch</th>
                  <th>Barang Keluar (kg)</th>
                  <th>Oli Terpakai</th>
                  <th>Durasi Oven</th>
                </tr>
              </thead>
              <tbody>
              @forelse($batches as $b)
                <tr>
                  <td class="fw-semibold">Oven #{{ $b->oven_no }}</td>
                  <td><span class="chip chip-green">Batch {{ $b->batch }}</span></td>
                  <td>{{ number_format((float)$b->keluar, 2) }}</td>
                  <td>{{ number_format((float)$b->oli, 2) }}</td>
                  <td>{{ number_format((float)$b->durasi, 2) }} jam</td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center text-muted py-4">
                    @if(!$selectedOrderId)
                      Pilih <b>Order</b> terlebih dahulu.
                    @else
                      Belum ada data oven untuk order ini.
                    @endif
                  </td>
                </tr>
              @endforelse
              </tbody>
            </table>
          </div>

          {{-- pager ala daily --}}
          @php
            $from = $batches->firstItem() ?? 0;
            $to   = $batches->lastItem() ?? 0;
            $tot  = $batches->total() ?? 0;
          @endphp
          <div class="pager">
            <div class="info">Menampilkan {{ $from }}–{{ $to }} dari {{ $tot }}</div>
            <div class="btn-group">
              {{-- gunakan query-string agar filter tetap terjaga --}}
              @if($batches->previousPageUrl())
                <a class="btn btn-outline-secondary btn-sm" href="{{ $batches->appends(request()->query())->previousPageUrl() }}">
                  <i class="bi bi-chevron-left"></i>
                </a>
              @else
                <button class="btn btn-outline-secondary btn-sm" disabled><i class="bi bi-chevron-left"></i></button>
              @endif

              @if($batches->nextPageUrl())
                <a class="btn btn-outline-secondary btn-sm" href="{{ $batches->appends(request()->query())->nextPageUrl() }}">
                  <i class="bi bi-chevron-right"></i>
                </a>
              @else
                <button class="btn btn-outline-secondary btn-sm" disabled><i class="bi bi-chevron-right"></i></button>
              @endif
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection
