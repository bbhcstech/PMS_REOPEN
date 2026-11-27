<?php

use App\Http\Controllers\PublicEmployeeOnboardingController;
use Illuminate\Support\Facades\Route;

// Public single-use self-onboarding & correction workflow (resolved safely to company tenant DB)
Route::prefix('onboarding')->group(function () {
    Route::get('{token}', [PublicEmployeeOnboardingController::class, 'show'])
        ->where('token', '[A-Za-z0-9]{32,128}')
        ->name('employee-onboarding.show');

    Route::post('{token}/send-otp', [PublicEmployeeOnboardingController::class, 'sendOtp'])
        ->middleware('throttle:10,1')
        ->where('token', '[A-Za-z0-9]{32,128}')
        ->name('employee-onboarding.send-otp');

    Route::post('{token}/verify-otp', [PublicEmployeeOnboardingController::class, 'verifyOtp'])
        ->middleware('throttle:15,1')
        ->where('token', '[A-Za-z0-9]{32,128}')
        ->name('employee-onboarding.verify-otp');

    Route::post('{token}/submit', [PublicEmployeeOnboardingController::class, 'submit'])
        ->middleware('throttle:10,1')
        ->where('token', '[A-Za-z0-9]{32,128}')
        ->name('employee-onboarding.submit');

    Route::get('correction/{token}', [PublicEmployeeOnboardingController::class, 'showCorrection'])
        ->where('token', '[A-Za-z0-9]{32,128}')
        ->name('employee-onboarding.correction.show');

    Route::post('correction/{token}/submit', [PublicEmployeeOnboardingController::class, 'submitCorrection'])
        ->middleware('throttle:10,1')
        ->where('token', '[A-Za-z0-9]{32,128}')
        ->name('employee-onboarding.correction.submit');
});

// Signed single-use account activation / password setup routes (used after HR approval)
Route::middleware('signed')->group(function () {
    Route::get('invite/employee/{company}/{user}', [\App\Http\Controllers\EmployeeInviteController::class, 'show'])
        ->whereNumber(['company', 'user'])
        ->name('employee-invite.show');
    Route::post('invite/employee/{company}/{user}', [\App\Http\Controllers\EmployeeInviteController::class, 'complete'])
        ->whereNumber(['company', 'user'])
        ->name('employee-invite.complete');
});
