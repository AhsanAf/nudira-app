@extends('layouts.app')
@section('content_title','Production • Stok Oli')

@section('content')
<div class="oil-scope">
  <div class="py-4 py-md-5">
    <div class="container">

      {{-- ========== SCOPED CSS: seragam dengan daily/oven ========== --}}
      <style>
        .oil-scope{
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

        /* grid cards statistik */
        .stats{display:grid; grid-template-columns:repeat(4,1fr); gap:1rem}
        @media (max-width:1199.98px){ .stats{ grid-template-columns:repeat(2,1fr);} }
        @media (max-width:575.98px){  .stats{ grid-template-columns:1fr;} }
        .stat{
          background:#fff; border-radius:1rem; border:1px solid #e8f0ff;
          box-shadow:0 6px 16px rgba(2,6,23,.06); padding:18px 16px;
        }

        /* tabel & pager : sama seperti daily */
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

        /* chip untuk angka */
        .chip{ display:inline-block; padding:.25rem .6rem; border-radius:999px; font-weight:700; font-size:.9rem; }
        .chip-green{ background:#e8f8ee; color:#118c4f; }
      </style>

      <div class="card cardx">
        {{-- HEADER gradient + aksi --}}
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center">
            <div class="title"><i class="bi bi-journal"></i><span>Stok Oli</span></div>
            <div class="d-flex gap-2">
              <a href="{{ route('production.oil-log.export') }}" class="btn btn-light btn-sm">
                <i class="bi bi-download me-1"></i> Ekspor
              </a>
              <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddOil">
                <i class="bi bi-plus-lg me-1"></i> Tambah Data
              </button>
            </div>
          </div>
        </div>

        <div class="card-body">
          {{-- banner info --}}
          <div class="soft mb-3">
            <b>Tanggal otomatis hari ini</b> • <span class="text-primary">Input hanya “Jumlah Oli” & “Keterangan”.</span>
          </div>

          {{-- cards statistik --}}
          <div class="stats mb-3">
            <div class="stat">
              <div class="text-muted small mb-1">Masuk Hari Ini</div>
              <div class="fs-3 fw-bold">{{ number_format((float)$masukHariIni, 2) }}</div>
              <div class="small text-secondary">Tanggal: {{ now()->format('d M Y') }}</div>
            </div>
            <div class="stat">
              <div class="text-muted small mb-1">Total Masuk</div>
              <div class="fs-3 fw-bold">{{ number_format((float)$totalMasuk, 2) }}</div>
              <div class="small text-secondary">Akumulasi seluruh input</div>
            </div>
            <div class="stat">
              <div class="text-muted small mb-1">Total Terpakai (Oven)</div>
              <div class="fs-3 fw-bold">{{ number_format((float)$totalTerpakai, 2) }}</div>
              <div class="small text-secondary">Dari Data Harian (jenis: oven)</div>
            </div>
            <div class="stat">
              <div class="text-muted small mb-1">Total Oli Saat Ini</div>
              <div class="fs-3 fw-bold">{{ number_format((float)$totalSisa, 2) }}</div>
              <div class="small text-secondary">= Total Masuk − Total Terpakai</div>
            </div>
          </div>

          {{-- tabel history --}}
          <div class="table-wrap">
            <table class="table table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th>Tanggal</th>
                  <th>Jumlah Oli</th>
                  <th>Keterangan</th>
                  <th>Waktu Input</th>
                </tr>
              </thead>
              <tbody>
              @forelse($history as $row)
                <tr>
                  <td class="fw-medium">{{ \Illuminate\Support\Carbon::parse($row->tanggal)->translatedFormat('d M Y') }}</td>
                  <td><span class="chip chip-green">{{ number_format((float)$row->jumlah_oli, 2) }}</span></td>
                  <td class="text-muted">{{ $row->keterangan }}</td>
                  <td>{{ \Illuminate\Support\Carbon::parse($row->created_at)->format('d-m-Y H:i') }}</td>
                </tr>
              @empty
                <tr><td colspan="4" class="text-center text-muted py-4">Belum ada data.</td></tr>
              @endforelse
              </tbody>
            </table>
          </div>

          {{-- pager ala daily --}}
          @php
            $from = $history->firstItem() ?? 0;
            $to   = $history->lastItem() ?? 0;
            $tot  = $history->total() ?? 0;
          @endphp
          <div class="pager">
            <div class="info">Menampilkan {{ $from }}–{{ $to }} dari {{ $tot }} data</div>
            <div class="btn-group">
              @if($history->previousPageUrl())
                <a class="btn btn-outline-secondary btn-sm" href="{{ $history->appends(request()->query())->previousPageUrl() }}">
                  <i class="bi bi-chevron-left"></i>
                </a>
              @else
                <button class="btn btn-outline-secondary btn-sm" disabled><i class="bi bi-chevron-left"></i></button>
              @endif

              @if($history->nextPageUrl())
                <a class="btn btn-outline-secondary btn-sm" href="{{ $history->appends(request()->query())->nextPageUrl() }}">
                  <i class="bi bi-chevron-right"></i>
                </a>
              @else
                <button class="btn btn-outline-secondary btn-sm" disabled><i class="bi bi-chevron-right"></i></button>
              @endif
            </div>
          </div>

          {{-- notifikasi error/sukses (tetap) --}}
          @if ($errors->any())
            <div class="alert alert-danger shadow-sm rounded-3 mt-3">
              <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
          @endif
          @if(session('success'))
            <div class="alert alert-success shadow-sm rounded-3 mt-2">{{ session('success') }}</div>
          @endif
        </div>
      </div>

      {{-- MODAL: Tambah --}}
      <div class="modal fade" id="modalAddOil" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content rounded-4 border-0">
            <div class="modal-header">
              <h5 class="modal-title">Input Stok Oli Hari Ini</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form id="formAddOil" method="POST" action="{{ route('production.oil-log.store') }}">
              @csrf
              <div class="modal-body">
                <div class="mb-3">
                  <label class="form-label">Tanggal</label>
                  <input type="text" class="form-control" value="{{ now()->format('d-m-Y') }}" disabled>
                </div>
                <div class="mb-3">
                  <label class="form-label">Jumlah Oli</label>
                  <input type="number" name="jumlah_oli" step="0.01" min="0" class="form-control" placeholder="cth: 2.00" required>
                </div>
                <div class="mb-1">
                  <label class="form-label">Keterangan (opsional)</label>
                  <input type="text" name="keterangan" maxlength="255" class="form-control" placeholder="catatan singkat">
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
              </div>
            </form>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  // Konfirmasi sebelum submit
  const form = document.getElementById('formAddOil');
  if(form){
    form.addEventListener('submit', function(e){
      e.preventDefault();
      const jumlah = form.querySelector('input[name="jumlah_oli"]').value || '0';
      Swal.fire({
        title: 'Simpan data?',
        html: `Jumlah oli: <b>${jumlah}</b>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, simpan',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: { confirmButton: 'btn btn-primary', cancelButton: 'btn btn-light' },
        buttonsStyling: false
      }).then((r)=>{ if(r.isConfirmed) form.submit(); });
    });
  }

  // Toast sukses setelah redirect
  @if(session('success'))
  Swal.fire({ icon:'success', title:'Berhasil', text:@json(session('success')), timer:1600, showConfirmButton:false });
  @endif
</script>
@endpush
@endsection
