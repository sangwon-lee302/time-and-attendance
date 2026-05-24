<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceCorrectionApplicationController;
use App\Http\Controllers\TimeLogController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->get('preview', fn () => view('preview'));

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
    Route::post('attendance-correction-application/{attendance}', [AttendanceCorrectionApplicationController::class, 'store'])
        ->name('store');
});
