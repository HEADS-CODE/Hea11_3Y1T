<?php

namespace App\Http\Controllers;

use App\Models\AdminProfile;
use App\Rules\PhilippinePhone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminProfileController extends Controller
{
    public function edit(): View
    {
        $profile = auth()->user()->adminProfile;

        if (! $profile) {
            $profile = new AdminProfile([
                'first_name' => auth()->user()->name,
                'last_name' => '',
                'gender' => 'Prefer not to say',
            ]);
        }

        return view('admin.profile', compact('profile'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:30', new PhilippinePhone()],
            'birth_date' => ['required', 'date', 'before_or_equal:today'],
            'gender' => ['required', Rule::in(config('membership.genders'))],
            'address' => ['nullable', 'string', 'max:500'],
            'unit_floor_building' => ['nullable', 'string', 'max:150'],
            'house_street' => ['required', 'string', 'max:150'],
            'barangay' => ['required', 'string', 'max:100'],
            'city_municipality' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'zip_code' => ['required', 'digits:4'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $profile = auth()->user()->adminProfile;
        if ($profile) {
            $profile->update($validated);
        } else {
            $validated['user_id'] = auth()->id();
            $profile = AdminProfile::create($validated);
        }

        auth()->user()->forceFill(['name' => $profile->full_name])->save();

        return back()->with('success', 'Administrator profile updated.');
    }
}
