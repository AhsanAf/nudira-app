<?php
// database/migrations/2025_09_12_000000_alter_messages_target_default.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // make sure it exists first
            if (Schema::hasColumn('messages', 'target')) {
                $table->string('target', 50)->default('all')->change();
            }
        });
    }
    public function down(): void
    {
        // optional rollback
    }
};
