<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\OnboardingToken;
use App\Models\User;
use App\Models\EmployeeProfile;
use App\Models\HrPolicyLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OnboardingWizardController extends Controller
{
    public function show(Request $request, $token)
    {
        $onboardingToken = OnboardingToken::where('token', $token)->firstOrFail();

        if (!$onboardingToken->isValid()) {
            return abort(403, 'Link expired or already used.');
        }

        $user = $onboardingToken->user;
        $profile = $user->employeeProfile ?? new EmployeeProfile();

        return view('employee.onboarding.wizard', compact('user', 'profile', 'token'));
    }

    public function submit(Request $request, $token)
    {
        $onboardingToken = OnboardingToken::where('token', $token)->firstOrFail();

        if (!$onboardingToken->isValid()) {
            return abort(403, 'Link expired or already used.');
        }

        $user = $onboardingToken->user;

        $validated = $request->validate([
            // Step 1: Personal
            'address' => 'required|string',
            'secondary_phone' => 'nullable|string',
            // Step 2: Emergency
            'emergency_contact_name' => 'required|string',
            'emergency_contact_phone' => 'required|string',
            // Step 3: Bank
            'bank_name' => 'required|string',
            'account_number' => 'required|string',
            'ifsc_code' => 'required|string',
            // Policy
            'policy_accepted' => 'accepted',
        ]);

        DB::transaction(function () use ($user, $validated, $onboardingToken, $request) {
            $user->employeeProfile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'address' => $validated['address'],
                    'secondary_phone' => $validated['secondary_phone'],
                    'emergency_contact_name' => $validated['emergency_contact_name'],
                    'emergency_contact_phone' => $validated['emergency_contact_phone'],
                    'bank_name' => $validated['bank_name'],
                    'account_number' => $validated['account_number'],
                    'ifsc_code' => $validated['ifsc_code'],
                ]
            );

            // Log Policy Acceptance
            $user->hrPolicyLogs()->create([
                'policy_version' => '1.0',
                'accepted_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Mark user as onboarding_completed (waiting for admin approval)
            $user->update(['onboarding_status' => 'onboarding_completed']);

            // Mark token as used
            $onboardingToken->update(['is_used' => true]);
        });

        return redirect()->route('login')->with('success', 'Onboarding completed! Waiting for Admin approval.');
    }
}
