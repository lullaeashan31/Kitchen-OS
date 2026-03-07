@extends('layouts.app')

@section('header')
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.staff.index') }}" class="p-2 bg-white rounded-lg border border-gray-200 text-gray-500 hover:text-blue-600 hover:border-blue-300 transition-all shadow-sm">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Edit Staff Member</h1>
            <p class="text-sm text-gray-500">Update profile details and system access.</p>
        </div>
    </div>
@endsection

@section('content')
    <div class="w-full pb-10">
        <form action="{{ route('admin.staff.update', $user) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden mb-6">
                <!-- Profile & Photo Header -->
                <div class="bg-white p-8 border-b border-gray-100 flex flex-col md:flex-row items-center gap-8">
                    <div class="shrink-0 relative group">
                        <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-white shadow-2xl bg-gray-100 ring-4 ring-blue-50">
                             @if($user->profile_photo_path)
                                <img id="photo_preview" src="{{ $user->profile_photo_url }}" class="w-full h-full object-cover">
                            @else
                                <div id="photo_placeholder" class="w-full h-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-4xl font-bold">
                                    {{ substr($user->name, 0, 2) }}
                                </div>
                                <img id="photo_preview" class="hidden w-full h-full object-cover">
                            @endif
                        </div>
                        <label for="profile_photo" class="absolute bottom-1 right-1 bg-blue-600 text-white p-2.5 rounded-full cursor-pointer hover:bg-blue-700 shadow-lg transition-all transform hover:scale-110 border-2 border-white">
                            <i data-lucide="camera" class="w-5 h-5"></i>
                        </label>
                        <input type="file" name="profile_photo" id="profile_photo" class="hidden" accept="image/*" onchange="previewImage(this)">
                    </div>
                    <div class="text-center md:text-left">
                        <h2 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h2>
                        <div class="flex items-center justify-center md:justify-start gap-2 mt-2">
                             <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700">
                                <i data-lucide="badge-check" class="w-3 h-3"></i>
                                {{ $user->role->label() }}
                            </span>
                            <span class="font-mono text-gray-500 bg-gray-100 px-2 py-1 rounded text-xs border border-gray-200">
                                {{ $user->staff_code }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="p-8">
                    <!-- Standard Fields -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                        <div class="space-y-6">
                             <div class="flex items-center gap-2 mb-4 pb-2 border-b border-gray-100">
                                <i data-lucide="user" class="w-5 h-5 text-blue-500"></i>
                                <h3 class="font-bold text-gray-700">Basic Information</h3>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Full Name</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                    class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all outline-none text-gray-800 placeholder-gray-400">
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Phone Number</label>
                                <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" required
                                    inputmode="numeric" pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '').substring(0, 10)" title="Enter 10 digit phone number" maxlength="10" minlength="10" placeholder="e.g. 9876543210"
                                    class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all outline-none text-gray-800 placeholder-gray-400">
                            </div>
                             <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Staff Code (Login ID)</label>
                                <input type="text" name="staff_code" value="{{ old('staff_code', $user->staff_code) }}" required maxlength="6"
                                    inputmode="numeric" pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '')" title="Enter 6 digit staff code"
                                    class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all outline-none font-mono tracking-widest text-center text-lg">
                                 <p class="text-xs text-blue-500 mt-2 flex items-center gap-1 font-medium">
                                    <i data-lucide="info" class="w-3 h-3"></i> Required for Tablet Login
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Monthly Salary (₹)</label>
                                <input type="number" name="monthly_salary" value="{{ old('monthly_salary', $user->monthly_salary) }}" min="0" step="0.01"
                                    class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all outline-none text-gray-800">
                            </div>
                            <div class="flex items-center gap-3">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="variable_enabled" value="1" {{ old('variable_enabled', $user->variable_enabled) ? 'checked' : '' }}
                                        class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                    <span class="text-sm font-bold text-gray-700">Variable Pay Enabled</span>
                                </label>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Max Variable Amount (₹)</label>
                                <input type="number" name="max_variable_amount" value="{{ old('max_variable_amount', $user->max_variable_amount) }}" min="0" step="0.01"
                                    class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all outline-none text-gray-800">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Monthly Off Days</label>
                                <input type="number" name="weekly_off_day" value="{{ old('weekly_off_day', $user->weekly_off_day) }}" 
                                    min="0" required
                                    class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all outline-none text-gray-800">
                                <p class="text-xs text-gray-500 mt-1">Total off days to deduct per month (e.g. 4)</p>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div class="flex items-center gap-2 mb-4 pb-2 border-b border-gray-100">
                                <i data-lucide="lock" class="w-5 h-5 text-orange-500"></i>
                                <h3 class="font-bold text-gray-700">Security & Password</h3>
                            </div>

                            <div class="bg-orange-50/50 p-6 rounded-xl border border-orange-100">
                                <label class="block text-sm font-bold text-gray-700 mb-2">New Password</label>
                                <div class="relative mb-4">
                                    <input type="password" name="password" id="password" placeholder="Leave blank to keep current"
                                        class="w-full px-4 py-3 pr-12 rounded-xl bg-white border border-gray-200 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 transition-all outline-none">
                                    <button type="button" onclick="togglePassword('password', 'eye-icon-password')" class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500 hover:text-orange-500 cursor-pointer">
                                        <i data-lucide="eye" id="eye-icon-password" class="w-5 h-5"></i>
                                    </button>
                                </div>
                                
                                <label class="block text-sm font-bold text-gray-700 mb-2">Confirm Password</label>
                                <div class="relative">
                                    <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Confirm new password"
                                        class="w-full px-4 py-3 pr-12 rounded-xl bg-white border border-gray-200 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 transition-all outline-none">
                                    <button type="button" onclick="togglePassword('password_confirmation', 'eye-icon-confirm')" class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500 hover:text-orange-500 cursor-pointer">
                                        <i data-lucide="eye" id="eye-icon-confirm" class="w-5 h-5"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Onboarding / Employee Profile Section -->
                    <div class="border-t border-gray-100 pt-8 mt-8">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <i data-lucide="file-text" class="w-5 h-5 text-green-600"></i>
                            Onboarding / Employee Profile
                        </h3>
                        <p class="text-sm text-gray-500 mb-6">Data filled by staff via onboarding link. You can view and edit here.</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Current Address</label>
                                <textarea name="address" rows="3" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all outline-none text-gray-800 placeholder-gray-400">{{ old('address', $user->employeeProfile?->address) }}</textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Secondary Phone</label>
                                <input type="tel" name="secondary_phone" value="{{ old('secondary_phone', $user->employeeProfile?->secondary_phone) }}"
                                    inputmode="numeric" pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '').substring(0, 10)" title="Enter 10 digits" maxlength="10" minlength="10" placeholder="e.g. 9876500000"
                                    class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all outline-none text-gray-800 placeholder-gray-400">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Joining Date</label>
                                <input type="date" name="joining_date" value="{{ old('joining_date', $user->employeeProfile?->joining_date ? \Carbon\Carbon::parse($user->employeeProfile->joining_date)->format('Y-m-d') : '') }}"
                                    class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all outline-none text-gray-800">
                            </div>
                            <div class="md:col-span-2 flex items-center gap-2 mt-2 mb-2">
                                <i data-lucide="phone-call" class="w-4 h-4 text-red-500"></i>
                                <span class="font-bold text-gray-700">Emergency Contact</span>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Emergency Contact Name</label>
                                <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $user->employeeProfile?->emergency_contact_name) }}"
                                    class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all outline-none text-gray-800 placeholder-gray-400">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Emergency Contact Phone</label>
                                <input type="tel" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $user->employeeProfile?->emergency_contact_phone) }}"
                                    inputmode="numeric" pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '').substring(0, 10)" title="Enter 10 digits" maxlength="10" minlength="10" placeholder="e.g. 9876512345"
                                    class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all outline-none text-gray-800 placeholder-gray-400">
                            </div>
                            <div class="md:col-span-2 flex items-center gap-2 mt-4 mb-2">
                                <i data-lucide="credit-card" class="w-4 h-4 text-green-500"></i>
                                <span class="font-bold text-gray-700">Bank Account Details</span>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Bank Name</label>
                                <input type="text" name="bank_name" value="{{ old('bank_name', $user->employeeProfile?->bank_name) }}"
                                    class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all outline-none text-gray-800 placeholder-gray-400">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Account Number</label>
                                <input type="text" name="account_number" value="{{ old('account_number', $user->employeeProfile?->account_number) }}"
                                    inputmode="numeric" pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '')" title="Numbers only" maxlength="50" placeholder="e.g. 123456789012"
                                    class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all outline-none text-gray-800 font-mono placeholder-gray-400">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">IFSC Code</label>
                                <input type="text" name="ifsc_code" value="{{ old('ifsc_code', $user->employeeProfile?->ifsc_code) }}"
                                    class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all outline-none text-gray-800 font-mono placeholder-gray-400">
                            </div>
                        </div>
                    </div>

                    <!-- Role & Permissions Section -->
                    <div class="border-t border-gray-100 pt-8">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <i data-lucide="briefcase" class="w-5 h-5 text-indigo-600"></i>
                            Role & Access
                        </h3>

                        @if(isset($roles) && $roles->isNotEmpty())
                        <div class="mb-6">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Job Role</label>
                            <select name="job_role_id" id="job_role_id" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none">
                                <option value="">— No role —</option>
                                @foreach($roles as $r)
                                    <option value="{{ $r->id }}" {{ old('job_role_id', $user->job_role_id) == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Role permissions are applied automatically. You can add more below.</p>
                        </div>
                        @endif

                        <label class="block text-sm font-bold text-gray-700 mb-3">Access Permissions</label>
                        <div class="bg-gray-50 p-6 rounded-2xl border border-gray-200">
                            @if($permissions->count() > 0)
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($permissions as $permission)
                                        <label class="flex items-center space-x-3 p-4 bg-white rounded-xl border border-gray-200 hover:border-blue-400 hover:shadow-md transition-all cursor-pointer group">
                                            <div class="relative flex items-center">
                                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" 
                                                    {{ $user->permissions->contains($permission->id) ? 'checked' : '' }}
                                                    class="peer w-5 h-5 text-blue-600 rounded focus:ring-blue-500 border-gray-300 transition-colors">
                                            </div>
                                            <span class="text-sm font-semibold text-gray-700 peer-checked:text-blue-700 transition-colors">{{ $permission->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-gray-500 text-sm italic">No custom permissions defined yet.</div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="bg-gray-50 px-8 py-6 border-t border-gray-100 flex justify-end items-center gap-4">
                    <a href="{{ route('admin.staff.index') }}" class="px-6 py-3 rounded-xl border border-gray-300 text-gray-700 font-bold hover:bg-gray-100 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-8 py-3 rounded-xl bg-blue-600 text-white font-bold shadow-lg hover:bg-blue-700 hover:shadow-blue-500/30 transition-all flex items-center gap-2">
                        <i data-lucide="save" class="w-5 h-5"></i> Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        lucide.createIcons();
        function previewImage(input) {
             const preview = document.getElementById('photo_preview');
             const placeholder = document.getElementById('photo_placeholder');
             
             if (input.files && input.files[0]) {
                 const reader = new FileReader();
                 
                 reader.onload = function(e) {
                     preview.src = e.target.result;
                     preview.classList.remove('hidden');
                     if(placeholder) placeholder.classList.add('hidden');
                 }
                 
                 reader.readAsDataURL(input.files[0]);
             }
        }

        // Toggle Password Visibility
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.setAttribute('data-lucide', 'eye-off');
            } else {
                input.type = 'password';
                icon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons();
        }
    </script>
@endsection