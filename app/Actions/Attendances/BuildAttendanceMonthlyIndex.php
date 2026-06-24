<?php

namespace App\Actions\Attendances;

use App\Models\Attendance;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriodImmutable;
use Illuminate\Support\Facades\Auth;

class BuildAttendanceMonthlyIndex
{
    /**
     * Run a query for the given user's attendances of the given month and
     * format data for rendering attendance monthly index page.
     *
     * @return array<string, User|CarbonImmutable|string|array<array<string, CarbonImmutable|Attendance|null>>>
     */
    public function build(
        User $user,
        CarbonImmutable $month,
    ): array {
        $startOfMonth = $month->startOfMonth();
        $endOfMonth   = $month->endOfMonth();

        $attendances = $user
            ->attendances()
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->withEndedBreakTimes()
            ->get()
            ->keyBy(fn (Attendance $attendance) => $attendance->date->day);

        return [
            ...(Auth::user()?->is_admin ? ['user' => $user] : []),
            'month'                => $month,
            'linkForPreviousMonth' => route('attendances.index', [
                'month' => $month->subMonth()->format('Y-m'),
            ]),
            'linkForNextMonth' => route('attendances.index', [
                'month' => $month->addMonth()->format('Y-m'),
            ]),
            'table' => collect(CarbonPeriodImmutable::create(
                $startOfMonth,
                $endOfMonth,
            ))
                ->map(fn (CarbonImmutable $date) => [
                    'date'       => $date,
                    'attendance' => $attendances->get($date->day),
                ])
                ->all(),
        ];
    }
}
