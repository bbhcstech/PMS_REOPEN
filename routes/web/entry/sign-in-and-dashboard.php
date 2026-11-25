<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/verify-otp', function () {
    return view('auth.verify-otp');
})->name('verify-otp');

Route::get('/hr-login', function () {
    return view('auth.login', ['loginTitle' => 'HR Login']);
})->middleware('guest')->name('hr.login');

Route::get('/manager-login', function () {
    return view('auth.login', ['loginTitle' => 'Manager Login']);
})->middleware('guest')->name('manager.login');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'module.access'])
    ->name('dashboard');
