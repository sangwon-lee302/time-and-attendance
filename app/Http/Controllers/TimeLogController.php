<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class TimeLogController extends Controller
{
    /**
     * Show time log creation view.
     */
    public function create(): View
    {
        $status = Attendance::resolveStatusForToday(Auth::user());

        return view('time-logs.create', [
            'status' => $status,
        ]);
    }

    /**
     * Store a newly created attendance resource in storage.
     */
    public function clockIn(): RedirectResponse
    {
        Auth::user()->attendances()->create([
            'date'          => today(),
            'clocked_in_at' => now(),
        ]);

        return redirect()->route('time-logs.create');
    }

    /**
     * Update clocked out time for the attendance resource in storage.
     */
    public function clockOut(): RedirectResponse
    {
        $attendance = Auth::user()->attendances()->where('date', today())->first();

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
    public function breakStart(): RedirectResponse
    {
        $attendance = Auth::user()->attendances()->where('date', today())->first();

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
    public function breakEnd(): RedirectResponse
    {
        $attendance = Auth::user()->attendances()->where('date', today())->first();

        if ($attendance) {
            $attendance->breakTimes()->whereNull('ended_at')->first()?->update([
                'ended_at' => now(),
            ]);
        }

        return redirect()->route('time-logs.create');
    }
}
