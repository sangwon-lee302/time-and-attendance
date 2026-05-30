<?php

namespace App\Http\Controllers;

use App\AttendanceCorrectionApplicationStatus;
use App\Http\Requests\AttendanceCorrectionRequest;
use App\Models\Attendance;
use App\Models\AttendanceCorrectionApplication;
use App\Services\CorrectionApplicationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceCorrectionApplicationController extends Controller
{
    /**
     * Shows an index page for the attendance correction application.
     */
    public function index(Request $request): View
    {
        $query = AttendanceCorrectionApplication::query();

        if ($request->query('status') == 'approved') {
            $query->whereStatus(AttendanceCorrectionApplicationStatus::Approved);
        } else {
            $query->whereStatus(AttendanceCorrectionApplicationStatus::Pending);
        }

        $applications = $query->whereHas('attendance.user', function ($query) {
            $query->whereId(Auth::id());
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
    public function store(
        AttendanceCorrectionRequest $request,
        Attendance $attendance,
        CorrectionApplicationService $correctionApplicationService
    ): RedirectResponse {
        $correctionApplicationService->storeCorrectionApplication(
            $request->validated(), $attendance
        );

        return redirect()->route('attendances.show', $attendance);
    }
}
