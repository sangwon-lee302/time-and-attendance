<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->get('/preview', fn () => view('preview'));
