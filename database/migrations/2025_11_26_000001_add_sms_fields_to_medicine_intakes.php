<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('medicine_intakes', function (Blueprint $table) {
            if (!Schema::hasColumn('medicine_intakes', 'sms_notified')) {
                $table->boolean('sms_notified')->default(false)->after('confirmed_at');
            }
            if (!Schema::hasColumn('medicine_intakes', 'sms_notified_at')) {
                $table->timestamp('sms_notified_at')->nullable()->after('sms_notified');
            }
        });
    }

    public function down()
    {
        Schema::table('medicine_intakes', function (Blueprint $table) {
            if (Schema::hasColumn('medicine_intakes', 'sms_notified_at')) {
                $table->dropColumn('sms_notified_at');
            }
            if (Schema::hasColumn('medicine_intakes', 'sms_notified')) {
                $table->dropColumn('sms_notified');
            }
        });
    }
};
