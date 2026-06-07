<?php

namespace App\Services;

use App\ApprovalStatus;
use App\Models\Attendance;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriodImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class AttendanceService
{
    /**
     * Prepare a set of data necessary for rendering attendance index page.
     *
     * @return array<int, array<string, CarbonImmutable|Attendance|null>>
     */
    public function prepareIndexView(
        User $user,
        CarbonImmutable $start,
        CarbonImmutable $end
    ): array {
        $attendances = $user->attendances()
            ->whereBetween('date', [$start, $end])
            ->with(['breakTimes' => fn ($query) => $query->whereNotNull('ended_at')
                ->select('id', 'attendance_id', 'started_at', 'ended_at'),
            ])
            ->get()
            ->keyBy(fn (Attendance $attendance) => $attendance->date->day);

        return collect(CarbonPeriodImmutable::create($start, $end))
            ->map(fn (CarbonImmutable $date) => [
                'date'       => $date,
                'attendance' => $attendances->get($date->day),
            ])
            ->all();
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
    public function updateAttendance(
        array $attributes,
        Attendance $attendance
    ): bool {
        try {
            return DB::transaction(function () use ($attributes, $attendance) {
                // update the attendance resource
                $attendance->update([
                    'clocked_in_at'  => $attributes['new_clocked_in_at'],
                    'clocked_out_at' => $attributes['new_clocked_out_at'],
                ]);

                // update or create corresponding break time resources
                $breaks = $attendance->breakTimes()->get()->keyBy('id');

                foreach ($attributes['breaks'] as $breakData) {
                    if ($breaks->has($breakData['break_time_id'])) {
                        $breaks->get($breakData['break_time_id'])->update([
                            'started_at' => $breakData['new_started_at'],
                            'ended_at'   => $breakData['new_ended_at'],
                        ]);

                        continue;
                    }

                    $attendance->breakTimes()->create([
                        'started_at' => $breakData['new_started_at'],
                        'ended_at'   => $breakData['new_ended_at'],
                    ]);
                }

                // set the status of the attendance correction to 'approved'
                $attendance->attendanceCorrections()
                    ->where('status', ApprovalStatus::Pending)
                    ->update(['status' => ApprovalStatus::Approved]);

                return true;
            });
        } catch (Throwable $e) {
            Log::error('勤怠修正エラー: '.$e->getMessage(), ['exception' => $e]);

            return false;
        }
    }
}
