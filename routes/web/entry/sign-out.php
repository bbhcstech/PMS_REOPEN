<?php

use Illuminate\Support\Facades\Route;

Route::get('/logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout.get');
