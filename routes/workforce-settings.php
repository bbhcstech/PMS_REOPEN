<?php

use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\ParentDepartmentController;
use Illuminate\Support\Facades\Route;

// Separate admin configuration navigation; reuse the company master-data behavior.
Route::middleware(['auth', 'role:admin', 'module.access'])
    ->prefix('admin/settings')->name('admin.settings.')->group(function () {
        foreach (['parent-departments' => ParentDepartmentController::class, 'departments' => DepartmentController::class] as $path => $controller) {
            Route::get("{$path}/archive", [$controller, 'archive'])->name("{$path}.archive");
            Route::post("{$path}/{id}/restore", [$controller, 'restore'])->name("{$path}.restore");
            Route::post("{$path}/bulk-delete", [$controller, 'bulkDestroy'])->name("{$path}.bulk-delete");
            if ($path === 'departments') {
                Route::delete("{$path}/bulk-destroy", [$controller, 'bulkDestroy'])->name("{$path}.bulkDestroy");
            }
            Route::resource($path, $controller);
        }

        Route::get('designations/next-code', [DesignationController::class, 'nextCode'])->name('designations.next-code');
        Route::put('designations/level-settings', [DesignationController::class, 'updateLevelSettings'])->name('designations.level-settings');
        Route::get('designations/designation-hierarchy', [DesignationController::class, 'hierarchy'])->name('designations.hierarchy');
        Route::get('designations/chart-data', [DesignationController::class, 'chartData'])->name('designations.chart-data');
        Route::post('designations/save-hierarchy', [DesignationController::class, 'saveHierarchy'])->name('designations.save-hierarchy');
        Route::get('designations/archive', [DesignationController::class, 'archive'])->name('designations.archive');
        Route::post('designations/bulk-archive', [DesignationController::class, 'bulkArchive'])->name('designations.bulk-archive');
        Route::post('designations/bulk-delete', [DesignationController::class, 'bulkDelete'])->name('designations.bulk-delete');
        Route::post('designations/{designation}/archive', [DesignationController::class, 'archiveDesignation'])->name('designations.archive.action');
        Route::post('designations/{id}/restore', [DesignationController::class, 'restore'])->name('designations.restore');
        Route::delete('designations/{id}/permanent', [DesignationController::class, 'deleteArchived'])->name('designations.delete-archived');
        Route::resource('designations', DesignationController::class);
    });
