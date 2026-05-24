<?php

namespace App\Models;

use App\AttendanceCorrectionApplicationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Override;

/**
 * @property int $id
 * @property int $attendance_id
 * @property int $status
 * @property string $new_clocked_in_at
 * @property string $new_clocked_out_at
 * @property string $remarks
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AttendanceCorrectionApplication newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AttendanceCorrectionApplication newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AttendanceCorrectionApplication query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AttendanceCorrectionApplication whereAttendanceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AttendanceCorrectionApplication whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AttendanceCorrectionApplication whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AttendanceCorrectionApplication whereNewClockedInAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AttendanceCorrectionApplication whereNewClockedOutAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AttendanceCorrectionApplication whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AttendanceCorrectionApplication whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AttendanceCorrectionApplication whereUpdatedAt($value)
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
            'status'             => AttendanceCorrectionApplicationStatus::class,
            'new_clocked_in_at'  => 'datetime',
            'new_clocked_out_at' => 'datetime',
            'created_at'         => 'datetime',
            'updated_at'         => 'datetime',
        ];
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
