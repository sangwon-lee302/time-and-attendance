<?php

namespace App;

enum BreakTimeCorrectionType: string
{
    case Update = 'update';
    case Add    = 'add';

    /**
     * Get the label for each case.
     */
    public function label(): string
    {
        return match ($this) {
            self::Update => '更新',
            self::Add    => '追加',
        };
    }
}
