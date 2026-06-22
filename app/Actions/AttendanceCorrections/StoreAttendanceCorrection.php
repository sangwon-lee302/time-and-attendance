<?php

namespace App\Actions\AttendanceCorrections;

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StoreAttendanceCorrection
{
    /**
     * Store a new attendance correction in database.
     */
    public function store(
        array $attributes,
        Attendance $attendance,
    ): ?AttendanceCorrection {
        $attributes['breaks'] = collect($attributes['breaks'] ?? [])
            ->filter(fn (array $breakTimeAttributes) => Arr::get(
                $breakTimeAttributes,
                'break_time_id',
                false,
            )
                || Arr::get($breakTimeAttributes, 'started_at', false),
            )
            ->all();

        if (blank($attributes['breaks'])) { // this is an incorrect implementation.
            return null;
        }

        if (Auth::guest()) {
            throw new RuntimeException(
                'Authentication is required to make a correction',
            );
        }

        return DB::transaction(function () use ($attributes, $attendance) {
            $attendanceCorrection = $attendance
                ->attendanceCorrections()
                ->create([
                    'requested_by'   => Auth::id(),
                    'clocked_in_at'  => $attributes['clocked_in_at'],
                    'clocked_out_at' => $attributes['clocked_out_at'],
                    'remarks'        => $attributes['remarks'],
                ]);

            $attendanceCorrection
                ->breakTimeCorrections()
                ->createMany(collect($attributes['breaks'])
                    ->map(fn (array $break) => [
                        'break_time_id' => $break['break_time_id'] ?? null,
                        'started_at'    => $break['started_at'],
                        'ended_at'      => $break['ended_at'],
                    ])
                    ->all()
                );

            return $attendanceCorrection;
        });
    }
}
