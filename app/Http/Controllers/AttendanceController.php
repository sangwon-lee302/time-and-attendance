<?php

namespace App\Http\Controllers;

use App\Actions\Attendances\BuildAttendanceIndex;
use App\CorrectionStatus;
use App\Models\Attendance;
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
        BuildAttendanceIndex $action,
    ): View {
        $month = CarbonImmutable::createFromFormat('Y-m',
            $request->query('month', now()->format('Y-m'))
        );

        $data = $action->build(Auth::user(), $month);

        return view('attendances.index', ['data' => $data]);
    }

    /**
     * Display the specified attendance.
     */
    public function show(Attendance $attendance): View
    {
        $attendance->load([
            'user:id,name',
            'breakTimes:id,attendance_id,started_at,ended_at',
            'attendanceCorrections' => fn ($attendanceCorrectionQuery) => $attendanceCorrectionQuery
                ->where('status', CorrectionStatus::Pending)
                ->select('id', 'attendance_id', 'remarks'),
        ]);

        $data = $attendance->toDisplayData();

        return view('attendances.show', ['data' => $data]);
    }
}
