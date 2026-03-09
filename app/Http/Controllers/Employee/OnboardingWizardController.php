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
            // SECTION A: PERSONAL INFORMATION
            'full_name_aadhaar' => 'nullable|string|max:255',
            'dob' => 'nullable|date',
            'gender' => 'nullable|string',
            'father_spouse_name' => 'nullable|string|max:255',
            'marital_status' => 'nullable|string',
            'blood_group' => 'nullable|string',
            'secondary_phone' => 'nullable|string|max:15',
            'permanent_address' => 'nullable|string',
            'address' => 'nullable|string',

            // SECTION B: IDENTIFICATION DOCUMENTS
            'aadhaar_number' => 'nullable|string|max:12',
            'pan_number' => 'nullable|string|max:10',
            'passport_number' => 'nullable|string',
            'dl_number' => 'nullable|string',
            'voter_id' => 'nullable|string',
            'submitted_documents' => 'nullable|array',

            // SECTION D: EDUCATIONAL QUALIFICATIONS
            'educational_qualifications' => 'nullable|array',

            // SECTION E: WORK EXPERIENCE
            'work_experience' => 'nullable|array',

            // SECTION F: BANK DETAILS
            'bank_name' => 'nullable|string',
            'account_holder_name' => 'nullable|string',
            'account_number' => 'nullable|string',
            'ifsc_code' => 'nullable|string',
            'account_type' => 'nullable|string',

            // SECTION G: EMERGENCY CONTACT
            'emergency_contacts_json' => 'nullable|array',

            // SECTION H: NOMINEE DETAILS
            'nominee_details' => 'nullable|array',

            // SECTION I: HOSPITALITY-SPECIFIC INFORMATION
            'medical_info' => 'nullable|array',
            'uniform_details' => 'nullable|array',

            // SECTION J, M, N, O acknowledgments
            'asset_acknowledged' => 'accepted',
            'police_verification_consent' => 'accepted',
            'leave_policy_acknowledged' => 'accepted',
            'probation_terms_acknowledged' => 'accepted',
            'grievance_acknowledged' => 'accepted',
            'declaration_accepted' => 'accepted',
            'digital_signature' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($user, $validated, $onboardingToken, $request) {
            $user->employeeProfile()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'kitchen_id' => $onboardingToken->kitchen_id,
                ],
                [
                    'kitchen_id' => $onboardingToken->kitchen_id,
                    // Personal & Bio
                    'full_name_aadhaar' => $validated['full_name_aadhaar'],
                    'dob' => $validated['dob'],
                    'gender' => $validated['gender'],
                    'father_spouse_name' => $validated['father_spouse_name'],
                    'marital_status' => $validated['marital_status'],
                    'blood_group' => $validated['blood_group'],
                    'secondary_phone' => $validated['secondary_phone'],
                    'permanent_address' => $validated['permanent_address'],
                    'address' => $validated['address'],

                    // Identification
                    'aadhaar_number' => $validated['aadhaar_number'],
                    'pan_number' => $validated['pan_number'],
                    'passport_number' => $validated['passport_number'],
                    'dl_number' => $validated['dl_number'],
                    'voter_id' => $validated['voter_id'],
                    'submitted_documents' => $validated['submitted_documents'],

                    // Tables / JSON
                    'educational_qualifications' => $validated['educational_qualifications'],
                    'employment_history' => $validated['work_experience'],  // maps form name to DB column
                    'salary_details_ext' => [
                        'account_holder_name' => $validated['account_holder_name'],
                        'account_type' => $validated['account_type'],
                    ],
                    // We also update original bank columns for backward compatibility if needed
                    'bank_name' => $validated['bank_name'],
                    'account_number' => $validated['account_number'],
                    'ifsc_code' => $validated['ifsc_code'],

                    'emergency_contacts_json' => $validated['emergency_contacts_json'],
                    'nominee_details' => $validated['nominee_details'],
                    'medical_info' => $validated['medical_info'],
                    'uniform_details' => $validated['uniform_details'],

                    // Acknowledgments log
                    'asset_acknowledgments' => [
                        'acknowledged_at' => now(),
                        'ip_address' => $request->ip()
                    ],
                    'probation_info' => [
                        'terms_accepted' => true,
                        'accepted_at' => now()
                    ],
                    'digital_signature' => $validated['digital_signature'],
                ]
            );

            // Log Policy Acceptance (HR Policy from original, maybe Section M/Grievance)
            $user->hrPolicyLogs()->create([
                'policy_version' => '2.0-MischiefFull',
                'accepted_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Mark user as onboarding_completed
            $user->update(['onboarding_status' => 'onboarding_completed']);

            // Mark token as used
            $onboardingToken->update(['is_used' => true]);
        });

        return view('employee.onboarding.success');
    }
}
