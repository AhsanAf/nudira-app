<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionOrder extends Model
{
    protected $table = 'production_orders';

    protected $fillable = [
        'tanggal_dibuat', 'tanggal_selesai', 'judul', 'qty_ton', 'status',
    ];

    protected $casts = [
        'tanggal_dibuat'  => 'date',
        'tanggal_selesai' => 'date',
        'qty_ton'         => 'decimal:2',
    ];

    public function dailies() {
        return $this->hasMany(ProductionDaily::class, 'production_order_id');
    }

    public function scopeActive($q) {
        return $q->where('status', 'ON PROGRESS');
    }
}
