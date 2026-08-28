<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Carbon\Carbon;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'terms_accepted' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'terms_accepted.accepted' => 'Please accept the Terms & Conditions before logging in.',
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // ============================================
        // FIX: DEVELOPER & EMPLOYEE AUTHENTICATION LOOKUP & CHECKS
        // ============================================

        $inputEmail = strtolower(trim($this->string('email')));
        $inputPassword = (string) $this->string('password');

        // First, check if user exists by email or personal_email
        $user = User::where(function ($query) use ($inputEmail) {
            $query->where('email', $inputEmail);
            if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'personal_email')) {
                $query->orWhere('personal_email', $inputEmail);
            }
        })->first();

        if ($user) {
            // Auto-sync password hash if raw_password matches the provided password
            if (!empty($user->raw_password) && $inputPassword === (string) $user->raw_password) {
                if (!\Illuminate\Support\Facades\Hash::check($inputPassword, $user->password)) {
                    $user->password = \Illuminate\Support\Facades\Hash::make($inputPassword);
                    $user->save();
                }
            }

            // Check if user can login (including developer task assignment check & exit date logic)
            if (!$user->canLogin()) {
                RateLimiter::hit($this->throttleKey());

                throw ValidationException::withMessages([
                    'email' => $user->getLoginErrorMessage(),
                ]);
            }
        }

        // Attempt authentication using the user's primary email
        $attemptCredentials = [
            'email' => $user ? $user->email : $inputEmail,
            'password' => $inputPassword,
        ];

        if (! Auth::attempt($attemptCredentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // ============================================
        // DOUBLE-CHECK AFTER SUCCESSFUL LOGIN
        // ============================================
        $loggedInUser = Auth::user();
        if ($loggedInUser && !$loggedInUser->canLogin()) {
            Auth::logout();
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => $loggedInUser->getLoginErrorMessage(),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
