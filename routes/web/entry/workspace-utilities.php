<?php

use App\Http\Controllers\DesignationController;
use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;

Route::get('/smtp-test', fn () => redirect()->route('admin.settings.email'))
    ->middleware(['auth', 'role:admin']);


Route::get('designations/next-code', [DesignationController::class, 'nextCode'])
     ->name('designations.next-code');

Route::get('/employees/next-id', [EmployeeController::class, 'nextId'])
    ->name('employees.next-id')
    ->middleware('auth');
