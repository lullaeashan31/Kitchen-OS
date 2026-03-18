@extends('layouts.app')

@section('header')
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.staff.index') }}" class="p-2 bg-white rounded-xl border border-slate-200 text-slate-500 hover:text-blue-600 hover:border-blue-300 transition-all shadow-sm group">
            <i data-lucide="arrow-left" class="w-5 h-5 group-hover:-translate-x-1 transition-transform"></i>
        </a>
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Edit Staff Profile</h1>
            <p class="text-sm font-medium text-slate-500">Managing <span class="text-blue-600 font-bold uppercase tracking-wider">{{ $user->name }}</span> ({{ $user->staff_code }})</p>
        </div>
    </div>
@endsection

@section('content')
<div class="max-w-6xl mx-auto space-y-6 pb-20">
    <form action="{{ route('admin.staff.update', $user) }}" method="POST" enctype="multipart/form-data" id="staffEditForm">
        @csrf
        @method('PUT')

        <!-- Profile Header Card (Glassmorphism) -->
        <div class="bg-white/80 backdrop-blur-xl rounded-[32px] shadow-xl shadow-slate-200/50 border border-white p-6 md:p-10 mb-8">
            <div class="flex flex-col md:flex-row items-center gap-8">
                <div class="relative group">
                    <div class="w-32 h-32 md:w-40 md:h-40 rounded-[40px] overflow-hidden border-4 border-white shadow-2xl bg-slate-100 ring-8 ring-blue-50/50">
                        @if($user->profile_photo_path)
                            <img id="photo_preview" src="{{ $user->profile_photo_url }}" class="w-full h-full object-cover">
                        @else
                            <div id="photo_placeholder" class="w-full h-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-5xl font-black">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <img id="photo_preview" class="hidden w-full h-full object-cover">
                        @endif
                    </div>
                    <label for="profile_photo" class="absolute -bottom-2 -right-2 bg-blue-600 text-white p-3 rounded-2xl cursor-pointer hover:bg-blue-700 shadow-xl transition-all transform hover:scale-110 border-4 border-white active:scale-95 group-hover:rotate-6">
                        <i data-lucide="camera" class="w-6 h-6"></i>
                    </label>
                    <input type="file" name="profile_photo" id="profile_photo" class="hidden" accept="image/*" onchange="previewImage(this)">
                </div>

                <div class="flex-1 text-center md:text-left space-y-3">
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-3">
                        <h2 class="text-3xl md:text-4xl font-black text-slate-900 tracking-tight">{{ $user->name }}</h2>
                        <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-[11px] font-black uppercase tracking-widest bg-blue-100 text-blue-700">
                            <i data-lucide="badge-check" class="w-3 h-3"></i>
                            {{ $user->role->label() }}
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-6 text-slate-500 font-bold text-sm">
                        <div class="flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-100">
                            <i data-lucide="hash" class="w-4 h-4 text-slate-400"></i>
                            <span class="font-mono tracking-wider">{{ $user->staff_code }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i data-lucide="phone" class="w-4 h-4 text-slate-400"></i>
                            {{ $user->phone }}
                        </div>
                        <div class="flex items-center gap-2">
                            <i data-lucide="calendar" class="w-4 h-4 text-slate-400"></i>
                            Joined {{ $user->created_at->format('M Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabbed Navigation -->
        <div class="bg-slate-100/50 p-1.5 rounded-[24px] inline-flex items-center gap-1 mb-8 w-full md:w-auto">
            <button type="button" onclick="switchTab('basic')" class="tab-btn active px-6 py-3 rounded-[20px] text-sm font-black uppercase tracking-widest transition-all" data-tab="basic">
                Account Settings
            </button>
            <button type="button" onclick="switchTab('onboarding')" class="tab-btn px-6 py-3 rounded-[20px] text-sm font-black uppercase tracking-widest transition-all text-slate-500 hover:text-slate-900" data-tab="onboarding">
                Onboarding Data
            </button>
            <button type="button" onclick="switchTab('access')" class="tab-btn px-6 py-3 rounded-[20px] text-sm font-black uppercase tracking-widest transition-all text-slate-500 hover:text-slate-900" data-tab="access">
                Roles & Access
            </button>
        </div>

        <!-- Tab Contents -->
        <div id="basic" class="tab-content">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Primary Stats/Quick Fields -->
                <div class="lg:col-span-2 space-y-8">
                    <div class="bg-white rounded-[40px] shadow-sm border border-slate-100 p-8 md:p-10">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-10 h-10 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600">
                                <i data-lucide="user" class="w-5 h-5"></i>
                            </div>
                            <h3 class="text-xl font-black text-slate-900 tracking-tight">Core Information</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Display Name</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                    class="w-full px-5 py-4 rounded-2xl bg-slate-50 border-2 border-slate-100 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/5 transition-all outline-none font-bold text-slate-800">
                            </div>

                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Phone Number</label>
                                <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" required
                                    inputmode="numeric" pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '').substring(0, 10)" maxlength="10"
                                    class="w-full px-5 py-4 rounded-2xl bg-slate-50 border-2 border-slate-100 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/5 transition-all outline-none font-bold text-slate-800">
                            </div>

                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">System Staff Code</label>
                                <input type="text" name="staff_code" value="{{ old('staff_code', $user->staff_code) }}" required maxlength="6"
                                    class="w-full px-5 py-4 rounded-2xl bg-slate-50 border-2 border-slate-100 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/5 transition-all outline-none font-mono text-center text-xl font-black tracking-widest text-blue-700">
                                <p class="text-[10px] font-bold text-blue-500/70 text-center uppercase tracking-wider">Used for biometric & tablet logins</p>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Monthly Off Days</label>
                                <div class="relative">
                                    <input type="number" name="weekly_off_day" value="{{ old('weekly_off_day', $user->weekly_off_day) }}" min="0" required
                                        class="w-full px-5 py-4 rounded-2xl bg-slate-50 border-2 border-slate-100 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/5 transition-all outline-none font-bold text-slate-800">
                                    <span class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs uppercase">Days</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Salary Information -->
                    <div class="bg-white rounded-[40px] shadow-sm border border-slate-100 p-8 md:p-10">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-10 h-10 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                                <i data-lucide="banknote" class="w-5 h-5"></i>
                            </div>
                            <h3 class="text-xl font-black text-slate-900 tracking-tight">Payroll Settings</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Base Monthly Salary (₹)</label>
                                <input type="number" name="monthly_salary" value="{{ old('monthly_salary', $user->monthly_salary) }}" min="0" step="0.01"
                                    class="w-full px-5 py-4 rounded-2xl bg-slate-50 border-2 border-slate-100 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/5 transition-all outline-none font-black text-lg text-slate-800">
                            </div>

                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Max Variable Incentive (₹)</label>
                                <input type="number" name="max_variable_amount" value="{{ old('max_variable_amount', $user->max_variable_amount) }}" min="0" step="0.01"
                                    class="w-full px-5 py-4 rounded-2xl bg-slate-50 border-2 border-slate-100 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/5 transition-all outline-none font-black text-lg text-slate-800">
                            </div>

                            <div class="md:col-span-2">
                                <label class="flex items-center gap-4 p-5 rounded-[24px] bg-slate-50 border-2 border-slate-100 cursor-pointer group hover:bg-slate-100 hover:border-slate-200 transition-all">
                                    <div class="relative flex items-center">
                                        <input type="checkbox" name="variable_enabled" value="1" {{ old('variable_enabled', $user->variable_enabled) ? 'checked' : '' }}
                                            class="peer w-6 h-6 rounded-lg border-2 border-slate-300 text-emerald-500 focus:ring-0 focus:ring-offset-0 transition-all checked:border-emerald-500">
                                        <i data-lucide="check" class="absolute inset-0 m-auto w-4 h-4 text-white opacity-0 peer-checked:opacity-100 pointer-events-none transition-opacity"></i>
                                    </div>
                                    <div class="flex-1">
                                        <span class="block text-sm font-black text-slate-800 uppercase tracking-tight">Enable Productivity Bonus</span>
                                        <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest leading-none mt-1">Allows managers to add variable pay during payroll cycles</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Security Sidebar -->
                <div class="space-y-8">
                    <div class="bg-slate-900 rounded-[40px] shadow-2xl p-8 md:p-10 text-white relative overflow-hidden group">
                        <div class="absolute -top-10 -right-10 w-40 h-40 bg-orange-500/10 rounded-full blur-[80px] group-hover:scale-150 transition-all duration-1000"></div>
                        
                        <div class="relative z-10 space-y-8">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-white/10 flex items-center justify-center text-orange-400">
                                    <i data-lucide="shield-lock" class="w-5 h-5"></i>
                                </div>
                                <h3 class="text-xl font-black tracking-tight">Access Control</h3>
                            </div>

                            <div class="space-y-6">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">New System Password</label>
                                    <div class="relative">
                                        <input type="password" name="password" id="password" placeholder="••••••••"
                                            class="w-full px-5 py-4 rounded-2xl bg-white/5 border-2 border-white/10 focus:border-orange-500 focus:bg-white/10 transition-all outline-none font-bold text-white placeholder-white/20">
                                        <button type="button" onclick="togglePassword('password', 'eye-basic')" class="absolute right-5 top-1/2 -translate-y-1/2 text-white/30 hover:text-orange-400 transition-colors">
                                            <i data-lucide="eye" id="eye-basic" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Confirm Identity</label>
                                    <input type="password" name="password_confirmation" placeholder="••••••••"
                                        class="w-full px-5 py-4 rounded-2xl bg-white/5 border-2 border-white/10 focus:border-orange-500 focus:bg-white/10 transition-all outline-none font-bold text-white placeholder-white/20">
                                </div>
                                
                                <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest text-center leading-relaxed">
                                    Leave blank to keep current credentials. Passwords are encrypted.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-blue-600 rounded-[40px] shadow-xl p-8 text-white">
                        <h4 class="font-black text-lg mb-4 flex items-center gap-2">
                            <i data-lucide="smartphone" class="w-5 h-5"></i>
                            Tablet Mode Tips
                        </h4>
                        <ul class="space-y-3 text-xs font-bold text-blue-100">
                            <li class="flex items-start gap-3">
                                <span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-[10px] shrink-0 italic">01</span>
                                Staff use their 6-digit code for quick clock-in/out.
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-[10px] shrink-0 italic">02</span>
                                Face recognition can be enabled in settings.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- ONBOARDING TAB -->
        <div id="onboarding" class="tab-content hidden">
            <div class="space-y-8">
                <!-- Data Filled By Staff Section -->
                <div class="bg-white rounded-[40px] shadow-sm border border-slate-100 p-8 md:p-10">
                    <div class="flex items-center justify-between mb-10">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                                <i data-lucide="clipboard-check" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <h3 class="text-2xl font-black text-slate-900 tracking-tight">Staff Submission Profile</h3>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Editable historical data</p>
                            </div>
                        </div>
                        <div class="hidden md:block">
                            <span class="px-5 py-2 rounded-2xl bg-slate-100 text-slate-600 text-[10px] font-black uppercase tracking-widest">
                                Status: {{ ucfirst($user->onboarding_status ?? 'N/A') }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="md:col-span-3 space-y-2">
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">Contact Address</label>
                            <textarea name="address" rows="3" 
                                class="w-full px-5 py-4 rounded-3xl bg-slate-50 border-2 border-slate-100 focus:border-indigo-500 focus:bg-white transition-all outline-none font-bold text-slate-800">{{ old('address', $user->employeeProfile?->address) }}</textarea>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">Secondary Phone</label>
                            <input type="tel" name="secondary_phone" value="{{ old('secondary_phone', $user->employeeProfile?->secondary_phone) }}" placeholder="Optional"
                                class="w-full px-5 py-4 rounded-2xl bg-slate-50 border-2 border-slate-100 focus:border-indigo-500 focus:bg-white transition-all outline-none font-bold text-slate-800">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">Expected Joining</label>
                            <input type="date" name="joining_date" value="{{ old('joining_date', $user->employeeProfile?->joining_date ? \Carbon\Carbon::parse($user->employeeProfile->joining_date)->format('Y-m-d') : '') }}"
                                class="w-full px-5 py-4 rounded-2xl bg-slate-50 border-2 border-slate-100 focus:border-indigo-500 focus:bg-white transition-all outline-none font-bold text-slate-800">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">Bank (Search Helper)</label>
                            <input type="text" name="bank_name" value="{{ old('bank_name', $user->employeeProfile?->bank_name) }}"
                                class="w-full px-5 py-4 rounded-2xl bg-slate-50 border-2 border-slate-100 focus:border-indigo-500 focus:bg-white transition-all outline-none font-bold text-slate-800 uppercase tracking-tighter">
                        </div>
                        
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">Account Number</label>
                            <input type="text" name="account_number" value="{{ old('account_number', $user->employeeProfile?->account_number) }}"
                                class="w-full px-5 py-4 rounded-2xl bg-slate-50 border-2 border-slate-100 focus:border-indigo-500 focus:bg-white transition-all outline-none font-mono font-bold text-slate-800 tracking-wider">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">IFSC Code</label>
                            <input type="text" name="ifsc_code" value="{{ old('ifsc_code', $user->employeeProfile?->ifsc_code) }}"
                                class="w-full px-5 py-4 rounded-2xl bg-slate-50 border-2 border-slate-100 focus:border-indigo-500 focus:bg-white transition-all outline-none font-mono font-bold text-slate-800 uppercase">
                        </div>
                    </div>
                </div>

                @if($user->employeeProfile)
                <!-- Detailed JSON Views (Read Only / Pretty Cards) -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Advanced Identity -->
                    <div class="bg-white rounded-[40px] shadow-sm border border-slate-100 p-8 flex flex-col">
                        <h4 class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] mb-6 flex items-center gap-2">
                            <i data-lucide="id-card" class="w-4 h-4 text-purple-500"></i> Identity Stack
                        </h4>
                        
                        <div class="space-y-4 flex-1">
                            @php
                                $idData = [
                                    ['label' => 'Aadhaar Card', 'val' => $user->employeeProfile->aadhaar_number, 'mono' => true],
                                    ['label' => 'PAN Card', 'val' => $user->employeeProfile->pan_number, 'mono' => true],
                                    ['label' => 'License No.', 'val' => $user->employeeProfile->dl_number, 'mono' => false],
                                    ['label' => 'Voter ID', 'val' => $user->employeeProfile->voter_id, 'mono' => false],
                                ];
                            @endphp

                            <div class="grid grid-cols-2 gap-4">
                                @foreach($idData as $id)
                                    @if($id['val'])
                                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                            <span class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ $id['label'] }}</span>
                                            <span class="block text-sm font-bold text-slate-700 @if($id['mono']) font-mono tracking-tight @endif">{{ $id['val'] }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                            @if($user->employeeProfile->submitted_documents)
                                <div class="mt-4">
                                    <span class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3">Submitted Documents Library</span>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach((array) $user->employeeProfile->submitted_documents as $doc)
                                            <span class="px-3 py-1 bg-purple-50 text-purple-700 rounded-full text-[10px] font-black uppercase tracking-wider border border-purple-100">
                                                {{ $doc }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Medical & Physical -->
                    <div class="bg-white rounded-[40px] shadow-sm border border-slate-100 p-8 relative overflow-hidden group">
                        <div class="absolute top-0 right-0 p-8">
                            <i data-lucide="activity" class="w-12 h-12 text-rose-500/10 group-hover:scale-125 transition-transform duration-500"></i>
                        </div>
                        <h4 class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] mb-6 flex items-center gap-2">
                            <i data-lucide="heart" class="w-4 h-4 text-rose-500"></i> Health & Uniform
                        </h4>

                        <div class="space-y-6">
                            @if($user->employeeProfile->blood_group)
                                <div class="inline-flex items-center gap-3 px-4 py-2 bg-rose-50 border border-rose-100 rounded-2xl">
                                    <span class="text-[10px] font-black text-rose-400 uppercase tracking-widest leading-none">Blood Group</span>
                                    <span class="text-lg font-black text-rose-600 leading-none">{{ $user->employeeProfile->blood_group }}</span>
                                </div>
                            @endif

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <span class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Medical Notes</span>
                                    @if($user->employeeProfile->medical_info)
                                        @php $med = (array) $user->employeeProfile->medical_info; @endphp
                                        <div class="text-[11px] font-bold text-slate-600 space-y-1">
                                            @if(!empty($med['conditions'])) <div class="flex gap-2"><span>•</span> {{ $med['conditions'] }}</div> @endif
                                            @if(!empty($med['allergies'])) <div class="flex gap-2"><span>•</span> <span class="text-rose-500">Allergy: {{ $med['allergies'] }}</span></div> @endif
                                            @if(empty($med['conditions']) && empty($med['allergies'])) <span class="italic font-normal">No specific data provided</span> @endif
                                        </div>
                                    @else
                                        <span class="text-xs italic text-slate-400">Clear background reported</span>
                                    @endif
                                </div>
                                
                                <div class="space-y-3">
                                    <span class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Uniform Sizes</span>
                                    @if($user->employeeProfile->uniform_details)
                                        @php $uni = (array) $user->employeeProfile->uniform_details; @endphp
                                        <div class="flex flex-col gap-2">
                                            @if(!empty($uni['shirt']))
                                                <div class="flex items-center justify-between text-[11px] font-bold py-1 border-b border-slate-50 italic">
                                                    <span>Shirt / Kurta</span>
                                                    <span class="text-blue-600 uppercase">{{ $uni['shirt'] }}</span>
                                                </div>
                                            @endif
                                            @if(!empty($uni['trouser']))
                                                <div class="flex items-center justify-between text-[11px] font-bold py-1 border-b border-slate-50 italic">
                                                    <span>Trouser</span>
                                                    <span class="text-blue-600 uppercase">{{ $uni['trouser'] }}</span>
                                                </div>
                                            @endif
                                            @if(!empty($uni['shoes']))
                                                <div class="flex items-center justify-between text-[11px] font-bold py-1 border-b border-slate-50 italic">
                                                    <span>Safety Shoes</span>
                                                    <span class="text-blue-600 uppercase">{{ $uni['shoes'] }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-xs italic text-slate-400">Not assigned</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Education & Experience Full Lists -->
                <div class="bg-white rounded-[40px] shadow-sm border border-slate-100 p-8 md:p-10">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                        <!-- Education -->
                        <div>
                            <h4 class="text-lg font-black text-slate-900 tracking-tight mb-6 flex items-center gap-3">
                                <span class="w-8 h-8 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center">
                                    <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                                </span>
                                Educational Milestones
                            </h4>
                            <div class="space-y-4 relative before:absolute before:left-[15px] before:top-2 before:bottom-2 before:w-[2px] before:bg-slate-100">
                                @if($user->employeeProfile->educational_qualifications)
                                    @foreach((array) $user->employeeProfile->educational_qualifications as $edu)
                                        <div class="relative pl-10">
                                            <div class="absolute left-0 top-1.5 w-8 h-8 rounded-full bg-white border-2 border-slate-100 flex items-center justify-center z-10">
                                                <div class="w-2 h-2 rounded-full bg-orange-400"></div>
                                            </div>
                                            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                                                <span class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ $edu['year'] ?? 'N/A' }}</span>
                                                <h5 class="text-sm font-black text-slate-800 tracking-tight">{{ $edu['name'] ?? 'Qualification' }}</h5>
                                                <p class="text-[11px] font-bold text-slate-500 uppercase mt-0.5 tracking-tighter">{{ $edu['inst'] ?? '' }}</p>
                                                @if(!empty($edu['grade']))
                                                    <span class="inline-block mt-2 px-2 py-0.5 bg-white rounded-lg text-[10px] font-black text-slate-700 shadow-sm">{{ $edu['grade'] }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="pl-10 text-xs italic text-slate-400">No records available</p>
                                @endif
                            </div>
                        </div>

                        <!-- Experience -->
                        <div>
                            <h4 class="text-lg font-black text-slate-900 tracking-tight mb-6 flex items-center gap-3">
                                <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                                    <i data-lucide="briefcase" class="w-4 h-4"></i>
                                </span>
                                Professional Journey
                            </h4>
                            <div class="space-y-4 relative before:absolute before:left-[15px] before:top-2 before:bottom-2 before:w-[2px] before:bg-slate-100">
                                @if($user->employeeProfile->employment_history)
                                    @foreach((array) $user->employeeProfile->employment_history as $job)
                                        <div class="relative pl-10">
                                            <div class="absolute left-0 top-1.5 w-8 h-8 rounded-full bg-white border-2 border-slate-100 flex items-center justify-center z-10">
                                                <div class="w-2 h-2 rounded-full bg-blue-400"></div>
                                            </div>
                                            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <h5 class="text-sm font-black text-slate-800 tracking-tight">{{ $job['company'] ?? 'Previous Company' }}</h5>
                                                        <p class="text-[11px] font-black text-blue-600 uppercase mt-0.5 tracking-tighter">{{ $job['role'] ?? '' }}</p>
                                                    </div>
                                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest px-2 py-1 bg-white rounded-lg">{{ $job['duration'] ?? '' }}</span>
                                                </div>
                                                @if(!empty($job['salary']))
                                                    <p class="text-[10px] font-bold text-slate-500 mt-2 flex items-center gap-1">
                                                        <i data-lucide="info" class="w-3 h-3"></i> Last Package: {{ $job['salary'] }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="pl-10 text-xs italic text-slate-400">No work history provided</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- ACCESS TAB -->
        <div id="access" class="tab-content hidden">
            <div class="space-y-8">
                <!-- Job Role Selector -->
                <div class="bg-white rounded-[40px] shadow-sm border border-slate-100 p-8 md:p-10">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                            <i data-lucide="briefcase" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-slate-900 tracking-tight">System Roles</h3>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Define primary responsibilities</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Assigned Designation</label>
                            <select name="job_role_id" id="job_role_id" class="w-full px-5 py-4 rounded-2xl border-2 border-slate-100 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 outline-none font-bold text-slate-800 appearance-none bg-slate-50">
                                <option value="">— Unassigned —</option>
                                @foreach($roles as $r)
                                    <option value="{{ $r->id }}" {{ old('job_role_id', $user->job_role_id) == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="p-6 rounded-3xl bg-indigo-50 border border-indigo-100">
                            <h5 class="text-xs font-black text-indigo-600 uppercase tracking-widest mb-2 flex items-center gap-2">
                                <i data-lucide="sparkles" class="w-3 h-3"></i> Role Inheritance
                            </h5>
                            <p class="text-xs font-bold text-indigo-900/60 leading-relaxed italic">Permissions in the role are automatically granted. Checkboxes below are for **EXTRA** overrides only.</p>
                        </div>
                    </div>
                </div>

                <!-- Custom Permissions Grid -->
                <div class="bg-white rounded-[40px] shadow-sm border border-slate-100 p-8 md:p-10">
                    <div class="flex items-center gap-4 mb-10">
                        <div class="w-12 h-12 rounded-2xl bg-slate-900 flex items-center justify-center text-white">
                            <i data-lucide="key" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-slate-900 tracking-tight">Granular Overrides</h3>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Specific module access</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @php
                            $rolePermissions = $user->jobRole ? $user->jobRole->permissions->pluck('id')->toArray() : [];
                        @endphp
                        @foreach($permissions as $permission)
                            @php
                                $isFromRole = in_array($permission->id, $rolePermissions);
                                $isDirect = $user->permissions->contains($permission->id);
                            @endphp
                            <label class="group relative flex items-center gap-4 p-5 rounded-[28px] border-2 {{ $isFromRole ? 'border-amber-100 bg-amber-50/30' : 'border-slate-50 bg-slate-50' }} hover:bg-white hover:border-blue-200 hover:shadow-xl hover:shadow-blue-500/10 transition-all cursor-pointer">
                                <div class="relative flex items-center shrink-0">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" 
                                        {{ $isDirect || $isFromRole ? 'checked' : '' }}
                                        @if($isFromRole) onclick="return false;" @endif
                                        class="peer w-6 h-6 rounded-lg border-2 border-slate-300 text-blue-600 focus:ring-0 focus:ring-offset-0 transition-all checked:border-blue-600">
                                    <i data-lucide="check" class="absolute inset-0 m-auto w-4 h-4 text-white opacity-0 peer-checked:opacity-100 pointer-events-none transition-opacity"></i>
                                </div>
                                <div class="flex-1">
                                    <span class="block text-sm font-black {{ $isFromRole ? 'text-amber-700' : 'text-slate-700' }} uppercase tracking-tight group-hover:text-blue-700 transition-colors">{{ str_replace(['module_', 'manage_'], '', $permission->name) }}</span>
                                    @if($isFromRole)
                                        <span class="inline-flex items-center gap-1.5 mt-1 border border-amber-200 bg-amber-100 px-2 py-0.5 rounded-full">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            <span class="text-[8px] font-black text-amber-700 uppercase tracking-widest">Via {{ $user->jobRole->name }}</span>
                                        </span>
                                    @else
                                        <span class="block text-[8px] font-black text-slate-400 uppercase tracking-widest mt-0.5">{{ $permission->slug }}</span>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Global Sticky Actions -->
        <div class="fixed bottom-10 left-1/2 -translate-x-1/2 w-[90%] max-w-2xl z-50">
            <div class="bg-slate-900/95 backdrop-blur-xl p-3 rounded-[32px] border border-white/10 shadow-2xl flex items-center justify-between gap-4">
                <a href="{{ route('admin.staff.index') }}" class="px-6 py-4 rounded-2xl text-white/50 hover:text-white font-black uppercase tracking-widest text-xs transition-colors">
                    Discard changes
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 px-10 py-4 rounded-2xl text-white font-black uppercase tracking-widest text-xs shadow-xl shadow-blue-500/30 transition-all active:scale-95 flex items-center gap-3">
                    <i data-lucide="save" class="w-5 h-5"></i>
                    Update Registry
                </button>
            </div>
        </div>
    </form>
</div>

<style>
    .tab-btn.active {
        background-color: white;
        color: #0f172a;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    }
</style>

<script>
    function switchTab(tabId) {
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        
        // Show target content
        document.getElementById(tabId).classList.remove('hidden');
        
        // Update button styles
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active', 'bg-white', 'text-slate-900', 'shadow-sm');
            btn.classList.add('text-slate-500');
            
            if (btn.getAttribute('data-tab') === tabId) {
                btn.classList.add('active', 'bg-white', 'text-slate-900', 'shadow-sm');
                btn.classList.remove('text-slate-500');
            }
        });
        
        lucide.createIcons();
    }

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

    // Initialize icons
    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
    });
</script>
@endsection