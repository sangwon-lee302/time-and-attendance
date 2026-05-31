<?php

use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\AuthenticatedSessionController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceCorrectionApplicationController;
use App\Http\Controllers\TimeLogController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->name('time-logs.')->group(function () {
    Route::get('attendance', [TimeLogController::class, 'create'])->name('create');
    Route::post('attendance/clock-in', [TimeLogController::class, 'clockIn'])->name('clock-in');
    Route::patch('attendance/clock-out', [TimeLogController::class, 'clockOut'])->name('clock-out');
    Route::post('attendance/break-start', [TimeLogController::class, 'breakStart'])->name('break-start');
    Route::patch('attendance/break-end', [TimeLogController::class, 'breakEnd'])->name('break-end');
});

Route::middleware(['auth', 'verified'])->name('attendances.')->group(function () {
    Route::get('attendance/list', [AttendanceController::class, 'index'])->name('index');
    Route::get('attendance/detail/{attendance}', [AttendanceController::class, 'show'])
        ->can('view', 'attendance')->name('show');
});

Route::middleware(['auth', 'verified'])->name('attendance-correction-applications.')->group(function () {
    Route::get('stamp_correction_request/list', [AttendanceCorrectionApplicationController::class, 'index'])
        ->name('index');
    Route::post('attendance-correction-application/{attendance}', [AttendanceCorrectionApplicationController::class, 'store'])
        ->name('store');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('login');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

        Route::get('attendance/list', [AdminAttendanceController::class, 'dailyIndex'])->name('attendances.daily-index');
        Route::get('attendance/staff/{user}', [AdminAttendanceController::class, 'monthlyIndex'])->name('attendances.monthly-index');
        Route::get('attendance/{attendance}', [AttendanceController::class, 'show'])->name('attendances.show');
        Route::patch('attendance/{attendance}', [AdminAttendanceController::class, 'update'])->name('attendances.update');

        Route::get('staff/list', [UserController::class, 'index'])->name('users.index');
    });
});
