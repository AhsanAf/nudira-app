<?php
// app/Models/ProductionOilLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionOilLog extends Model
{
    protected $table = 'production_oil_logs';

    protected $fillable = [
        'tanggal',
        'jumlah_oli',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah_oli' => 'decimal:2',
    ];

    // urut terbaru dulu
    public function scopeLatestFirst($q)
    {
        return $q->orderBy('tanggal', 'desc')->orderBy('id', 'desc');
    }
}
