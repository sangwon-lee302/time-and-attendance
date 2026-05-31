<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property int $id
 * @property int $user_id
 * @property CarbonImmutable $date
 * @property CarbonImmutable $clocked_in_at
 * @property CarbonImmutable|null $clocked_out_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 *
 * @method static \Database\Factories\AttendanceFactory factory($count = null, $state = [])
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
 * @property-read User $user
 * @property-read Collection<int, BreakTime> $breakTimes
 * @property-read int|null $break_times_count
 * @property-read Collection<int, AttendanceCorrectionApplication> $attendanceCorrectionApplications
 * @property-read int|null $attendance_correction_applications_count
 * @property-read CarbonInterval $total_break_time
 * @property-read CarbonInterval $total_working_time
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
            'date'           => 'date',
            'clocked_in_at'  => 'datetime',
            'clocked_out_at' => 'datetime',
            'created_at'     => 'datetime',
            'updated_at'     => 'datetime',
        ];
    }

    /**
     * Get the user that owns the attendance.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the correction applications for the attendance.
     *
     * @return HasMany<AttendanceCorrectionApplication, $this>
     */
    public function attendanceCorrectionApplications(): HasMany
    {
        return $this->hasMany(AttendanceCorrectionApplication::class);
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

    /**
     * Get the total break time for the attendance.
     *
     * @return Attribute<CarbonInterval, never>
     */
    protected function totalBreakTime(): Attribute
    {
        return Attribute::make(
            get: fn () => CarbonInterval::seconds($this->breakTimes
                ->filter(fn (BreakTime $break) => $break->ended_at !== null)
                ->sum(fn (BreakTime $break) => $break->started_at
                    ->diffInSeconds($break->ended_at)
                )
            )->cascade()
        );
    }

    /**
     * Get the total working time for the attendance.
     *
     * @return Attribute<CarbonInterval, never>
     */
    protected function totalWorkingTime(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->clocked_out_at === null) {
                    return null;
                }

                return CarbonInterval::seconds(
                    $this->clocked_in_at->diffInSeconds($this->clocked_out_at)
                    - $this->total_break_time->totalSeconds
                )->cascade();
            }
        );
    }
}
