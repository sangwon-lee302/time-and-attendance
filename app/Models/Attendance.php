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
 *
 * @property-read User $user
 * @property-read Collection<int, BreakTime> $breakTimes
 * @property-read int|null $break_times_count
 * @property-read Collection<int, AttendanceCorrection> $attendanceCorrections
 * @property-read int|null $attendance_corrections_count
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
     * Get the attendance corrections for the attendance.
     *
     * @return HasMany<AttendanceCorrection, $this>
     */
    public function attendanceCorrections(): HasMany
    {
        return $this->hasMany(AttendanceCorrection::class);
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

        return $attendance->breakTimes()
            ->whereNull('ended_at')
            ->exists()
                ? '休憩中'
                : '出勤中';
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
                ->filter(fn (BreakTime $breakTime) => $breakTime->ended_at !== null)
                ->sum(fn (BreakTime $breakTime) => $breakTime->started_at
                    ->diffInSeconds($breakTime->ended_at)
                )
            )
                ->cascade(),
        );
    }

    /**
     * Get the total working time for the attendance.
     *
     * @return Attribute<CarbonInterval|null, never>
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
                )
                    ->cascade();
            },
        );
    }

    /**
     * Convert the given attendance into a display data for the stamp detail table.
     *
     * @return array<string, mixed>
     */
    public function toDisplayData(): array
    {
        $pendingStampCorrection = $this->attendanceCorrections->first();

        return [
            'id'           => $this->id,
            'name'         => $this->user->name,
            'year'         => $this->date->format('Y年'),
            'date'         => $this->date->format('n月j日'),
            'clockedInAt'  => $this->clocked_in_at->format('H:i'),
            'clockedOutAt' => $this->clocked_out_at?->format('H:i') ?? '',
            'breakTimes'   => $this->breakTimes->map(fn (BreakTime $breakTime) => [
                'id'        => $breakTime->id,
                'startedAt' => $breakTime->started_at->format('H:i'),
                'endedAt'   => $breakTime->ended_at?->format('H:i') ?? '',
            ]),
            'remarks'     => $pendingStampCorrection?->remarks ?? '',
            'isPending'   => $pendingStampCorrection !== null,
            'breaksCount' => $this->breakTimes->count(),
        ];
    }
}
