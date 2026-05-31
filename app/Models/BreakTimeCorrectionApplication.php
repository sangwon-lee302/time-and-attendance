<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property int $id
 * @property int $attendance_correction_application_id
 * @property int|null $break_time_id
 * @property CarbonImmutable $new_started_at
 * @property CarbonImmutable $new_ended_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTimeCorrectionApplication newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTimeCorrectionApplication newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTimeCorrectionApplication query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTimeCorrectionApplication whereAttendanceCorrectionApplicationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTimeCorrectionApplication whereBreakTimeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTimeCorrectionApplication whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTimeCorrectionApplication whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTimeCorrectionApplication whereNewEndedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTimeCorrectionApplication whereNewStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTimeCorrectionApplication whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['break_time_id', 'new_started_at', 'new_ended_at'])]
class BreakTimeCorrectionApplication extends Model
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
