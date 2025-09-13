<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('production_dailies', function (Blueprint $table) {
            // Tambah kolom durasi (menit)
            $table->unsignedInteger('durasi_oven_menit')->nullable()->after('oli');
        });

        // Migrasi data lama: waktu_oven (TIME) -> durasi_oven_menit (INT menit)
        // Catatan: ini untuk MySQL/MariaDB. Jika pakai SQLite, lewati perintah DB::statement ini.
        try {
            DB::statement('UPDATE production_dailies SET durasi_oven_menit = FLOOR(TIME_TO_SEC(waktu_oven)/60) WHERE waktu_oven IS NOT NULL');
        } catch (\Throwable $e) {
            // Jika DBMS tidak mendukung fungsi ini, abaikan migrasi data otomatis.
        }

        // Hapus kolom lama
        Schema::table('production_dailies', function (Blueprint $table) {
            if (Schema::hasColumn('production_dailies', 'waktu_oven')) {
                $table->dropColumn('waktu_oven');
            }
        });
    }

    public function down(): void
    {
        // Kembalikan kolom lama
        Schema::table('production_dailies', function (Blueprint $table) {
            $table->time('waktu_oven')->nullable()->after('oli');
        });

        // Migrasi balik: durasi (menit) -> TIME
        try {
            DB::statement('UPDATE production_dailies SET waktu_oven = SEC_TO_TIME(COALESCE(durasi_oven_menit,0)*60)');
        } catch (\Throwable $e) {
            // abaikan jika tidak didukung
        }

        // Hapus kolom durasi baru
        Schema::table('production_dailies', function (Blueprint $table) {
            if (Schema::hasColumn('production_dailies', 'durasi_oven_menit')) {
                $table->dropColumn('durasi_oven_menit');
            }
        });
    }
};
