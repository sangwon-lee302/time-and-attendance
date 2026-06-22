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
    public function store(
        array $attributes,
        Attendance $attendance,
    ): ?AttendanceCorrection {
        $attributes['breaks'] = collect($attributes['breaks'] ?? [])
            ->filter(fn (array $breakTimeAttributes) => array_key_exists(
                'break_time_id',
                $breakTimeAttributes,
            )
                || filled($breakTimeAttributes['started_at']),
            )
            ->all();

        if (! $this->checkForCorrections($attributes, $attendance)) {
            return null;
        }

        if (Auth::guest()) {
            throw new RuntimeException(
                'Authentication is required to make a correction',
            );
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
    protected function checkForCorrections(
        array $attributes,
        Attendance $attendance,
    ): bool {
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
        $breakTimes           = $attendance->breakTimes;
        foreach ($attributes['breaks'] as $breakTimeAttributes) {
            if (! array_key_exists('break_time_id', $breakTimeAttributes)) {
                continue;
            }

            $isStartedAtCorrected = $breakTimes
                ->find($breakTimeAttributes['break_time_id'])
                ->started_at
                ->setSecond(0)
                ->format('Y-m-d H:i:s')
                !== $breakTimeAttributes['started_at'];
            $isEndedAtCorrected = $breakTimes
                ->find($breakTimeAttributes['break_time_id'])
                ->ended_at
                ->setSecond(0)
                ->format('Y-m-d H:i:s')
                !== $breakTimeAttributes['ended_at'];

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
