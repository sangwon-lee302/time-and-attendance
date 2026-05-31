<?php

namespace App\Http\Controllers;

use App\AttendanceCorrectionApplicationStatus;
use App\Models\Attendance;
use App\Services\AttendanceService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the attendances.
     */
    public function index(
        Request $request,
        AttendanceService $attendanceService
    ): View {
        $month = CarbonImmutable::createFromFormat('Y-m',
            $request->query('month', now()->format('Y-m'))
        );

        $displayData = $attendanceService->prepareIndexView(
            Auth::user(),
            $month->startOfMonth(),
            $month->endOfMonth()
        );

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
            'breakTimes:id,attendance_id,started_at,ended_at',
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
