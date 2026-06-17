<?php

namespace App\Http\Controllers;

use App\Models\Designation; // Make sure this line is at the top
use App\Http\Requests\ProfileUpdateRequest;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Symfony\Component\Process\Process;

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
    public function update(ProfileUpdateRequest $request): RedirectResponse
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

        if (($dobChanged || $request->hasFile('government_id_card')) && $request->hasFile('government_id_card')) {
            $governmentIdDobCheck = $this->verifyGovernmentIdDob(
                $request->file('government_id_card')->getRealPath(),
                $request->dob
            );

            if (! $governmentIdDobCheck['ok']) {
                return back()
                    ->withErrors(['government_id_card' => $governmentIdDobCheck['message']])
                    ->withInput();
            }
        }

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            $image = $request->file('profile_image');
            $imageName = time() . '-' . $image->getClientOriginalName();

            // Store in: public/admin/uploads/profile-images/
            $image->move(public_path('admin/uploads/profile-images'), $imageName);

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

            $employeeDetail->save();
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    private function verifyGovernmentIdDob(string $imagePath, string $providedDob): array
    {
        $ocrText = $this->readTextFromImage($imagePath);

        if ($ocrText === null || trim($ocrText) === '') {
            return [
                'ok' => false,
                'message' => 'Could not read DOB from the government ID image. Please upload a clear JPEG, PNG, or JPG image with visible DOB.',
            ];
        }

        $providedDate = Carbon::parse($providedDob)->format('Y-m-d');
        $detectedDates = $this->extractDatesFromText($ocrText);

        if (empty($detectedDates)) {
            return [
                'ok' => false,
                'message' => 'No valid DOB was found in the government ID image. Please upload an ID image where DOB is clearly visible.',
            ];
        }

        if (! in_array($providedDate, $detectedDates, true)) {
            return [
                'ok' => false,
                'message' => 'DOB mismatch. The DOB found on the government ID does not match the provided DOB.',
            ];
        }

        return ['ok' => true, 'message' => 'DOB matched.'];
    }

    private function readTextFromImage(string $imagePath): ?string
    {
        $binary = config('services.ocr.tesseract_binary', 'tesseract');
        $variants = $this->createOcrImageVariants($imagePath);
        $generatedVariants = array_filter($variants, fn ($path) => $path !== $imagePath);
        $results = [];
        $errors = [];

        try {
            foreach ($variants as $variant) {
                foreach ([6, 11] as $pageSegmentationMode) {
                    $process = new Process([
                        $binary,
                        $variant,
                        'stdout',
                        '-l',
                        'eng',
                        '--oem',
                        '1',
                        '--psm',
                        (string) $pageSegmentationMode,
                    ]);
                    $process->setTimeout(30);
                    $process->run();

                    if ($process->isSuccessful() && trim($process->getOutput()) !== '') {
                        $results[] = $process->getOutput();
                    } else {
                        $errors[] = trim($process->getErrorOutput() ?: $process->getOutput());
                    }
                }
            }
        } catch (\Throwable $e) {
            $errors[] = $e->getMessage();
        } finally {
            foreach ($generatedVariants as $variant) {
                @unlink($variant);
            }
        }

        if (empty($results)) {
            Log::warning('Profile government ID OCR failed', [
                'errors' => array_values(array_filter($errors)),
            ]);

            return null;
        }

        return implode("\n", array_unique($results));
    }

    private function createOcrImageVariants(string $imagePath): array
    {
        if (! function_exists('imagecreatefromstring')) {
            return [$imagePath];
        }

        $imageInfo = @getimagesize($imagePath);
        if (! $imageInfo || ($imageInfo[0] * $imageInfo[1]) > 40000000) {
            return [$imagePath];
        }

        $source = @imagecreatefromstring(file_get_contents($imagePath));
        if (! $source) {
            return [$imagePath];
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $scale = max(1, min(3, 2400 / max($width, $height)));
        $scanWidth = (int) round($width * $scale);
        $scanHeight = (int) round($height * $scale);
        $scan = imagecreatetruecolor($scanWidth, $scanHeight);
        imagecopyresampled($scan, $source, 0, 0, 0, 0, $scanWidth, $scanHeight, $width, $height);
        imagedestroy($source);

        imagefilter($scan, IMG_FILTER_GRAYSCALE);
        imagefilter($scan, IMG_FILTER_CONTRAST, -35);
        imageconvolution($scan, [
            [-1, -1, -1],
            [-1, 9, -1],
            [-1, -1, -1],
        ], 1, 0);

        $enhancedPath = tempnam(sys_get_temp_dir(), 'profile-id-ocr-');
        if ($enhancedPath === false || ! imagepng($scan, $enhancedPath)) {
            imagedestroy($scan);
            return [$imagePath];
        }

        $threshold = imagecreatetruecolor($scanWidth, $scanHeight);
        imagecopy($threshold, $scan, 0, 0, 0, 0, $scanWidth, $scanHeight);
        imagefilter($threshold, IMG_FILTER_CONTRAST, -75);
        imagefilter($threshold, IMG_FILTER_BRIGHTNESS, 10);

        $thresholdPath = tempnam(sys_get_temp_dir(), 'profile-id-ocr-');
        if ($thresholdPath === false || ! imagepng($threshold, $thresholdPath)) {
            $thresholdPath = null;
        }

        imagedestroy($threshold);
        imagedestroy($scan);

        return array_values(array_filter([$imagePath, $enhancedPath, $thresholdPath]));
    }

    private function extractDatesFromText(string $text): array
    {
        $text = preg_replace('/(?<=\d)[Oo](?=\d)|(?<=\d)[Oo](?=[\/.\-])|(?<=[\/.\-])[Oo](?=\d)/', '0', $text);
        $text = preg_replace('/(?<=\d)[Il](?=\d)|(?<=\d)[Il](?=[\/.\-])|(?<=[\/.\-])[Il](?=\d)/', '1', $text);
        $dates = [];
        $patterns = [
            '/\b(\d{1,2})\s*[\/\-.]\s*(\d{1,2})\s*[\/\-.]\s*(\d{4})\b/',
            '/\b(\d{4})\s*[\/\-.]\s*(\d{1,2})\s*[\/\-.]\s*(\d{1,2})\b/',
            '/\b(\d{1,2})\s+(Jan|January|Feb|February|Mar|March|Apr|April|May|Jun|June|Jul|July|Aug|August|Sep|Sept|September|Oct|October|Nov|November|Dec|December)\s+(\d{4})\b/i',
            '/\b(Jan|January|Feb|February|Mar|March|Apr|April|May|Jun|June|Jul|July|Aug|August|Sep|Sept|September|Oct|October|Nov|November|Dec|December)\s+(\d{1,2}),?\s+(\d{4})\b/i',
        ];

        foreach ($patterns as $pattern) {
            if (! preg_match_all($pattern, $text, $matches, PREG_SET_ORDER)) {
                continue;
            }

            foreach ($matches as $match) {
                $date = $this->normalizeDetectedDate($match);
                if ($date) {
                    $dates[] = $date;
                }
            }
        }

        return array_values(array_unique($dates));
    }

    private function normalizeDetectedDate(array $match): ?string
    {
        $raw = preg_replace('/\s*([\/.\-])\s*/', '$1', trim($match[0]));
        $formats = [
            'd/m/Y', 'd-m-Y', 'd.m.Y',
            'm/d/Y', 'm-d-Y', 'm.d.Y',
            'Y/m/d', 'Y-m-d', 'Y.m.d',
            'd M Y', 'd F Y',
            'M d Y', 'F d Y',
            'M d, Y', 'F d, Y',
        ];

        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $raw);
                if ($date && $date->format($format) !== false) {
                    return $date->format('Y-m-d');
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        return $this->normalizeDate($raw);
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
