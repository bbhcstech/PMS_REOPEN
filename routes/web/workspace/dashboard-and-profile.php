<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::post('/sticky-notes', [DashboardController::class, 'notestore'])->name('sticky_notes.store');
Route::patch('/sticky-notes/{stickyNote}/complete', [DashboardController::class, 'stickyNoteComplete'])->name('sticky_notes.complete');
Route::delete('/sticky-notes/{stickyNote}', [DashboardController::class, 'stickyNoteDestroy'])->name('sticky_notes.destroy');
Route::post('/timers/store', [DashboardController::class, 'timersstore'])->name('dashboard-timers.store');

Route::post('/dashboard/clock-in', [DashboardController::class, 'clockIn'])->name('dashboard.clockin');
Route::post('/dashboard/clock-out', [DashboardController::class, 'clockOut'])->name('dashboard.clockout');
Route::post('/dashboard/employee-welcome-seen', [DashboardController::class, 'markEmployeeWelcomeSeen'])->name('dashboard.employeeWelcomeSeen');

Route::get('/search', [DashboardController::class, 'globalSearch'])->name('dashboard.search');

/*
|----------------------------------------------------------------------
| User profile
|----------------------------------------------------------------------
*/

Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
