<?php

namespace App\Models;

use App\CorrectionStatus;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use Database\Factories\AttendanceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;

/**
 * @property int $id
 * @property int $user_id
 * @property CarbonImmutable $date
 * @property CarbonImmutable $clocked_in_at
 * @property CarbonImmutable|null $clocked_out_at
 * @property CarbonImmutable|null $deleted_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read User $user
 * @property-read Collection<int, BreakTime> $breakTimes
 * @property-read int|null $break_times_count
 * @property-read Collection<int, BreakTime> $endedBreakTimes
 * @property-read int|null $ended_break_times_count
 * @property-read Collection<int, AttendanceCorrection> $attendanceCorrections
 * @property-read int|null $attendance_corrections_count
 * @property-read CarbonInterval $total_break_time
 * @property-read CarbonInterval $total_working_time
 *
 * @method static \Database\Factories\AttendanceFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance withoutTrashed()
 * @method static Builder<static>|Attendance withEndedBreakTimes()
 *
 * @mixin \Eloquent
 */
#[Fillable(['date', 'clocked_in_at', 'clocked_out_at'])]
class Attendance extends Model
{
    /** use HasFactory<AttendanceFactory> */
    use HasFactory, SoftDeletes;

    #[Override]
    protected function casts(): array
    {
        return [
            'date'           => 'date',
            'clocked_in_at'  => 'datetime',
            'clocked_out_at' => 'datetime',
            'deleted_at'     => 'datetime',
            'created_at'     => 'datetime',
            'updated_at'     => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<AttendanceCorrection, $this> */
    public function attendanceCorrections(): HasMany
    {
        return $this->hasMany(AttendanceCorrection::class);
    }

    /** @return HasMany<BreakTime, $this> */
    public function breakTimes(): HasMany
    {
        return $this->hasMany(BreakTime::class);
    }

    /** @return HasMany<BreakTime, $this> */
    public function endedBreakTimes(): HasMany
    {
        return $this->hasMany(BreakTime::class)->whereNotNull('ended_at');
    }

    /** @return Attribute<CarbonInterval, never> */
    protected function totalBreakTime(): Attribute
    {
        return Attribute::make(
            get: fn () => CarbonInterval::seconds($this
                ->endedBreakTimes
                ->sum(fn (BreakTime $breakTime) => $breakTime
                    ->started_at
                    ->diffInSeconds($breakTime->ended_at),
                )
            )
                ->cascade(),
        );
    }

    /** @return Attribute<CarbonInterval|null, never> */
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

    #[Scope]
    protected function withEndedBreakTimes(Builder $query): void
    {
        $query->with('endedBreakTimes:id,attendance_id,started_at,ended_at');
    }

    /** @return array<string, int|string|array<string, int|string>|bool> */
    public function toDisplayData(): array
    {
        $pendingAttendanceCorrection = $this
            ->attendanceCorrections()
            ->where('status', CorrectionStatus::Pending)
            ->first();

        return [
            'id'           => $this->id,
            'name'         => $this->user->name,
            'year'         => $this->date->format('Y年'),
            'date'         => $this->date->format('n月j日'),
            'clockedInAt'  => $this->clocked_in_at->format('H:i'),
            'clockedOutAt' => $this->clocked_out_at?->format('H:i') ?? '',
            'breakTimes'   => $this->endedBreakTimes->map(fn (BreakTime $breakTime) => [
                'id'        => $breakTime->id,
                'startedAt' => $breakTime->started_at->format('H:i'),
                'endedAt'   => $breakTime->ended_at?->format('H:i') ?? '',
            ]),
            'remarks'     => $pendingAttendanceCorrection->remarks ?? '',
            'isPending'   => $pendingAttendanceCorrection !== null,
            'breaksCount' => $this->endedBreakTimes()->count(),
        ];
    }
}
