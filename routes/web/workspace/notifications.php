<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.all');
Route::get('/notifications/{id}/open', [NotificationController::class, 'open'])->name('notifications.open');
Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
Route::post('/notifications/section/{section}/read', [NotificationController::class, 'markSectionAsRead'])->name('notifications.section.read');
Route::post('/notifications/clear-all', [NotificationController::class, 'clearAll'])->name('notifications.clearAll');
Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unreadCount');
Route::get('/notifications/latest', [NotificationController::class, 'latest'])->name('notifications.latest');
Route::get('/notifications/sidebar', [NotificationController::class, 'sidebar'])->name('notifications.sidebar');
