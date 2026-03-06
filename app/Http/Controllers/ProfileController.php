<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Show the current user's profile (My Profile).
     */
    public function show(Request $request)
    {
        $user = $request->user()->load(['employeeProfile', 'permissions', 'hrPolicyLogs']);
        return view('profile.show', compact('user'));
    }

    /**
     * Show the form for editing the current user's profile.
     */
    public function edit(Request $request)
    {
        $user = $request->user()->load('employeeProfile');
        return view('profile.edit', compact('user'));
    }

    /**
     * Update the current user's profile.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|digits:10|unique:users,phone,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'profile_photo' => 'nullable|image|max:5120',
            'address' => 'nullable|string',
            'secondary_phone' => 'nullable|digits:10',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|digits:10',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'ifsc_code' => 'nullable|string|max:20',
            'joining_date' => 'nullable|date',
        ]);

        $userData = ['name' => $validated['name'], 'phone' => $validated['phone']];
        if (!empty($request->password)) {
            $userData['password'] = Hash::make($request->password);
        }
        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $userData['profile_photo_path'] = $path;
        }
        $user->update($userData);

        $user->employeeProfile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'address' => $validated['address'] ?? null,
                'secondary_phone' => $validated['secondary_phone'] ?? null,
                'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
                'bank_name' => $validated['bank_name'] ?? null,
                'account_number' => $validated['account_number'] ?? null,
                'ifsc_code' => $validated['ifsc_code'] ?? null,
                'joining_date' => $validated['joining_date'] ?? null,
            ]
        );

        return redirect()->route('profile.show')->with('success', 'Profile updated successfully.');
    }
}
