<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeaveController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {

Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
Route::get('/hr/attendance', [AttendanceController::class, 'hrAttendance'])->name('attendance.hr');
Route::post('/attendance/mark', [AttendanceController::class, 'markAttendance'])->name('attendance.mark');

Route::get('/admin/authority-attendance', [AttendanceController::class, 'authorityIndex'])->name('admin.authority-attendance');
Route::get('/admin/authority-leaves', [LeaveController::class, 'authorityIndex'])->name('admin.authority-leaves');
Route::get('/attendance/{attendance}/photo', [\App\Http\Controllers\WorkforceFileController::class, 'photo'])->name('attendance.photo');
Route::get('/leaves/{leave}/attachment', [\App\Http\Controllers\WorkforceFileController::class, 'attachment'])->name('leaves.attachment');

// Filter (remove duplicate below)
Route::get('/attendance/filter', [AttendanceController::class, 'filter'])->name('attendance.filter');

Route::get('/attendance/details', [AttendanceController::class, 'showAttendanceDetails'])->name('attendance.details');

// Settings
Route::get('/attendance/settings', [AttendanceController::class, 'settings'])->name('attendance.settings');
Route::post('/attendance/settings', [AttendanceController::class, 'updateSettings'])->name('attendance.settings.update');

// Remove this duplicate ↓ (same name + same controller)
// Archive
Route::get('/attendance/archive', [AttendanceController::class, 'archive'])->name('attendance.archive');
Route::post('/attendance/{id}/restore', [AttendanceController::class, 'restore'])->name('attendance.restore');
Route::post('/attendance/month/archive', [AttendanceController::class, 'archiveMonth'])->name('attendance.month.archive');
});
