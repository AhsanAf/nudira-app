<?
// database/migrations/xxxx_xx_xx_xxxxxx_create_messages_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('messages', function (Blueprint $t) {
            $t->id();
            $t->string('subject',100);
            $t->text('body');
            $t->unsignedBigInteger('from_user_id')->nullable();
            $t->timestamps();
            $t->foreign('from_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }
    public function down(): void { Schema::dropIfExists('messages'); }
};
