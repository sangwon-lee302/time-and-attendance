<?php

namespace App\Models;

use App\BreakTimeCorrectionType;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property int $id
 * @property int $attendance_correction_id
 * @property int|null $break_time_id
 * @property CarbonImmutable $started_at
 * @property CarbonImmutable $ended_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 *
 * @method static \Database\Factories\BreakTimeCorrectionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTimeCorrection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTimeCorrection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTimeCorrection query()
 *
 * @mixin \Eloquent
 */
#[Fillable(['break_time_id', 'requested_started_at', 'requested_ended_at'])]
class BreakTimeCorrection extends Model
{
    use HasFactory;

    #[Override]
    protected function casts()
    {
        return [
            'correction_type'      => BreakTimeCorrectionType::class,
            'original_started_at'  => 'datetime',
            'original_ended_at'    => 'datetime',
            'requested_started_at' => 'datetime',
            'requested_ended_at'   => 'datetime',
            'created_at'           => 'datetime',
            'updated_at'           => 'datetime',
        ];
    }

    /**
     * The model's default values for attributes.
     */
    protected $attributes = ['correction_type' => BreakTimeCorrectionType::Update];
}
