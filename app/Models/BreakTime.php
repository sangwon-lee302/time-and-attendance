<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\BreakTimeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;

/**
 * @property int $id
 * @property int $attendance_id
 * @property CarbonImmutable $started_at
 * @property CarbonImmutable|null $ended_at
 * @property CarbonImmutable|null $deleted_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 *
 * @method static \Database\Factories\BreakTimeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTime newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTime newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTime onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTime query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTime withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BreakTime withoutTrashed()
 *
 * @mixin \Eloquent
 */
#[Fillable(['started_at', 'ended_at'])]
class BreakTime extends Model
{
    /** @use HasFactory<BreakTimeFactory> */
    use HasFactory, SoftDeletes;

    #[Override]
    protected function casts()
    {
        return [
            'started_at' => 'datetime',
            'ended_at'   => 'datetime',
            'deleted_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
