<?php

namespace App;

enum CorrectionStatus: string
{
    case Pending  = 'pending';
    case Approved = 'approved';

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
