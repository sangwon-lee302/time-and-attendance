<?php

namespace App\Actions\AttendanceCorrections;

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StoreAttendanceCorrection
{
    /**
     * Store a new attendance correction in database.
     *
     * @throws RuntimeException
     */
    public function store(array $attributes, Attendance $attendance): ?AttendanceCorrection
    {
        $attributes['breaks'] = collect($attributes['breaks'] ?? [])
            ->filter(fn (array $break) => array_key_exists('break_time_id', $break)
                || filled($break['started_at']),
            )
            ->all();

        if (! $this->checkForCorrections($attributes, $attendance)) {
            return null;
        }

        return DB::transaction(function () use ($attributes, $attendance) {
            $attendanceCorrection = $attendance
                ->attendanceCorrections()
                ->create([
                    'requested_by'   => Auth::id(),
                    'clocked_in_at'  => $attributes['clocked_in_at'],
                    'clocked_out_at' => $attributes['clocked_out_at'],
                    'remarks'        => $attributes['remarks'],
                ]);

            $attendanceCorrection
                ->breakTimeCorrections()
                ->createMany(collect($attributes['breaks'])
                    ->map(fn (array $break) => [
                        'break_time_id' => $break['break_time_id'] ?? null,
                        'started_at'    => $break['started_at'],
                        'ended_at'      => $break['ended_at'],
                    ])
                    ->all()
                );

            return $attendanceCorrection;
        });
    }

    /**
     * Check if the given attribute indeed has corrections.
     */
    protected function checkForCorrections(array $attributes, Attendance $attendance): bool
    {
        $isClockedInAtCorrected = $attendance
            ->clocked_in_at
            ->setSecond(0)
            ->format('Y-m-d H:i:s')
            !== $attributes['clocked_in_at'];

        $isClockedOutAtCorrected = $attendance
            ->clocked_out_at
            ->setSecond(0)
            ->format('Y-m-d H:i:s')
            !== $attributes['clocked_out_at'];

        $isBreakTimeCorrected = false;
        $endedBreakTimes      = $attendance->endedBreakTimes;
        foreach ($attributes['breaks'] as $break) {
            if (! array_key_exists('break_time_id', $break)) {
                continue;
            }

            $isStartedAtCorrected = $endedBreakTimes
                ->find($break['break_time_id'])
                ->started_at
                ->setSecond(0)
                ->format('Y-m-d H:i:s')
                !== $break['started_at'];
            $isEndedAtCorrected = $endedBreakTimes
                ->find($break['break_time_id'])
                ->ended_at
                ->setSecond(0)
                ->format('Y-m-d H:i:s')
                !== $break['ended_at'];

            if ($isStartedAtCorrected || $isEndedAtCorrected) {
                $isBreakTimeCorrected = true;
                break;
            }
        }

        return $isClockedInAtCorrected
            || $isClockedOutAtCorrected
            || $isBreakTimeCorrected;
    }
}
