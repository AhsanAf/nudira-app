<?php
// database/migrations/2025_09_07_000001_add_fields_daily_v2.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('production_dailies', function (Blueprint $t) {
            // Dipakai di MIXING & GRIND
            if (!Schema::hasColumn('production_dailies', 'jenis_material')) {
                $t->string('jenis_material')->nullable(); // 'Batok Kelapa','Kayu','Residu','Batok Regrind'
            }

            // MIXING
            if (!Schema::hasColumn('production_dailies', 'raw_material_kg')) {
                $t->decimal('raw_material_kg', 12, 2)->nullable();
            }
            if (!Schema::hasColumn('production_dailies', 'tepung_kg')) {
                $t->decimal('tepung_kg', 12, 2)->nullable();
            }
            if (!Schema::hasColumn('production_dailies', 'water_glass_kg')) {
                $t->decimal('water_glass_kg', 12, 2)->nullable();
            }

            // OVEN (biarkan jika sudah ada di migrasi lama)
            // $t->unsignedTinyInteger('nomor_oven')->nullable();
            // $t->decimal('keluar_kg', 12, 2)->nullable();
            // $t->decimal('oli_liter', 12, 2)->nullable();
            // $t->decimal('durasi_oven_jam', 8, 2)->nullable();

            // PACKING
            if (!Schema::hasColumn('production_dailies', 'packing_order_kg')) {
                $t->decimal('packing_order_kg', 12, 2)->nullable();
            }
            if (!Schema::hasColumn('production_dailies', 'packed_kg')) {
                $t->decimal('packed_kg', 12, 2)->nullable();
            }
            if (!Schema::hasColumn('production_dailies', 'reject_kg')) {
                $t->decimal('reject_kg', 12, 2)->nullable();
            }

            // GRIND
            if (!Schema::hasColumn('production_dailies', 'bahan_baku_kg')) {
                $t->decimal('bahan_baku_kg', 12, 2)->nullable();
            }
            if (!Schema::hasColumn('production_dailies', 'residu_keluar_kg')) {
                $t->decimal('residu_keluar_kg', 12, 2)->nullable();
            }
            if (!Schema::hasColumn('production_dailies', 'hasil_dismill_kg')) {
                $t->decimal('hasil_dismill_kg', 12, 2)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('production_dailies', function (Blueprint $t) {
            $cols = [
                'jenis_material','raw_material_kg','tepung_kg','water_glass_kg',
                'packing_order_kg','packed_kg','reject_kg',
                'bahan_baku_kg','residu_keluar_kg','hasil_dismill_kg',
            ];
            foreach ($cols as $c) {
                if (Schema::hasColumn('production_dailies', $c)) {
                    $t->dropColumn($c);
                }
            }
        });
    }
};
