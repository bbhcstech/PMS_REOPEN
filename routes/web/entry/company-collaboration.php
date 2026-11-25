<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CommunityMessageController;
use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/company/assistant', [\App\Http\Controllers\CompanyAiController::class, 'index'])->name('company.ai.index');
    Route::post('/company/assistant', [\App\Http\Controllers\CompanyAiController::class, 'ask'])->middleware('throttle:company-ai')->name('company.ai.ask');
    // Community Message Module Routes
    Route::middleware(['feature:community'])->group(function () {
        Route::get('/community', [CommunityMessageController::class, 'index'])->name('community.index');
        Route::get('/community/messages', [CommunityMessageController::class, 'fetchMessages'])->name('community.messages');
        Route::get('/community/messages/{id}/attachment', [CommunityMessageController::class, 'attachment'])->name('community.attachment');
        Route::post('/community/messages', [CommunityMessageController::class, 'store'])->name('community.store');
        Route::put('/community/messages/{id}', [CommunityMessageController::class, 'update'])->name('community.update');
        Route::delete('/community/messages/{id}', [CommunityMessageController::class, 'destroy'])->name('community.destroy');
        Route::post('/community/messages/{id}/react', [CommunityMessageController::class, 'react'])->name('community.react');
        Route::post('/community/messages/{id}/pin', [CommunityMessageController::class, 'togglePin'])->name('community.pin');
    });

    // Company Events Module Routes
    Route::middleware(['feature:events'])->group(function () {
        Route::get('/events', [EventController::class, 'index'])->name('events.index');
        Route::get('/events/calendar-data', [EventController::class, 'calendarData'])->name('events.calendar-data');
        Route::post('/events', [EventController::class, 'store'])->name('events.store');
        Route::get('/events/{id}', [EventController::class, 'show'])->name('events.show');
        Route::put('/events/{id}', [EventController::class, 'update'])->name('events.update');
        Route::delete('/events/{id}', [EventController::class, 'destroy'])->name('events.destroy');
        Route::post('/events/{id}/publish', [EventController::class, 'publish'])->name('events.publish');
        Route::post('/events/{id}/cancel', [EventController::class, 'cancel'])->name('events.cancel');
    });
    Route::post('/events/{id}/rsvp', [EventController::class, 'rsvp'])->name('events.rsvp');

    // Event Memories / Gallery Photo Routes
    Route::post('/events/{id}/photos', [EventController::class, 'uploadPhotos'])->name('events.photos.upload');
    Route::get('/events/{id}/photos', [EventController::class, 'getPhotos'])->name('events.photos.index');
    Route::put('/events/{id}/photos/{photoId}', [EventController::class, 'updatePhoto'])->name('events.photos.update');
    Route::post('/events/{id}/photos/{photoId}/cover', [EventController::class, 'setGalleryCover'])->name('events.photos.cover');
    Route::post('/events/{id}/photos/reorder', [EventController::class, 'reorderPhotos'])->name('events.photos.reorder');
    Route::delete('/events/{id}/photos/{photoId}', [EventController::class, 'deletePhoto'])->name('events.photos.destroy');
    Route::delete('/events/{id}/photos-bulk', [EventController::class, 'deleteBulkPhotos'])->name('events.photos.destroy-bulk');

    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])
        ->name('attendance.clockIn');
    Route::post('/users/{user}/change-password', [\App\Http\Controllers\UserPasswordChangeController::class, 'changePassword'])
        ->name('users.change-password');
    Route::post('/password-changed-logout', [\App\Http\Controllers\UserPasswordChangeController::class, 'passwordChangedLogout'])
        ->name('password-changed-logout');
    Route::get('/check-password-status', [\App\Http\Controllers\UserPasswordChangeController::class, 'checkPasswordStatus'])
        ->name('check-password-status');
    Route::post('/user/change-own-password', [\App\Http\Controllers\UserPasswordChangeController::class, 'changeOwnPassword'])
        ->name('user.change-own-password');
});
