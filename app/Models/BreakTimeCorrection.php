<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property int $id
 * @property int $attendance_correction_id
 * @property int|null $break_time_id
 * @property CarbonImmutable $new_started_at
 * @property CarbonImmutable $new_ended_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTimeCorrection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTimeCorrection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTimeCorrection query()
 *
 * @mixin \Eloquent
 */
#[Fillable(['break_time_id', 'new_started_at', 'new_ended_at'])]
class BreakTimeCorrection extends Model
{
    #[Override]
    protected function casts()
    {
        return [
            'new_started_at' => 'datetime',
            'new_ended_at'   => 'datetime',
            'created_at'     => 'datetime',
            'updated_at'     => 'datetime',
        ];
    }
}
