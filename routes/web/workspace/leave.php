<?php

use App\Http\Controllers\LeaveController;
use Illuminate\Support\Facades\Route;

Route::post('/leaves/bulk-delete', [LeaveController::class, 'bulkDelete'])
    ->name('leaves.bulk-delete');
Route::post('/leaves/bulk-action', [LeaveController::class, 'bulkAction'])
    ->name('leaves.bulkAction');
Route::post('/leaves/bulk-archive', [LeaveController::class, 'bulkArchive'])
    ->name('leaves.bulk-archive');
Route::post('/leaves/archive-all', [LeaveController::class, 'archiveAll'])
    ->name('leaves.archive-all');
Route::post('/leaves/update-paid-status', [LeaveController::class, 'updatePaidStatus'])
    ->name('leaves.updatePaidStatus');

Route::get('leaves/calendar', [LeaveController::class, 'calendar'])->name('leaves.calendar');
Route::get('/leaves/calendar/data', [LeaveController::class, 'calendarData'])->name('leaves.calendar.data');
Route::get('/leaves/archive/list', [LeaveController::class, 'archive'])->name('leaves.archive');
Route::post('/leaves/archive/{id}/restore', [LeaveController::class, 'restore'])->name('leaves.restore');

Route::get('/leaves', [LeaveController::class, 'index'])->name('leaves.index');
// The signed-in user's own leave requests (HR's requests to the admin; "My Leaves" is HR's employee queue).
Route::get('/leaves/my-requests', [LeaveController::class, 'myLeaves'])->name('leaves.mine');
Route::get('/leaves/apology-letters', [LeaveController::class, 'apologyLetters'])->name('leaves.apology-letters.index');
Route::get('/leaves/apology-letters/create', [LeaveController::class, 'createApologyLetter'])->name('leaves.apology-letters.create');
Route::post('/leaves/apology-letters', [LeaveController::class, 'storeApologyLetter'])->name('leaves.apology-letters.store');
Route::get('/leaves/apology-letters/sample/download', [LeaveController::class, 'downloadApologySample'])->name('leaves.apology-letters.sample.download');
Route::get('/leaves/apology-letters/archive/list', [LeaveController::class, 'archivedApologyLetters'])->name('leaves.apology-letters.archive');
Route::post('/leaves/apology-letters/bulk-archive', [LeaveController::class, 'archiveApologyLetters'])->name('leaves.apology-letters.bulk-archive');
Route::post('/leaves/apology-letters/bulk-restore', [LeaveController::class, 'restoreApologyLetters'])->name('leaves.apology-letters.bulk-restore');
Route::post('/leaves/apology-letters/{letter}/archive', [LeaveController::class, 'archiveApologyLetter'])->name('leaves.apology-letters.archive.action');
Route::post('/leaves/apology-letters/{letter}/restore', [LeaveController::class, 'restoreApologyLetter'])->name('leaves.apology-letters.restore');
Route::get('/leaves/apology-letters/{letter}', [LeaveController::class, 'showApologyLetter'])->name('leaves.apology-letters.show');
Route::patch('/leaves/apology-letters/{letter}/review', [LeaveController::class, 'reviewApologyLetter'])->name('leaves.apology-letters.review');
Route::get('/leaves/create', [LeaveController::class, 'create'])->name('leaves.create');
Route::post('/leaves/store', [LeaveController::class, 'store'])->name('leaves.store');
Route::post('/leaves/{leave}/archive', [LeaveController::class, 'archiveLeave'])->name('leaves.archive.action');
Route::delete('/leaves/{id}', [LeaveController::class, 'destroy'])->name('leaves.destroy');
Route::get('leaves/{leave}/edit', [LeaveController::class, 'edit'])->name('leaves.edit');
Route::put('leaves/{leave}', [LeaveController::class, 'update'])->name('leaves.update');
Route::get('/leaves/report', [LeaveController::class, 'leaveReport'])->name('leaves.report');
Route::get('/admin/leaves/report', [LeaveController::class, 'leaveReport'])->name('admin.leave.report');
Route::get('/leaves/{leave}', [LeaveController::class, 'show'])->name('leaves.show');
Route::get('/leaves/{leave}/status', fn (\App\Models\Leave $leave) => redirect()->route('leaves.show', $leave->id));
Route::match(['patch', 'post'], '/leaves/{leave}/status', [LeaveController::class, 'updateStatus'])->name('leaves.updateStatus');



Route::post('/leaves/policy', [LeaveController::class, 'updatePolicy'])->name('leaves.update-policy');
Route::post('/leaves/types', [LeaveController::class, 'storeLeaveType'])->name('leaves.store-type');
Route::post('/leaves/reset/{id}', [LeaveController::class, 'resetEmployeeLeaves'])->name('leaves.reset-employee-leaves');
Route::get('/leaves/export', [LeaveController::class, 'export'])->name('leaves.export');
