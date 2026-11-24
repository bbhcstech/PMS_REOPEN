<?php

use App\Http\Controllers\Admin\RecruitmentController;
use Illuminate\Support\Facades\Route;

Route::get('/admin/hr/recruitment', [RecruitmentController::class, 'index'])->name('recruitment.index');
Route::post('/admin/hr/recruitment', [RecruitmentController::class, 'store'])->name('recruitment.store');
Route::get('/admin/hr/recruitment/{id}', [RecruitmentController::class, 'show'])->name('recruitment.show');
Route::get('/admin/hr/recruitment/{id}/download', [RecruitmentController::class, 'download'])->name('recruitment.download');
Route::post('/admin/hr/recruitment/{id}/status', [RecruitmentController::class, 'updateStatus'])->name('recruitment.status');
Route::delete('/admin/hr/recruitment/{id}', [RecruitmentController::class, 'destroy'])->name('recruitment.destroy');

// Appraisal Module Routes (Under HR)
Route::get('/admin/hr/appraisal', [\App\Http\Controllers\Admin\AppraisalController::class, 'index'])->name('appraisal.index');
Route::post('/admin/hr/appraisal', [\App\Http\Controllers\Admin\AppraisalController::class, 'store'])->name('appraisal.store');
Route::post('/admin/hr/appraisal/behaviour', [\App\Http\Controllers\Admin\AppraisalController::class, 'saveBehaviour'])->name('appraisal.behaviour.save');
Route::put('/admin/hr/appraisal/{id}', [\App\Http\Controllers\Admin\AppraisalController::class, 'update'])->name('appraisal.update');
Route::post('/admin/hr/appraisal/auto-calculate', [\App\Http\Controllers\Admin\AppraisalController::class, 'autoCalculate'])->name('appraisal.autoCalculate');
Route::delete('/admin/hr/appraisal/{id}', [\App\Http\Controllers\Admin\AppraisalController::class, 'destroy'])->name('appraisal.destroy');
