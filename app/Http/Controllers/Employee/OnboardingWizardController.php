<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\OnboardingToken;
use App\Models\User;
use App\Models\EmployeeProfile;
use App\Models\HrPolicyLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OnboardingWizardController extends Controller
{
    public function show(Request $request, $token)
    {
        $onboardingToken = OnboardingToken::where('token', $token)->firstOrFail();

        if (!$onboardingToken->isValid()) {
            return abort(403, 'Link expired or already used.');
        }

        $user = $onboardingToken->user;
        if (!$user) {
            return abort(404, 'User associated with this link no longer exists.');
        }
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
        if (!$user) {
            return abort(404, 'User associated with this link no longer exists.');
        }

        $validated = $request->validate([
            // SECTION A: PERSONAL INFORMATION
            'full_name_aadhaar' => 'required|string|max:255',
            'dob' => 'required|date',
            'gender' => 'required|string',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'father_spouse_name' => 'required|string|max:255',
            'marital_status' => 'required|string',
            'blood_group' => 'required|string',
            'secondary_phone' => 'nullable|digits:10',
            'mother_name' => 'required|string|max:255',
            'permanent_address' => 'required|string',
            'address' => 'required|string',
            'profile_photo' => 'nullable|image|max:5120',

            // SECTION B: IDENTIFICATION DOCUMENTS
            'aadhaar_number' => 'required|string|max:12',
            'pan_number' => 'required|string|max:10',
            'passport_number' => 'nullable|string',
            'dl_number' => 'nullable|string',
            'voter_id' => 'nullable|string',
            'submitted_documents' => 'nullable|array',
            'aadhaar_card_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'pan_card_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',

            // SECTION D: EDUCATIONAL QUALIFICATIONS
            'educational_qualifications' => 'nullable|array',

            // SECTION E: WORK EXPERIENCE
            'work_experience' => 'nullable|array',
            'references' => 'nullable|array',

            // SECTION F: BANK DETAILS
            'bank_name' => 'required|string',
            'account_holder_name' => 'required|string',
            'account_number' => 'required|string',
            'ifsc_code' => 'required|string',
            'account_type' => 'required|string',

            // SECTION G: EMERGENCY CONTACT
            'emergency_contacts_json' => 'required|array|min:1',
            'emergency_contacts_json.*.name' => 'required|string|max:255',
            'emergency_contacts_json.*.relation' => 'required|string|max:255',
            'emergency_contacts_json.*.mobile' => 'required|string|max:15',

            // SECTION H: NOMINEE DETAILS
            'nominee_details' => 'required|array',
            'nominee_details.name' => 'required|string|max:255',
            'nominee_details.relation' => 'required|string|max:255',
            'nominee_details.dob' => 'required|date',

            // SECTION I: HOSPITALITY-SPECIFIC INFORMATION
            'medical_conditions' => 'nullable|string',
            'allergies' => 'nullable|string',
            'health_declaration' => 'nullable|array',
            'last_checkup_date' => 'nullable|date',
            'uniform_details' => 'required|array',
            'uniform_details.shirt' => 'required|string',
            'uniform_details.trouser' => 'required|string',
            'uniform_details.shoes' => 'required|string',

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
                    'full_name_aadhaar' => $validated['full_name_aadhaar'] ?? null,
                    'dob' => $validated['dob'] ?? null,
                    'gender' => $validated['gender'] ?? null,
                    'father_spouse_name' => $validated['father_spouse_name'] ?? null,
                    'marital_status' => $validated['marital_status'] ?? null,
                    'blood_group' => $validated['blood_group'] ?? null,
                    'secondary_phone' => $validated['secondary_phone'] ?? null,
                    'mother_name' => $validated['mother_name'] ?? null,
                    'permanent_address' => $validated['permanent_address'] ?? null,
                    'address' => $validated['address'] ?? null,

                    // Identification
                    'aadhaar_number' => $validated['aadhaar_number'] ?? null,
                    'pan_number' => $validated['pan_number'] ?? null,
                    'passport_number' => $validated['passport_number'] ?? null,
                    'dl_number' => $validated['dl_number'] ?? null,
                    'voter_id' => $validated['voter_id'] ?? null,
                    'submitted_documents' => $validated['submitted_documents'] ?? null,

                    // Tables / JSON
                    'educational_qualifications' => $validated['educational_qualifications'] ?? null,
                    'employment_history' => $validated['work_experience'] ?? null,
                    'references' => $validated['references'] ?? null,
                    'salary_details_ext' => [
                        'account_holder_name' => $validated['account_holder_name'] ?? null,
                        'account_type' => $validated['account_type'] ?? null,
                    ],
                    // We also update original bank columns for backward compatibility if needed
                    'bank_name' => $validated['bank_name'] ?? null,
                    'account_number' => $validated['account_number'] ?? null,
                    'ifsc_code' => $validated['ifsc_code'] ?? null,

                    'emergency_contacts_json' => $validated['emergency_contacts_json'] ?? null,
                    'nominee_details' => $validated['nominee_details'] ?? null,
                    'medical_info' => [
                        'conditions' => $validated['medical_conditions'] ?? null,
                        'allergies' => $validated['allergies'] ?? null,
                        'health_declaration' => $validated['health_declaration'] ?? [],
                        'last_checkup_date' => $validated['last_checkup_date'] ?? null,
                    ],
                    'uniform_details' => $validated['uniform_details'] ?? null,

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
                'kitchen_id' => $onboardingToken->kitchen_id,
                'policy_version' => '2.0-MischiefFull',
                'accepted_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);


            // Handle File Uploads
            if ($request->hasFile('profile_photo')) {
                $user->update(['profile_photo_path' => $request->file('profile_photo')->store('profile-photos', 'public')]);
            }

            if ($request->hasFile('aadhaar_card_file')) {
                // We'll store it on the profile if there was a column, but for now just log it
                // Or store in public since it's an internal portal demo
                $request->file('aadhaar_card_file')->store('identity-docs', 'public');
            }

            if ($request->hasFile('pan_card_file')) {
                $request->file('pan_card_file')->store('identity-docs', 'public');
            }

            // Update User fields if they were changed
            $user->update([
                'onboarding_status' => 'onboarding_completed',
                'email' => $validated['email'] ?? $user->email,
            ]);

            // Mark token as used
            $onboardingToken->update(['is_used' => true]);
        });

        return view('employee.onboarding.success');
    }

    public function checkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email', 'user_id' => 'required|exists:users,id']);
        
        $taken = \App\Models\User::where('email', $request->email)
            ->where('id', '!=', $request->user_id)
            ->exists();
            
        return response()->json(['taken' => $taken]);
    }
}
