<?php

use App\Exports\AttendanceExport;
use App\Http\Controllers\AttendanceController;
use Illuminate\Support\Facades\Route;

Route::get('attendance/export/excel', [AttendanceExport::class, 'exportExcel'])
    ->name('attendance.export.excel');

Route::get('attendance/export/pdf', [AttendanceExport::class, 'exportPdf'])
    ->name('attendance.export.pdf');

Route::get('attendance/filter', [App\Http\Controllers\AttendanceController::class, 'filter'])->name('attendance.filter');


// Route::post('/attendance/get-employee-timeline', [AttendanceController::class, 'getEmployeeTimeline'])
//     ->name('attendance.getEmployeeTimeline');

// Route::post('/attendance/employee-locations', 'AttendanceController@getEmployeeLocations')
//     ->name('attendance.getEmployeeLocations');

// Route::get('/attendance/export-employee-locations', 'AttendanceController@exportEmployeeLocations')
//     ->name('attendance.exportEmployeeLocations');
// Employee Location Tracking Routes


Route::post('/attendance/employee-locations', [AttendanceController::class, 'getEmployeeLocations'])
    ->name('attendance.getEmployeeLocations');

Route::post('/attendance/employee-locations', [AttendanceController::class, 'getEmployeeLocations'])->name('attendance.getEmployeeLocations');
Route::post('/attendance/get-employee-timeline', [AttendanceController::class, 'getEmployeeTimeline'])->name('attendance.getEmployeeTimeline');






Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
Route::get('/attendance/filter', [AttendanceController::class, 'filter'])->name('attendance.filter');
Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clockIn');

// Admin-only routes - controller will check role
Route::get('/attendance/create', [AttendanceController::class, 'create'])->name('attendance.create');
Route::post('/attendance/store', [AttendanceController::class, 'store'])->name('attendance.store');
Route::get('/attendance/by-member', [AttendanceController::class, 'byMember'])->name('attendance.byMember');
Route::get('/attendance/by-hour', [AttendanceController::class, 'byHour'])->name('attendance.byHour');
Route::get('/attendance/map-view', [AttendanceController::class, 'todayAttendanceByMap'])->name('attendance.today.map');
Route::get('/attendance/report', [AttendanceController::class, 'attendanceReport'])->name('attendance.report');
Route::get('/attendance/export/excel', [AttendanceController::class, 'exportExcel'])->name('attendance.export.excel');
Route::get('/attendance/export/pdf', [AttendanceController::class, 'exportPdf'])->name('attendance.export.pdf');
Route::get('/attendance/export/multi-pdf', [AttendanceController::class, 'exportMultiPdf'])->name('attendance.export.multi.pdf');
Route::post('/attendance/mark', [AttendanceController::class, 'markAttendance'])->name('attendance.mark');
Route::get('/attendance/edit', [AttendanceController::class, 'edit'])->name('attendance.edit');
Route::put('/attendance/{attendance}', [AttendanceController::class, 'update'])->name('attendance.update');

// Employee-only route (optional separate view)
Route::get('/my-attendance', [AttendanceController::class, 'employeeIndex'])->name('attendance.employee.index');




/*
|--------------------------------------------------------------------------
| Utility / Debug routes (mostly dev helpers)
|--------------------------------------------------------------------------
*/

Route::get('attendance/export/multi-pdf', [AttendanceController::class, 'exportMultiPdf'])
    ->name('attendance.export.multi_pdf');
