<?php

use Illuminate\Support\Facades\Route;

Route::middleware('signed')->group(function () {
    Route::get('invite/employee/{company}/{user}', [\App\Http\Controllers\EmployeeInviteController::class, 'show'])
        ->whereNumber(['company', 'user'])
        ->name('employee-invite.show');
    Route::post('invite/employee/{company}/{user}', [\App\Http\Controllers\EmployeeInviteController::class, 'complete'])
        ->whereNumber(['company', 'user'])
        ->name('employee-invite.complete');
});
