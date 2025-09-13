<?php

namespace App\Http\Controllers;

use App\Models\Logistik;
use Illuminate\Http\Request;

class LogistikController extends Controller
{
    public function index()
    {
        $items = Logistik::orderByDesc('tanggal')->get();

        // Data siap-pakai untuk JS (label manusiawi)
        $itemsJs = $items->map(function ($it) {
            return [
                'tanggal'     => optional($it->tanggal)->format('Y-m-d'),
                'nama'        => $it->nama_logistik,
                'jumlah'      => (int) $it->jumlah_logistik,
                'jenisBarang' => match ($it->jenis_barang) {
                    'mentah' => 'Barang Mentah',
                    'jadi'   => 'Barang Jadi',
                    default  => null,
                },
                'keterangan'  => $it->keterangan,
                'alurLabel'   => match ($it->alur) {
                    'masuk'  => 'Barang Masuk',
                    'keluar' => 'Barang Keluar',
                    default  => null,
                },
            ];
        })->values();

        return view('logistik.index', compact('items', 'itemsJs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tanggal'         => ['required','date'],
            'nama_logistik'   => ['required','string','max:255'],
            'jumlah_logistik' => ['required','integer','min:1'],
            'keterangan'      => ['nullable','string','max:50'],
            'jenis_barang'    => ['required','in:mentah,jadi'],
            'alur'            => ['required','in:masuk,keluar'],
        ]);

        $item = Logistik::create($data);

        return $request->wantsJson()
            ? response()->json(['message'=>'created','item'=>$item], 201)
            : redirect()->route('inventory.logistik.index')->with('success','Data berhasil ditambahkan');
    }
}
