<?php

use App\Http\Controllers\OrganizationDirectoryController;
use Illuminate\Support\Facades\Route;

Route::get('/organization', [OrganizationDirectoryController::class, 'index'])->name('organization.index');
Route::delete('/organization/employees', [OrganizationDirectoryController::class, 'deleteEmployees'])->name('organization.employees.delete');
Route::get('/organization/employees/{employee}', [OrganizationDirectoryController::class, 'show'])->name('organization.show');
Route::patch('/organization/employees/{employee}/directory-profile', [OrganizationDirectoryController::class, 'updateDirectoryProfile'])->name('organization.directory-profile.update');
