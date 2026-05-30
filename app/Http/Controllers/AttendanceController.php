<?php

namespace App\Http\Controllers;

use App\AttendanceCorrectionApplicationStatus;
use App\Models\Attendance;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the attendances.
     */
    public function index(Request $request): View
    {
        $month = CarbonImmutable::createFromFormat('Y-m',
            $request->query('month', now()->format('Y-m'))
        );
        $startOfMonth = $month->startOfMonth();
        $endOfMonth   = $month->endOfMonth();

        $attendances = Auth::user()->attendances()
            ->whereBetween('date', [
                $startOfMonth->format('Y-m-d H:i:s'),
                $endOfMonth->format('Y-m-d H:i:s'),
            ])
            ->with(['breakTimes' => function ($query) {
                $query->whereNotNull('ended_at')
                    ->select('attendance_id', 'started_at', 'ended_at');
            }])
            ->get()
            ->keyBy(fn (Attendance $attendance) => $attendance->date->day);

        $displayData = collect(CarbonPeriod::create($startOfMonth, $endOfMonth))
            ->map(fn (Carbon $date) => [
                'date'       => $date,
                'attendance' => $attendances->get($date->day),
            ]);

        return view('attendances.index', [
            'month'       => $month,
            'displayData' => $displayData,
        ]);
    }

    /**
     * Display the specified attendance.
     */
    public function show(Attendance $attendance): View
    {
        $attendance->load([
            'breakTimes' => function ($query) {
                $query->select('id', 'attendance_id', 'started_at', 'ended_at');
            },
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
