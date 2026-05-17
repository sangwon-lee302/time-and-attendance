<?php

use App\Http\Controllers\TimeLogController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->get('/preview', fn () => view('preview'));

Route::middleware(['auth', 'verified'])->name('time-logs.')->group(function () {
    Route::get('/attendance', [TimeLogController::class, 'create'])->name('create');
    Route::post('/attendance/clock-in', [TimeLogController::class, 'clockIn'])->name('clock-in');
});
