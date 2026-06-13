<?php

namespace App\Services;

use App\ApprovalStatus;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\BreakTimeCorrection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StampCorrectionService
{
    /**
     * Store a new stamp correction.
     */
    public function storeStampCorrection(
        array $attributes,
        Attendance $attendance
    ): AttendanceCorrection {
        $attributes['breaks'] = collect($attributes['breaks'] ?? [])
            ->filter(fn (array $break) => ! empty($break['break_time_id'])
                || ! empty($break['started_at']),
            )
            ->all();

        return DB::transaction(function () use ($attributes, $attendance) {
            $attendanceCorrection = $attendance
                ->attendanceCorrections()
                ->create([
                    'clocked_in_at'  => $attributes['clocked_in_at'],
                    'clocked_out_at' => $attributes['clocked_out_at'],
                    'remarks'        => $attributes['remarks'],
                ]);

            $attendanceCorrection
                ->breakTimeCorrections()
                ->createMany(collect($attributes['breaks'] ?? [])
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
     * Approve the given stamp correction and reflect it to storage.
     */
    public function approveStampCorrection(
        AttendanceCorrection $attendanceCorrection
    ): AttendanceCorrection {
        if ($attendanceCorrection->status !== ApprovalStatus::Pending) {
            throw new RuntimeException('Only pending corrections can be approved');
        }

        $attendanceCorrection->loadMissing([
            'attendance:id',
            'breakTimeCorrections:id,attendance_correction_id,break_time_id,started_at,ended_at',
        ]);

        return DB::transaction(function () use ($attendanceCorrection) {
            $attendance = $attendanceCorrection->attendance;

            $attendance->update([
                'clocked_in_at'  => $attendanceCorrection->clocked_in_at,
                'clocked_out_at' => $attendanceCorrection->clocked_out_at,
            ]);

            // update or create corresponding break time resources
            $attendanceCorrection
                ->breakTimeCorrections
                ->each(function (BreakTimeCorrection $breakTimeCorrection) use (
                    $attendance,
                ) {
                    $breakTime = $breakTimeCorrection->break_time_id
                        ? $attendance
                            ->breakTimes()
                            ->findOrFail($breakTimeCorrection->break_time_id)
                        : $attendance->breakTimes()->make();

                    $breakTime->fill([
                        'started_at' => $breakTimeCorrection->started_at,
                        'ended_at'   => $breakTimeCorrection->ended_at,
                    ]);
                    $breakTime->save();
                });

            $attendanceCorrection->update(['status' => ApprovalStatus::Approved]);

            return $attendanceCorrection;
        });
    }
}
