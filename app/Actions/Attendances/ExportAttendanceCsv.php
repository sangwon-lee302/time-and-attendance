<?php

namespace App\Actions\Attendances;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportAttendanceCsv
{
    /**
     * Export the given user's attendances of the given month as a CSV.
     */
    public function export(User $user, Request $request): StreamedResponse
    {
        $month = CarbonImmutable::createFromFormat('Y-m',
            $request->query('month', now()->format('Y-m'))
        );

        $attendances = $user
            ->attendances()
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
