<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DataOvenController extends Controller
{
    public function index(Request $request)
{
    // --- Filter dari query ---
    $selectedOven = (int) $request->query('oven', 1);
    if ($selectedOven < 1 || $selectedOven > 5) $selectedOven = 1;

    // Ambil orders: semua ON PROGRESS + 10 SELESAI terakhir (urut dari yang terbaru di-update)
    $ordersOn = DB::table('production_orders')
        ->select('id','judul','status','updated_at')
        ->whereRaw("UPPER(COALESCE(status,'')) = 'ON PROGRESS'")
        ->orderByDesc('updated_at')
        ->limit(200)
        ->get();

    $ordersDone = DB::table('production_orders')
        ->select('id','judul','status','updated_at')
        ->whereRaw("UPPER(COALESCE(status,'')) = 'SELESAI'")
        ->orderByDesc('updated_at')
        ->limit(10)
        ->get();

    // Gabungkan: ON PROGRESS dulu, lalu SELESAI (10 terakhir)
    $orders = $ordersOn->concat($ordersDone);

    // Tentukan order terpilih
    $selectedOrderId = $request->query('order_id');
    if (!$selectedOrderId && $orders->count() > 0) {
        $selectedOrderId = $orders->first()->id; // default: item pertama di gabungan
    }
    $selectedOrderId = $selectedOrderId ? (int) $selectedOrderId : null;

    // Jika belum ada order aktif → tabel kosong
    $rows = collect();
    if ($selectedOrderId) {
        $rows = DB::table('production_dailies as d')
            ->select('d.*')
            ->where('d.production_order_id', $selectedOrderId)
            ->where(function ($q) {
                $q->whereRaw("LOWER(TRIM(jenis)) = 'oven'")
                  ->orWhereRaw("LOWER(TRIM(jenis)) LIKE 'oven %'")
                  ->orWhereRaw("LOWER(TRIM(jenis)) LIKE '% oven%'");
            })
            ->orderBy('d.tanggal')
            ->orderBy('d.id')
            ->get();
    }

    // Normalisasi kolom penting
    $mapped = $rows->map(function ($r) {
        return (object) [
            'id'       => $r->id,
            'tanggal'  => $r->tanggal,
            'oven_no'  => $this->ovenNo($r),
            'keluar'   => $this->num($this->val($r, ['keluar_kg','hasil_keluar_kg','keluar','hasil_keluar'])),
            'oli'      => $this->num($this->val($r, ['oli_liter'])),
            'durasi'   => $this->num($this->val($r, ['durasi_oven_jam'])),
        ];
    });

    // Filter oven sesuai kartu yang dipilih
    $filtered = $mapped->filter(fn($x) => $x->oven_no === $selectedOven)->values();

    // Bangun batch agregat
    $batches = $this->buildBatches($filtered, $selectedOven);

    // Pagination 10 baris
    $perPage = 10;
    $page    = \Illuminate\Pagination\Paginator::resolveCurrentPage('page') ?: 1;
    $paged   = new \Illuminate\Pagination\LengthAwarePaginator(
        $batches->slice(($page - 1) * $perPage, $perPage)->values(),
        $batches->count(), $perPage, $page,
        ['path' => $request->url(), 'query' => $request->query()]
    );

    return view('oven.index', [
        'selectedOven'    => $selectedOven,
        'selectedOrderId' => $selectedOrderId,
        'orders'          => $orders,      // gabungan ON PROGRESS + 10 SELESAI
        'ordersOn'        => $ordersOn,    // untuk optgroup di view
        'ordersDone'      => $ordersDone,  // untuk optgroup di view
        'batches'         => $paged,
    ]);
}


    /** ---------- Helpers ---------- */

    protected function val($row, array $candidates)
    {
        foreach ($candidates as $c) {
            if (property_exists($row, $c)) return $row->$c;
        }
        return null;
    }

    protected function num($v): float
    {
        if ($v === null || $v === '') return 0.0;
        if (is_string($v)) $v = str_replace([',',' '], ['.',''], $v);
        return (float) $v;
    }

    protected function durasiJam($row): float
    {
        foreach (['durasi_jam','durasi','lama_jam','jam'] as $c) {
            if (property_exists($row, $c) && $row->$c !== null) return $this->num($row->$c);
        }
        foreach (['durasi_menit','menit'] as $c) {
            if (property_exists($row, $c) && $row->$c !== null) return $this->num($row->$c) / 60.0;
        }
        return 0.0;
    }

    protected function ovenNo($row): int
    {
        foreach (['nomor_oven','oven_no','no_oven','oven_id','oven'] as $c) {
            if (property_exists($row, $c) && $row->$c !== null && $row->$c !== '') {
                return (int) $row->$c;
            }
        }
        $jenis = property_exists($row,'jenis') ? (string)$row->jenis : '';
        if (preg_match('/oven\s*#?\s*(\d+)/i', $jenis, $m)) return (int) $m[1];
        return 1;
    }

    // Build batch: akumulasi sampai "keluar" > 0 → batch selesai
    protected function buildBatches(Collection $rows, int $ovenNo): Collection
    {
        $batchNo = 0;
        $sumOli = 0.0;
        $sumDur = 0.0;
        $sumKeluar = 0.0;

        $out = collect();

        foreach ($rows as $r) {
            if ($batchNo === 0) $batchNo = 1;

            $sumOli    += $r->oli;
            $sumDur    += $r->durasi;
            $sumKeluar += $r->keluar;

            if ($r->keluar > 0) {
                $out->push((object)[
                    'oven_no' => $ovenNo,
                    'batch'   => $batchNo,
                    'keluar'  => $sumKeluar,
                    'oli'     => $sumOli,
                    'durasi'  => $sumDur,
                ]);
                $batchNo += 1;
                $sumOli = $sumDur = $sumKeluar = 0.0;
            }
        }

        // Batch berjalan (belum panen)
        if ($batchNo > 0 && ($sumOli > 0 || $sumDur > 0 || $sumKeluar >= 0)) {
            $out->push((object)[
                'oven_no' => $ovenNo,
                'batch'   => $batchNo,
                'keluar'  => $sumKeluar,
                'oli'     => $sumOli,
                'durasi'  => $sumDur,
            ]);
        }

        return $out->sortByDesc('batch')->values();
    }
}
