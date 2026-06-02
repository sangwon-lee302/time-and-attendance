<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property int $id
 * @property int $attendance_id
 * @property CarbonImmutable $started_at
 * @property CarbonImmutable|null $ended_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 *
 * @method static \Database\Factories\BreakTimeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTime newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTime newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTime query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTime whereAttendanceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTime whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTime whereEndedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTime whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTime whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTime whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['started_at', 'ended_at'])]
class BreakTime extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
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
