<?php

use App\CorrectionStatus;
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
        Schema::create('attendance_corrections', function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId('attendance_id')
                ->constrained()
                ->restrictOnDelete();
            $table
                ->foreignId('requested_by')
                ->constrained('users')
                ->restrictOnDelete();
            $table
                ->foreignId('decided_by')
                ->constrained('users')
                ->restrictOnDelete();
            $table
                ->string('status')
                ->default(CorrectionStatus::Pending->value);
            $table->dateTime('original_clocked_in_at');
            $table->dateTime('original_clocked_out_at');
            $table->dateTime('requested_clocked_in_at');
            $table->dateTime('requested_clocked_out_at');
            $table->string('remarks');
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_corrections');
    }
};
