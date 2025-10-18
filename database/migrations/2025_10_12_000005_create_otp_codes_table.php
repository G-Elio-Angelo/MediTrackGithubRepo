<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(){
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();

            // ✅ Explicitly reference users.user_id instead of users.id
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                  ->references('user_id')
                  ->on('users')
                  ->cascadeOnDelete();

            $table->string('code', 8);
            $table->boolean('used')->default(false);
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(){
        Schema::dropIfExists('otp_codes');
    }
};
