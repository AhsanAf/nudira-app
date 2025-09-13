<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProductionOilLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;


class ProductionOilLogController extends Controller
{
    public function index(Request $request)
    {
        // history: SETIAP INPUT (tidak di-group), terbaru dulu, 10 per halaman
        $history = ProductionOilLog::select('id','tanggal','jumlah_oli','keterangan','created_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10);

        $today         = Carbon::today();
        $masukHariIni  = ProductionOilLog::whereDate('tanggal', $today)->sum('jumlah_oli');
        $totalMasuk    = ProductionOilLog::sum('jumlah_oli');
        $totalTerpakai = $this->sumOvenOliTerpakai(); // dari production_dailies.oli_terpakai
        $totalSisa     = max(0, (float)$totalMasuk - (float)$totalTerpakai);

        return view('oil-log.index', compact(
            'history', 'masukHariIni', 'totalMasuk', 'totalTerpakai', 'totalSisa'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'jumlah_oli' => ['required','numeric','min:0'],
            'keterangan' => ['nullable','string','max:255'],
        ]);

        ProductionOilLog::create([
            'tanggal'    => Carbon::today()->toDateString(), // auto hari ini
            'jumlah_oli' => $data['jumlah_oli'],
            'keterangan' => $data['keterangan'] ?? null,
        ]);

        return back()->with('success', 'Data oli berhasil disimpan.');
    }

    // Ekspor CSV sederhana untuk menyamai tombol "Ekspor"
    public function export(): StreamedResponse
    {
        $filename = 'stok_oli_'.now()->format('Ymd_His').'.csv';
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Tanggal','Jumlah Oli','Keterangan','Waktu Input']);
            ProductionOilLog::orderByDesc('created_at')->chunk(200, function ($rows) use ($out) {
                foreach ($rows as $r) {
                    fputcsv($out, [
                        optional($r->tanggal)->format('Y-m-d'),
                        (string)$r->jumlah_oli,
                        (string)($r->keterangan ?? ''),
                        optional($r->created_at)->format('Y-m-d H:i:s'),
                    ]);
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    // Ambil pemakaian oli dari Data Harian (jenis oven)
    protected function sumOvenOliTerpakai(): float
{
    if (!Schema::hasTable('production_dailies')) {
        return 0.0;
    }

    // baris yang jenisnya oven (fleksibel: 'oven', 'oven ...', '... oven')
    $base = DB::table('production_dailies')->where(function ($q) {
        $q->whereRaw("LOWER(TRIM(jenis)) = 'oven'")
          ->orWhereRaw("LOWER(TRIM(jenis)) LIKE 'oven %'")
          ->orWhereRaw("LOWER(TRIM(jenis)) LIKE '% oven'")
          ->orWhereRaw("LOWER(TRIM(jenis)) LIKE '% oven %'");
    });

    // kandidat nama kolom yang mungkin ada di skema kamu
    $candidates = [
        'oli_terpakai_kg', 'oli_terpakai',
        'oli_liter' // fallback terakhir kalau memang diberi nama 'oli'
    ];

    $total = 0.0;
    foreach ($candidates as $col) {
        if (Schema::hasColumn('production_dailies', $col)) {
            $total += (float) $base->sum($col);
        }
    }
    return $total;
}
}
