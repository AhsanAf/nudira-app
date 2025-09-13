<?php
// database/migrations/2025_09_13_000001_add_read_at_to_messages_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('messages', 'read_at')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->timestamp('read_at')->nullable()->after('updated_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('messages', 'read_at')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->dropColumn('read_at');
            });
        }
    }
};
