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
            'date' => today(),
            'clocked_in_at' => now(),
        ]);

        return redirect()->route('time-logs.create');
    }
}
