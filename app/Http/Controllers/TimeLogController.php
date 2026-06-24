<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class TimeLogController extends Controller
{
    public function create(): View
    {
        $status = Auth::user()->current_attendance_status;

        return view('time-logs.create', ['status' => $status]);
    }

    public function clockIn(): RedirectResponse
    {
        Auth::user()->attendances()->create([
            'date'          => today(),
            'clocked_in_at' => now(),
        ]);

        return redirect()->route('time-logs.create');
    }

    public function clockOut(): RedirectResponse
    {
        $this->getTodaysAttendance()->update(['clocked_out_at' => now()]);

        return redirect()->route('time-logs.create');
    }

    public function startBreak(): RedirectResponse
    {
        $this->getTodaysAttendance()->breakTimes()->create(['started_at' => now()]);

        return redirect()->route('time-logs.create');
    }

    public function endBreak(): RedirectResponse
    {
        $this
            ->getTodaysAttendance()
            ->breakTimes()
            ->whereNull('ended_at')
            ->firstOrFail()
            ->update(['ended_at' => now()]);

        return redirect()->route('time-logs.create');
    }

    protected function getTodaysAttendance(): Attendance
    {
        return Auth::user()
            ->attendances()
            ->whereBetween('date', [today()->startOfDay(), today()->endOfDay()])
            ->firstOrFail();
    }
}
