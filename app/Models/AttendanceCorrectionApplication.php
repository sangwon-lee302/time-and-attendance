<?php

namespace App\Models;

use App\ApplicationStatus;
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
 * @property ApplicationStatus $status
 * @property CarbonImmutable $new_clocked_in_at
 * @property CarbonImmutable $new_clocked_out_at
 * @property string $remarks
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 *
 * @method static \Database\Factories\AttendanceCorrectionApplicationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AttendanceCorrectionApplication newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AttendanceCorrectionApplication newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AttendanceCorrectionApplication query()
 *
 * @property-read Attendance $attendance
 * @property-read Collection<int, BreakTimeCorrectionApplication> $breakTimeCorrectionApplications
 * @property-read int|null $break_time_correction_applications_count
 *
 * @mixin \Eloquent
 */
#[Fillable('status', 'new_clocked_in_at', 'new_clocked_out_at', 'remarks')]
class AttendanceCorrectionApplication extends Model
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
            'status'             => ApplicationStatus::class,
            'new_clocked_in_at'  => 'datetime',
            'new_clocked_out_at' => 'datetime',
            'created_at'         => 'datetime',
            'updated_at'         => 'datetime',
        ];
    }

    /**
     * Get the attendance that owns the attendance correction application.
     *
     * @return BelongsTo<Attendance, $this>
     */
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * Get the break time correction applications for the attendance correction application.
     *
     * @return HasMany<BreakTimeCorrectionApplication, $this>
     */
    public function breakTimeCorrectionApplications(): HasMany
    {
        return $this->hasMany(BreakTimeCorrectionApplication::class);
    }
}
