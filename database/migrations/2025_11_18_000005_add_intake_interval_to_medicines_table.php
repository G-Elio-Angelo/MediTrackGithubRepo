<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->integer('intake_interval_minutes')->default(30)->after('supplier_name');
        });
    }

    public function down()
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropColumn('intake_interval_minutes');
        });
    }
};
