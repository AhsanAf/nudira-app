<?php

namespace App\View\Components\logistik;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class formlogistik extends Component
{
    public $tanggal, $nama_logistik, $jumlah_logistik, $kategori, $keterangan;
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.logistik.formlogistik');
    }
}
