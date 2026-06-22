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
        Schema::create('break_time_corrections', function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId('attendance_correction_id')
                ->constrained()
                ->restrictOnDelete();
            $table
                ->foreignId('break_time_id')
                ->nullable()
                ->constrained()
                ->restrictOnDelete();
            $table->dateTime('started_at');
            $table->dateTime('ended_at');
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('break_time_corrections');
    }
};
