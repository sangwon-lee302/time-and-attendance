<?php

namespace App\Policies;

use App\CorrectionStatus;
use App\Models\Attendance;
use App\Models\User;

class AttendancePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the given attendance can be viewed by the user.
     */
    public function view(User $user, Attendance $attendance): bool
    {
        return $user->is_admin || $user->id === $attendance->user_id;
    }

    /**
     * Determine if the stamp correction for the given attendance can be created by
     * the user.
     */
    public function createStampCorrection(User $user, Attendance $attendance): bool
    {
        return $user->is_admin
            || (
                $user->id === $attendance->user_id
                && $attendance->attendanceCorrections()
                    ->where('status', CorrectionStatus::Pending)
                    ->doesntExist()
            );
    }
}
