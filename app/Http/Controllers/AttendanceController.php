<?php

namespace App\Http\Controllers;

use App\AttendanceCorrectionApplicationStatus;
use App\Models\Attendance;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the attendances.
     */
    public function index()
    {
        $month = CarbonImmutable::createFromFormat('Y-m',
            request()->query('month', now()->format('Y-m'))
        );

        $dates = collect(CarbonPeriod::create(
            $month->startOfMonth(),
            $month->endOfMonth()
        ))->map(fn ($date) => $date->format('Y-m-d'))->toArray();

        $attendances = auth()->user()->attendances()
            ->with(['breakTimes' => function ($query) {
                $query->whereNotNull('ended_at');
            }])
            // DATE() is necessary for sqlite in-memory testing since sqlite does not have a date type and stores dates as text
            ->whereIn(DB::raw('DATE(date)'), $dates)
            ->get()
            ->keyBy(function ($attendance) {
                return $attendance->date->format('Y-m-d');
            });

        return view('attendances.index', [
            'month'       => $month,
            'dates'       => $dates,
            'attendances' => $attendances,
        ]);
    }

    /**
     * Display the specified attendance.
     */
    public function show(Attendance $attendance)
    {
        $attendance->load([
            'breakTimes',
            'attendanceCorrectionApplications' => function ($query) {
                $query->whereStatus(AttendanceCorrectionApplicationStatus::Pending)
                    ->select('attendance_id', 'remarks');
            },
        ]);

        $pendingApplication = $attendance->attendanceCorrectionApplications->first();

        return view('attendances.show', [
            'attendance'         => $attendance,
            'pendingApplication' => $pendingApplication,
        ]);
    }
}
