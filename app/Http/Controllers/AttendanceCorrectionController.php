<?php

namespace App\Http\Controllers;

use App\Actions\AttendanceCorrections\StoreAttendanceCorrection;
use App\CorrectionStatus;
use App\Http\Requests\StoreAttendanceCorrectionRequest;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Log;
use Throwable;

class AttendanceCorrectionController extends Controller
{
    /**
     * Display a listing of attendance corrections.
     */
    public function index(Request $request): View
    {
        $attendanceCorrections = AttendanceCorrection::when(
            ! Auth::user()->is_admin,
            fn ($query) => $query->whereHas(
                'attendance',
                fn ($subQuery) => $subQuery->where('user_id', Auth::id()),
            ),
        )
            ->when(
                $request->query('status') === 'approved',
                fn ($query) => $query->where('status', CorrectionStatus::Approved),
                fn ($query) => $query->where('status', CorrectionStatus::Pending),
            )
            ->with([
                'attendance:id,date,user_id',
                'attendance.user:id,name',
            ])
            ->get();

        return view('attendance-corrections.index', [
            'attendanceCorrections' => $attendanceCorrections,
        ]);
    }

    /**
     * Store a newly created attendance correction in database.
     */
    public function store(
        Attendance $attendance,
        StoreAttendanceCorrectionRequest $request,
        StoreAttendanceCorrection $action,
    ): RedirectResponse {
        try {
            $attendanceCorrection = $action->store(
                $request->validated(),
                $attendance,
            );

            if (! $attendanceCorrection) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('custom_message', '修正箇所がありません');
            }

            return redirect()->back();
        } catch (Throwable $th) {
            Log::error('勤怠修正申請保存エラー: '.$th->getMessage(), [
                'exception' => $th,
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('custom_message', 'エラーが発生しました');
        }
    }
}
