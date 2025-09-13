<?php
// database/migrations/2025_09_09_000000_create_production_oil_logs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('production_oil_logs', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->index();                  // auto diisi hari ini saat simpan
            $table->decimal('jumlah_oli', 12, 2)->default(0);   // jumlah oli
            $table->string('keterangan', 255)->nullable();      // opsional (disimpan, tidak ditampilkan di tabel)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_oil_logs');
    }
};
