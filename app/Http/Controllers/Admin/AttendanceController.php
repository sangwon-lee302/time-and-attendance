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
use Log;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the attendances.
     */
    public function dailyIndex(Request $request): View
    {
        $date = CarbonImmutable::createFromFormat(
            'Y-m-d',
            $request->query('date', now()->format('Y-m-d')),
        );

        $attendances = Attendance::whereBetween('date', [
            $date->startOfDay(),
            $date->endOfDay(),
        ])
            ->with([
                'user:id,name',
                'breakTimes' => fn ($query) => $query
                    ->whereNotNull('ended_at')
                    ->select('id', 'attendance_id', 'started_at', 'ended_at'),
            ])
            ->get();

        return view('admin.attendances.index', [
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

        $displayData = $attendanceService->prepareMonthlyIndexView($user, $month);

        return view('attendances.index', ['displayData' => $displayData]);
    }

    /**
     * Update the specified attendance and its corresponding breaks.
     */
    public function update(
        Attendance $attendance,
        StoreStampCorrectionRequest $request,
        StampCorrectionService $stampCorrectionService,
    ): RedirectResponse {
        try {
            $stampCorrectionService->approveStampCorrection(
                $stampCorrectionService->storeStampCorrection(
                    $request->validated(),
                    $attendance,
                ),
            );

            return redirect()->back();
        } catch (Throwable $th) {
            Log::error('勤怠情報更新エラー: '.$th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withInput();
        }
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
