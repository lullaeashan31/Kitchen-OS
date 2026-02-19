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
                <!-- Personal Information Section -->
                <div>
                    <h5 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <i data-lucide="user-circle" class="w-4 h-4 text-blue-500"></i>
                        Personal Information
                    </h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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

                <!-- Emergency Contact Section -->
                <div>
                    <h5 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <i data-lucide="phone-call" class="w-4 h-4 text-red-500"></i>
                        Emergency Contact
                    </h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if($user->employeeProfile->emergency_contact_name)
                            <div>
                                <label class="text-xs text-gray-500 uppercase font-semibold">Contact Name</label>
                                <p class="text-gray-900 font-medium mt-1">{{ $user->employeeProfile->emergency_contact_name }}</p>
                            </div>
                        @endif
                        @if($user->employeeProfile->emergency_contact_phone)
                            <div>
                                <label class="text-xs text-gray-500 uppercase font-semibold">Contact Phone</label>
                                <p class="text-gray-900 font-medium mt-1">{{ $user->employeeProfile->emergency_contact_phone }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Bank Details Section -->
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

                <!-- Additional Information -->
                @if($user->employeeProfile->identity_proof_path || $user->employeeProfile->employment_history || $user->employeeProfile->references)
                    <div>
                        <h5 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                            <i data-lucide="file-plus" class="w-4 h-4 text-purple-500"></i>
                            Additional Information
                        </h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @if($user->employeeProfile->identity_proof_path)
                                <div>
                                    <label class="text-xs text-gray-500 uppercase font-semibold">Identity Proof</label>
                                    <p class="text-gray-900 font-medium mt-1">
                                        <a href="{{ asset('storage/' . $user->employeeProfile->identity_proof_path) }}" target="_blank" class="text-blue-600 hover:underline flex items-center gap-1">
                                            <i data-lucide="file" class="w-3 h-3"></i> View Document
                                        </a>
                                    </p>
                                </div>
                            @endif
                            @if($user->employeeProfile->employment_history)
                                <div class="md:col-span-2">
                                    <label class="text-xs text-gray-500 uppercase font-semibold">Employment History</label>
                                    <div class="mt-1 text-sm text-gray-700">
                                        @if(is_array($user->employeeProfile->employment_history))
                                            <ul class="list-disc list-inside space-y-1">
                                                @foreach($user->employeeProfile->employment_history as $history)
                                                    <li>{{ $history }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p>{{ $user->employeeProfile->employment_history }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            @if($user->employeeProfile->references)
                                <div class="md:col-span-2">
                                    <label class="text-xs text-gray-500 uppercase font-semibold">References</label>
                                    <div class="mt-1 text-sm text-gray-700">
                                        @if(is_array($user->employeeProfile->references))
                                            <ul class="list-disc list-inside space-y-1">
                                                @foreach($user->employeeProfile->references as $reference)
                                                    <li>{{ $reference }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p>{{ $user->employeeProfile->references }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- HR Policy Acceptance -->
        @if($user->hrPolicyLogs->count() > 0)
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
                No onboarding data available yet. Staff member has not completed the onboarding form.
            </p>
        </div>
    @endif

    <!-- Permissions -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i data-lucide="lock" class="w-5 h-5 text-purple-600"></i>
            Permissions
        </h4>
        @if($user->permissions->count() > 0)
            <div class="flex flex-wrap gap-2">
                @foreach($user->permissions as $permission)
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-700 border border-purple-200">
                        <i data-lucide="check" class="w-3 h-3"></i>
                        {{ $permission->name }}
                    </span>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 text-sm">No custom permissions assigned. Using default role permissions.</p>
        @endif
    </div>
</div>
