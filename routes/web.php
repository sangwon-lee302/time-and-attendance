<?php

use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\AuthenticatedSessionController;
use App\Http\Controllers\Admin\StampCorrectionController as AdminStampCorrectionController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionController;
use App\Http\Controllers\TimeLogController;
use App\Http\Controllers\UserController;
use App\Models\Attendance;
use Illuminate\Support\Facades\Route;

// for admin login
Route::middleware(['guest'])
    ->controller(AuthenticatedSessionController::class)
    ->prefix('admin/login')
    ->name('admin.login')
    ->group(function () {
        Route::get('/', 'create');
        Route::post('/', 'store');
    });

Route::middleware(['auth', 'verified'])->group(function () {
    // for punch in and out
    Route::controller(TimeLogController::class)
        ->prefix('attendance')
        ->name('time-logs.')
        ->group(function () {
            Route::get('/', 'create')->name('create');
            Route::post('clock-in', 'clockIn')->name('clock-in');
            Route::patch('clock-out', 'clockOut')->name('clock-out');
            Route::post('break-start', 'breakStart')->name('break-start');
            Route::patch('break-end', 'breakEnd')->name('break-end');
        });

    // for attendances
    Route::controller(AttendanceController::class)
        ->prefix('attendance')
        ->name('attendances.')
        ->group(function () {
            Route::get('list', 'index')->name('index');
            Route::get('detail/{attendance}', 'show')
                ->can('view', Attendance::class)
                ->name('show');
        });

    // for stamp corrections
    Route::controller(StampCorrectionController::class)
        ->name('stamp-corrections.')
        ->group(function () {
            Route::get('stamp_correction_request/list', 'index')->name('index');
            Route::post('stamp-correction/{attendance}', 'store')->name('store');
        });
});

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // for admin logout
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
            ->name('logout');

        // for attendances
        // the following single route reuses AttendanceController instead of
        // AdminAttendanceController
        Route::get('attendance/{attendance}', [AttendanceController::class, 'show'])
            ->name('attendances.show');
        Route::controller(AdminAttendanceController::class)
            ->prefix('attendance')
            ->name('attendances.')
            ->group(function () {
                Route::get('list', 'dailyIndex')->name('daily-index');
                Route::get('staff/{user}', 'monthlyIndex')->name('monthly-index');
                Route::patch('{attendance}', 'update')->name('update');
                Route::get('{user}/export', 'export')->name('export');
            });

        // for users
        Route::get('staff/list', [UserController::class, 'index'])
            ->name('users.index');

        // for stamp corrections
        Route::controller(AdminStampCorrectionController::class)
            ->prefix('stamp_correction_request/approve/{attendance_correction}')
            ->name('stamp-corrections.')
            ->group(function () {
                Route::get('/', 'show')->name('show');
                Route::patch('/', 'approve')->name('approve');
            });
    });
