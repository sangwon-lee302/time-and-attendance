<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('break_time_correction_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_correction_application_id')
                ->constrained(indexName: 'break_time_crr_app_attendance_crr_app_id')
                ->cascadeOnDelete();
            $table->foreignId('break_time_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
            $table->dateTime('new_started_at');
            $table->dateTime('new_ended_at');
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('break_time_correction_applications');
    }
};
