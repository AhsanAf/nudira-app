<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('lab_tests', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->index();          // tanggal uji (default: today)
            $table->string('sample_name', 120);        // nama sampel
            // variabel mentah
            $table->decimal('a', 12, 4)->default(0);
            $table->decimal('b', 12, 4)->default(0);
            $table->decimal('c', 12, 4)->default(0);
            $table->decimal('d', 12, 4)->default(0);
            // hasil %
            $table->decimal('mc_pct', 8, 2)->default(0);   // moisture
            $table->decimal('ash_pct', 8, 2)->default(0);  // ash
            $table->decimal('vm_pct', 8, 2)->default(0);   // volatile
            $table->decimal('fc_pct', 8, 2)->default(0);   // fixed carbon
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('lab_tests');
    }
};
