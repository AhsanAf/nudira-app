<?php

namespace App\Http\Controllers;

use App\Models\ProductionDaily;
use App\Models\ProductionOrder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductionDailyController extends Controller
{
    /**
     * Tampilkan halaman Data Harian.
     * - $orders: hanya Order ON PROGRESS (untuk dropdown).
     * - $itemsJs: seluruh data harian (untuk tabel + pagination di sisi client).
     */
    public function index(Request $request)
    {
        // Order aktif yang bisa dipilih user
        $orders = ProductionOrder::where('status', 'ON PROGRESS')
            ->orderByDesc('id')
            ->get(['id', 'judul', 'qty_ton', 'status']);

        // Data harian + relasi order (untuk label "Order … (SUCSESS)" saat status SELESAI)
        $rows = ProductionDaily::with('order')
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        // Bentuk data untuk JS di view
        $itemsJs = $rows->map(function (ProductionDaily $d) {
            return [
                'id'          => $d->id,
                'tanggal'     => optional($d->tanggal)->toDateString(),
                'jenis'       => $d->jenis,
                'ket'         => $d->keterangan,

                // Info order
                'orderTitle'  => $d->order?->judul,
                'orderStatus' => $d->order?->status,   // "ON PROGRESS" | "SELESAI"
                'orderQtyTon' => $d->order?->qty_ton,

                // MIXING / GRIND shared
                'jenis_material'  => $d->jenis_material,

                // MIXING
                'raw_material_kg' => $d->raw_material_kg,
                'tepung_kg'       => $d->tepung_kg,
                'water_glass_kg'  => $d->water_glass_kg,

                // OVEN
                'nomor_oven'      => $d->nomor_oven,
                'keluar_kg'       => $d->keluar_kg,
                'oli_liter'       => $d->oli_liter,
                'durasi_oven_jam' => $d->durasi_oven_jam,

                // PACKING
                'packing_order_kg'=> $d->packing_order_kg,
                'packed_kg'       => $d->packed_kg,
                'reject_kg'       => $d->reject_kg,

                // GRIND
                'bahan_baku_kg'    => $d->bahan_baku_kg,
                'residu_keluar_kg' => $d->residu_keluar_kg,
                'hasil_dismill_kg' => $d->hasil_dismill_kg,
            ];
        });

        return view('daily.index', [
            'orders'  => $orders,
            'itemsJs' => $itemsJs,
        ]);
    }

    /**
     * Simpan Data Harian (WAJIB punya production_order_id yang ON PROGRESS).
     * Field per-jenis diverifikasi sesuai kebutuhan.
     */
    public function store(Request $request)
    {
        $jenis = $request->input('jenis');

        // Wajib ada Order ON PROGRESS untuk semua jenis
        $base = [
            'jenis' => ['required', Rule::in(['mixing', 'oven', 'packing', 'grind'])],
            'keterangan' => ['nullable', 'string', 'max:100'],
            'production_order_id' => [
                'required',
                Rule::exists('production_orders', 'id')->where(fn ($q) => $q->where('status', 'ON PROGRESS')),
            ],
        ];

        // Aturan tambahan per jenis
        $rules = [
            'mixing' => [
                'jenis_material'  => ['required', Rule::in(['Batok Kelapa','Kayu','Residu','Batok Regrind'])],
                'raw_material_kg' => ['required','numeric','min:0.01'],
                'tepung_kg'       => ['required','numeric','min:0.01'],
                'water_glass_kg'  => ['required','numeric','min:0.01'],
            ],
            'oven' => [
                'nomor_oven'      => ['required','integer','min:1','max:5'],
                'keluar_kg'       => ['required','numeric','min:0'],
                'oli_liter'       => ['required','numeric','min:0'],
                'durasi_oven_jam' => ['required','numeric','min:0.1'],
            ],
            'packing' => [
                // production_order_id sudah diwajibkan di $base
                'packed_kg'       => ['required','numeric','min:0'],
                'reject_kg'       => ['required','numeric','min:0'],
            ],
            'grind' => [
                'jenis_material'   => ['required', Rule::in(['Batok Kelapa','Kayu','Residu','Batok Regrind'])],
                'bahan_baku_kg'    => ['required','numeric','min:0.01'],
                'residu_keluar_kg' => ['required','numeric','min:0'],
                'hasil_dismill_kg' => ['required','numeric','min:0'],
            ],
        ];

        $data = $request->validate(array_merge($base, $rules[$jenis] ?? []));
        $data['tanggal'] = now()->toDateString();

        // Auto-hitungan untuk PACKING: ambil qty_ton order × 1000 (kg)
        if ($jenis === 'packing') {
            $ord = ProductionOrder::find($data['production_order_id']);
            $data['packing_order_kg'] = $ord ? ($ord->qty_ton * 1000) : null;
        }

        $row = ProductionDaily::create($data)->load('order');

        // Siapkan payload yang dipakai tabel JS di view
        $payload = [
            'id'              => $row->id,
            'tanggal'         => optional($row->tanggal)->toDateString(),
            'jenis'           => $row->jenis,
            'ket'             => $row->keterangan,

            'orderTitle'      => $row->order?->judul,
            'orderStatus'     => $row->order?->status,
            'orderQtyTon'     => $row->order?->qty_ton,

            'jenis_material'  => $row->jenis_material,
            'raw_material_kg' => $row->raw_material_kg,
            'tepung_kg'       => $row->tepung_kg,
            'water_glass_kg'  => $row->water_glass_kg,

            'nomor_oven'      => $row->nomor_oven,
            'keluar_kg'       => $row->keluar_kg,
            'oli_liter'       => $row->oli_liter,
            'durasi_oven_jam' => $row->durasi_oven_jam,

            'packing_order_kg'=> $row->packing_order_kg,
            'packed_kg'       => $row->packed_kg,
            'reject_kg'       => $row->reject_kg,

            'bahan_baku_kg'    => $row->bahan_baku_kg,
            'residu_keluar_kg'  => $row->residu_keluar_kg,
            'hasil_dismill_kg'  => $row->hasil_dismill_kg,
        ];

        return response()->json([
            'message' => 'OK',
            'data'    => $payload,
        ], 201);
    }
}
