<?php

use App\Http\Controllers\HolidayController;
use Illuminate\Support\Facades\Route;

Route::post('/holidays/bulk-action', [HolidayController::class, 'bulkAction'])->name('holiday.bulkAction');
Route::get('/holidays/export/list', [HolidayController::class, 'export'])->name('holidays.export');
Route::get('/holidays/import/sample', [HolidayController::class, 'sample'])->name('holidays.sample');
Route::post('/holidays/import/excel', [HolidayController::class, 'importExcel'])->name('holidays.import.excel');
Route::post('/holidays/import/image', [HolidayController::class, 'importImage'])->name('holidays.import.image');
Route::get('/holidays/archive/list', [HolidayController::class, 'archive'])->name('holidays.archive');
Route::post('/holidays/bulk-archive', [HolidayController::class, 'bulkArchive'])->name('holidays.bulk-archive');
Route::post('/holidays/{holiday}/archive', [HolidayController::class, 'archiveHoliday'])->name('holidays.archive.action');
Route::post('/holidays/archive/{id}/restore', [HolidayController::class, 'restore'])->name('holidays.restore');
Route::resource('holidays', HolidayController::class)->except(['show']);

Route::get('employee-holidays', [HolidayController::class, 'employeeView'])->name('employee.holidays');

// ✅ FIXED: Change 'calendarView' to 'calendar'
Route::get('calendar-holidays', [HolidayController::class, 'calendar'])->name('holidays.calendar');

Route::post('/holidays/mark', [HolidayController::class, 'markHoliday'])->name('holidays.mark');

// ✅ Optional: Add Employee Calendar route
Route::get('employee/calendar-holidays', [HolidayController::class, 'calendar'])->name('employee.holidays.calendar');
