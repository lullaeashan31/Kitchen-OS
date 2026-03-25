<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class StaffController extends Controller
{
    public function index(string $kitchen_slug)
    {
        $staff = User::where('role', \App\Enums\UserRole::Staff)
            ->with(['employeeProfile', 'jobRole'])
            ->get();
        return view('admin.staff.index', compact('staff'));
    }

    public function create(string $kitchen_slug)
    {
        $permissions = Permission::orderBy('name')->get();
        $roles = Role::orderBy('name')->get();
        return view('admin.staff.create', compact('permissions', 'roles'));
    }

    public function store(Request $request, string $kitchen_slug)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|digits:10|unique:users',
            'staff_code' => 'required|numeric|digits:6|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'profile_photo' => 'nullable|image|max:5120', // Optional 5MB max
            'job_role_id' => 'nullable|exists:roles,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
            'monthly_salary' => 'nullable|numeric|min:0',
            'salary_type' => 'required|in:monthly,daily,hourly',
            'daily_salary' => 'nullable|numeric|min:0',
            'hourly_salary' => 'nullable|numeric|min:0',
            'variable_enabled' => 'boolean',
            'max_variable_amount' => 'nullable|numeric|min:0',
            'weekly_off_day' => 'nullable|numeric|min:0|max:31',
        ]);

        $createData = [
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'staff_code' => $validated['staff_code'],
            'password' => Hash::make($validated['password']),
            'role' => \App\Enums\UserRole::Staff,
            'salary_type' => $validated['salary_type'],
            'monthly_salary' => $validated['monthly_salary'] ?? 0,
            'daily_salary' => $validated['daily_salary'] ?? 0,
            'hourly_salary' => $validated['hourly_salary'] ?? 0,
            'variable_enabled' => $request->has('variable_enabled'),
            'max_variable_amount' => $validated['max_variable_amount'] ?? 0,
            'weekly_off_day' => $validated['weekly_off_day'] ?? 0,
            'onboarding_status' => 'pending',
            'is_password_changed' => false, // Force change
            'job_role_id' => $validated['job_role_id'] ?? null,
            'kitchen_id' => app()->has('current_kitchen') ? app('current_kitchen')->id : null,
        ];

        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $createData['profile_photo_path'] = $path;
        }

        $user = User::create($createData);

        $permissionIds = $request->permissions ?? [];
        $user->permissions()->sync($permissionIds);

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

    public function edit(string $kitchen_slug, string $id)
    {
        $user = User::where('role', \App\Enums\UserRole::Staff)
            ->with(['employeeProfile', 'jobRole'])
            ->findOrFail($id);

        // Ensure we can see onboarding data even if older profiles were saved without kitchen_id
        $rawProfile = \App\Models\EmployeeProfile::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->first();
        if ($rawProfile) {
            $user->setRelation('employeeProfile', $rawProfile);
        }
        $permissions = Permission::orderBy('name')->get();
        $roles = Role::orderBy('name')->get();
        return view('admin.staff.edit', compact('user', 'permissions', 'roles'));
    }

    public function update(Request $request, string $kitchen_slug, string $id)
    {
        $user = User::where('role', \App\Enums\UserRole::Staff)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|digits:10|unique:users,phone,' . $id,
            'staff_code' => 'required|numeric|digits:6|unique:users,staff_code,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'profile_photo' => 'nullable|image|max:5120',
            'job_role_id' => 'nullable|exists:roles,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
            // Salary & employment (User)
            'monthly_salary' => 'nullable|numeric|min:0',
            'salary_type' => 'required|in:monthly,daily,hourly',
            'daily_salary' => 'nullable|numeric|min:0',
            'hourly_salary' => 'nullable|numeric|min:0',
            'variable_enabled' => 'boolean',
            'max_variable_amount' => 'nullable|numeric|min:0',
            'weekly_off_day' => 'nullable|numeric|min:0|max:31',
            // Employee profile / onboarding
            'address' => 'nullable|string',
            'secondary_phone' => 'nullable|digits:10',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|digits:10',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'ifsc_code' => 'nullable|string|max:20',
            'joining_date' => 'nullable|date',
        ]);

        if (!empty($request->password)) {
            $validated['password'] = Hash::make($request->password);
        } else {
            unset($validated['password']);
        }

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $validated['profile_photo_path'] = $path;
        }

        $userData = [
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'staff_code' => $validated['staff_code'],
            'salary_type' => $validated['salary_type'],
            'monthly_salary' => $validated['monthly_salary'] ?? 0,
            'daily_salary' => $validated['daily_salary'] ?? 0,
            'hourly_salary' => $validated['hourly_salary'] ?? 0,
            'variable_enabled' => $request->boolean('variable_enabled'),
            'max_variable_amount' => $validated['max_variable_amount'] ?? 0,
            'weekly_off_day' => $validated['weekly_off_day'] ?? 0,
            'job_role_id' => $validated['job_role_id'] ?? null,
        ];
        if (isset($validated['password'])) {
            $userData['password'] = $validated['password'];
        }
        if (isset($validated['profile_photo_path'])) {
            $userData['profile_photo_path'] = $validated['profile_photo_path'];
        }
        $user->update($userData);

        $permissionIds = $request->permissions ?? [];
        $user->permissions()->sync($permissionIds);

        // Update or create employee profile (onboarding data)
        $profileData = [
            'address' => $validated['address'] ?? null,
            'secondary_phone' => $validated['secondary_phone'] ?? null,
            'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
            'bank_name' => $validated['bank_name'] ?? null,
            'account_number' => $validated['account_number'] ?? null,
            'ifsc_code' => $validated['ifsc_code'] ?? null,
            'joining_date' => $validated['joining_date'] ?? null,
        ];
        $user->employeeProfile()->updateOrCreate(
            ['user_id' => $user->id],
            $profileData
        );

        return redirect()->route('admin.staff.index')->with('success', 'Staff member updated successfully.');
    }

    public function destroy(string $kitchen_slug, string $id)
    {
        $user = User::where('role', \App\Enums\UserRole::Staff)->findOrFail($id);
        $user->delete();

        return redirect()->route('admin.staff.index')->with('success', 'Staff member deleted successfully.');
    }

    public function approve(string $kitchen_slug, string $id)
    {
        $user = User::where('role', \App\Enums\UserRole::Staff)->findOrFail($id);
        $user->update(['onboarding_status' => 'active']);

        return redirect()->route('admin.staff.index')->with('success', 'Staff onboarding approved and account activated.');
    }

    public function show(string $kitchen_slug, string $id)
    {
        $user = User::where('role', \App\Enums\UserRole::Staff)
            ->with(['employeeProfile', 'permissions', 'hrPolicyLogs'])
            ->findOrFail($id);

        // Load full onboarding profile ignoring tenant scope so older data is visible
        $rawProfile = \App\Models\EmployeeProfile::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->first();
        if ($rawProfile) {
            $user->setRelation('employeeProfile', $rawProfile);
        }

        return view('admin.staff.show', compact('user'));
    }
}
