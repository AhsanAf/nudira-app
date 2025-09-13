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
    Schema::table('production_dailies', function (Blueprint $table) {
        $table->decimal('oli_terpakai', 12, 2)->nullable()->after('keluar_kg');
    });
}

public function down(): void
{
    Schema::table('production_dailies', function (Blueprint $table) {
        $table->dropColumn('oli_terpakai');
    });
}

};
