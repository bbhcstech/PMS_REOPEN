<?php

use Illuminate\Support\Facades\Route;

Route::get('/admin/company-complaints', [\App\Http\Controllers\Admin\CompanyComplaintController::class, 'index'])->name('admin.company-complaints.index');
Route::get('/admin/company-complaints/create', [\App\Http\Controllers\Admin\CompanyComplaintController::class, 'create'])->name('admin.company-complaints.create');
Route::post('/admin/company-complaints', [\App\Http\Controllers\Admin\CompanyComplaintController::class, 'store'])->name('admin.company-complaints.store');
Route::get('/admin/company-complaints/{id}', [\App\Http\Controllers\Admin\CompanyComplaintController::class, 'show'])->name('admin.company-complaints.show');
Route::get('/admin/company-complaints/{id}/messages', [\App\Http\Controllers\Admin\CompanyComplaintController::class, 'messages'])->name('admin.company-complaints.messages');
Route::post('/admin/company-complaints/{id}/reply', [\App\Http\Controllers\Admin\CompanyComplaintController::class, 'reply'])->name('admin.company-complaints.reply');
Route::match(['POST', 'PATCH'], '/admin/company-complaints/{id}/reopen', [\App\Http\Controllers\Admin\CompanyComplaintController::class, 'reopen'])->name('admin.company-complaints.reopen');
Route::get('/admin/company-complaints/{id}/attachment/{attachmentId}', [\App\Http\Controllers\Admin\CompanyComplaintController::class, 'downloadAttachment'])->name('admin.company-complaints.attachment');

// Platform Notifications Routes (Tenant Admin)
Route::get('/admin/company-notifications', [\App\Http\Controllers\Admin\CompanyNotificationController::class, 'index'])->name('admin.company-notifications.index');
Route::post('/admin/company-notifications/read-all', [\App\Http\Controllers\Admin\CompanyNotificationController::class, 'markAllRead'])->name('admin.company-notifications.read-all');
Route::post('/admin/company-notifications/{id}/read', [\App\Http\Controllers\Admin\CompanyNotificationController::class, 'markAsRead'])->name('admin.company-notifications.read');
Route::get('/admin/company-notifications/unread-count', [\App\Http\Controllers\Admin\CompanyNotificationController::class, 'unreadCount'])->name('admin.company-notifications.unread-count');
