<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('medicine_intakes', function (Blueprint $table) {
            $table->integer('quantity')->default(1)->after('medicine_id');
        });
    }

    public function down()
    {
        Schema::table('medicine_intakes', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });
    }
};
