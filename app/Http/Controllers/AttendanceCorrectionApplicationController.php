<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceCorrectionApplicationRequest;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;

class AttendanceCorrectionApplicationController extends Controller
{
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
