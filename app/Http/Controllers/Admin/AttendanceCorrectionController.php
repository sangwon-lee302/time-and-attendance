<?php

namespace App\Http\Controllers\Admin;

use App\Actions\AttendanceCorrections\ApproveAttendanceCorrection;
use App\Http\Controllers\Controller;
use App\Models\AttendanceCorrection;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Log;
use Throwable;

class AttendanceCorrectionController extends Controller
{
    public function show(AttendanceCorrection $attendanceCorrection): View
    {
        $attendanceCorrection->load([
            'attendance:id,date,user_id',
            'attendance.user:id,name',
            'attendance.breakTimes' => fn ($query) => $query
                ->whereNotNull('ended_at')
                ->select('id', 'attendance_id', 'started_at', 'ended_at'),
        ]);

        $displayData = $attendanceCorrection->toDisplayData();

        return view('admin.attendance-corrections.show', [
            'displayData' => $displayData,
        ]);
    }

    public function approve(
        AttendanceCorrection $attendanceCorrection,
        ApproveAttendanceCorrection $approveAttendanceCorrection,
    ): RedirectResponse {
        try {
            $approveAttendanceCorrection->approve($attendanceCorrection);
        } catch (Throwable $th) {
            Log::error('勤怠修正申請承認エラー: '.$th->getMessage(), ['exception' => $th]);
        }

        return redirect()->back();
    }
}
