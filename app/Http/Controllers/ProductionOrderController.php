<?php

namespace App\Http\Controllers;

use App\Models\ProductionOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ProductionOrderController extends Controller
{
    public function index()
    {
        // Tabel admin: urutan terbaru di atas
        $orders = ProductionOrder::orderByDesc('tanggal_dibuat')
            ->orderByDesc('id')
            ->get();

        // JS-friendly
        $ordersJs = $orders->map(function ($o) {
            return [
                'id'              => $o->id,
                'tanggal_dibuat'  => optional($o->tanggal_dibuat)->format('Y-m-d'),
                'tanggal_selesai' => optional($o->tanggal_selesai)->format('Y-m-d'),
                'judul'           => $o->judul,
                'qty_ton'         => $o->qty_ton,
                'status'          => $o->status,
            ];
        });

        return view('orders.index', compact('ordersJs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'judul'   => 'required|string|max:120',     // contoh: "Order ke Bandung"
            'qty_ton' => 'required|numeric|min:0.01',
        ]);

        $row = ProductionOrder::create([
            'tanggal_dibuat' => Carbon::now()->toDateString(), // auto today
            'judul'          => $data['judul'],
            'qty_ton'        => $data['qty_ton'],
            'status'         => 'ON PROGRESS',
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'OK',
                'data'    => [
                    'id'              => $row->id,
                    'tanggal_dibuat'  => $row->tanggal_dibuat?->format('Y-m-d'),
                    'tanggal_selesai' => $row->tanggal_selesai?->format('Y-m-d'),
                    'judul'           => $row->judul,
                    'qty_ton'         => $row->qty_ton,
                    'status'          => $row->status,
                ]
            ]);
        }
        return back()->with('success', 'Order dibuat');
    }

    public function finish(ProductionOrder $order, Request $request)
    {
        if ($order->status === 'SELESAI') {
            return response()->json(['message' => 'Order sudah selesai'], 422);
        }

        $order->update([
            'status'          => 'SELESAI',
            'tanggal_selesai' => Carbon::now()->toDateString(),
        ]);

        // Catatan: setelah selesai, order keluar dari daftar aktif
        return response()->json([
            'message' => 'OK',
            'data'    => [
                'id'              => $order->id,
                'tanggal_dibuat'  => $order->tanggal_dibuat?->format('Y-m-d'),
                'tanggal_selesai' => $order->tanggal_selesai?->format('Y-m-d'),
                'judul'           => $order->judul,
                'qty_ton'         => $order->qty_ton,
                'status'          => $order->status,
            ]
        ]);
    }

    // Endpoint untuk Data Harian: ambil Order yang masih ON PROGRESS
    public function activeList()
{
    $rows = \App\Models\ProductionOrder::where('status','ON PROGRESS')
        ->orderByDesc('tanggal_dibuat')
        ->get(['id','judul','qty_ton','tanggal_dibuat']);

    $data = $rows->map(fn($r)=>[
        'id'      => $r->id,
        'judul'   => $r->judul,
        'qty_ton' => (float)$r->qty_ton,
        'tanggal' => optional($r->tanggal_dibuat)->format('d/m/Y'),
    ]);

    return response()->json(['data'=>$data]);
}
}
