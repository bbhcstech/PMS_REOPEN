<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Designation;
use App\Models\GovernmentIdVerification;
use App\Services\GovernmentIdDobVerifier;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        // Get active designations from database
        $designations = Designation::where('status', 'active')
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        if (auth()->user()->role == 'admin' || auth()->user()->role == 'employee' || auth()->user()->role == 'client') {
            return view('profile.edit', [
                'user' => $request->user(),
                'designations' => $designations // Pass designations to view
            ]);
        }
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request, GovernmentIdDobVerifier $governmentIdDobVerifier): RedirectResponse
    {
        $user = $request->user();
        $employeeDetail = $user->employeeDetail;
        $isEmployeeProfile = ($user->role ?? '') === 'employee';
        $currentDob = $this->normalizeDate($user->dob ?? $employeeDetail?->dob);
        $newDob = $this->normalizeDate($request->dob);
        $dobChanged = $newDob !== null && $newDob !== $currentDob;

        // Validate designation exists in database
        if ($isEmployeeProfile && $request->has('designation')) {
            $designationExists = Designation::where('name', $request->designation)
                ->where('status', 'active')
                ->exists();

            if (!$designationExists && $request->designation !== '' && $request->designation !== null) {
                return back()->withErrors(['designation' => 'Selected designation is not valid.']);
            }
        }

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            $image = $request->file('profile_image');
            $imageName = time() . '-' . $image->getClientOriginalName();
            $profileImageDirectory = public_path('admin/uploads/profile-images');

            if (! is_dir($profileImageDirectory)) {
                mkdir($profileImageDirectory, 0755, true);
            }

            // Store in: public/admin/uploads/profile-images/
            $image->move($profileImageDirectory, $imageName);

            // Save the relative path to DB
            $user->profile_image = 'admin/uploads/profile-images/' . $imageName;
        }

        $governmentIdCardPath = null;
        if ($request->hasFile('government_id_card')) {
            $image = $request->file('government_id_card');
            $fileName = time() . '-government-id-' . $image->getClientOriginalName();
            $governmentIdDirectory = public_path('admin/uploads/government-id-cards');
            if (! is_dir($governmentIdDirectory)) {
                mkdir($governmentIdDirectory, 0755, true);
            }
            $image->move($governmentIdDirectory, $fileName);
            $governmentIdCardPath = 'admin/uploads/government-id-cards/' . $fileName;
        }

        $governmentIdVerification = null;
        if ($dobChanged && $governmentIdCardPath) {
            $governmentIdVerification = $governmentIdDobVerifier->verify(
                public_path($governmentIdCardPath),
                $request->dob
            );
        }

        // Update user data (excluding password fields)
        $user->name = $request->name;
        $user->email = $request->email;
        if ($isEmployeeProfile && $request->has('designation')) {
            $user->designation = $request->designation;
        }
        $user->mobile = $request->mobile;
        $user->gender = $request->gender;
        $user->dob = $request->dob;
        $user->marital_status = $request->marital_status;
        $user->address = $request->address;
        $user->about = $request->about;
        $user->country = $request->country;
        $user->language = $request->language;
        if ($isEmployeeProfile && $request->has('slack_id')) {
            $user->slack_id = $request->slack_id;
        }
        if ($governmentIdCardPath && ! $employeeDetail) {
            $user->government_id_card = $governmentIdCardPath;
        }
        if ($governmentIdVerification && ! $employeeDetail) {
            $user->government_id_verification_status = $governmentIdVerification['status'];
        }
        $user->email_notify = $request->email_notify;
        $user->google_calendar = $request->google_calendar;

        // Reset email verification if email changed
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($employeeDetail) {
            $employeeDetail->fill([
                'mobile' => $request->mobile,
                'gender' => $request->gender,
                'dob' => $request->dob,
                'marital_status' => $request->marital_status,
                'address' => $request->address,
                'about' => $request->about,
                'country' => $request->country,
                'language' => $request->language,
            ]);

            if ($isEmployeeProfile && $request->has('slack_id')) {
                $employeeDetail->slack_member_id = $request->slack_id;
            }

            if ($governmentIdCardPath) {
                $employeeDetail->government_id_card = $governmentIdCardPath;
            }

            if ($governmentIdVerification) {
                $employeeDetail->government_id_verification_status = $governmentIdVerification['status'];
            }

            $employeeDetail->save();
        }

        if ($governmentIdVerification && $governmentIdCardPath) {
            GovernmentIdVerification::create([
                'user_id' => $user->id,
                'employee_detail_id' => $employeeDetail?->id,
                'submitted_dob' => $request->dob,
                'image_path' => $governmentIdCardPath,
                'ocr_text' => $governmentIdVerification['ocr_text'],
                'ocr_detected_dob' => $governmentIdVerification['detected_dob'],
                'verification_status' => $governmentIdVerification['status'],
                'ocr_message' => $governmentIdVerification['message'],
                'ocr_errors' => $governmentIdVerification['errors'],
                'reviewed_at' => $governmentIdVerification['approved'] ? now() : null,
            ]);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
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

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
