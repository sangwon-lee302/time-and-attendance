<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the attendances.
     */
    public function index(Request $request): View
    {
        $date = CarbonImmutable::createFromFormat('Y-m-d',
            $request->query('date', now()->format('Y-m-d'))
        );

        $attendances = Attendance::with([
            'user:id,name',
            'breakTimes' => function ($query) {
                $query->whereNotNull('ended_at')
                    ->select('attendance_id', 'started_at', 'ended_at');
            },
        ])->whereDate('date', $date->format('Y-m-d'))->get();

        return view('admin.attendances-index', [
            'date'        => $date,
            'attendances' => $attendances,
        ]);
    }
}
