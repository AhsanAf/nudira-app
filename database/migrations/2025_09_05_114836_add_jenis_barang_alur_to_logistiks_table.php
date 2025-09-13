<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('logistiks', function (Blueprint $table) {
            if (!Schema::hasColumn('logistiks', 'jenis_barang')) {
                $table->enum('jenis_barang', ['mentah', 'jadi'])->nullable()->after('jumlah_logistik');
            }
            if (!Schema::hasColumn('logistiks', 'alur')) {
                $table->enum('alur', ['masuk', 'keluar'])->nullable()->after('jenis_barang');
            }
        });
    }

    public function down(): void
    {
        Schema::table('logistiks', function (Blueprint $table) {
            if (Schema::hasColumn('logistiks', 'alur')) $table->dropColumn('alur');
            if (Schema::hasColumn('logistiks', 'jenis_barang')) $table->dropColumn('jenis_barang');
        });
    }
};
    