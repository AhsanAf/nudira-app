<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

class PackingController extends Controller
{
    public function index(Request $request)
    {
        // ===== Orders: ON PROGRESS + 10 SELESAI =====
        $ordersOn = DB::table('production_orders')
            ->select('id','judul','status','updated_at','qty_ton')
            ->whereRaw("UPPER(COALESCE(status,'')) = 'ON PROGRESS'")
            ->orderByDesc('updated_at')->limit(200)->get();

        $ordersDone = DB::table('production_orders')
            ->select('id','judul','status','updated_at','qty_ton')
            ->whereRaw("UPPER(COALESCE(status,'')) = 'SELESAI'")
            ->orderByDesc('updated_at')->limit(10)->get();

        $selectedOrderId = (int) $request->query('order_id', 0);
        if (!$selectedOrderId) {
            $first = $ordersOn->first() ?: $ordersDone->first();
            $selectedOrderId = $first->id ?? 0;
        }

        // Info order (ton → kg)
        $order = null; $orderQtyKg = 0.0;
        if ($selectedOrderId) {
            $order = DB::table('production_orders')->select('id','judul','qty_ton')
                      ->where('id',$selectedOrderId)->first();
            $orderQtyKg = $order ? (float)$order->qty_ton * 1000.0 : 0.0;
        }

        // ===== Agregasi harian: packing & oven =====
        $packingByDate = collect();  // tgl, packed_kg, reject_kg
        $ovenByDate    = collect();  // tgl, oven_out
        $totalPacked   = 0.0;
        $totalReject   = 0.0;
        $totalOvenOut  = 0.0;

        if ($selectedOrderId) {
            // PACKING per tanggal
            $packingByDate = DB::table('production_dailies as d')
                ->select(
                    'd.tanggal',
                    DB::raw('SUM(COALESCE(d.packed_kg, 0)) as packed_kg'),
                    DB::raw('SUM(COALESCE(d.reject_kg, 0)) as reject_kg')
                )
                ->where('d.production_order_id', $selectedOrderId)
                ->whereRaw("LOWER(TRIM(d.jenis)) = 'packing'")
                ->groupBy('d.tanggal')
                ->orderBy('d.tanggal', 'asc')
                ->get();

            // Total packed/reject untuk kartu & donut
            $totalPacked = (float) DB::table('production_dailies as d')
                ->where('d.production_order_id', $selectedOrderId)
                ->whereRaw("LOWER(TRIM(d.jenis)) = 'packing'")
                ->sum(DB::raw('COALESCE(d.packed_kg, 0)'));

            $totalReject = (float) DB::table('production_dailies as d')
                ->where('d.production_order_id', $selectedOrderId)
                ->whereRaw("LOWER(TRIM(d.jenis)) = 'packing'")
                ->sum(DB::raw('COALESCE(d.reject_kg, 0)'));

            // OVEN per tanggal (untuk tabel harian)
            $ovenByDate = DB::table('production_dailies as d')
                ->select('d.tanggal', DB::raw('SUM(COALESCE(d.keluar_kg, 0)) as oven_out'))
                ->where('d.production_order_id', $selectedOrderId)
                ->where(function($q){
                    $q->whereRaw("LOWER(TRIM(d.jenis)) = 'oven'")
                      ->orWhereRaw("LOWER(TRIM(d.jenis)) LIKE 'oven %'")
                      ->orWhereRaw("LOWER(TRIM(d.jenis)) LIKE '% oven%'");
                })
                ->groupBy('d.tanggal')
                ->orderBy('d.tanggal', 'asc')
                ->get()
                ->keyBy('tanggal');

            // Total oven keluar untuk donut
            $totalOvenOut = (float) DB::table('production_dailies as d')
                ->where('d.production_order_id', $selectedOrderId)
                ->where(function($q){
                    $q->whereRaw("LOWER(TRIM(d.jenis)) = 'oven'")
                      ->orWhereRaw("LOWER(TRIM(d.jenis)) LIKE 'oven %'")
                      ->orWhereRaw("LOWER(TRIM(d.jenis)) LIKE '% oven%'");
                })
                ->sum(DB::raw('COALESCE(d.keluar_kg, 0)'));
        }

        // Gabung harian mengikuti tanggal PACKING
        $rows = collect();
        foreach ($packingByDate as $p) {
            $ov = $ovenByDate->get($p->tanggal);
            $ovenOut = $ov ? (float)$ov->oven_out : 0.0;
            $packed  = (float)$p->packed_kg;
            $reject  = (float)$p->reject_kg;

            // Selisih = Oven Keluar - (Terpacking + Reject)
            $selisih = $ovenOut - ($packed + $reject);

            $rows->push((object)[
                'tanggal' => $p->tanggal,
                'oven_out'=> $ovenOut,
                'packed'  => $packed,
                'reject'  => $reject,
                'selisih' => $selisih,
            ]);
        }

        // Pagination 10 baris (DESC tanggal)
        $rowsDesc = $rows->sortByDesc('tanggal')->values();
        $perPage  = 10;
        $page     = Paginator::resolveCurrentPage('page') ?: 1;
        $paged    = new LengthAwarePaginator(
            $rowsDesc->slice(($page-1)*$perPage, $perPage)->values(),
            $rowsDesc->count(), $perPage, $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Data chart timeline (kalau nanti dibutuhkan)
        $asc = $rows->sortBy('tanggal')->values();
        $chart = [
            'labels'  => $asc->pluck('tanggal'),
            'oven'    => $asc->pluck('oven_out'),
            'packed'  => $asc->pluck('packed'),
            'reject'  => $asc->pluck('reject'),
            'selisih' => $asc->pluck('selisih'),
        ];

        // Donut "Komposisi (%)" ala Grinder (3 segmen: Terpacking, Reject, SelisihTotal)
        $selisihTotal = $totalOvenOut - ($totalPacked + $totalReject);
        if ($selisihTotal < 0) $selisihTotal = 0; // jangan negatif untuk komposisi

        $donut = [
            'labels' => ['Terpacking', 'Reject', 'Selisih'],
            'data'   => [
                round($totalPacked, 2),
                round($totalReject, 2),
                round($selisihTotal, 2),
            ],
        ];

        // Sisa target produksi
        $sisaHarusDibuat = max($orderQtyKg - $totalPacked, 0);

        return view('packing.index', [
            'ordersOn'        => $ordersOn,
            'ordersDone'      => $ordersDone,
            'selectedOrderId' => $selectedOrderId,
            'order'           => $order,
            'orderQtyKg'      => $orderQtyKg,
            'totalPacked'     => $totalPacked,
            'sisaHarusDibuat' => $sisaHarusDibuat,
            'rows'            => $paged,
            'chart'           => $chart,
            'donut'           => $donut,
        ]);
    }
}
