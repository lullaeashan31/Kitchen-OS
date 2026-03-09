<div class="space-y-6">
    <!-- Profile Header -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-100">
        <div class="flex items-center gap-6">
            <div class="w-20 h-20 rounded-full flex items-center justify-center text-white font-bold text-2xl shadow-lg overflow-hidden bg-gray-200 border-4 border-white">
                @if($user->profile_photo_path)
                    <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
                        {{ substr($user->name, 0, 2) }}
                    </div>
                @endif
            </div>
            <div>
                <h3 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h3>
                <div class="flex items-center gap-3 mt-2">
                    <span class="font-mono text-gray-600 bg-white px-3 py-1 rounded-lg text-sm font-bold tracking-widest border border-gray-200">
                        {{ $user->staff_code }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700">
                        <i data-lucide="badge-check" class="w-3 h-3"></i>
                        {{ $user->role->label() }}
                    </span>
                    @if($user->onboarding_status === 'pending')
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700">Pending</span>
                    @elseif($user->onboarding_status === 'onboarding_completed')
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-700">Wait Approval</span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">Active</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Basic Information -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i data-lucide="user" class="w-5 h-5 text-blue-600"></i>
            Basic Information
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-xs text-gray-500 uppercase font-semibold">Phone</label>
                <p class="text-gray-900 font-medium">{{ $user->phone ?? 'N/A' }}</p>
            </div>
            <div>
                <label class="text-xs text-gray-500 uppercase font-semibold">Email</label>
                <p class="text-gray-900 font-medium">{{ $user->email ?? 'N/A' }}</p>
            </div>
            <div>
                <label class="text-xs text-gray-500 uppercase font-semibold">Joined Date</label>
                <p class="text-gray-900 font-medium">{{ $user->created_at->format('M d, Y') }}</p>
            </div>
            <div>
                <label class="text-xs text-gray-500 uppercase font-semibold">Monthly Salary</label>
                <p class="text-gray-900 font-medium">₹{{ number_format($user->monthly_salary ?? 0, 2) }}</p>
            </div>
            @if($user->variable_enabled)
                <div>
                    <label class="text-xs text-gray-500 uppercase font-semibold">Variable Pay Enabled</label>
                    <p class="text-gray-900 font-medium">Yes (Max: ₹{{ number_format($user->max_variable_amount ?? 0, 2) }})</p>
                </div>
            @endif
            @if($user->weekly_off_day)
                <div>
                    <label class="text-xs text-gray-500 uppercase font-semibold">Weekly Off Day</label>
                    <p class="text-gray-900 font-medium">{{ ucfirst($user->weekly_off_day) }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Onboarding/Employee Profile Data -->
    @if($user->employeeProfile)
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i data-lucide="file-text" class="w-5 h-5 text-green-600"></i>
                Onboarding Information
            </h4>
            <div class="space-y-6">
                <!-- Personal Information -->
                <div>
                    <h5 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <i data-lucide="user-circle" class="w-4 h-4 text-blue-500"></i>
                        Personal Information
                    </h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if($user->employeeProfile->full_name_aadhaar)
                            <div>
                                <label class="text-xs text-gray-500 uppercase font-semibold">Full Name (Aadhaar)</label>
                                <p class="text-gray-900 font-medium mt-1">{{ $user->employeeProfile->full_name_aadhaar }}</p>
                            </div>
                        @endif
                        @if($user->employeeProfile->dob)
                            <div>
                                <label class="text-xs text-gray-500 uppercase font-semibold">Date of Birth</label>
                                <p class="text-gray-900 font-medium mt-1">{{ $user->employeeProfile->dob->format('M d, Y') }}</p>
                            </div>
                        @endif
                        @if($user->employeeProfile->gender)
                            <div>
                                <label class="text-xs text-gray-500 uppercase font-semibold">Gender</label>
                                <p class="text-gray-900 font-medium mt-1">{{ $user->employeeProfile->gender }}</p>
                            </div>
                        @endif
                        @if($user->employeeProfile->marital_status)
                            <div>
                                <label class="text-xs text-gray-500 uppercase font-semibold">Marital Status</label>
                                <p class="text-gray-900 font-medium mt-1">{{ $user->employeeProfile->marital_status }}</p>
                            </div>
                        @endif
                        @if($user->employeeProfile->father_spouse_name)
                            <div>
                                <label class="text-xs text-gray-500 uppercase font-semibold">Father / Spouse Name</label>
                                <p class="text-gray-900 font-medium mt-1">{{ $user->employeeProfile->father_spouse_name }}</p>
                            </div>
                        @endif
                        @if($user->employeeProfile->blood_group)
                            <div>
                                <label class="text-xs text-gray-500 uppercase font-semibold">Blood Group</label>
                                <p class="text-gray-900 font-medium mt-1">{{ $user->employeeProfile->blood_group }}</p>
                            </div>
                        @endif
                        @if($user->employeeProfile->permanent_address)
                            <div class="md:col-span-2">
                                <label class="text-xs text-gray-500 uppercase font-semibold">Permanent Address</label>
                                <p class="text-gray-900 font-medium mt-1">{{ $user->employeeProfile->permanent_address }}</p>
                            </div>
                        @endif
                        @if($user->employeeProfile->address)
                            <div class="md:col-span-2">
                                <label class="text-xs text-gray-500 uppercase font-semibold">Current Address</label>
                                <p class="text-gray-900 font-medium mt-1">{{ $user->employeeProfile->address }}</p>
                            </div>
                        @endif
                        @if($user->employeeProfile->secondary_phone)
                            <div>
                                <label class="text-xs text-gray-500 uppercase font-semibold">Secondary Phone</label>
                                <p class="text-gray-900 font-medium mt-1">{{ $user->employeeProfile->secondary_phone }}</p>
                            </div>
                        @endif
                        @if($user->employeeProfile->joining_date)
                            <div>
                                <label class="text-xs text-gray-500 uppercase font-semibold">Joining Date</label>
                                <p class="text-gray-900 font-medium mt-1">{{ $user->employeeProfile->joining_date->format('M d, Y') }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Identification & Documents -->
                @if($user->employeeProfile->aadhaar_number || $user->employeeProfile->pan_number || $user->employeeProfile->passport_number || $user->employeeProfile->dl_number || $user->employeeProfile->voter_id || $user->employeeProfile->submitted_documents)
                    <div>
                        <h5 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                            <i data-lucide="id-card" class="w-4 h-4 text-purple-500"></i>
                            Identification & Documents
                        </h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @if($user->employeeProfile->aadhaar_number)
                                <div>
                                    <label class="text-xs text-gray-500 uppercase font-semibold">Aadhaar Number</label>
                                    <p class="text-gray-900 font-medium mt-1 font-mono tracking-[0.25em]">{{ $user->employeeProfile->aadhaar_number }}</p>
                                </div>
                            @endif
                            @if($user->employeeProfile->pan_number)
                                <div>
                                    <label class="text-xs text-gray-500 uppercase font-semibold">PAN Number</label>
                                    <p class="text-gray-900 font-medium mt-1 font-mono uppercase">{{ $user->employeeProfile->pan_number }}</p>
                                </div>
                            @endif
                            @if($user->employeeProfile->dl_number)
                                <div>
                                    <label class="text-xs text-gray-500 uppercase font-semibold">Driver's License</label>
                                    <p class="text-gray-900 font-medium mt-1">{{ $user->employeeProfile->dl_number }}</p>
                                </div>
                            @endif
                            @if($user->employeeProfile->voter_id)
                                <div>
                                    <label class="text-xs text-gray-500 uppercase font-semibold">Voter ID</label>
                                    <p class="text-gray-900 font-medium mt-1">{{ $user->employeeProfile->voter_id }}</p>
                                </div>
                            @endif
                            @if($user->employeeProfile->passport_number)
                                <div>
                                    <label class="text-xs text-gray-500 uppercase font-semibold">Passport Number</label>
                                    <p class="text-gray-900 font-medium mt-1">{{ $user->employeeProfile->passport_number }}</p>
                                </div>
                            @endif
                            @if($user->employeeProfile->submitted_documents)
                                <div class="md:col-span-2">
                                    <label class="text-xs text-gray-500 uppercase font-semibold">Submitted Documents</label>
                                    <ul class="mt-1 text-sm text-gray-700 list-disc list-inside space-y-1">
                                        @foreach((array) $user->employeeProfile->submitted_documents as $doc)
                                            <li>{{ $doc }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Education & Work History -->
                @if($user->employeeProfile->educational_qualifications || $user->employeeProfile->employment_history)
                    <div>
                        <h5 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                            <i data-lucide="graduation-cap" class="w-4 h-4 text-indigo-500"></i>
                            Education & Work History
                        </h5>
                        <div class="space-y-4">
                            @if($user->employeeProfile->educational_qualifications)
                                <div>
                                    <label class="text-xs text-gray-500 uppercase font-semibold">Educational Qualifications</label>
                                    <div class="mt-1 text-sm text-gray-700">
                                        @foreach((array) $user->employeeProfile->educational_qualifications as $edu)
                                            <div class="flex items-center justify-between py-1 border-b border-dashed border-gray-100 last:border-0">
                                                <span>{{ $edu['name'] ?? '' }} @if(!empty($edu['inst'])) – {{ $edu['inst'] }} @endif</span>
                                                <span class="text-gray-500 text-xs">
                                                    @if(!empty($edu['year'])) {{ $edu['year'] }} @endif
                                                    @if(!empty($edu['grade'])) · {{ $edu['grade'] }} @endif
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($user->employeeProfile->employment_history)
                                <div>
                                    <label class="text-xs text-gray-500 uppercase font-semibold">Employment History</label>
                                    <div class="mt-1 text-sm text-gray-700">
                                        @foreach((array) $user->employeeProfile->employment_history as $job)
                                            <div class="py-1 border-b border-dashed border-gray-100 last:border-0">
                                                <div class="font-medium">
                                                    {{ $job['company'] ?? '' }}
                                                    @if(!empty($job['role'])) – {{ $job['role'] }} @endif
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    @if(!empty($job['duration'])) {{ $job['duration'] }} @endif
                                                    @if(!empty($job['salary'])) · Last Salary: {{ $job['salary'] }} @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Emergency Contacts -->
                <div>
                    <h5 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <i data-lucide="phone-call" class="w-4 h-4 text-red-500"></i>
                        Emergency Contacts
                    </h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if($user->employeeProfile->emergency_contact_name)
                            <div>
                                <label class="text-xs text-gray-500 uppercase font-semibold">Primary Contact Name</label>
                                <p class="text-gray-900 font-medium mt-1">{{ $user->employeeProfile->emergency_contact_name }}</p>
                            </div>
                        @endif
                        @if($user->employeeProfile->emergency_contact_phone)
                            <div>
                                <label class="text-xs text-gray-500 uppercase font-semibold">Primary Contact Phone</label>
                                <p class="text-gray-900 font-medium mt-1">{{ $user->employeeProfile->emergency_contact_phone }}</p>
                            </div>
                        @endif
                    </div>
                    @if($user->employeeProfile->emergency_contacts_json)
                        <div class="mt-4 space-y-2 text-sm text-gray-700">
                            @foreach((array) $user->employeeProfile->emergency_contacts_json as $contact)
                                <div class="flex items-center justify-between px-3 py-2 bg-gray-50 rounded-lg border border-gray-100">
                                    <div>
                                        <div class="font-medium">{{ $contact['name'] ?? 'Contact' }}</div>
                                        <div class="text-xs text-gray-500">{{ $contact['relation'] ?? '' }}</div>
                                    </div>
                                    <div class="font-mono text-sm text-gray-800">{{ $contact['mobile'] ?? '' }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Bank Details -->
                <div>
                    <h5 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <i data-lucide="credit-card" class="w-4 h-4 text-green-500"></i>
                        Bank Account Details
                    </h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if($user->employeeProfile->bank_name)
                            <div>
                                <label class="text-xs text-gray-500 uppercase font-semibold">Bank Name</label>
                                <p class="text-gray-900 font-medium mt-1">{{ $user->employeeProfile->bank_name }}</p>
                            </div>
                        @endif
                        @if($user->employeeProfile->account_number)
                            <div>
                                <label class="text-xs text-gray-500 uppercase font-semibold">Account Number</label>
                                <p class="text-gray-900 font-medium mt-1 font-mono">{{ $user->employeeProfile->account_number }}</p>
                            </div>
                        @endif
                        @if($user->employeeProfile->ifsc_code)
                            <div>
                                <label class="text-xs text-gray-500 uppercase font-semibold">IFSC Code</label>
                                <p class="text-gray-900 font-medium mt-1 font-mono">{{ $user->employeeProfile->ifsc_code }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Nominee -->
                @if($user->employeeProfile->nominee_details)
                    @php $nom = $user->employeeProfile->nominee_details; @endphp
                    <div>
                        <h5 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                            <i data-lucide="heart" class="w-4 h-4 text-rose-500"></i>
                            Nominee Details
                        </h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-800">
                            @if(!empty($nom['name']))
                                <div>
                                    <label class="text-xs text-gray-500 uppercase font-semibold">Nominee Name</label>
                                    <p class="mt-1 font-medium">{{ $nom['name'] }}</p>
                                </div>
                            @endif
                            @if(!empty($nom['relation']))
                                <div>
                                    <label class="text-xs text-gray-500 uppercase font-semibold">Relationship</label>
                                    <p class="mt-1 font-medium">{{ $nom['relation'] }}</p>
                                </div>
                            @endif
                            @if(!empty($nom['dob']))
                                <div>
                                    <label class="text-xs text-gray-500 uppercase font-semibold">Nominee DOB</label>
                                    <p class="mt-1 font-medium">{{ \Carbon\Carbon::parse($nom['dob'])->format('M d, Y') }}</p>
                                </div>
                            @endif
                            @if(isset($nom['share']))
                                <div>
                                    <label class="text-xs text-gray-500 uppercase font-semibold">Benefit Share</label>
                                    <p class="mt-1 font-medium">{{ $nom['share'] }}%</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Medical & Uniform -->
                @if($user->employeeProfile->medical_info || $user->employeeProfile->uniform_details)
                    <div>
                        <h5 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                            <i data-lucide="stethoscope" class="w-4 h-4 text-emerald-500"></i>
                            Health & Uniform
                        </h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-800">
                            @if($user->employeeProfile->medical_info)
                                @php $med = $user->employeeProfile->medical_info; @endphp
                                <div>
                                    <label class="text-xs text-gray-500 uppercase font-semibold">Medical Fitness</label>
                                    <p class="mt-1 font-medium">{{ $med['fitness_checked'] ?? 'N/A' }}</p>
                                </div>
                                @if(!empty($med['conditions']))
                                    <div class="md:col-span-2">
                                        <label class="text-xs text-gray-500 uppercase font-semibold">Medical Conditions / Allergies</label>
                                        <p class="mt-1">{{ $med['conditions'] }}</p>
                                    </div>
                                @endif
                            @endif

                            @if($user->employeeProfile->uniform_details)
                                @php $uni = $user->employeeProfile->uniform_details; @endphp
                                <div>
                                    <label class="text-xs text-gray-500 uppercase font-semibold">Uniform Sizes</label>
                                    <ul class="mt-1 text-sm text-gray-800 space-y-1">
                                        @if(!empty($uni['shirt'])) <li>Shirt/Kurta: <span class="font-medium">{{ $uni['shirt'] }}</span></li> @endif
                                        @if(!empty($uni['trouser'])) <li> Trouser/Pants: <span class="font-medium">{{ $uni['trouser'] }}</span></li> @endif
                                        @if(!empty($uni['shoes'])) <li>Safety Shoes: <span class="font-medium">{{ $uni['shoes'] }}</span></li> @endif
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @if($user->hrPolicyLogs && $user->hrPolicyLogs->count() > 0)
            <div class="bg-white rounded-xl border border-gray-200 p-6 mt-6">
                <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i data-lucide="file-check" class="w-5 h-5 text-indigo-600"></i>
                    HR Policy Acceptance
                </h4>
                <div class="space-y-3">
                    @foreach($user->hrPolicyLogs as $log)
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg border border-green-200">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Policy Version {{ $log->policy_version }}</p>
                                <p class="text-xs text-gray-500">Accepted on {{ $log->accepted_at->format('M d, Y h:i A') }}</p>
                            </div>
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                <i data-lucide="check-circle" class="w-3 h-3"></i>
                                Accepted
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @else
        <div class="bg-yellow-50 rounded-xl border border-yellow-200 p-6">
            <p class="text-yellow-800 text-sm flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4"></i>
                No onboarding data available yet.
            </p>
        </div>
    @endif

    <!-- Permissions -->
    @if($user->permissions && $user->permissions->count() > 0)
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i data-lucide="lock" class="w-5 h-5 text-purple-600"></i>
                Permissions
            </h4>
            <div class="flex flex-wrap gap-2">
                @foreach($user->permissions as $permission)
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-700 border border-purple-200">
                        <i data-lucide="check" class="w-3 h-3"></i>
                        {{ $permission->name }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif
</div>
