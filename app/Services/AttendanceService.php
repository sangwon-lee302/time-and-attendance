<?php

namespace App\Services;

use App\ApprovalStatus;
use App\Models\Attendance;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriodImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceService
{
    /**
     * Prepare a set of data necessary for rendering monthly attendance index page.
     *
     * @return array<int, array<string, CarbonImmutable|Attendance|null>>
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
            ...($user->is_admin ? ['user' => $user] : []),
            'month'                => $month,
            'linkForPreviousMonth' => route('attendances.index', [
                'month' => $month->subMonth()->format('Y-m'),
            ]),
            'linkForNextMonth' => route('attendances.index', [
                'month' => $month->addMonth()->format('Y-m'),
            ]),
            'table' => collect(CarbonPeriodImmutable::create($startOfMonth, $endOfMonth))
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
        $response->headers->set(
            'Content-Disposition',
            'attachment; filename="attendances.csv"'
        );

        return $response;
    }

    /**
     * Update the given attendance resource and its corresponding break time
     * resources.
     */
    public function updateAttendance(array $attributes, Attendance $attendance): void
    {
        DB::transaction(function () use ($attributes, $attendance) {
            $attendance->update([
                'clocked_in_at'  => $attributes['clocked_in_at'],
                'clocked_out_at' => $attributes['clocked_out_at'],
            ]);

            // update or create corresponding break time resources
            $attendance->breakTimes()->upsert(collect($attributes['breaks'])
                ->map(fn (array $break) => [
                    'id'            => $break['break_time_id'] ?? null,
                    'attendance_id' => $attendance->id,
                    'started_at'    => $break['started_at'],
                    'ended_at'      => $break['ended_at'],
                    'updated_at'    => now(),
                ])
                ->toArray(),
                ['id'],
                ['started_at', 'ended_at', 'updated_at'],
            );

            // set the status of the attendance correction to 'approved'
            $attendance
                ->attendanceCorrections()
                ->where('status', ApprovalStatus::Pending)
                ->firstOrFail()
                ->update(['status' => ApprovalStatus::Approved]);
        });
    }
}
