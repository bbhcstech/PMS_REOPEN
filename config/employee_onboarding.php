<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Employee Invitation Expiration (Hours)
    |--------------------------------------------------------------------------
    |
    | Configurable lifetime for single-use employee self-onboarding links and
    | correction links. Defaults to 72 hours (3 days).
    |
    */
    'expiry_hours' => (int) env('EMPLOYEE_INVITE_EXPIRY_HOURS', 72),

    /*
    |--------------------------------------------------------------------------
    | Email Verification OTP Lifetime (Minutes)
    |--------------------------------------------------------------------------
    |
    | For open invitation links generated without a bound email address, the
    | applicant verifies ownership of their email using a one-time code.
    |
    */
    'otp_expiry_minutes' => (int) env('EMPLOYEE_ONBOARDING_OTP_MINUTES', 15),

    /*
    |--------------------------------------------------------------------------
    | Private Document Disk
    |--------------------------------------------------------------------------
    |
    | Pending onboarding uploads (profile pictures, CVs, ID documents) are
    | kept on private storage and served only through authorized controllers.
    |
    */
    'private_disk' => env('EMPLOYEE_ONBOARDING_DISK', 'local'),
];
