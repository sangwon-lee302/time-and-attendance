<?php

namespace App\Services;

use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class StampCorrectionApplicationService
{
    /**
     * Store a new attendance correction application resource and its
     * corresponding break correction application resources.
     */
    public function storeCorrectionApplication(
        array $attributes,
        Attendance $attendance
    ): bool {
        try {
            return DB::transaction(function () use ($attributes, $attendance) {
                $attendance->attendanceCorrectionApplications()
                    ->create([
                        'new_clocked_in_at'  => $attributes['new_clocked_in_at'],
                        'new_clocked_out_at' => $attributes['new_clocked_out_at'],
                        'remarks'            => $attributes['remarks'],
                    ])
                    ->breakTimeCorrectionApplications()
                    ->createMany(collect($attributes['breaks'] ?? [])
                        ->map(fn (array $breakData) => [
                            'break_time_id'  => $breakData['break_time_id'] ?? null,
                            'new_started_at' => $breakData['new_started_at'],
                            'new_ended_at'   => $breakData['new_ended_at'],
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
