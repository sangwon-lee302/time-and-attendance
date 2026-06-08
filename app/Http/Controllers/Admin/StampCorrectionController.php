<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceCorrection;
use Illuminate\Contracts\View\View;

class StampCorrectionController extends Controller
{
    public function show(AttendanceCorrection $attendanceCorrection): View
    {
        $attendanceCorrection->load([
            'attendance:id,date,user_id',
            'attendance.user:id,name',
            'attendance.breakTimes' => fn ($query) => $query->whereNotNull('ended_at')
                ->select('id', 'attendance_id', 'started_at', 'ended_at'),
        ]);

        $displayData = $attendanceCorrection->toDisplayData();

        return view('admin.stamp-corrections.show', ['displayData' => $displayData]);
    }
}
