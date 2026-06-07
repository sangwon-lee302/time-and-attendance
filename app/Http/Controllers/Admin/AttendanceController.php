<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStampCorrectionRequest;
use App\Models\Attendance;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\StampCorrectionService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the attendances.
     */
    public function dailyIndex(Request $request): View
    {
        $date = CarbonImmutable::createFromFormat('Y-m-d',
            $request->query('date', now()->format('Y-m-d'))
        );

        $attendances = Attendance::whereBetween('date', [
            $date->startOfDay(),
            $date->endOfDay(),
        ])
            ->with([
                'user:id,name',
                'breakTimes' => fn ($query) => $query->whereNotNull('ended_at')
                    ->select('id', 'attendance_id', 'started_at', 'ended_at'),
            ])
            ->get();

        return view('admin.attendances-index', [
            'date'        => $date,
            'attendances' => $attendances,
        ]);
    }

    /**
     * Display a listing of attendance resources of the given user.
     */
    public function monthlyIndex(
        User $user,
        Request $request,
        AttendanceService $attendanceService
    ): View {
        $month = CarbonImmutable::createFromFormat('Y-m',
            $request->query('month', now()->format('Y-m'))
        );

        $linkForPreviousMonth = $this->getMonthlyIndexUrl($user, $month->subMonth());
        $linkForNextMonth = $this->getMonthlyIndexUrl($user, $month->addMonth());

        $displayData = $attendanceService->prepareIndexView(
            $user,
            $month->startOfMonth(),
            $month->endOfMonth()
        );

        return view('attendances.index', [
            'user'                 => $user,
            'month'                => $month,
            'linkForPreviousMonth' => $linkForPreviousMonth,
            'linkForNextMonth'     => $linkForNextMonth,
            'displayData'          => $displayData,
        ]);
    }

    /**
     * Get a link of an attendance index page for the given month.
     */
    private function getMonthlyIndexUrl(
        User $user,
        CarbonImmutable $month
    ): string {
        return route('admin.attendances.monthly-index', [
            'user'  => $user,
            'month' => $month->format('Y-m'),
        ]);
    }

    /**
     * Update the specified attendance and its corresponding breaks.
     */
    public function update(
        Attendance $attendance,
        StoreStampCorrectionRequest $request,
        StampCorrectionService $stampCorrectionService,
        AttendanceService $attendanceService
    ): RedirectResponse {
        $validated = $request->validated();

        $stampCorrectionService->storeStampCorrection(
            $validated, $attendance
        );

        $attendanceService->updateAttendance($validated, $attendance);

        return redirect()->route('admin.attendances.show', $attendance);
    }

    /**
     * Export attendance information as a csv.
     */
    public function export(
        User $user,
        Request $request,
        AttendanceService $attendanceService
    ): StreamedResponse {
        return $attendanceService->CSVExport($user, $request);
    }
}
