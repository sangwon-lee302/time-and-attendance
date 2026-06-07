<?php

namespace App\Models;

use App\ApprovalStatus;
use Carbon\CarbonImmutable;
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
 * @property ApprovalStatus $status
 * @property CarbonImmutable $clocked_in_at
 * @property CarbonImmutable $clocked_out_at
 * @property string $remarks
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 *
 * @method static \Database\Factories\AttendanceCorrectionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AttendanceCorrection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AttendanceCorrection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AttendanceCorrection query()
 *
 * @property-read Attendance $attendance
 * @property-read Collection<int, BreakTimeCorrection> $breakTimeCorrections
 * @property-read int|null $break_time_corrections_count
 *
 * @mixin \Eloquent
 */
#[Fillable('status', 'clocked_in_at', 'clocked_out_at', 'remarks')]
class AttendanceCorrection extends Model
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
            'status'         => ApprovalStatus::class,
            'clocked_in_at'  => 'datetime',
            'clocked_out_at' => 'datetime',
            'created_at'     => 'datetime',
            'updated_at'     => 'datetime',
        ];
    }

    /**
     * Get the attendance that owns the attendance correction.
     *
     * @return BelongsTo<Attendance, $this>
     */
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * Get the break time corrections for the attendance correction.
     *
     * @return HasMany<BreakTimeCorrection, $this>
     */
    public function breakTimeCorrections(): HasMany
    {
        return $this->hasMany(BreakTimeCorrection::class);
    }
}
