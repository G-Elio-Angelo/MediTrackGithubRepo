<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop unused tables if they exist
        if (Schema::hasTable('distributions')) {
            Schema::dropIfExists('distributions');
        }

        if (Schema::hasTable('patients')) {
            Schema::dropIfExists('patients');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate 'patients' table
        if (!Schema::hasTable('patients')) {
            Schema::create('patients', function (Blueprint $table) {
                $table->id();
                $table->string('fullname');
                $table->string('phone')->nullable();
                $table->date('dob')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // Recreate 'distributions' table
        if (!Schema::hasTable('distributions')) {
            Schema::create('distributions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('medicine_id')->constrained('medicines')->cascadeOnDelete();
                $table->foreignId('patient_id')->nullable()->constrained('patients')->nullOnDelete();

                // user_id references users.user_id
                $table->unsignedBigInteger('user_id')->nullable();
                $table->foreign('user_id')->references('user_id')->on('users')->nullOnDelete();

                $table->integer('quantity')->default(1);
                $table->date('given_at')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();
            });
        }
    }
};
