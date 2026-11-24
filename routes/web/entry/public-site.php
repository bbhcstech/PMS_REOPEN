<?php

use App\Http\Controllers\FrontendUIController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FrontendUIController::class, 'index'])->name('home');

// ===========================================
// Product Routes
// ===========================================
Route::prefix('product')->name('product.')->group(function () {
    Route::get('/tasks', [FrontendUIController::class, 'productTasks'])->name('tasks');
    Route::get('/gantt', [FrontendUIController::class, 'productGantt'])->name('gantt');
    Route::get('/kanban', [FrontendUIController::class, 'productKanban'])->name('kanban');
    Route::get('/attendance', [FrontendUIController::class, 'productAttendance'])->name('attendance');
    Route::get('/leave', [FrontendUIController::class, 'productLeave'])->name('leave');
    Route::get('/performance', [FrontendUIController::class, 'productPerformance'])->name('performance');
    Route::get('/reports', [FrontendUIController::class, 'productReports'])->name('reports');
    Route::get('/dashboard', [FrontendUIController::class, 'productDashboard'])->name('dashboard');
    Route::get('/analytics', [FrontendUIController::class, 'productAnalytics'])->name('analytics');
});

Route::get('/solutions', [FrontendUIController::class, 'solutions'])->name('solutions');
Route::prefix('solutions')->name('solutions.')->group(function () {
    Route::get('/enterprise', [FrontendUIController::class, 'solutionsEnterprise'])->name('enterprise');
    Route::get('/startups', [FrontendUIController::class, 'solutionsStartups'])->name('startups');
    Route::get('/hr', [FrontendUIController::class, 'solutionsHr'])->name('hr');
    Route::get('/developers', [FrontendUIController::class, 'solutionsDevelopers'])->name('developers');
    Route::get('/remote', [FrontendUIController::class, 'solutionsRemote'])->name('remote');
});

// ===========================================
// Features & Pricing
// ===========================================
Route::get('/features', [FrontendUIController::class, 'features'])->name('features');
Route::get('/pricing', [FrontendUIController::class, 'pricing'])->name('pricing');

// ===========================================
// Resources Routes
// ===========================================
Route::get('/resources', [FrontendUIController::class, 'resources'])->name('resources');
Route::prefix('resources')->name('resources.')->group(function () {
    Route::get('/blog', [FrontendUIController::class, 'blog'])->name('blog');
    Route::get('/blog/{slug}', [FrontendUIController::class, 'blogSingle'])->name('blog.single');
    Route::get('/docs', [FrontendUIController::class, 'documentation'])->name('docs');
    Route::get('/api', [FrontendUIController::class, 'api'])->name('api');
    Route::get('/help', [FrontendUIController::class, 'helpCenter'])->name('help');
    Route::get('/faq', [FrontendUIController::class, 'faq'])->name('faq');
});

// ===========================================
// Company Routes
// ===========================================
Route::get('/about', [FrontendUIController::class, 'about'])->name('about');
Route::get('/contact', [FrontendUIController::class, 'contact'])->name('contact');
Route::get('/privacy', [FrontendUIController::class, 'privacy'])->name('privacy');
Route::get('/terms', [FrontendUIController::class, 'terms'])->name('terms');

Route::prefix('company')->name('company.')->group(function () {
    Route::get('/about', [FrontendUIController::class, 'about'])->name('about');
    Route::get('/careers', [FrontendUIController::class, 'careers'])->name('careers');
    Route::get('/contact', [FrontendUIController::class, 'contact'])->name('contact');
    Route::post('/contact', [FrontendUIController::class, 'contactSubmit'])->name('contact.submit');
    Route::get('/privacy', [FrontendUIController::class, 'privacy'])->name('privacy');
    Route::get('/terms', [FrontendUIController::class, 'terms'])->name('terms');
});
