<?php

namespace App\Http\Controllers;

use App\AttendanceCorrectionApplicationStatus;
use App\Http\Requests\AttendanceCorrectionApplicationRequest;
use App\Models\Attendance;
use App\Models\AttendanceCorrectionApplication;
use Illuminate\Support\Facades\DB;

class AttendanceCorrectionApplicationController extends Controller
{
    /**
     * Shows an index page for the attendance correction application.
     */
    public function index()
    {
        $query = AttendanceCorrectionApplication::query();

        if (request()->query('status') == 'approved') {
            $query->whereStatus(AttendanceCorrectionApplicationStatus::Approved);
        } else {
            $query->whereStatus(AttendanceCorrectionApplicationStatus::Pending);
        }

        $applications = $query->whereHas('attendance.user', function ($query) {
            $query->whereId(auth()->id());
        })->with([
            'attendance:id,date,user_id',
            'attendance.user:id,name',
        ])->get();

        return view('attendance-correction-applications.show', [
            'applications' => $applications,
        ]);
    }

    /**
     * Store a newly created attendance correction application in storage.
     */
    public function store(AttendanceCorrectionApplicationRequest $request, Attendance $attendance)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $attendance) {
            $attendanceCorrectionApplication = $attendance->attendanceCorrectionApplications()->create([
                'new_clocked_in_at'  => $validated['new_clocked_in_at'],
                'new_clocked_out_at' => $validated['new_clocked_out_at'],
                'remarks'            => $validated['remarks'],
            ]);

            $breaks = collect($validated['breaks'] ?? [])->map(fn ($break) => [
                'break_time_id'  => $break['break_time_id'] ?? null,
                'new_started_at' => $break['new_started_at'],
                'new_ended_at'   => $break['new_ended_at'],
            ])->all();

            $attendanceCorrectionApplication->breakTimeCorrectionApplications()
                ->createMany($breaks);
        });

        return redirect()->route('attendances.show', $attendance);
    }
}
