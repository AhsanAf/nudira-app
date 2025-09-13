<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Logistik;

class StokBarangController extends Controller
{
    public function index(Request $request)
    {
        // Optional search by name
        $q = trim($request->get('q', ''));

        // Build base query aggregating stock (masuk - keluar)
        $base = Logistik::query()
            ->select([
                'nama_logistik as nama',
                'jenis_barang',
                DB::raw("SUM(CASE WHEN alur='masuk' THEN jumlah_logistik ELSE -jumlah_logistik END) as jumlah")
            ])
            ->when($q !== '', function($query) use ($q) {
                $query->where('nama_logistik', 'like', "%{$q}%");
            })
            ->groupBy('nama_logistik','jenis_barang')
            ->orderBy('nama_logistik');

        // Clone for each jenis
        $barangJadi = (clone $base)->where('jenis_barang', 'jadi')->having('jumlah', '!=', 0)->get();
        $barangMentah = (clone $base)->where('jenis_barang', 'mentah')->having('jumlah', '!=', 0)->get();

        // Totals
        $totalJadi = $barangJadi->sum(function($r){ return (int)$r->jumlah; });
        $totalMentah = $barangMentah->sum(function($r){ return (int)$r->jumlah; });

        return view('stokbarang.index', compact('barangJadi','barangMentah','q','totalJadi','totalMentah'));
    }
}