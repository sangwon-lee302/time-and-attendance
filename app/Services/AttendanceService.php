<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriodImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceService
{
    /**
     * Prepare a set of data necessary for rendering monthly attendance index page.
     *
     * @return array<string, User|CarbonImmutable|string|array<array<string, CarbonImmutable|Attendance|null>>>
     */
    public function prepareMonthlyIndexView(
        User $user,
        CarbonImmutable $month,
    ): array {
        $startOfMonth = $month->startOfMonth();
        $endOfMonth   = $month->endOfMonth();

        $attendances = $user->attendances()
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->with(['breakTimes' => fn ($query) => $query->whereNotNull('ended_at')
                ->select('id', 'attendance_id', 'started_at', 'ended_at'),
            ])
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

    /**
     * Export a user's monthly attendance list as a CSV.
     */
    public function CSVExport(User $user, Request $request): StreamedResponse
    {
        $month = CarbonImmutable::createFromFormat('Y-m',
            $request->query('month', now()->format('Y-m'))
        );

        $attendances = $user->attendances()
            ->whereBetween('date', [$month->startOfMonth(), $month->endOfMonth()])
            ->with(['breakTimes:id,attendance_id,started_at,ended_at'])
            ->lazy();

        $response = new StreamedResponse(function () use ($attendances) {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['日付', '出勤', '退勤', '休憩', '合計']);

            foreach ($attendances as $attendance) {
                fputcsv($handle, [
                    $attendance->date->isoFormat('MM/DD(ddd)'),
                    $attendance->clocked_in_at,
                    $attendance->clocked_out_at ?? '',
                    $attendance->total_break_time->format('%h:%I'),
                    $attendance->total_working_time?->format('%h:%I') ?? '',
                ]);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $fileName = "attendance_{$month->format('Ym')}_{$user->name}.csv";
        $response->headers->set(
            'Content-Disposition',
            'attachment; filename="'.$fileName.'"',
        );

        return $response;
    }
}
