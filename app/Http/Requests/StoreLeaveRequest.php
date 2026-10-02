<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'exists:users,id'],
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:2000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:5120'],
            'apology_note' => ['nullable', 'string', 'max:2000'],
            'emergency_flag' => ['nullable'],
            'half_day_flag' => ['nullable'],
            'contact_during_leave' => [
                'nullable',
                'string',
                'max:25',
                'regex:/^\+[1-9]\d{6,14}$/',
            ],
            'status' => ['nullable', 'in:pending,approved,rejected'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'end_date.after_or_equal' => "End date can't be backdated. Please select a date on or after the start date.",
            'contact_during_leave.regex' => 'Please enter a valid phone number with country code (e.g. +919876543210).',
            'contact_during_leave.max'   => 'Contact number must not exceed 25 characters.',
        ];
    }
}
