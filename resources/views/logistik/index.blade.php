{{-- resources/views/logistik/index.blade.php --}}
@extends('layouts.app')
@section('content_title','Inventory')

@section('content')
<div class="logistik-scope">
  <div class="inventory-surface py-4 py-md-5">
    <div class="container">

      {{-- ===== Data dari server ===== --}}
      <script>window.initialItems = @json($itemsJs ?? []);</script>

      {{-- ===== Scoped CSS (halaman ini saja) ===== --}}
      <style>
        /* Surface & header  */
        .logistik-scope{
          --brand:#5b7cfa; --brand-2:#7ed7c1;
          --header-grad:linear-gradient(90deg, rgba(91,124,250,.95), rgba(126,215,193,.95)); /* tidak dipakai lagi */
        }
        /* Hilangkan background gradient besar */
        .logistik-scope .inventory-surface{
          background: none !important;     /* <— ini yang menghilangkan warna latar besar */
          background-color: transparent !important;
          border-radius:24px;
        }

        .logistik-scope .inventory-card{
          border:0;border-radius:1.25rem;background:#fff;
          box-shadow:0 10px 30px rgba(35,42,70,.15);overflow:hidden
        }
        /* Hilangkan gradient header card + buat teks gelap agar terbaca */
        .logistik-scope .inventory-card .card-header{
          border:0;
          padding:1.1rem 1.25rem;
          background: linear-gradient(90deg,#6a95f7,#63c3e9,#6ce1b2) !important; /* gradient balik */
          color:#fff !important;
          position:sticky;
          top:0;
          z-index:3;
        }
        .logistik-scope .header-title{display:flex;align-items:center;gap:.6rem;font-weight:700}

        /* Toolbar (rapi & responsive, Reset tidak terpotong) */
        .toolbar{display:flex;flex-wrap:wrap;gap:.6rem .75rem;align-items:center}
        .toolbar .search{flex:1 1 360px;min-width:260px}
        .toolbar .chip{background:#f2f4ff;border:1px solid #e1e6ff;border-radius:.75rem;padding:.35rem .6rem;display:flex;align-items:center;gap:.4rem;color:#4251a3;font-weight:600}
        .toolbar .dates{display:flex;align-items:center;gap:.6rem}
        .toolbar .dates .form-control{width:170px}
        .toolbar .reset{margin-left:auto}
        @media (max-width:576px){
          .toolbar .search{flex:1 1 100%}
          .toolbar .dates .form-control{width:140px}
          .toolbar .reset{margin-left:0}
        }

        /* Table */
        .logistik-scope .table-responsive{border-radius:1rem;overflow:auto;border:1px solid #eef1f6;background:#fff}
        .logistik-scope thead th{position:sticky;top:0;z-index:2;background:#f7f9ff;border-bottom:1px solid #e8ecfb!important;color:#3b4271;font-weight:700!important;text-transform:uppercase;letter-spacing:.3px;font-size:.8rem}
        .logistik-scope tbody td{vertical-align:middle;border-color:#f0f2fa!important}
        .logistik-scope tbody tr{transition:transform .06s ease, background-color .2s ease}
        .logistik-scope tbody tr:hover{background:#f9fbff;transform:translateY(-1px)}
        .logistik-scope th.sortable{cursor:pointer;user-select:none}
        .logistik-scope th.sortable .sort-ind{opacity:.35;margin-left:.35rem}
        .logistik-scope th.sortable.active .sort-ind{opacity:1}
        .empty-state{text-align:center;padding:2.5rem 1rem;color:#8190b1}
        .empty-state .bi{font-size:2rem}

        /* Alur chip */
        .badge-chip{display:inline-block;padding:.35rem .6rem;border-radius:999px;font-weight:700}
        .alur-masuk {background:rgba(7,164,105,.12);color:#0a7b56;border:1px solid rgba(7,164,105,.22)}
        .alur-keluar{background:rgba(220,53,69,.10);color:#b02a37;border:1px solid rgba(220,53,69,.22)}

        /* Pagination */
        .pager{display:flex;justify-content:space-between;align-items:center;gap:.75rem;flex-wrap:wrap;margin-top:1rem}
        .pager .info{color:#556;opacity:.85}
        .pager .btn-group .btn{min-width:42px}

        /* ===== Modal: compact + tanpa ruang kosong ===== */
        .logistik-scope .modal .form-label{font-weight:700;color:#2a2f55}
        .logistik-scope .modal .invalid-feedback{display:block}
        .logistik-scope .modal .form-control,
        .logistik-scope .modal .form-select,
        .logistik-scope .modal textarea{width:100%}

        /* Lebar modal diperkecil agar tidak terasa “terlalu lebar” */
        @media (min-width:768px){ .logistik-scope .modal-lg{ --bs-modal-width: 720px; } }
        @media (min-width:992px){ .logistik-scope .modal-lg{ --bs-modal-width: 760px; } }

        /* Grid khusus di dalam modal → 2 kolom di md+, 1 kolom di mobile */
        .form-grid{display:grid;grid-template-columns:1fr;gap:1rem 1.25rem}
        @media (min-width:768px){ .form-grid{grid-template-columns:1fr 1fr} }
        .span-2{grid-column:1 / -1}
      </style>

      {{-- ===== Card ===== --}}
      <div class="card inventory-card">
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center">
            <div class="header-title"><i class="bi bi-box-seam-fill"></i><span>Logistik • Tabel Data</span></div>
            <div class="d-flex align-items-center gap-2">
              <button class="btn btn-light btn-sm" id="exportCsvBtn"><i class="bi bi-download"></i> <span class="text">Ekspor</span></button>
              <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#addModal"><i class="bi bi-plus-lg"></i> <span class="text">Tambah Data</span></button>
            </div>
          </div>
        </div>

        <div class="card-body">
          {{-- Toolbar --}}
          <div class="toolbar mb-3">
            <div class="search input-group">
              <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
              <input id="searchInput" type="search" class="form-control border-start-0" placeholder="Cari nama/keterangan…">
            </div>
            <div class="chip"><i class="bi bi-filter-circle"></i> Filter:</div>
            <div class="dates">
              <input id="dateFrom" type="date" class="form-control form-control-sm" title="Dari tanggal">
              <span class="text-secondary small">s.d.</span>
              <input id="dateTo"   type="date" class="form-control form-control-sm" title="Sampai tanggal">
            </div>
            <button id="resetFilterBtn" class="btn btn-outline-secondary btn-sm reset">
              <i class="bi bi-arrow-counterclockwise"></i> Reset
            </button>
          </div>

          {{-- Tabel --}}
          <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
              <colgroup>
                <col style="width:140px"><col><col style="width:120px"><col style="width:170px"><col><col style="width:160px">
              </colgroup>
              <thead>
                <tr>
                  <th class="sortable" data-key="tanggal">Tanggal <i class="bi bi-arrow-down-up sort-ind"></i></th>
                  <th class="sortable" data-key="nama">Nama Barang <i class="bi bi-arrow-down-up sort-ind"></i></th>
                  <th class="text-end sortable" data-key="jumlah">Jumlah <i class="bi bi-arrow-down-up sort-ind"></i></th>
                  <th class="text-center sortable" data-key="jenisBarang">Jenis Barang <i class="bi bi-arrow-down-up sort-ind"></i></th>
                  <th class="sortable" data-key="keterangan">Keterangan <i class="bi bi-arrow-down-up sort-ind"></i></th>
                  <th class="text-center sortable" data-key="alurLabel">Alur <i class="bi bi-arrow-down-up sort-ind"></i></th>
                </tr>
              </thead>
              <tbody id="tableBody"></tbody>
            </table>
          </div>

          <div id="emptyState" class="empty-state d-none">
            <i class="bi bi-inboxes"></i>
            <p class="mt-2 mb-0">Belum ada data yang cocok dengan filter.</p>
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

      {{-- ===== Modal: Tambah Data ===== --}}
      <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <form id="addForm" class="modal-content" action="{{ route('inventory.logistik.store') }}" method="POST" novalidate>
            @csrf
            <div class="modal-header">
              <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Tambah Data</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
              <div class="form-grid">
                <div>
                  <label for="tanggalInput" class="form-label">Tanggal</label>
                  <input id="tanggalInput" name="tanggal" type="date" class="form-control" required>
                </div>
                <div>
                  <label for="namaInput" class="form-label">Nama Barang</label>
                  <input id="namaInput" name="nama_logistik" type="text" class="form-control" placeholder="Masukan Nama Barang" required>
                </div>
                <div>
                  <label for="jumlahInput" class="form-label">Jumlah</label>
                  <input id="jumlahInput" name="jumlah_logistik" type="number" min="1" step="1" inputmode="numeric" class="form-control" placeholder="Contoh : 100" required>
                </div>
                <div>
                  <label for="jenisBarangSelect" class="form-label">Jenis Barang</label>
                  <select id="jenisBarangSelect" name="jenis_barang" class="form-select" required>
                    <option value="" disabled selected>Pilih</option>
                    <option value="mentah">Barang Mentah</option>
                    <option value="jadi">Barang Jadi</option>
                  </select>
                </div>
                <div>
                  <label for="alurSelect" class="form-label">Alur</label>
                  <select id="alurSelect" name="alur" class="form-select" required>
                    <option value="" disabled selected>Pilih</option>
                    <option value="masuk">Barang Masuk</option>
                    <option value="keluar">Barang Keluar</option>
                  </select>
                </div>
                <div class="span-2">
                  <label for="ketInput" class="form-label">Keterangan</label>
                  <textarea id="ketInput" name="keterangan" rows="3" class="form-control" maxlength="50" placeholder="Masukan keterangan..."></textarea>
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

      {{-- Toast --}}
      <div class="position-fixed bottom-0 end-0 p-3" style="z-index:1080">
        <div id="savedToast" class="toast align-items-center text-bg-dark border-0" role="status" aria-live="polite" aria-atomic="true">
          <div class="d-flex">
            <div class="toast-body"><i class="bi bi-check2-circle me-2"></i>Data berhasil ditambahkan.</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Tutup"></button>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection

@push('scripts')
  {{-- SweetAlert2 --}}
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  /* ====== State ====== */
  const PAGE_SIZE = 10;
  let items = (window.initialItems || []).slice();
  let sortBy = 'tanggal', sortDir = 'desc', page = 1;

  const idID = new Intl.DateTimeFormat('id-ID', { day:'2-digit', month:'2-digit', year:'numeric' });
  const nfID  = new Intl.NumberFormat('id-ID');

  function fmtDate(iso){
    if(!iso) return '-';
    const [y,m,d] = String(iso).split('-').map(Number);
    const dt = new Date(y,(m||1)-1,(d||1));
    return isNaN(dt) ? '-' : idID.format(dt);
  }
  function fmtQty(n){ const num=Number(n); return Number.isFinite(num) ? nfID.format(num) : '-'; }
  function inRange(iso, from, to){ if(!from && !to) return true; if(from && iso < from) return false; if(to && iso > to) return false; return true; }

  const tbody = document.getElementById('tableBody');
  const emptyState = document.getElementById('emptyState');
  const pageInfo = document.getElementById('pageInfo');
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');

  function getView(){
    const q  = document.getElementById('searchInput').value.trim().toLowerCase();
    const df = document.getElementById('dateFrom').value;
    const dt = document.getElementById('dateTo').value;

    let view = items.filter(it=>{
      const matchSearch = q ? (
        (it.nama||'').toLowerCase().includes(q) ||
        (it.keterangan||'').toLowerCase().includes(q) ||
        (it.jenisBarang||'').toLowerCase().includes(q) ||
        (it.alurLabel||'').toLowerCase().includes(q)
      ) : true;
      const matchDate = inRange(it.tanggal, df, dt);
      return matchSearch && matchDate;
    });

    view.sort((a,b)=>{
      let va=a[sortBy] ?? '', vb=b[sortBy] ?? '';
      if(sortBy==='jumlah'){ va=Number(va)||0; vb=Number(vb)||0; }
      if(va===vb) return 0;
      return (sortDir==='asc') ? (va>vb?1:-1) : (va<vb?1:-1);
    });
    return view;
  }

  function render(){
    const view = getView();
    const total = view.length;
    const maxPage = Math.max(1, Math.ceil(total / PAGE_SIZE));
    if(page > maxPage) page = maxPage;

    const start = (page-1)*PAGE_SIZE;
    const end   = Math.min(total, start+PAGE_SIZE);
    const rows  = view.slice(start, end);

    tbody.innerHTML = '';
    if(rows.length===0){
      emptyState.classList.remove('d-none');
      pageInfo.textContent = 'Menampilkan 0–0 dari 0';
      prevBtn.disabled = nextBtn.disabled = true;
      return;
    }
    emptyState.classList.add('d-none');

    const frag = document.createDocumentFragment();
    rows.forEach(it=>{
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${fmtDate(it.tanggal)}</td>
        <td class="fw-semibold text-dark">${it.nama}</td>
        <td class="text-end">${fmtQty(it.jumlah)}</td>
        <td class="text-center">${it.jenisBarang || '-'}</td>
        <td class="text-secondary">${it.keterangan || '-'}</td>
        <td class="text-center">
          ${it.alurLabel ? `<span class="badge-chip ${it.alurLabel.includes('Masuk')?'alur-masuk':'alur-keluar'}">${it.alurLabel}</span>` : '-'}
        </td>`;
      frag.appendChild(tr);
    });
    tbody.appendChild(frag);

    pageInfo.textContent = `Menampilkan ${start+1}–${end} dari ${total}`;
    prevBtn.disabled = page<=1;
    nextBtn.disabled = page>=maxPage;
  }

  // Sort
  document.querySelectorAll('th.sortable').forEach(th=>{
    th.addEventListener('click', ()=>{
      const key = th.dataset.key;
      if(sortBy===key){ sortDir = (sortDir==='asc') ? 'desc' : 'asc'; }
      else { sortBy=key; sortDir = (key==='tanggal' || key==='jumlah') ? 'desc' : 'asc'; }
      document.querySelectorAll('th.sortable').forEach(x=>x.classList.remove('active'));
      th.classList.add('active');
      page = 1; render();
    });
  });

  // Filter
  ['searchInput','dateFrom','dateTo'].forEach(id=>{
    document.getElementById(id).addEventListener('input', ()=>{ page=1; render(); });
  });
  document.getElementById('resetFilterBtn').addEventListener('click', ()=>{
    document.getElementById('searchInput').value='';
    document.getElementById('dateFrom').value='';
    document.getElementById('dateTo').value='';
    page=1; render();
  });

  // Pagination
  prevBtn.addEventListener('click', ()=>{ page=Math.max(1,page-1); render(); });
  nextBtn.addEventListener('click', ()=>{ page=page+1; render(); });

  // Export
  document.getElementById('exportCsvBtn').addEventListener('click', ()=>{
    const headers = ['Tanggal','Nama Barang','Jumlah','Jenis Barang','Keterangan','Alur'];
    const rows = getView().map(it=>[
      fmtDate(it.tanggal), it.nama, it.jumlah ?? '', it.jenisBarang ?? '', it.keterangan ?? '', it.alurLabel ?? ''
    ]);
    const csv = [headers, ...rows].map(r=>r.map(s=>`"${String(s).replace(/"/g,'""')}"`).join(',')).join('\r\n');
    const blob = new Blob([csv], {type:'text/csv;charset=utf-8;'});
    const url  = URL.createObjectURL(blob);
    const a = Object.assign(document.createElement('a'), {href:url, download:'logistik.csv'});
    document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
  });

  // ===== Submit (AJAX) + SweetAlert confirm + loader =====
  const addModal = new bootstrap.Modal(document.getElementById('addModal'));

  document.getElementById('addForm').addEventListener('submit', async (e)=>{
    e.preventDefault();

    const tanggal = document.getElementById('tanggalInput').value;
    const nama    = document.getElementById('namaInput').value.trim();
    const jumlah  = Number(document.getElementById('jumlahInput').value);
    const jenis   = document.getElementById('jenisBarangSelect').value;
    const alur    = document.getElementById('alurSelect').value;
    const ket     = document.getElementById('ketInput').value.trim();
    const submitBtn = e.submitter || document.querySelector('#addForm button[type="submit"]');

    // Validasi UI
    let ok = true;
    document.getElementById('tanggalInput').classList.toggle('is-invalid', !tanggal);
    document.getElementById('namaInput').classList.toggle('is-invalid', !nama);
    document.getElementById('jumlahInput').classList.toggle('is-invalid', !(Number.isFinite(jumlah) && jumlah>=1));
    document.getElementById('jenisBarangSelect').classList.toggle('is-invalid', !jenis);
    document.getElementById('alurSelect').classList.toggle('is-invalid', !alur);
    if(!tanggal || !nama || !jenis || !alur || !(Number.isFinite(jumlah) && jumlah>=1)) ok=false;

    if(!ok){
      Swal.fire({icon:'warning', title:'Lengkapi data', text:'Periksa kembali isian yang wajib diisi.'});
      return;
    }

    // Konfirmasi "apakah data sudah benar?"
    const jenisLabel = jenis==='mentah' ? 'Barang Mentah' : 'Barang Jadi';
    const alurLabel  = alur==='masuk' ? 'Barang Masuk' : 'Barang Keluar';
    const summaryHtml = `
      <div class="text-start" style="line-height:1.6">
        <div><b>Tanggal</b>: ${fmtDate(tanggal)}</div>
        <div><b>Nama Barang</b>: ${nama}</div>
        <div><b>Jumlah</b>: ${nfID.format(jumlah)}</div>
        <div><b>Jenis Barang</b>: ${jenisLabel}</div>
        <div><b>Alur</b>: ${alurLabel}</div>
        <div><b>Keterangan</b>: ${ket ? ket : '-'}</div>
      </div>`;

    const ask = await Swal.fire({
      icon: 'question',
      title: 'Simpan data?',
      html: summaryHtml,
      showCancelButton: true,
      confirmButtonText: 'Ya, simpan',
      cancelButtonText: 'Cek lagi'
    });
    if(!ask.isConfirmed) return;

    // Kunci tombol & tampilkan loader
    submitBtn.disabled = true;
    Swal.fire({
      title: 'Menyimpan data…',
      html: 'Mohon tunggu sebentar',
      allowOutsideClick: false,
      allowEscapeKey: false,
      showConfirmButton: false,
      didOpen: () => { Swal.showLoading(); }
    });

    try{
      const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      const res = await fetch(`{{ route('inventory.logistik.store') }}`, {
        method: 'POST',
        headers: {
          'Content-Type':'application/json',
          'X-CSRF-TOKEN': token,
          'X-Requested-With':'XMLHttpRequest',
          'Accept':'application/json'
        },
        body: JSON.stringify({
          tanggal,
          nama_logistik: nama,
          jumlah_logistik: jumlah,
          jenis_barang: jenis,   // 'mentah' | 'jadi'
          alur: alur,            // 'masuk' | 'keluar'
          keterangan: ket
        })
      });

      if(!res.ok){
        let msg = 'Terjadi kesalahan saat menyimpan.';
        try{
          const j = await res.json();
          if(j?.errors){
            msg = Object.values(j.errors).flat().map(e=>`• ${e}`).join('<br>');
          }else if(j?.message){ msg = j.message; }
        }catch{ }
        Swal.fire({icon:'error', title:'Gagal', html: msg});
        return;
      }

      // Sukses → update tabel + tutup modal
      items.push({ tanggal, nama, jumlah, jenisBarang: jenisLabel, keterangan: ket, alurLabel });
      const totalAfter = getView().length;
      page = Math.max(1, Math.ceil(totalAfter / PAGE_SIZE));
      render();

      addModal.hide();
      e.target.reset();

      Swal.fire({
        icon:'success',
        title:'Tersimpan',
        text:'Data logistik berhasil ditambahkan.',
        timer: 1600,
        showConfirmButton:false
      });
    }catch(err){
      console.error(err);
      Swal.fire({icon:'error', title:'Gagal', text:'Tidak dapat terhubung ke server.'});
    }finally{
      submitBtn.disabled = false;
    }
  });

  render();
});
</script>
@endpush
