<?php

namespace App\Http\Controllers;

use App\ApprovalStatus;
use App\Http\Requests\StoreStampCorrectionRequest;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Services\StampCorrectionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StampCorrectionController extends Controller
{
    /**
     * Display a listing of stamp corrections.
     */
    public function index(Request $request): View
    {
        $stampCorrections = AttendanceCorrection::when(
            ! Auth::user()->is_admin,
            fn ($query) => $query->whereHas(
                'attendance',
                fn ($subQuery) => $subQuery->where('user_id', Auth::id()),
            ),
        )
            ->when($request->query('status') === 'approved',
                fn ($query) => $query->where('status', ApprovalStatus::Approved),
                fn ($query) => $query->where('status', ApprovalStatus::Pending),
            )
            ->with([
                'attendance:id,date,user_id',
                'attendance.user:id,name',
            ])
            ->get();

        return view('stamp-corrections.index', [
            'stampCorrections' => $stampCorrections,
        ]);
    }

    /**
     * Store a newly created stamp correction in storage.
     */
    public function store(
        StoreStampCorrectionRequest $request,
        Attendance $attendance,
        StampCorrectionService $stampCorrectionService
    ): RedirectResponse {
        $stampCorrectionService->storeStampCorrection(
            $request->validated(), $attendance
        );

        return redirect()->route('attendances.show', $attendance);
    }
}
