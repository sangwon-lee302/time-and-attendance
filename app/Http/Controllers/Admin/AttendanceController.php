<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceCorrectionRequest;
use App\Models\Attendance;
use App\Services\AttendanceService;
use App\Services\CorrectionApplicationService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the attendances.
     */
    public function index(Request $request): View
    {
        $date = CarbonImmutable::createFromFormat('Y-m-d',
            $request->query('date', now()->format('Y-m-d'))
        );

        $attendances = Attendance::with([
            'user:id,name',
            'breakTimes' => function ($query) {
                $query->whereNotNull('ended_at')
                    ->select('attendance_id', 'started_at', 'ended_at');
            },
        ])->whereDate('date', $date->format('Y-m-d'))->get();

        return view('admin.attendances-index', [
            'date'        => $date,
            'attendances' => $attendances,
        ]);
    }

    /**
     * Update the specified attendance and its corresponding breaks.
     */
    public function update(
        AttendanceCorrectionRequest $request,
        Attendance $attendance,
        CorrectionApplicationService $correctionApplicationService,
        AttendanceService $attendanceService
    ): RedirectResponse {
        $validated = $request->validated();

        $correctionApplicationService->storeCorrectionApplication(
            $validated, $attendance
        );

        $attendanceService->updateAttendance($validated, $attendance);

        return redirect()->route('admin.attendances.show');
    }
}
