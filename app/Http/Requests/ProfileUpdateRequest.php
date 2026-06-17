<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;


class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();
        $currentDob = $user?->dob ?? $user?->employeeDetail?->dob;
        $dobChanged = $this->filled('dob') && $this->normalizeDate($this->input('dob')) !== $this->normalizeDate($currentDob);

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user?->id),
            ],
            'password' => ['nullable', 'string', 'min:8'],
            'designation' => ['nullable', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'gender' => ['nullable', 'in:male,female,other'],
            'dob' => ['nullable', 'date'],
            'marital_status' => ['nullable', 'in:single,married'],
            'address' => ['nullable', 'string'],
            'about' => ['nullable', 'string'],
            'country' => ['nullable', 'string', 'max:100'],
            'language' => ['nullable', 'string', 'max:50'],
            'slack_id' => ['nullable', 'string', 'max:100'],
            'email_notify' => ['nullable', 'boolean'],
            'google_calendar' => ['nullable', 'boolean'],
            'profile_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,gif',
                'max:2048',
            ],
            'government_id_card' => [
                Rule::requiredIf($dobChanged),
                'image',
                'mimes:jpg,jpeg,png',
                'max:4096',
            ],
        ];
    }

    private function normalizeDate($date): ?string
    {
        if (empty($date)) {
            return null;
        }

        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

}
