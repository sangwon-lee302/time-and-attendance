<?php

namespace App\Http\Controllers;

use App\Models\Attendance;

class TimeLogController extends Controller
{
    /**
     * Show time log creation view.
     */
    public function create()
    {
        $status = Attendance::resolveStatusForToday(auth()->user());

        return view('time-logs.create', [
            'status' => $status,
        ]);
    }

    /**
     * Store a newly created attendance resource in storage.
     */
    public function clockIn()
    {
        auth()->user()->attendances()->create([
            'date'          => today(),
            'clocked_in_at' => now(),
        ]);

        return redirect()->route('time-logs.create');
    }

    /**
     * Update clocked out time for the attendance resource in storage.
     */
    public function clockOut()
    {
        $attendance = auth()->user()->attendances()->where('date', today())->first();

        if ($attendance) {
            $attendance->update([
                'clocked_out_at' => now(),
            ]);
        }

        return redirect()->route('time-logs.create');
    }

    /**
     * Store a newly created break time resource in storage.
     */
    public function breakStart()
    {
        $attendance = auth()->user()->attendances()->where('date', today())->first();

        if ($attendance) {
            $attendance->breakTimes()->create([
                'started_at' => now(),
            ]);
        }

        return redirect()->route('time-logs.create');
    }

    /**
     * Update break end time for the attendance resource in storage.
     */
    public function breakEnd()
    {
        $attendance = auth()->user()->attendances()->where('date', today())->first();

        if ($attendance) {
            $attendance->breakTimes()->whereNull('ended_at')->first()?->update([
                'ended_at' => now(),
            ]);
        }

        return redirect()->route('time-logs.create');
    }
}
