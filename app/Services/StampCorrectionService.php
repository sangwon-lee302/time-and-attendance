<?php

namespace App\Services;

use App\Models\Attendance;
use Illuminate\Support\Facades\DB;

class StampCorrectionService
{
    /**
     * Store a new stamp correction.
     */
    public function storeStampCorrection(
        array $attributes,
        Attendance $attendance
    ): void {
        DB::transaction(function () use ($attributes, $attendance) {
            $attendance->attendanceCorrections()
                ->create([
                    'clocked_in_at'  => $attributes['clocked_in_at'],
                    'clocked_out_at' => $attributes['clocked_out_at'],
                    'remarks'        => $attributes['remarks'],
                ])
                ->breakTimeCorrections()
                ->createMany(collect($attributes['breaks'] ?? [])
                    ->map(fn (array $break) => [
                        'break_time_id' => $break['break_time_id'] ?? null,
                        'started_at'    => $break['started_at'],
                        'ended_at'      => $break['ended_at'],
                    ])
                    ->all()
                );
        });
    }
}
