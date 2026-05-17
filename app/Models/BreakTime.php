<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Override;

/**
 * @property int $id
 * @property int $attendance_id
 * @property string $started_at
 * @property string|null $ended_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
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
