<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Support\CountryPhone;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class CountryCodeController extends Controller
{
    private function authorizeAdmin(): void
    {
        $user = auth()->user();
        if (! $user) {
            abort(403, 'Unauthorized');
        }

        if (\Illuminate\Support\Facades\Auth::guard('super_admin')->check()) {
            return;
        }

        $role = $user->normalizedRole();
        if (in_array($role, ['admin', 'superadmin'], true)) {
            return;
        }

        abort_unless($user->canViewModule('settings') || $user->canViewModule('localization-settings'), 403, 'Unauthorized access to Country Codes settings.');
    }

    public function index(Request $request): View
    {
        $this->authorizeAdmin();

        Country::ensureAllCountriesSeeded();

        $query = Country::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone_code', 'like', "%{$search}%")
                  ->orWhere('iso_code', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 25);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 25;

        $countries = $query->orderBy('name')->paginate($perPage)->withQueryString();

        $stats = [
            'total'          => Country::count(),
            'unique_codes'   => Country::whereNotNull('phone_code')->distinct()->count('phone_code'),
            'default_code'   => '+91',
            'default_country'=> 'India',
        ];

        return view('admin.settings.country-codes.index', compact('countries', 'stats', 'perPage'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name'       => 'required|string|max:191|unique:countries,name',
            'phone_code' => ['required', 'string', 'max:15', 'regex:/^\+?[0-9]{1,6}$/'],
            'iso_code'   => 'required|string|max:10',
            'min_digits' => 'required|integer|min:3|max:18',
            'max_digits' => 'required|integer|min:3|max:18',
        ]);

        $phoneCode = trim($validated['phone_code']);
        if (! str_starts_with($phoneCode, '+')) {
            $phoneCode = '+' . $phoneCode;
        }

        $isoCode = strtoupper(trim($validated['iso_code']));
        $flagUrl = 'https://flagcdn.com/w20/' . strtolower($isoCode) . '.png';

        Country::create([
            'name'       => trim($validated['name']),
            'phone_code' => $phoneCode,
            'iso_code'   => $isoCode,
            'min_digits' => (int) $validated['min_digits'],
            'max_digits' => (int) $validated['max_digits'],
            'flag_url'   => $flagUrl,
        ]);

        return redirect()->route('admin.settings.country-codes.index')
            ->with('success', "Country code for {$validated['name']} ({$phoneCode}) added successfully.");
    }

    public function update(Request $request, Country $country_code): RedirectResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name'       => 'required|string|max:191|unique:countries,name,' . $country_code->id,
            'phone_code' => ['required', 'string', 'max:15', 'regex:/^\+?[0-9]{1,6}$/'],
            'iso_code'   => 'required|string|max:10',
            'min_digits' => 'required|integer|min:3|max:18',
            'max_digits' => 'required|integer|min:3|max:18',
        ]);

        $phoneCode = trim($validated['phone_code']);
        if (! str_starts_with($phoneCode, '+')) {
            $phoneCode = '+' . $phoneCode;
        }

        $isoCode = strtoupper(trim($validated['iso_code']));
        $flagUrl = 'https://flagcdn.com/w20/' . strtolower($isoCode) . '.png';

        $country_code->update([
            'name'       => trim($validated['name']),
            'phone_code' => $phoneCode,
            'iso_code'   => $isoCode,
            'min_digits' => (int) $validated['min_digits'],
            'max_digits' => (int) $validated['max_digits'],
            'flag_url'   => $flagUrl,
        ]);

        return redirect()->route('admin.settings.country-codes.index')
            ->with('success', "Country code for {$country_code->name} updated successfully.");
    }

    public function destroy(Country $country_code): RedirectResponse
    {
        $this->authorizeAdmin();

        $name = $country_code->name;
        $country_code->delete();

        return redirect()->route('admin.settings.country-codes.index')
            ->with('success', "Country code for {$name} deleted successfully.");
    }

    public function resync(): RedirectResponse
    {
        $this->authorizeAdmin();

        $map = CountryPhone::map();
        $conn = (new Country)->getConnectionName() ?: 'tenant';

        foreach ($map as $name => $meta) {
            $flagUrl = 'https://flagcdn.com/w20/' . strtolower($meta['iso'] ?? 'in') . '.png';
            DB::connection($conn)->table('countries')->updateOrInsert(
                ['name' => $name],
                [
                    'phone_code' => $meta['dial_code'] ?? '+91',
                    'iso_code'   => strtoupper($meta['iso'] ?? 'IN'),
                    'min_digits' => (int) ($meta['min_digits'] ?? 10),
                    'max_digits' => (int) ($meta['max_digits'] ?? 10),
                    'flag_url'   => $flagUrl,
                ]
            );
        }

        return redirect()->route('admin.settings.country-codes.index')
            ->with('success', 'All world country codes have been re-synchronized and updated in the database.');
    }
}
