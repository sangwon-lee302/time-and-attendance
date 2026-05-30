<?php

namespace App\Services;

use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AttendanceService
{
    /**
     * Update the given attendance resource and its corresponding break time
     * resources.
     */
    public function updateAttendance(
        array $attributes,
        Attendance $attendance
    ): bool {
        try {
            return DB::transaction(function () use ($attributes, $attendance) {
                $attendance->update([
                    'clocked_in_at'  => $attributes['new_clocked_in_at'],
                    'clocked_out_at' => $attributes['new_clocked_out_at'],
                ]);

                $breaks = $attendance->breakTimes()->get()->keyBy('id');

                foreach ($attributes['breaks'] as $breakData) {
                    if ($breaks->has($breakData['break_time_id'])) {
                        $breaks->get($breakData['break_time_id'])->update([
                            'started_at' => $breakData['new_started_at'],
                            'ended_at'   => $breakData['new_ended_at'],
                        ]);

                        continue;
                    }

                    $attendance->breakTimes()->create([
                        'started_at' => $breakData['new_started_at'],
                        'ended_at'   => $breakData['new_ended_at'],
                    ]);
                }

                return true;
            });
        } catch (Throwable $e) {
            Log::error('勤怠修正エラー: '.$e->getMessage(), [
                'exception' => $e,
                'user_id'   => Auth::id(),
                'input'     => $attributes,
            ]);

            return false;
        }
    }
}
