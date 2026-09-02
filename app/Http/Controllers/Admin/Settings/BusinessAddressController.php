<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\BusinessAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class BusinessAddressController extends Controller
{
    public function index()
    {
        $addresses = BusinessAddress::all();
        return view('admin.settings.business-address.index', compact('addresses'));
    }

    public function create()
    {
        return view('admin.settings.business-address.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_name' => 'required|string|max:255',
            'location'    => 'required|string|max:255',
            'email'       => 'nullable|email|max:255',
            'phone'       => 'nullable|string|max:25',
            'address'     => 'required|string',
            'country'     => 'required|string|max:100',
            'tax_name'    => 'nullable|string|max:100',
            'logo'        => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:3072',
            'is_default'  => 'sometimes|boolean',
        ]);

        $validated['is_default'] = $request->boolean('is_default');

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'branch_logo_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('uploads/branch-logo');

            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $filename);
            $validated['logo'] = 'uploads/branch-logo/' . $filename;
        }

        if ($validated['is_default']) {
            BusinessAddress::where('is_default', true)->update(['is_default' => false]);
        }

        BusinessAddress::create($validated);

        return redirect()->route('admin.settings.business-address.index')
            ->with('success', 'Branch address created successfully.');
    }

    public function edit(BusinessAddress $businessAddress)
    {
        $addresses = BusinessAddress::all();
        return view('admin.settings.business-address.edit', compact('businessAddress', 'addresses'));
    }

    public function update(Request $request, BusinessAddress $businessAddress)
    {
        $validated = $request->validate([
            'branch_name' => 'required|string|max:255',
            'location'    => 'required|string|max:255',
            'email'       => 'nullable|email|max:255',
            'phone'       => 'nullable|string|max:25',
            'address'     => 'required|string',
            'country'     => 'required|string|max:100',
            'tax_name'    => 'nullable|string|max:100',
            'logo'        => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:3072',
            'is_default'  => 'sometimes|boolean',
        ]);

        $validated['is_default'] = $request->boolean('is_default');

        if ($request->hasFile('logo')) {
            // Remove old branch logo if present
            if ($businessAddress->logo && File::exists(public_path($businessAddress->logo))) {
                File::delete(public_path($businessAddress->logo));
            }

            $file = $request->file('logo');
            $filename = 'branch_logo_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('uploads/branch-logo');

            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $filename);
            $validated['logo'] = 'uploads/branch-logo/' . $filename;
        } else {
            if ($businessAddress->logo) {
                $validated['logo'] = $businessAddress->logo;
            }
        }

        if ($validated['is_default'] && !$businessAddress->is_default) {
            BusinessAddress::where('is_default', true)
                ->where('id', '!=', $businessAddress->id)
                ->update(['is_default' => false]);
        }

        $businessAddress->update($validated);

        return redirect()->route('admin.settings.business-address.index')
            ->with('success', 'Branch address updated successfully.');
    }

    public function destroy(BusinessAddress $businessAddress)
    {
        if (BusinessAddress::count() <= 1) {
            return redirect()->route('admin.settings.business-address.index')
                ->with('error', 'Cannot delete the only business address.');
        }

        if ($businessAddress->is_default) {
            $newDefault = BusinessAddress::where('id', '!=', $businessAddress->id)->first();
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        if ($businessAddress->logo && File::exists(public_path($businessAddress->logo))) {
            File::delete(public_path($businessAddress->logo));
        }

        $businessAddress->delete();

        return redirect()->route('admin.settings.business-address.index')
            ->with('success', 'Branch address deleted successfully.');
    }

    public function makeDefault(Request $request, BusinessAddress $businessAddress)
    {
        BusinessAddress::where('is_default', true)->update(['is_default' => false]);

        $businessAddress->update(['is_default' => true]);

        return redirect()->route('admin.settings.business-address.index')
            ->with('success', 'Default business address updated.');
    }
}
