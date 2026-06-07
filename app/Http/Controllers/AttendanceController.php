<?php

namespace App\Http\Controllers;

use App\ApprovalStatus;
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

        $linkForPreviousMonth = $this->getMonthlyIndexUrl($month->subMonth());
        $linkForNextMonth     = $this->getMonthlyIndexUrl($month->addMonth());

        $displayData = $attendanceService->prepareIndexView(
            Auth::user(),
            $month->startOfMonth(),
            $month->endOfMonth()
        );

        return view('attendances.index', [
            'month'                => $month,
            'linkForPreviousMonth' => $linkForPreviousMonth,
            'linkForNextMonth'     => $linkForNextMonth,
            'displayData'          => $displayData,
        ]);
    }

    /**
     * Get a link for an attendance index page for the given month.
     */
    private function getMonthlyIndexUrl(CarbonImmutable $month): string
    {
        return route('attendances.index', [
            'month' => $month->format('Y-m'),
        ]);
    }

    /**
     * Display the specified attendance.
     */
    public function show(Attendance $attendance): View
    {
        $attendance->load([
            'user:id,name',
            'breakTimes:id,attendance_id,started_at,ended_at',
            'attendanceCorrections' => function ($query) {
                $query->where('status', ApprovalStatus::Pending)
                    ->select('id', 'attendance_id', 'remarks');
            },
        ]);

        $displayData = $attendance->toDisplayData();

        return view('attendances.show', ['displayData' => $displayData]);
    }
}
