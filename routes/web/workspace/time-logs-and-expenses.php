<?php

use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\TimeLogController;
use Illuminate\Support\Facades\Route;

Route::post('/timelogs/bulk-status-update', [TimeLogController::class, 'bulkStatusUpdate'])->name('timelogs.bulkStatusUpdate');

Route::prefix('projects/{project}')->name('projects.')->group(function () {
    Route::get('timelogs', [TimeLogController::class, 'index'])->name('timelogs.index');
    Route::get('timelogs/create', [TimeLogController::class, 'create'])->name('timelogs.create');
});

Route::get('timelogs/calendar', [TimeLogController::class, 'calendar'])->name('timelogs.calendar');
Route::get('timelogs/by-employee', [TimeLogController::class, 'byEmployee'])->name('timelogs.byEmployee');

Route::resource('timelogs', TimeLogController::class);
Route::get('/projects/{project}/timelogs', [TimeLogController::class, 'index'])->name('projects.timelogs.index');

Route::get('/timelogs/get-task-employee/{taskId}', [TimeLogController::class, 'getTaskEmployee']);
Route::get('/project/{projectId}/tasks', [TimeLogController::class, 'getTasksByProject'])->name('timelogs.tasks.byProject');

/*
|----------------------------------------------------------------------
| Expenses (per project)
|----------------------------------------------------------------------
*/

Route::prefix('projects/{project}')->group(function () {
    Route::resource('expenses', ExpenseController::class)->except(['show']);
});
