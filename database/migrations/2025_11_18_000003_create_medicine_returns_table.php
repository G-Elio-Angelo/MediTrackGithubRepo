<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('medicine_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->nullable()->constrained('medicines')->onDelete('set null');
            $table->string('batch_number')->nullable();
            $table->integer('quantity')->default(1);
            $table->string('supplier_name')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('medicine_returns');
    }
};
