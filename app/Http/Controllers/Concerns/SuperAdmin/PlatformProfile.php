<?php

namespace App\Http\Controllers\Concerns\SuperAdmin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

trait PlatformProfile
{
    public function profile(Request $request): View
    {
        $this->authorizeSuperAdmin();

        $saUser = auth('super_admin')->user();
        $webUser = auth()->user();
        // Always get a fresh copy from the DB so saved data shows up immediately
        $user = $saUser ? $saUser->fresh() : ($webUser ? $webUser->fresh() : null);

        if (!$user) {
            $user = $request->user('super_admin') ?? $request->user();
        }

        return view('superadmin.profile', compact('user'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $saUser = auth('super_admin')->user();
        $webUser = auth()->user();
        $user = $saUser ?? $webUser;

        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in to manage your profile.');
        }

        $data = $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'email'               => ['required', 'string', 'max:255', 'email:rfc', 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'],
            'mobile'              => [
                'nullable',
                'string',
                'max:50',
                function ($attribute, $value, $fail) {
                    if (!empty($value) && !\App\Support\CountryPhone::validateFormattedNumber($value)) {
                        $fail(\App\Support\CountryPhone::getValidationErrorMessage($value));
                    }
                }
            ],
            'gender'              => ['nullable', 'string', 'max:20'],
            'date_of_birth'       => ['nullable', 'date'],
            'marital_status'      => ['nullable', 'string', 'max:50'],
            'country'             => ['nullable', 'string', 'max:100'],
            'language'            => ['nullable', 'string', 'max:100'],
            'address'             => ['nullable', 'string'],
            'about'               => ['nullable', 'string'],
            'email_notifications' => ['nullable', 'boolean'],
            'google_calendar'     => ['nullable', 'boolean'],
            'profile_image'       => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:5120'],
            'govt_id_card'        => ['nullable', 'file', 'mimes:pdf,jpeg,png,jpg', 'max:10240'],
        ], [
            'email.regex'         => 'Please enter a valid email address (e.g. name@company.com).',
        ]);

        // Email uniqueness check safely matching model guard
        if ($saUser) {
            $emailTaken = \Illuminate\Support\Facades\DB::connection('central')
                ->table('super_admins')
                ->where('email', $data['email'])
                ->where('id', '!=', $saUser->id)
                ->exists();
        } else {
            $emailTaken = $user->getConnection()->table($user->getTable())
                ->where('email', $data['email'])
                ->where('id', '!=', $user->id)
                ->exists();
        }
        if ($emailTaken) {
            return back()->withInput()->withErrors(['email' => 'This email is already taken by another administrator.']);
        }

        $schemaBuilder = $user->getConnection()->getSchemaBuilder();
        $tableColumns = $schemaBuilder->getColumnListing($user->getTable());
        if ($user instanceof \App\Models\Central\SuperAdmin) {
            // Repair missing profile columns on older central databases before saving.
            $profileColumns = ['mobile', 'gender', 'date_of_birth', 'marital_status', 'country', 'language', 'address', 'about', 'govt_id_card', 'email_notifications', 'google_calendar'];
            if (array_diff($profileColumns, $tableColumns)) {
                (require database_path('migrations/central/2026_09_29_000001_add_profile_fields_to_super_admins_table.php'))->up();
            }
        } elseif (! in_array('date_of_birth', $tableColumns, true) && in_array('dob', $tableColumns, true) && array_key_exists('date_of_birth', $data)) {
            $data['dob'] = $data['date_of_birth'];
            unset($data['date_of_birth']);
        }

        if ($request->hasFile('profile_image')) {
            $file = $request->file('profile_image');
            $dir = public_path('uploads/profile');
            if (!\Illuminate\Support\Facades\File::exists($dir)) {
                \Illuminate\Support\Facades\File::makeDirectory($dir, 0755, true);
            }
            $filename = time() . '_profile_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $filename);
            $data['profile_image'] = 'uploads/profile/' . $filename;
        } else {
            // Preserve existing profile image — do not overwrite with null
            unset($data['profile_image']);
        }

        if ($request->hasFile('govt_id_card')) {
            $file = $request->file('govt_id_card');
            $dir = public_path('uploads/documents');
            if (!\Illuminate\Support\Facades\File::exists($dir)) {
                \Illuminate\Support\Facades\File::makeDirectory($dir, 0755, true);
            }
            $filename = time() . '_govtid_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $filename);
            $data['govt_id_card'] = 'uploads/documents/' . $filename;
        } else {
            unset($data['govt_id_card']);
        }

        $data['email_notifications'] = $request->boolean('email_notifications');
        $data['google_calendar'] = $request->boolean('google_calendar');

        // Use the model's own connection (central) to get the column list — NOT the default DB facade
        $schemaBuilder = $user->getConnection()->getSchemaBuilder();
        $tableColumns  = $schemaBuilder->getColumnListing($user->getTable());

        if (! in_array('govt_id_card', $tableColumns, true) && in_array('government_id_card', $tableColumns, true) && array_key_exists('govt_id_card', $data)) {
            $data['government_id_card'] = $data['govt_id_card'];
            unset($data['govt_id_card']);
        }

        $updateData = [];
        foreach ($data as $key => $val) {
            if (in_array($key, $tableColumns, true)) {
                $updateData[$key] = $val;
            }
        }

        if (!empty($updateData)) {
            // Data is already validated and limited to existing profile columns.
            // Legacy and central account models have different fillable lists.
            $user->forceFill($updateData)->save();
            // Refresh the session so navbar avatar and name reflect changes immediately
            if ($user instanceof \App\Models\Central\SuperAdmin) {
                auth('super_admin')->setUser($user->fresh());
            }
        }

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }
}
