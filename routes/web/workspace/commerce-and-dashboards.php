<?php

use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Route::get('/orders/{id}/items', [OrderController::class, 'getItems'])->name('orders.items');
Route::post('/orders/bulk-payment-status', [OrderController::class, 'bulkUpdatePaymentStatus'])->name('orders.bulk-payment-status');
Route::post('/orders/{id}/payment-status', [OrderController::class, 'updatePaymentStatus'])->name('orders.update-payment-status');

Route::get('/account/dashboard-project', [DashboardController::class, 'project'])->name('dashproject');
Route::get('/account/dashboard-advanced', [DashboardController::class, 'clientDashboard'])->name('dashboard.client');
Route::get('/dashboard-advanced', [DashboardController::class, 'ticketDashboard'])->name('dashboard.ticket');
Route::get('/hr-dashboard', [DashboardController::class, 'hrindex'])->name('hr.dashboard');
