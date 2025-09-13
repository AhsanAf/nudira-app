<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('production_dailies', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');                // auto diisi now() saat store
            $table->string('jenis', 20);            // mixing | oven | packing

            // MIXING
            $table->decimal('raw_material', 12, 2)->nullable(); // kg
            $table->decimal('tepung', 12, 2)->nullable();       // kg

            // OVEN
            $table->unsignedTinyInteger('nomor_oven')->nullable();  // 1..5
            $table->decimal('keluar_kg', 12, 2)->nullable();
            $table->string('oli', 50)->nullable();
            $table->time('waktu_oven')->nullable();

            // PACKING
            $table->decimal('reject_kg', 12, 2)->nullable();

            $table->string('keterangan', 120)->nullable();
            $table->timestamps();
            $table->index(['tanggal', 'jenis']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_dailies');
    }
};
