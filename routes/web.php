<?php

use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\AttendanceCorrectionController as AdminAttendanceCorrectionController;
use App\Http\Controllers\Admin\AuthenticatedSessionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceCorrectionController;
use App\Http\Controllers\TimeLogController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    // for punch in and out
    Route::controller(TimeLogController::class)
        ->prefix('attendance')
        ->name('time-logs.')
        ->group(function () {
            Route::get('/', 'create')->name('create');
            Route::post('clock-in', 'clockIn')->name('clock-in');
            Route::patch('clock-out', 'clockOut')->name('clock-out');
            Route::post('start-break', 'startBreak')->name('start-break');
            Route::patch('end-break', 'endBreak')->name('end-break');
        });

    // for attendances
    Route::controller(AttendanceController::class)
        ->prefix('attendance')
        ->name('attendances.')
        ->group(function () {
            Route::get('list', 'index')->name('index');
            Route::get('detail/{attendance}', 'show')
                ->can('view', 'attendance')
                ->name('show');
        });

    // for attendance corrections
    Route::controller(AttendanceCorrectionController::class)
        ->name('attendance-corrections.')
        ->group(function () {
            Route::get('stamp_correction_request/list', 'index')->name('index');
            Route::post('attendance-correction/{attendance}', 'store')->name('store');
        });
});

// for admin login
Route::middleware(['guest'])
    ->controller(AuthenticatedSessionController::class)
    ->prefix('admin/login')
    ->name('admin.login')
    ->group(function () {
        Route::get('/', 'create');
        Route::post('/', 'store')->name('.store');
    });

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // for admin logout
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
            ->name('logout');

        // for attendances
        Route::controller(AdminAttendanceController::class)
            ->prefix('attendance')
            ->name('attendances.')
            ->group(function () {
                Route::get('list', 'dailyIndex')->name('daily-index');
                Route::get('staff/{user}', 'monthlyIndex')->name('monthly-index');
                Route::put('{attendance}', 'update')->name('update');
                Route::get('{user}/export', 'export')->name('export');
            });
        // the following route reuses AttendanceController instead of
        // AdminAttendanceController
        Route::get('attendance/{attendance}', [AttendanceController::class, 'show'])
            ->name('attendances.show');

        // for users
        Route::get('staff/list', [UserController::class, 'index'])
            ->name('users.index');
    });

// for admin attendance corrections
// URIs aren't prefixed by 'admin/' to comply with the specs
Route::middleware(['auth', 'admin'])
    ->controller(AdminAttendanceCorrectionController::class)
    ->name('admin.attendance-corrections.')
    ->group(function () {
        Route::get('stamp_correction_request/approve/{attendance_correction}', 'show')
            ->name('show');
        Route::put('attendance-correction/approve/{attendance_correction}', 'approve')
            ->name('approve');
    });
