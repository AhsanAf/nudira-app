<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('logistiks', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('nama_logistik');
            $table->integer('jumlah_logistik');
            $table->enum('kategori', ['masuk', 'keluar']);
            $table->text('keterangan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logistiks');
    }
};
