<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait OrderQtyColumn
{
    /**
     * Menghasilkan ekspresi SELECT yang SELALU punya alias qty_ton,
     * apapun nama kolom aslinya di tabel production_orders.
     * Urutan fallback: qty_ton → quantity_ton → qty → NULL.
     */
    protected function qtyTonSelect()
    {
        if (Schema::hasColumn('production_orders', 'qty_ton')) {
            return DB::raw('qty_ton as qty_ton');
        }
        if (Schema::hasColumn('production_orders', 'quantity_ton')) {
            return DB::raw('quantity_ton as qty_ton');
        }
        if (Schema::hasColumn('production_orders', 'qty')) {
            return DB::raw('qty as qty_ton');
        }
        return DB::raw('NULL as qty_ton');
    }
}
