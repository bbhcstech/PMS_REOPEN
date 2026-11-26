<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'developer.access'])->prefix('developer')->name('developer.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DeveloperPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/my-work', [\App\Http\Controllers\DeveloperPortalController::class, 'myWork'])->name('my-work');
    Route::get('/my-contributions', [\App\Http\Controllers\DeveloperPortalController::class, 'myContributions'])->name('my-contributions');
    Route::get('/deadlines', [\App\Http\Controllers\DeveloperPortalController::class, 'deadlines'])->name('deadlines');
    Route::get('/notifications', [\App\Http\Controllers\DeveloperPortalController::class, 'notifications'])->name('notifications');
    Route::get('/profile', [\App\Http\Controllers\DeveloperPortalController::class, 'profile'])->name('profile');
    Route::post('/profile', [\App\Http\Controllers\DeveloperPortalController::class, 'updateProfile'])->name('profile.update');
    Route::get('/settings', [\App\Http\Controllers\DeveloperPortalController::class, 'settings'])->name('settings');
    Route::post('/settings/password', [\App\Http\Controllers\DeveloperPortalController::class, 'updatePassword'])->name('settings.password');
    Route::post('/tasks/{id}/status', [\App\Http\Controllers\DeveloperPortalController::class, 'updateTaskStatus'])->name('tasks.status');
    Route::post('/tasks/{id}/notes', [\App\Http\Controllers\DeveloperPortalController::class, 'addTaskNote'])->name('tasks.notes');
    Route::get('/tasks/{id}/details', [\App\Http\Controllers\DeveloperPortalController::class, 'getTaskDetails'])->name('tasks.details');
});
