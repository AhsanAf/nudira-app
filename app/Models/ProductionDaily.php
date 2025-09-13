<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductionDaily extends Model
{
    use HasFactory;

    protected $table = 'production_dailies';

    protected $fillable = [
        'tanggal','production_order_id','jenis','keterangan',
        // MIXING & GRIND
        'jenis_material',
        // MIXING
        'raw_material_kg','tepung_kg','water_glass_kg',
        // OVEN
        'nomor_oven','keluar_kg','oli_liter','durasi_oven_jam',
        // PACKING
        'packing_order_kg','packed_kg','reject_kg',
        // GRIND
        'bahan_baku_kg','residu_keluar_kg','hasil_dismill_kg',
    ];

    protected $casts = [
        'tanggal'           => 'date',
        'raw_material_kg'   => 'decimal:2',
        'tepung_kg'         => 'decimal:2',
        'water_glass_kg'    => 'decimal:2',
        'keluar_kg'         => 'decimal:2',
        'oli_liter'         => 'decimal:2',
        'durasi_oven_jam'   => 'decimal:2',
        'packing_order_kg'  => 'decimal:2',
        'packed_kg'         => 'decimal:2',
        'reject_kg'         => 'decimal:2',
        'bahan_baku_kg'     => 'decimal:2',
        'residu_keluar_kg'  => 'decimal:2',
        'hasil_dismill_kg'  => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(ProductionOrder::class, 'production_order_id');
    }
}
