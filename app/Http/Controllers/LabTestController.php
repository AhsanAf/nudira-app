<?php

namespace App\Http\Controllers;

use App\Models\LabTest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LabTestController extends Controller
{
    /**
     * List + data donut (default: baris terbaru)
     */
    public function index(Request $request)
    {
        $tests = LabTest::orderByDesc('tanggal')->orderByDesc('id')->paginate(10);

        $donut = [
            'labels' => ['Moisture','Ash','Volatile','Fixed C'],
            'data'   => [0,0,0,100],
        ];

        if ($tests->count() > 0) {
            $x = $tests->first();
            $donut['data'] = [
                (float)$x->mc_pct,
                (float)$x->ash_pct,
                (float)$x->vm_pct,
                (float)$x->fc_pct,
            ];
        }

        return view('lab.index', compact('tests','donut'));
    }

    /**
     * Definisi (sesuai foto ASTM D-1672):
     * A = awal, B = 105°C (kering), C = 950°C (coke+ash, setelah VM), D = 750°C (ash)
     * MC%  = (A - B)/A * 100
     * VM%  = (B - C)/B * 100
     * Ash% =  D / B * 100
     * FC%  = 100 - (MC + VM + Ash)   // agar total donut = 100
     */
    public function store(Request $request)
    {
        $v = $request->validate([
            'sample_name' => ['required','string','max:120'],
            'a' => ['required','numeric','min:0'],
            'b' => ['required','numeric','min:0'],
            'c' => ['required','numeric','min:0'], // C = 950°C (coke+ash / setelah VM)
            'd' => ['required','numeric','min:0'], // D = 750°C (ash)
        ]);

        $A = (float)$v['a'];
        $B = (float)$v['b'];
        $C = (float)$v['c'];
        $D = (float)$v['d'];

        $mc  = $A > 0 ? (($A - $B) / $A) * 100 : 0.0;
        $vm  = $B > 0 ? (($B - $C) / $B) * 100 : 0.0;
        $ash = $B > 0 ? ( $D / $B ) * 100      : 0.0;
        $fc  = 100 - ($mc + $vm + $ash);  // by difference

        // bulatkan + clamp
        $mc  = max(0, round($mc,  2));
        $vm  = max(0, round($vm,  2));
        $ash = max(0, round($ash, 2));
        $fc  = max(0, round($fc,  2));

        LabTest::create([
            'tanggal'     => Carbon::today(),
            'sample_name' => $v['sample_name'],
            'a' => $A, 'b' => $B, 'c' => $C, 'd' => $D,
            'mc_pct' => $mc, 'vm_pct' => $vm, 'ash_pct' => $ash, 'fc_pct' => $fc,
        ]);

        return redirect()->route('production.lab.index')
            ->with('success', 'Hasil uji berhasil disimpan.');
    }
}
