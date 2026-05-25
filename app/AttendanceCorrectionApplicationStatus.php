<?php

namespace App;

enum AttendanceCorrectionApplicationStatus: int
{
    case Pending  = 0;
    case Approved = 1;

    /**
     * Get the label for each case.
     */
    public function label(): string
    {
        return match ($this) {
            AttendanceCorrectionApplicationStatus::Pending  => '承認待ち',
            AttendanceCorrectionApplicationStatus::Approved => '承認済み',
        };
    }
}
