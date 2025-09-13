<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logistik extends Model
{
protected $fillable = [
  'tanggal',
  'nama_logistik',
  'jumlah_logistik',
  'keterangan',
  'jenis_barang', // NEW
  'alur',         // NEW
  'kategori',     // (biarkan ada utk fallback data lama)
];

protected $casts = [
  'tanggal' => 'date',
];
}
