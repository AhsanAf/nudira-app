<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_dibuat');              // auto now(), tidak bisa diedit
            $table->date('tanggal_selesai')->nullable(); // set saat “Selesai” ditekan
            $table->string('judul', 120);                // contoh: “Order ke Bandung”
            $table->decimal('qty_ton', 10, 2);           // nominal tons
            $table->string('status', 20)->default('ON PROGRESS'); // ON PROGRESS | SELESAI
            $table->timestamps();
            $table->index(['status','tanggal_dibuat']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('production_orders');
    }
};
