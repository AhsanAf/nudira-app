<?php

namespace App\Http\Controllers;

use App\Models\ProductionDaily;
use App\Models\ProductionOrder;
use Illuminate\Support\Carbon;

class GrinderController extends Controller
{
    public function index()
    {
        // Ambil baris GRIND (toleran kapital/spasi)
        $rows = ProductionDaily::query()
            ->with(['order:id,judul,status'])
            ->where(function ($q) {
                $q->whereRaw("LOWER(TRIM(jenis)) = 'grind'")
                  ->orWhereRaw("LOWER(TRIM(jenis)) LIKE 'grind %'");
            })
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get([
                'id',
                'production_order_id',
                'jenis_material',
                // PENTING: hanya kolom yang ada di DB kamu
                'bahan_baku_kg',
                'hasil_dismill_kg',
                'residu_keluar_kg',
                'tanggal',
            ]);

        // Bentuk payload untuk front-end
        $itemsJs = $rows->map(function ($r) {
            return [
                'id'          => $r->id,
                'order_id'    => $r->production_order_id ? (string) $r->production_order_id : null,
                'orderTitle'  => optional($r->order)->judul,
                'orderStatus' => optional($r->order)->status,
                'tanggal'     => $r->tanggal ? Carbon::parse($r->tanggal)->toDateString() : null,
                'material'    => (string) ($r->jenis_material ?? '-'),
                'bahan'       => (float) ($r->bahan_baku_kg ?? 0),   // ← dari bahan_baku_kg
                'dismill'     => (float) ($r->hasil_dismill_kg ?? 0),
                'residu'      => (float) ($r->residu_keluar_kg ?? 0),
            ];
        });

        // 10 order terakhir (status apa pun)
        $ordersLatest = ProductionOrder::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(10)
            ->get(['id', 'judul', 'status']);

        return view('grinder.index', [
            'itemsJs'      => $itemsJs,
            'ordersLatest' => $ordersLatest,
        ]);
    }
}
