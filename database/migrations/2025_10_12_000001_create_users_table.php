<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(){
        Schema::create('users', function(Blueprint $table){
            $table->id('user_id');
            $table->string('username');
            $table->string('email')->unique();
            $table->string('phone_number')->nullable();
            $table->string('password');
            $table->string('role')->default('bhw');
            $table->timestamps();
        });
    }
    public function down(){ Schema::dropIfExists('users'); }
};
