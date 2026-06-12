<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceCorrection;
use App\Services\StampCorrectionService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Log;
use Throwable;

class StampCorrectionController extends Controller
{
    public function show(AttendanceCorrection $attendanceCorrection): View
    {
        $attendanceCorrection->load([
            'attendance:id,date,user_id',
            'attendance.user:id,name',
            'attendance.breakTimes' => fn (Builder $query) => $query
                ->whereNotNull('ended_at')
                ->select('id', 'attendance_id', 'started_at', 'ended_at'),
        ]);

        $displayData = $attendanceCorrection->toDisplayData();

        return view('admin.stamp-corrections.show', [
            'displayData' => $displayData,
        ]);
    }

    public function approve(
        AttendanceCorrection $attendanceCorrection,
        StampCorrectionService $stampCorrectionService,
    ): RedirectResponse {
        try {
            $stampCorrectionService->approveStampCorrection($attendanceCorrection);

            return redirect()->back();
        } catch (Throwable $th) {
            Log::error('勤怠修正申請承認エラー: '.$th->getMessage(), ['exception' => $th]);

            return redirect()->back();
        }
    }
}
