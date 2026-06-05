<?php

use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\AuthenticatedSessionController;
use App\Http\Controllers\Admin\CorrectionApplicationController as AdminCorrectionApplicationController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CorrectionApplicationController;
use App\Http\Controllers\TimeLogController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('attendance')->group(function () {
        Route::get('/', [TimeLogController::class, 'create'])->name('time-logs.create');
        Route::post('clock-in', [TimeLogController::class, 'clockIn'])
            ->name('time-logs.clock-in');
        Route::patch('clock-out', [TimeLogController::class, 'clockOut'])
            ->name('time-logs.clock-out');
        Route::post('break-start', [TimeLogController::class, 'breakStart'])
            ->name('time-logs.break-start');
        Route::patch('break-end', [TimeLogController::class, 'breakEnd'])
            ->name('time-logs.break-end');

        Route::get('list', [AttendanceController::class, 'index'])
            ->name('attendances.index');
        Route::get('detail/{attendance}', [AttendanceController::class, 'show'])
            ->can('view', 'attendance')
            ->name('attendances.show');
    });

    Route::get(
        'stamp_correction_request/list',
        [CorrectionApplicationController::class, 'index']
    )
        ->name('attendance-correction-applications.index');
    Route::post(
        'attendance-correction-application/{attendance}',
        [CorrectionApplicationController::class, 'store']
    )
        ->name('attendance-correction-applications.store');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])
            ->name('login');
        Route::post('login', [AuthenticatedSessionController::class, 'store'])
            ->name('login');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
            ->name('logout');

        Route::get('attendance/list', [AdminAttendanceController::class, 'dailyIndex'])
            ->name('attendances.daily-index');
        Route::get(
            'attendance/staff/{user}',
            [AdminAttendanceController::class, 'monthlyIndex']
        )
            ->name('attendances.monthly-index');
        Route::get('attendance/{attendance}', [AttendanceController::class, 'show'])
            ->name('attendances.show');
        Route::patch(
            'attendance/{attendance}',
            [AdminAttendanceController::class, 'update']
        )
            ->name('attendances.update');

        Route::get('staff/list', [UserController::class, 'index'])->name('users.index');

        Route::get(
            'attendance/{user}/export',
            [AdminAttendanceController::class, 'export']
        )
            ->name('export');
    });
});
