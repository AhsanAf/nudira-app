<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LabTest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LabTestController extends Controller
{
    public function index(Request $request)
    {
        $tests = LabTest::orderByDesc('tanggal')->orderByDesc('id')->paginate(10);

        // donut default = baris terbaru (jika ada)
        $donut = ['labels' => ['Moisture','Ash','Volatile','Fixed C'], 'data' => [0,0,0,100]];
        if ($tests->count() > 0) {
            $latest = $tests->first();
            $donut['data'] = [
                (float)$latest->mc_pct,
                (float)$latest->ash_pct,
                (float)$latest->vm_pct,
                (float)$latest->fc_pct,
            ];
        }

        return view('lab.index', compact('tests','donut'));
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'sample_name' => ['required','string','max:120'],
            'a' => ['required','numeric','min:0'],
            'b' => ['required','numeric','min:0'],
            'c' => ['required','numeric','min:0'],
            'd' => ['required','numeric','min:0'],
        ]);

        $A = (float)$v['a']; $B = (float)$v['b']; $C = (float)$v['c']; $D = (float)$v['d'];

        // Perhitungan (aman terhadap pembagian 0)
        $mc  = $A > 0 ? (($A - $B) / $A) * 100 : 0;
        $ash = $B > 0 ? ($D / $B) * 100 : 0;
        $vm  = $B > 0 ? (($B - $C) / $B) * 100 : 0;
        $fc  = 100 - $mc - $ash - $vm;

        // Normalisasi
        $mc  = round(max($mc, 0), 2);
        $ash = round(max($ash, 0), 2);
        $vm  = round(max($vm, 0), 2);
        $fc  = round(max($fc, 0), 2);

        LabTest::create([
            'tanggal'     => Carbon::today(),
            'sample_name' => $v['sample_name'],
            'a' => $A, 'b' => $B, 'c' => $C, 'd' => $D,
            'mc_pct' => $mc, 'ash_pct' => $ash, 'vm_pct' => $vm, 'fc_pct' => $fc,
        ]);

        return redirect()
            ->route('production.lab.index')
            ->with('success', 'Hasil uji berhasil disimpan.');
    }
}
