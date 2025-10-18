<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(){
        Schema::create('medicines', function(Blueprint $table){
            $table->id();
            $table->string('medicine_name');
            $table->string('batch_number')->nullable();
            $table->integer('stock')->default(0);
            $table->date('expiry_date')->nullable();
            $table->string('unit')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(){ Schema::dropIfExists('medicines'); }
};
