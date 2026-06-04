<?php

namespace App\Policies;

use App\ApplicationStatus;
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
     * Determine if the correction application for the given attendance can be created by the user.
     */
    public function createCorrectionApplication(User $user, Attendance $attendance): bool
    {
        return $user->is_admin
            || (
                $user->id === $attendance->user_id
                && $attendance->attendanceCorrectionApplications()
                    ->where('status', ApplicationStatus::Pending)
                    ->doesntExist()
            );
    }
}
