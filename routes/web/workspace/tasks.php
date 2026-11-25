<?php

use App\Http\Controllers\SubTaskController;
use App\Http\Controllers\TaskCategoryController;
use App\Http\Controllers\TaskCommentController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskLabelController;
use App\Http\Controllers\TaskTimerController;
use App\Http\Controllers\TimeLogController;
use App\Models\Task;
use Illuminate\Support\Facades\Route;

Route::delete('/tasks/bulk-delete', [TaskController::class, 'bulkDelete'])->name('tasks.bulkDelete');
Route::post('/tasks/bulk-status-update', [TaskController::class, 'bulkStatusUpdate'])->name('tasks.bulkStatusUpdate');

Route::resource('tasks', TaskController::class)->except(['show']);
Route::get('/projects/{project}/tasks', [TaskController::class, 'index'])->name('projects.tasks.index');
Route::get('projects/{project}/tasks/board', [TaskController::class, 'taskBoard'])->name('projects.tasks.board');
Route::post('/tasks/{task}/update-status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');
Route::post('/tasks/{task}/toggle-pin', [TaskController::class, 'togglePin'])->name('tasks.toggle-pin');

Route::post('/tasks/{task}/notes', [TaskController::class, 'storeNote'])->name('tasks.notes.store');

Route::get('/projects/{id}/tasks', [TimeLogController::class, 'getTasks']); // helper for timelog forms

// Task calendar + boards
Route::get('/tasks/calendar', [TaskController::class, 'calendarView'])->name('tasks.calendar');
Route::get('/users/tasks/board', [TaskController::class, 'userTaskBoard'])->name('users.tasks.board');
Route::get('/tasks/waiting-approval', [TaskController::class, 'waitingApproval'])->name('tasks.waiting-approval');

// Task labels / categories
Route::post('/labels', [TaskController::class, 'storeLabel'])->name('labels.store');
Route::resource('task-categories', TaskCategoryController::class)->only(['store']);
Route::post('task-categories/{task_category}/delete', [TaskCategoryController::class, 'destroy'])->name('task-categories.force-delete');

// Second labels routes (overrides previous, kept exactly as original)
Route::post('/labels', [TaskLabelController::class, 'store'])->name('labels.store');
Route::post('/labels/{id}', [TaskLabelController::class, 'destroy'])->name('labels.destroy');

// Task show
Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');

// Task timers
Route::post('/tasks/{task}/timer/start', [TaskTimerController::class, 'start'])->name('task-timer.start');
Route::post('/timers/start', [TaskTimerController::class, 'store'])->name('timers.store');
Route::post('/globaltaskstimer/stop', [TaskTimerController::class, 'globalstop'])->name('globaltasktimer.stop');
Route::post('/tasks/{task}/timer/stop', [TaskTimerController::class, 'stop'])->name('task-timer.stop');
Route::post('/tasks/{task}/timer/pause', [TaskTimerController::class, 'pause'])->name('task-timer.pause');
Route::post('/tasks/{task}/timer/resume', [TaskTimerController::class, 'resume'])->name('task-timer.resume');

Route::get('/tasks/{task}/mark-complete', [TaskController::class, 'markComplete'])->name('tasks.markComplete');

// Task files
Route::post('/tasks/{task}/upload-file', [TaskController::class, 'uploadFile'])->name('tasks.uploadFile');
Route::delete('/tasks/{task}/file-delete', [TaskController::class, 'deleteFile'])->name('tasks.deleteFile');

// Subtasks
Route::post('/tasks/{task}/subtasks', [SubTaskController::class, 'store'])->name('subtasks.store');
Route::get('subtasks/{subtask}/edit', [SubTaskController::class, 'edit'])->name('subtasks.edit');
Route::put('subtasks/{subtask}', [SubTaskController::class, 'update'])->name('subtasks.update');
Route::delete('subtasks/{subtask}', [SubTaskController::class, 'destroy'])->name('subtasks.destroy');
Route::delete('/subtasks/{subtask}/file', [SubTaskController::class, 'deleteFile'])->name('subtask.file.delete');

// Task comments
Route::post('/tasks/{task}/comments', [TaskCommentController::class, 'store'])->name('task-comments.store');
