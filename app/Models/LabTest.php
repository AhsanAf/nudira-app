<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabTest extends Model
{
// app/Models/LabTest.php
// app/Models/LabTest.php
protected $fillable = [
  'tanggal','sample_name','a','b','c','d','mc_pct','vm_pct','ash_pct','fc_pct',
];



    protected $casts = [
        'tanggal' => 'date',
        'a' => 'decimal:4', 'b' => 'decimal:4', 'c' => 'decimal:4', 'd' => 'decimal:4',
        'mc_pct' => 'decimal:2', 'ash_pct' => 'decimal:2', 'vm_pct' => 'decimal:2', 'fc_pct' => 'decimal:2',
    ];
}
