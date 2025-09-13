<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Rename qty -> input_oli (tipe & default tetap mengikuti kolom lama)
        if (Schema::hasColumn('oil_stocks', 'qty') && !Schema::hasColumn('oil_stocks', 'input_oli')) {
            Schema::table('oil_stocks', function (Blueprint $table) {
                $table->renameColumn('qty', 'input_oli');
            });
        }
    }

    public function down(): void
    {
        // Balikkan jika perlu
        if (Schema::hasColumn('oil_stocks', 'input_oli') && !Schema::hasColumn('oil_stocks', 'qty')) {
            Schema::table('oil_stocks', function (Blueprint $table) {
                $table->renameColumn('input_oli', 'qty');
            });
        }
    }
};
