<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('oil_stocks', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->index();      // tanggal input
            $table->decimal('qty', 12, 2)->default(0); // jumlah oli masuk (+)
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('oil_stocks');
    }
};
