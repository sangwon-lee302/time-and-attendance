<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\BreakTimeCorrectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
 * @method static \Database\Factories\BreakTimeCorrectionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTimeCorrection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTimeCorrection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTimeCorrection query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTimeCorrection whereAttendanceCorrectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTimeCorrection whereBreakTimeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTimeCorrection whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTimeCorrection whereEndedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTimeCorrection whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTimeCorrection whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTimeCorrection whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['break_time_id', 'started_at', 'ended_at'])]
class BreakTimeCorrection extends Model
{
    /** @use HasFactory<BreakTimeCorrectionFactory> */
    use HasFactory;

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
