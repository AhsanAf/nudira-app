<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // ========= ORDERS (fleksibel) =========
        $table = Schema::hasTable('production_orders') ? 'production_orders'
               : (Schema::hasTable('orders') ? 'orders' : null);

        $ordersJs = [];
        if ($table) {
            $has   = fn($c) => Schema::hasColumn($table, $c);
            $colDate   = collect(['tanggal','date','order_date','created_at'])->first($has);
            $colOrder  = collect(['judul','order','name','title','no_order'])->first($has);
            $colQty    = collect(['qty_ton','qty','quantity','jumlah'])->first($has);
            $colStatus = collect(['status','state','progress_status'])->first($has);
            $colId     = $has('id') ? 'id' : ($colDate ?: null);

            $rows = DB::table($table)
                ->when($colId, fn($q) => $q->orderBy($colId, 'desc'))
                ->limit(200)->get();

            $toStatus = function ($raw) {
                $v = is_string($raw) ? strtolower(trim($raw)) : $raw;
                $done = ['finished','selesai','done','complete','completed','1','true',1,true];
                return in_array($v, $done, true) ? 'SELESAI' : 'ON PROGRESS';
            };
            $toDate = function ($v) {
                try { return \Illuminate\Support\Carbon::parse($v)->format('Y-m-d'); }
                catch (\Throwable) { return now()->format('Y-m-d'); }
            };

            $ordersJs = $rows->map(function ($r) use ($colDate,$colOrder,$colQty,$colStatus,$toDate,$toStatus) {
                $order = $colOrder ? (string)($r->{$colOrder} ?? '') : '';
                if ($order === '' && isset($r->id)) $order = 'ORD-'.$r->id;

                return [
                    't'     => $colDate ? $toDate($r->{$colDate}) : now()->format('Y-m-d'),
                    'order' => $order,
                    'qty'   => $colQty ? (int)($r->{$colQty} ?? 0) : 0,
                    'st'    => $colStatus ? $toStatus($r->{$colStatus}) : 'ON PROGRESS',
                ];
            })->values()->all();
        }

        // ========= NOTIFIKASI (dummy) =========
        $notifJs = [
            ['icon'=>'bi-exclamation-circle','cls'=>'icon-amber','title'=>'Order butuh konfirmasi','sub'=>'ORD-24002 menunggu approval','time'=>'2 mnt lalu','unread'=>true],
            ['icon'=>'bi-box','cls'=>'icon-green','title'=>'Stok bahan baku cukup','sub'=>'Serbuk kayu grade A tersedia','time'=>'1 jam lalu','unread'=>false],
            ['icon'=>'bi-gear','cls'=>'icon-blue','title'=>'Maintenance terjadwal','sub'=>'Grinder #2 14:00–15:30','time'=>'Hari ini','unread'=>true],
        ];

        // ========= PESAN (per-user read) =========
        $userId = auth()->id() ?? 0;

        $msgJs = DB::table('messages')
            ->leftJoin('users', 'users.id', '=', 'messages.from_user_id')
            ->leftJoin('message_reads as mr', function ($join) use ($userId) {
                $join->on('mr.message_id', '=', 'messages.id')
                     ->where('mr.user_id', '=', $userId);
            })
            ->selectRaw('
                messages.id,
                messages.subject,
                messages.body,
                messages.created_at,
                COALESCE(users.name,"admin") as from_name,
                CASE WHEN mr.read_at IS NULL THEN 1 ELSE 0 END as unread
            ')
            ->orderByDesc('messages.id')
            ->limit(50)
            ->get()
            ->map(function ($m) {
                return [
                    'id'         => (int)$m->id,
                    'subject'    => (string)($m->subject ?? ''),
                    'body'       => (string)($m->body ?? ''),
                    'from'       => (string)($m->from_name ?? 'admin'),
                    'created_at' => optional($m->created_at)->format('Y-m-d H:i:s') ?: now()->format('Y-m-d H:i:s'),
                    'unread'     => (int)$m->unread === 1,
                ];
            })->toArray();

        return view('dashboard.index', compact('ordersJs','notifJs','msgJs'));
    }
}
