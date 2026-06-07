<?php

namespace App\Services;

use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class StampCorrectionService
{
    /**
     * Store a new stamp correction.
     */
    public function storeStampCorrection(
        array $attributes,
        Attendance $attendance
    ): bool {
        try {
            return DB::transaction(function () use ($attributes, $attendance) {
                $attendance->attendanceCorrections()
                    ->create([
                        'clocked_in_at'  => $attributes['clocked_in_at'],
                        'clocked_out_at' => $attributes['clocked_out_at'],
                        'remarks'            => $attributes['remarks'],
                    ])
                    ->breakTimeCorrections()
                    ->createMany(collect($attributes['breaks'] ?? [])
                        ->map(fn (array $breakData) => [
                            'break_time_id'  => $breakData['break_time_id'] ?? null,
                            'started_at' => $breakData['started_at'],
                            'ended_at'   => $breakData['ended_at'],
                        ])
                        ->all()
                    );

                return true;
            });
        } catch (Throwable $e) {
            Log::error('勤怠修正申請保存エラー: '.$e->getMessage(), ['exception' => $e]);

            return false;
        }
    }
}
