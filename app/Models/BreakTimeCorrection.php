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
 * @property CarbonImmutable $started_at
 * @property CarbonImmutable $ended_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTimeCorrection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTimeCorrection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTimeCorrection query()
 *
 * @mixin \Eloquent
 */
#[Fillable(['break_time_id', 'started_at', 'ended_at'])]
class BreakTimeCorrection extends Model
{
    #[Override]
    protected function casts()
    {
        return [
            'started_at' => 'datetime',
            'ended_at'   => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
