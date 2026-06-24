<?php

namespace App\Models;

use App\CorrectionStatus;
use Carbon\CarbonImmutable;
use Database\Factories\AttendanceCorrectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property int $id
 * @property int $attendance_id
 * @property int $requested_by
 * @property CorrectionStatus $status
 * @property CarbonImmutable $clocked_in_at
 * @property CarbonImmutable $clocked_out_at
 * @property string $remarks
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read User|null $requester
 * @property-read Attendance $attendance
 * @property-read Collection<int, BreakTimeCorrection> $breakTimeCorrections
 * @property-read int|null $break_time_corrections_count
 *
 * @method static \Database\Factories\AttendanceCorrectionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AttendanceCorrection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AttendanceCorrection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AttendanceCorrection query()
 *
 * @mixin \Eloquent
 */
#[Fillable('requested_by', 'status', 'clocked_in_at', 'clocked_out_at', 'remarks')]
class AttendanceCorrection extends Model
{
    /** @use HasFactory<AttendanceCorrectionFactory> */
    use HasFactory;

    #[Override]
    protected function casts(): array
    {
        return [
            'status'         => CorrectionStatus::class,
            'clocked_in_at'  => 'datetime',
            'clocked_out_at' => 'datetime',
            'created_at'     => 'datetime',
            'updated_at'     => 'datetime',
        ];
    }

    protected $attributes = ['status' => CorrectionStatus::Pending];

    /** @return BelongsTo<User, $this> */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /** @return BelongsTo<Attendance, $this> */
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    /** @return HasMany<BreakTimeCorrection, $this> */
    public function breakTimeCorrections(): HasMany
    {
        return $this->hasMany(BreakTimeCorrection::class);
    }

    /** @return array<string, int|string|bool|array<string, int|string>> */
    public function toDisplayData(): array
    {
        $attendance = $this->attendance;

        return [
            'id'           => $this->id,
            'name'         => $attendance->user->name,
            'year'         => $attendance->date->format('Y年'),
            'date'         => $attendance->date->format('n月j日'),
            'clockedInAt'  => $this->clocked_in_at->format('H:i'),
            'clockedOutAt' => $this->clocked_out_at->format('H:i'),
            'breakTimes'   => $this
                ->breakTimeCorrections
                ->map(fn (BreakTimeCorrection $breakTimeCorrection) => [
                    'id'        => $breakTimeCorrection->id,
                    'startedAt' => $breakTimeCorrection->started_at->format('H:i'),
                    'endedAt'   => $breakTimeCorrection->ended_at->format('H:i'),
                ]),
            'remarks'     => $this->remarks,
            'isPending'   => true,
            'breaksCount' => $this->breakTimeCorrections->count(),
            'isApproved'  => $this->status === CorrectionStatus::Approved,
        ];
    }
}
