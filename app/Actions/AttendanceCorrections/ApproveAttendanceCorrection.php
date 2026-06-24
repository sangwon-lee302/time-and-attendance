<?php

namespace App\Actions\AttendanceCorrections;

use App\CorrectionStatus;
use App\Models\AttendanceCorrection;
use App\Models\BreakTimeCorrection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ApproveAttendanceCorrection
{
    /**
     * Approve the given attendance correction.
     *
     * @throws RuntimeException
     */
    public function approve(AttendanceCorrection $attendanceCorrection): AttendanceCorrection
    {
        if ($attendanceCorrection->status !== CorrectionStatus::Pending) {
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
                ->each(function (BreakTimeCorrection $breakTimeCorrection) use ($attendance) {
                    $breakTime = $breakTimeCorrection->break_time_id
                        ? $attendance
                            ->endedBreakTimes()
                            ->findOrFail($breakTimeCorrection->break_time_id)
                        : $attendance->breakTimes()->make();

                    $breakTime->fill([
                        'started_at' => $breakTimeCorrection->started_at,
                        'ended_at'   => $breakTimeCorrection->ended_at,
                    ]);

                    $breakTime->save();
                });

            $attendanceCorrection->update(['status' => CorrectionStatus::Approved]);

            return $attendanceCorrection;
        });
    }
}
