<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class StaffController extends Controller
{
    public function index()
    {
        $staff = User::where('role', \App\Enums\UserRole::Staff)->get();
        return view('admin.staff.index', compact('staff'));
    }

    public function create()
    {
        $permissions = Permission::all();
        return view('admin.staff.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15|unique:users',
            'staff_code' => 'required|string|max:6|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'profile_photo' => 'nullable|image|max:5120', // Optional 5MB max
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
            'monthly_salary' => 'required|numeric|min:0',
            'variable_enabled' => 'boolean',
            'max_variable_amount' => 'nullable|numeric|min:0',
            'weekly_off_day' => 'required|string',
        ]);

        $createData = [
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'staff_code' => $validated['staff_code'],
            'password' => Hash::make($validated['password']),
            'role' => \App\Enums\UserRole::Staff,
            'monthly_salary' => $validated['monthly_salary'],
            'variable_enabled' => $request->has('variable_enabled'),
            'max_variable_amount' => $validated['max_variable_amount'] ?? 0,
            'weekly_off_day' => $validated['weekly_off_day'],
            'onboarding_status' => 'pending',
            'is_password_changed' => false, // Force change
        ];

        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $createData['profile_photo_path'] = $path;
        }

        $user = User::create($createData);

        if (!empty($request->permissions)) {
            $user->permissions()->sync($request->permissions);
        }

        // Generate Onboarding Token
        $token = \Illuminate\Support\Str::random(32);
        $user->onboardingTokens()->create([
            'token' => $token,
            'expires_at' => now()->addDays(7),
        ]);

        // Logic to send Email/SMS would go here
        // For now, we'll just flash the link (for dev/testing)
        $onboardingLink = route('onboarding.wizard', ['token' => $token]);

        return redirect()->route('admin.staff.index')->with('success', 'Staff member created successfully. Onboarding Link: ' . $onboardingLink);
    }

    public function edit(string $id)
    {
        $user = User::where('role', \App\Enums\UserRole::Staff)->findOrFail($id);
        $permissions = Permission::all();
        return view('admin.staff.edit', compact('user', 'permissions'));
    }

    public function update(Request $request, string $id)
    {
        $user = User::where('role', \App\Enums\UserRole::Staff)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15|unique:users,phone,' . $id,
            'staff_code' => 'required|string|max:6|unique:users,staff_code,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'profile_photo' => 'nullable|image|max:5120',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        if (!empty($request->password)) {
            $validated['password'] = Hash::make($request->password);
        } else {
            unset($validated['password']);
        }

        if ($request->hasFile('profile_photo')) {
            // Delete old photo if exists
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $validated['profile_photo_path'] = $path;
        }

        $user->update($validated);

        // Sync permissions (if empty, detach all)
        $user->permissions()->sync($request->permissions ?? []);

        return redirect()->route('admin.staff.index')->with('success', 'Staff member updated successfully.');
    }

    public function destroy(string $id)
    {
        $user = User::where('role', \App\Enums\UserRole::Staff)->findOrFail($id);
        $user->delete();

        return redirect()->route('admin.staff.index')->with('success', 'Staff member deleted successfully.');
    }
}
