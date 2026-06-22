<?php

namespace App\Http\Controllers\Admin;

use App\Actions\AttendanceCorrections\ApproveAttendanceCorrection;
use App\Actions\AttendanceCorrections\StoreAttendanceCorrection;
use App\Actions\Attendances\BuildAttendanceIndex;
use App\Actions\Attendances\ExportAttendanceCsv;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttendanceCorrectionRequest;
use App\Models\Attendance;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Log;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the attendances.
     */
    public function dailyIndex(Request $request): View
    {
        $date = CarbonImmutable::createFromFormat(
            'Y-m-d',
            $request->query('date', now()->format('Y-m-d')),
        );

        $attendances = Attendance::whereBetween('date', [
            $date->startOfDay(),
            $date->endOfDay(),
        ])
            ->with([
                'user:id,name',
                'breakTimes' => fn ($query) => $query
                    ->whereNotNull('ended_at')
                    ->select('id', 'attendance_id', 'started_at', 'ended_at'),
            ])
            ->get();

        return view('admin.attendances.index', [
            'date'        => $date,
            'attendances' => $attendances,
        ]);
    }

    /**
     * Display a listing of attendance resources of the given user.
     */
    public function monthlyIndex(
        User $user,
        Request $request,
        BuildAttendanceIndex $action,
    ): View {
        $month = CarbonImmutable::createFromFormat('Y-m',
            $request->query('month', now()->format('Y-m'))
        );

        $data = $action->build($user, $month);

        return view('attendances.index', ['data' => $data]);
    }

    /**
     * Update the specified attendance and its corresponding breaks.
     */
    public function update(
        Attendance $attendance,
        StoreAttendanceCorrectionRequest $request,
        StoreAttendanceCorrection $storeAction,
        ApproveAttendanceCorrection $approveAction,
    ): RedirectResponse {
        try {
            $approveAction->approve(
                $storeAction->store($request->validated(), $attendance),
            );

            return redirect()->back();
        } catch (Throwable $th) {
            Log::error('勤怠情報更新エラー: '.$th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withInput(); // ユーザーに何も知らせないの？
        }
    }

    /**
     * Export attendance information as a csv.
     */
    public function export(
        User $user,
        Request $request,
        ExportAttendanceCsv $action,
    ): StreamedResponse {
        return $action->export($user, $request);
    }
}
