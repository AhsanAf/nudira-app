<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('production_dailies', function (Blueprint $t) {
            // Tambah hanya yang belum ada supaya aman di semua mesin
            if (!Schema::hasColumn('production_dailies','nomor_oven')) {
                $t->unsignedTinyInteger('nomor_oven')->nullable();
            }
            if (!Schema::hasColumn('production_dailies','keluar_kg')) {
                $t->decimal('keluar_kg', 12, 2)->nullable();
            }
            if (!Schema::hasColumn('production_dailies','oli_liter')) {
                $t->decimal('oli_liter', 12, 2)->nullable();
            }
            if (!Schema::hasColumn('production_dailies','durasi_oven_jam')) {
                $t->decimal('durasi_oven_jam', 8, 2)->nullable();
            }
        });

        // OPTIONAL: kalau sebelumnya kamu punya kolom lama bernama "waktu_oven" atau "durasi_menit", dan ingin diganti
        // untuk rename kolom butuh paket doctrine/dbal:
        // composer require doctrine/dbal
        // Schema::table('production_dailies', function (Blueprint $t) {
        //     if (Schema::hasColumn('production_dailies','waktu_oven') && !Schema::hasColumn('production_dailies','durasi_oven_jam')) {
        //         $t->renameColumn('waktu_oven','durasi_oven_jam');
        //     }
        // });
    }

    public function down(): void
    {
        Schema::table('production_dailies', function (Blueprint $t) {
            if (Schema::hasColumn('production_dailies','nomor_oven'))      $t->dropColumn('nomor_oven');
            if (Schema::hasColumn('production_dailies','keluar_kg'))       $t->dropColumn('keluar_kg');
            if (Schema::hasColumn('production_dailies','oli_liter'))       $t->dropColumn('oli_liter');
            if (Schema::hasColumn('production_dailies','durasi_oven_jam')) $t->dropColumn('durasi_oven_jam');
        });
    }
};
