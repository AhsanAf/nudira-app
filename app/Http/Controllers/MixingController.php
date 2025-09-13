<?php

namespace App\Http\Controllers;

use App\Models\ProductionDaily;
use App\Models\ProductionOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MixingController extends Controller
{
    public function index()
    {
        // 10 order terakhir (ON PROGRESS & SELESAI)
        $ordersLatest = ProductionOrder::query()
            ->select('id','judul','status')
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->map(fn($o) => [
                'id'     => $o->id,
                'judul'  => $o->judul,
                'status' => $o->status,
            ]);

        // Helper: buat COALESCE hanya dari kolom yang ada
        $coalesce = function (string $table, array $candidates, string $alias) {
            $available = array_values(array_filter(
                $candidates,
                fn($c) => Schema::hasColumn($table, $c)
            ));
            if (empty($available)) {
                // semua kandidat tidak ada → hardcode 0 agar aman
                return DB::raw("0 as {$alias}");
            }
            return DB::raw('COALESCE('.implode(',', $available).',0) as '.$alias);
        };

        $table = 'production_dailies';

        // Tentukan kolom tanggal yang tersedia (fallback ke created_at)
        $orderCol = Schema::hasColumn($table, 'tanggal') ? 'tanggal' : 'created_at';

        $rows = ProductionDaily::query()
            ->where(function($q){
                $q->whereRaw("LOWER(TRIM(jenis)) = 'mixing'")
                  ->orWhereRaw("LOWER(TRIM(jenis)) LIKE 'mixing %'");
            })
            ->select([
                'id',
                'production_order_id as order_id',
                // Bahan baku: gunakan hanya kolom yang benar-benar ada
                $coalesce($table, [
                    'raw_material_kg',   // nama yang paling mungkin
                    'raw_material',      // alias lama (jika ada)
                ], 'raw_material_kg'),
                // Tepung
                $coalesce($table, [
                    'tepung_kg',
                    'tepung',
                ], 'tepung_kg'),
                // Water glass
                $coalesce($table, [
                    'water_glass_kg',
                    'water_glass',
                    'waterglass_kg',
                ], 'water_glass_kg'),
                'jenis_material',
                // ikutkan tanggal bila ada agar mudah ditampilkan (opsional)
                DB::raw(Schema::hasColumn($table, 'tanggal') ? 'tanggal' : 'created_at as tanggal'),
            ])
            ->orderByDesc($orderCol)
            ->orderByDesc('id')
            ->get();

        // Data untuk JS (bersih)
        $mixJs = $rows->map(fn($r) => [
            'order_id'        => $r->order_id,
            'jenis_material'  => $r->jenis_material,
            'raw_material_kg' => (float) $r->raw_material_kg,
            'tepung_kg'       => (float) $r->tepung_kg,
            'water_glass_kg'  => (float) $r->water_glass_kg,
            'tanggal'         => (string) $r->tanggal,
        ]);

        return view('mixing.index', [
            'mixJs'        => $mixJs,
            'ordersLatest' => $ordersLatest,
        ]);
    }
}
