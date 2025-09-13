<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void {
    Schema::table('production_dailies', function (Blueprint $t) {
        if (!Schema::hasColumn('production_dailies','bahan_baku_kg'))   $t->decimal('bahan_baku_kg',12,2)->nullable();
        if (!Schema::hasColumn('production_dailies','residu_keluar_kg'))$t->decimal('residu_keluar_kg',12,2)->nullable();
        if (!Schema::hasColumn('production_dailies','hasil_dismill_kg'))$t->decimal('hasil_dismill_kg',12,2)->nullable();
        // kalau perlu, sekalian:
        if (!Schema::hasColumn('production_dailies','jenis_material'))  $t->string('jenis_material')->nullable();
        if (!Schema::hasColumn('production_dailies','raw_material_kg')) $t->decimal('raw_material_kg',12,2)->nullable();
        if (!Schema::hasColumn('production_dailies','tepung_kg'))       $t->decimal('tepung_kg',12,2)->nullable();
        if (!Schema::hasColumn('production_dailies','water_glass_kg'))  $t->decimal('water_glass_kg',12,2)->nullable();
        if (!Schema::hasColumn('production_dailies','packing_order_kg'))$t->decimal('packing_order_kg',12,2)->nullable();
        if (!Schema::hasColumn('production_dailies','packed_kg'))       $t->decimal('packed_kg',12,2)->nullable();
        if (!Schema::hasColumn('production_dailies','reject_kg'))       $t->decimal('reject_kg',12,2)->nullable();
    });
}

};
