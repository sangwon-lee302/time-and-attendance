<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Override;

/**
 * @property int $id
 * @property int $user_id
 * @property string $date
 * @property string $clocked_in_at
 * @property string|null $clocked_out_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereClockedInAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereClockedOutAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['date', 'clocked_in_at', 'clocked_out_at'])]
class Attendance extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'date'           => 'datetime',
            'clocked_in_at'  => 'datetime',
            'clocked_out_at' => 'datetime',
            'created_at'     => 'datetime',
            'updated_at'     => 'datetime',
        ];
    }

    /**
     * Get the break times for the attendance.
     * 
     * @return HasMany<BreakTime, $this>
     */
    public function breakTimes(): HasMany
    {
        return $this->hasMany(BreakTime::class);
    }

    /**
     * Resolve the attendance status of the given user for today.
     *
     * Return '勤務外' if no attendance exists, '退勤済' if the user has already
     * clocked out, '休憩中' if a break is currently ongoing, or '出勤中' otherwise.
     */
    public static function resolveStatusForToday(User $user): string
    {
        $attendance = $user->attendances()->where('date', today())->first();

        if (! $attendance) {
            return '勤務外';
        }

        if ($attendance->clocked_out_at) {
            return '退勤済';
        }

        $isBreakOngoing = $attendance->breakTimes()
            ->whereNull('ended_at')
            ->exists();

        return $isBreakOngoing ? '休憩中' : '出勤中';
    }
}
