<?php

namespace App;

enum ApplicationStatus: int
{
    case Pending  = 0;
    case Approved = 1;

    /**
     * Get the label for each case.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending  => '承認待ち',
            self::Approved => '承認済み',
        };
    }
}
