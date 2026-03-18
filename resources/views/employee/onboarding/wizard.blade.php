<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Onboarding | Mischief Food Pvt Ltd</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style type="text/tailwindcss">
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700&display=swap');
        
        :root {
            --brand-red: #E11D48;
            --brand-dark: #0F172A;
        }

        body { 
            font-family: 'Inter', sans-serif;
            background-color: #F8FAFC;
            -webkit-tap-highlight-color: transparent;
        }

        h1, h2, h3, h4, .font-outfit { 
            font-family: 'Outfit', sans-serif; 
        }

        .step { 
            display: none; 
            animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }
        
        .step.active { display: block; }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .glass {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }

        .form-input { 
            @apply w-full px-4 md:px-5 py-3 md:py-3.5 rounded-xl md:rounded-2xl bg-white border border-slate-200 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 transition-all outline-none text-slate-800 shadow-sm text-sm md:text-base;
        }

        .table-input {
            @apply w-full px-3 py-2.5 rounded-xl bg-white border border-slate-100 focus:border-rose-400 focus:ring-4 focus:ring-rose-500/10 transition-all outline-none text-sm text-slate-900 placeholder:text-slate-400;
        }

        .btn-fancy {
            @apply relative overflow-hidden px-6 md:px-8 py-3.5 md:py-4 rounded-xl md:rounded-2xl font-bold transition-all active:scale-95 flex items-center justify-center gap-2 text-sm md:text-base;
        }

        .btn-primary {
            @apply bg-rose-600 text-white shadow-xl shadow-rose-500/20 hover:bg-rose-700 hover:shadow-rose-500/30;
        }

        .btn-secondary {
            @apply bg-slate-100 text-slate-600 hover:bg-slate-200;
        }

        .stepper-item {
            @apply flex flex-col items-center gap-1.5 relative z-10 opacity-30 transition-all duration-500;
        }

        .stepper-item.completed { @apply opacity-100; }
        .stepper-item.active { @apply opacity-100 scale-105 md:scale-110 font-bold; }

        .stepper-circle {
            @apply w-8 h-8 md:w-10 md:h-10 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center transition-all duration-500;
        }

        .stepper-item.active .stepper-circle {
            @apply bg-rose-600 text-white shadow-lg shadow-rose-500/40;
        }

        .stepper-item.completed .stepper-circle {
            @apply bg-emerald-500 text-white;
        }

        .stepper-line-bg {
            @apply absolute top-4 md:top-5 left-8 right-8 h-[2px] bg-slate-100 -z-10;
        }

        .stepper-line-progress {
            @apply absolute top-4 md:top-5 left-8 h-[2px] bg-rose-600 -z-10 transition-all duration-700 ease-out;
        }

        /* Abstract Background shapes */
        .bg-shape {
            @apply absolute -z-10 blur-[100px] opacity-20 pointer-events-none;
        }
    </style>
</head>

<body class="relative overflow-x-hidden min-h-screen pb-32">
    <!-- Abstract background elements -->
    <div class="bg-shape w-[500px] h-[500px] bg-rose-400 -top-40 -left-40 rounded-full animate-pulse"></div>
    <div class="bg-shape w-[400px] h-[400px] bg-blue-400 bottom-0 -right-20 rounded-full" style="animation-delay: 2s;"></div>

    <!-- Top Navigation / Progress Header -->
    <header class="glass sticky top-0 z-50 py-3 md:py-4 mb-4 md:mb-8 shadow-sm">
        <div class="max-w-6xl mx-auto px-4 md:px-6 flex flex-col md:flex-row items-center justify-between gap-4 md:gap-6">
            <div class="flex items-center gap-3 md:gap-4 w-full md:w-auto">
                <div class="w-10 h-10 md:w-12 md:h-12 bg-rose-600 rounded-xl md:rounded-2xl flex items-center justify-center text-white rotate-3 shadow-xl shadow-rose-500/30 shrink-0">
                    <i data-lucide="utensils" class="w-6 h-6 md:w-7 md:h-7"></i>
                </div>
                <div>
                    <h1 class="text-lg md:text-xl font-black text-slate-900 leading-none tracking-tight uppercase">{{ $user->kitchen ? $user->kitchen->name : 'Mischief Food' }}</h1>
                    <p class="text-[9px] md:text-[10px] text-rose-500 font-black tracking-[0.2em] mt-1 md:mt-1.5 uppercase opacity-80">Onboarding portal</p>
                </div>
            </div>

            <!-- Modern Stepper -->
            <div class="flex flex-col flex-1 max-w-2xl w-full gap-4">
                <div class="flex items-center justify-between px-4 relative">
                    <!-- Background Line -->
                    <div class="stepper-line-bg"></div>
                    <!-- Progress Line Overlay -->
                    <div class="stepper-line-progress" id="stepper_progress_line" style="width: 0%;"></div>

                    @foreach([
                        ['icon' => 'user', 'name' => 'Personal'],
                        ['icon' => 'id-card', 'name' => 'Docs'],
                        ['icon' => 'graduation-cap', 'name' => 'History'],
                        ['icon' => 'landmark', 'name' => 'Bank'],
                        ['icon' => 'shield-half', 'name' => 'Nominee'],
                        ['icon' => 'shirt', 'name' => 'Uniform'],
                        ['icon' => 'file-check', 'name' => 'Policy'],
                        ['icon' => 'stethoscope', 'name' => 'Health'],
                        ['icon' => 'pen-tool', 'name' => 'Final']
                    ] as $index => $step)
                        <div class="stepper-item {{ $index === 0 ? 'active' : '' }}" id="stepper_{{ $index + 1 }}">
                            <div class="stepper-circle">
                                <i data-lucide="{{ $step['icon'] }}" class="w-4 h-4 md:w-5 md:h-5"></i>
                            </div>
                            <span class="text-[10px] hidden sm:block md:hidden lg:block uppercase tracking-wider font-bold">{{ $step['name'] }}</span>
                        </div>
                    @endforeach
                </div>
                <!-- Header Progress Bar -->
                <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                    <div id="header_progress" class="h-full bg-rose-600 transition-all duration-1000 ease-out" style="width: 11%;"></div>
                </div>
            </div>
        </div>
    </header>


    <main class="max-w-4xl mx-auto px-6">
        <form action="{{ route('onboarding.submit', ['token' => $token]) }}" method="POST" id="onboarding_form">
            @csrf

            @if($errors->any())
                @php
                    // Map fields to wizard steps so we only show errors for the active step
                    $fieldStepMapBanner = [
                        'asset_acknowledged' => 6,
                        'police_verification_consent' => 7,
                        'leave_policy_acknowledged' => 7,
                        'grievance_acknowledged' => 8,
                        'probation_terms_acknowledged' => 8,
                        'digital_signature' => 9,
                        'declaration_accepted' => 9,
                    ];
                    $errorStepBanner = null;
                    foreach ($fieldStepMapBanner as $field => $stepIndex) {
                        if ($errors->has($field)) {
                            $errorStepBanner = $errorStepBanner ? min($errorStepBanner, $stepIndex) : $stepIndex;
                        }
                    }
                    $stepFields = [];
                    if ($errorStepBanner) {
                        foreach ($fieldStepMapBanner as $field => $stepIndex) {
                            if ($stepIndex === $errorStepBanner) {
                                $stepFields[] = $field;
                            }
                        }
                    }
                    $stepErrors = [];
                    if (!empty($stepFields)) {
                        foreach ($errors->getMessages() as $field => $messages) {
                            if (in_array($field, $stepFields, true)) {
                                foreach ($messages as $msg) {
                                    $stepErrors[] = $msg;
                                }
                            }
                        }
                    } else {
                        // Fallback: show all if we couldn't map to a specific step
                        $stepErrors = $errors->all();
                    }
                @endphp
                <div id="validation_error_banner" class="mb-6 bg-red-50 border border-red-200 rounded-3xl p-6">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center text-red-600 shrink-0">
                            <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="font-black text-red-900 mb-2">Please fix the following errors:</p>
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($stepErrors as $error)
                                    <li class="text-sm text-red-700 font-medium">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <!-- STEP 1: PERSONAL INFORMATION -->
            <div class="step active" id="step_1">
                <div class="bg-white/70 backdrop-blur-xl rounded-3xl md:rounded-[40px] shadow-2xl shadow-slate-200/50 border border-white p-5 md:p-12">
                    <div class="mb-6 md:mb-10 text-center md:text-left">
                        <span class="inline-block px-4 py-1 rounded-full bg-rose-50 text-rose-600 text-xs font-black uppercase tracking-widest mb-3 md:mb-4">Section A</span>
                        <h2 class="text-2xl md:text-4xl font-black text-slate-900 mb-2 md:mb-3 tracking-tight">Personal Information</h2>
                        <p class="text-slate-500 text-sm md:text-lg">Let's start with the basics. Please answer as per your Aadhaar details.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-8">
                        <div class="space-y-4 md:col-span-2">
                             <div class="flex flex-col md:flex-row items-center gap-6 p-6 bg-slate-50 rounded-[32px] border border-slate-100">
                                <div class="relative group">
                                    <div id="photo_preview" class="w-24 h-24 md:w-32 md:h-32 rounded-[32px] bg-white border-2 border-dashed border-slate-200 flex items-center justify-center overflow-hidden transition-all group-hover:border-rose-300">
                                        <i data-lucide="user" class="w-10 h-10 text-slate-300"></i>
                                    </div>
                                    <label class="absolute -bottom-2 -right-2 w-10 h-10 bg-rose-600 rounded-xl flex items-center justify-center text-white cursor-pointer shadow-lg shadow-rose-500/30 hover:bg-rose-700 transition-all">
                                        <i data-lucide="camera" class="w-5 h-5"></i>
                                        <input type="file" name="profile_photo" class="hidden" accept="image/*" onchange="previewImage(this, 'photo_preview')">
                                    </label>
                                </div>
                                <div class="text-center md:text-left">
                                    <h3 class="font-black text-slate-900">Profile Photo</h3>
                                    <p class="text-xs text-slate-500 mt-1">Please upload a clear, professional photo of yourself.</p>
                                </div>
                             </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 ml-1">Full Name (as per Aadhaar)</label>
                            <input type="text" name="full_name_aadhaar" value="{{ old('full_name_aadhaar', $user->name) }}" required class="form-input" placeholder="Enter full name">
                            @error('full_name_aadhaar')
                                <span class="text-xs text-rose-600 font-bold ml-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 ml-1">Date of Birth</label>
                            <input type="date" name="dob" value="{{ old('dob') }}" required class="form-input">
                            @error('dob')
                                <span class="text-xs text-rose-600 font-bold ml-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 ml-1">Gender</label>
                            <select name="gender" required class="form-input appearance-none">
                                <option value="">Identify Gender</option>
                                <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('gender')
                                <span class="text-xs text-rose-600 font-bold ml-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 ml-1">Blood Group</label>
                            <select name="blood_group" required class="form-input">
                                <option value="">Select Group</option>
                                @foreach(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'] as $bg)
                                    <option value="{{ $bg }}" {{ old('blood_group') == $bg ? 'selected' : '' }}>{{ $bg }}</option>
                                @endforeach
                            </select>
                            @error('blood_group')
                                <span class="text-xs text-rose-600 font-bold ml-1">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 ml-1">Marital Status</label>
                            <select name="marital_status" required class="form-input">
                                <option value="">Current Status</option>
                                <option value="Single" {{ old('marital_status') == 'Single' ? 'selected' : '' }}>Single</option>
                                <option value="Married" {{ old('marital_status') == 'Married' ? 'selected' : '' }}>Married</option>
                                <option value="Widowed" {{ old('marital_status') == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                                <option value="Divorced" {{ old('marital_status') == 'Divorced' ? 'selected' : '' }}>Divorced</option>
                            </select>
                            @error('marital_status')
                                <span class="text-xs text-rose-600 font-bold ml-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 ml-1">Email Address</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="form-input" placeholder="Enter personal email">
                            @error('email')
                                <span class="text-xs text-rose-600 font-bold ml-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 ml-1">Father's Name</label>
                            <input type="text" name="father_spouse_name" value="{{ old('father_spouse_name') }}" required class="form-input" placeholder="Father's full name">
                            @error('father_spouse_name')
                                <span class="text-xs text-rose-600 font-bold ml-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 ml-1">Mother's Name</label>
                            <input type="text" name="mother_name" value="{{ old('mother_name') }}" required class="form-input" placeholder="Mother's full name">
                            @error('mother_name')
                                <span class="text-xs text-rose-600 font-bold ml-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8 mt-2 md:mt-4 border-t border-slate-50 pt-6 md:pt-8">
                            <div class="space-y-2">
                                <label class="text-sm font-bold text-slate-700 ml-1">Permanent Address</label>
                                <textarea name="permanent_address" required rows="3" class="form-input resize-none" placeholder="H.No, Street, Landmark, City, State, PIN">{{ old('permanent_address') }}</textarea>
                                @error('permanent_address')
                                    <span class="text-xs text-rose-600 font-bold ml-1">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-bold text-slate-700 ml-1">Current Address</label>
                                <textarea name="address" required rows="3" class="form-input resize-none" placeholder="Same as permanent or different?">{{ old('address') }}</textarea>
                                @error('address')
                                    <span class="text-xs text-rose-600 font-bold ml-1">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-sm font-bold text-slate-700 ml-1">Alternate Phone (Optional)</label>
                            <input type="tel" name="secondary_phone" maxlength="10" class="form-input" placeholder="Emergency backup number">
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 2: DOCUMENTS -->
            <div class="step" id="step_2">
                <div class="bg-white/70 backdrop-blur-xl rounded-3xl md:rounded-[40px] shadow-2xl shadow-slate-200/50 border border-white p-5 md:p-12">
                    <div class="mb-6 md:mb-10">
                        <span class="inline-block px-4 py-1 rounded-full bg-rose-50 text-rose-600 text-xs font-black uppercase tracking-widest mb-3 md:mb-4">Section B</span>
                        <h2 class="text-2xl md:text-4xl font-black text-slate-900 mb-2 md:mb-3 tracking-tight">Identity Verification</h2>
                        <p class="text-slate-500 text-sm md:text-lg">Security is our priority. Please provide your official document handles.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-8">
                        <div class="p-5 md:p-6 bg-slate-50 rounded-[28px] md:rounded-[32px] border border-slate-100 space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-slate-400 shadow-sm">
                                    <i data-lucide="fingerprint" class="w-5 h-5"></i>
                                </div>
                                <label class="cursor-pointer bg-white px-3 py-1.5 rounded-lg text-[10px] font-black uppercase text-rose-600 border border-slate-100 shadow-sm hover:bg-rose-50 transition-all">
                                    Upload Card
                                    <input type="file" name="aadhaar_card_file" class="hidden" accept="image/*,application/pdf">
                                </label>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-black text-slate-900 uppercase tracking-tighter">Aadhaar Card (12 Digits)</label>
                                <input type="text" name="aadhaar_number" value="{{ old('aadhaar_number') }}" required maxlength="12" pattern="\d{12}" class="form-input font-bold tracking-[0.2em] md:tracking-[0.3em] text-center" placeholder="0000 0000 0000">
                                @error('aadhaar_number')
                                    <span class="text-xs text-rose-600 font-bold ml-1">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="p-5 md:p-6 bg-slate-50 rounded-[28px] md:rounded-[32px] border border-slate-100 space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-slate-400 shadow-sm">
                                    <i data-lucide="file-text" class="w-5 h-5"></i>
                                </div>
                                <label class="cursor-pointer bg-white px-3 py-1.5 rounded-lg text-[10px] font-black uppercase text-rose-600 border border-slate-100 shadow-sm hover:bg-rose-50 transition-all">
                                    Upload Card
                                    <input type="file" name="pan_card_file" class="hidden" accept="image/*,application/pdf">
                                </label>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-black text-slate-900 uppercase tracking-tighter">PAN Card (10 Chars)</label>
                                <input type="text" name="pan_number" value="{{ old('pan_number') }}" required maxlength="10" class="form-input font-bold tracking-[0.2em] md:tracking-[0.3em] text-center uppercase" placeholder="ABCDE1234F">
                                @error('pan_number')
                                    <span class="text-xs text-rose-600 font-bold ml-1">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6 pt-4">
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-400 uppercase tracking-widest ml-1">Driver's License</label>
                                <input type="text" name="dl_number" class="form-input text-sm" placeholder="Optional">
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-400 uppercase tracking-widest ml-1">Voter ID</label>
                                <input type="text" name="voter_id" class="form-input text-sm" placeholder="Optional">
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-400 uppercase tracking-widest ml-1">Passport</label>
                                <input type="text" name="passport_number" class="form-input text-sm" placeholder="Optional">
                            </div>
                        </div>

                        <div class="md:col-span-2 mt-6 md:mt-8 pt-6 md:pt-8 border-t border-slate-100">
                            <label class="block text-lg md:text-xl font-black text-slate-900 mb-4 md:mb-6">Physical Documents checklist</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
                                @foreach(['Aadhaar Card Copy', 'PAN Card Copy', 'Passport Size Photos (2)', 'Educational Certificates', 'Experience Certificates', 'Address Proof'] as $doc)
                                    <label class="group flex items-center gap-3 md:gap-4 p-4 md:p-5 bg-white rounded-2xl border border-slate-100 cursor-pointer hover:border-rose-300 hover:shadow-md transition-all">
                                        <div class="relative flex items-center justify-center">
                                            <input type="checkbox" name="submitted_documents[]" value="{{ $doc }}" class="peer w-6 h-6 rounded-lg text-rose-600 focus:ring-rose-500 border-slate-200 transition-all">
                                            <i data-lucide="check" class="w-4 h-4 text-white absolute opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none"></i>
                                        </div>
                                        <span class="text-[13px] md:text-sm font-bold text-slate-600 group-hover:text-rose-600 transition-colors">{{ $doc }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- STEP 3: PROFESSIONAL JOURNEY -->
            <div class="step" id="step_3">
                <div class="bg-white/70 backdrop-blur-xl rounded-2xl md:rounded-[40px] shadow-2xl shadow-slate-200/50 border border-white p-4 md:p-12">
                    <div class="mb-6 md:mb-10">
                        <span class="inline-block px-4 py-1 rounded-full bg-rose-50 text-rose-600 text-xs font-black uppercase tracking-widest mb-3 md:mb-4">Section D & E</span>
                        <h2 class="text-2xl md:text-4xl font-black text-slate-900 mb-2 md:mb-3 tracking-tight">Professional Journey</h2>
                        <p class="text-slate-500 text-sm md:text-lg">Showcase your skills, education, and where you've cooked before.</p>
                    </div>

                    <div class="space-y-8 md:space-y-12">
                        <!-- Education -->
                        <div class="p-5 md:p-8 bg-slate-900 rounded-3xl md:rounded-[32px] text-white shadow-2xl">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6 md:mb-8">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center">
                                        <i data-lucide="graduation-cap" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg md:text-xl font-black tracking-tight">Educational Milestone</h3>
                                        <p class="text-[10px] text-white/40 uppercase tracking-widest font-bold">Academic background</p>
                                    </div>
                                </div>
                                <button type="button" onclick="addRow('edu_table')" class="w-full sm:w-auto bg-rose-600 hover:bg-rose-700 px-5 py-3 rounded-2xl text-xs font-black transition-all flex items-center justify-center gap-2 shadow-lg shadow-rose-600/20">
                                    <i data-lucide="plus" class="w-4 h-4"></i> Add Qualification
                                </button>
                            </div>

                            <!-- Desktop Header -->
                            <div class="hidden md:grid grid-cols-4 gap-4 px-4 mb-2 text-[10px] font-black uppercase tracking-widest text-white/80">
                                <div>Degree / Standard</div>
                                <div>Institution</div>
                                <div>Passing Year</div>
                                <div>Grade / %</div>
                            </div>

                            <div class="space-y-4" id="edu_container">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 pb-4">
                                <div class="space-y-1">
                                        <label class="text-[10px] font-black uppercase tracking-widest text-white/60 block ml-1">Degree / Standard</label>
                                        <input type="text" name="educational_qualifications[0][name]" placeholder="e.g. B.Tech / 12th" required class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 outline-none transition-all placeholder:text-white/20">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[10px] font-black uppercase tracking-widest text-white/60 block ml-1">Institution</label>
                                        <input type="text" name="educational_qualifications[0][inst]" placeholder="School/College" required class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 outline-none transition-all placeholder:text-white/20">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[10px] font-black uppercase tracking-widest text-white/60 block ml-1">Passing Year</label>
                                        <input type="text" name="educational_qualifications[0][year]" placeholder="Year" required class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 outline-none transition-all placeholder:text-white/20">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[10px] font-black uppercase tracking-widest text-white/60 block ml-1">Grade / %</label>
                                        <input type="text" name="educational_qualifications[0][grade]" placeholder="GPA / %" required class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 outline-none transition-all placeholder:text-white/20">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Work Experience -->
                        <div class="p-5 md:p-8 bg-white border border-slate-100 rounded-3xl md:rounded-[32px] shadow-sm mb-8">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6 md:mb-8">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 bg-slate-50 text-slate-800 rounded-xl flex items-center justify-center">
                                        <i data-lucide="briefcase" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg md:text-xl font-black text-slate-900 tracking-tight">Past Experience</h3>
                                        <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold">Work history</p>
                                    </div>
                                </div>
                                <button type="button" onclick="addRow('exp_table')" class="w-full sm:w-auto bg-slate-900 hover:bg-slate-800 text-white px-5 py-3 rounded-2xl text-xs font-black transition-all flex items-center justify-center gap-2">
                                    <i data-lucide="plus" class="w-4 h-4"></i> Add Experience
                                </button>
                            </div>

                            <!-- Desktop Header -->
                            <div class="hidden md:grid grid-cols-4 gap-4 px-4 mb-2 text-[10px] font-black uppercase tracking-widest text-slate-600">
                                <div>Company Name</div>
                                <div>Designation</div>
                                <div>Duration</div>
                                <div>Last Salary</div>
                            </div>

                            <div class="space-y-4" id="exp_container">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 pb-4">
                                    <div class="space-y-1">
                                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 block ml-1">Company Name</label>
                                        <input type="text" name="work_experience[0][company]" placeholder="e.g. Taj Hotel" required class="form-input">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 block ml-1">Designation</label>
                                        <input type="text" name="work_experience[0][role]" placeholder="e.g. Commi 1" required class="form-input">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 block ml-1">Duration</label>
                                        <input type="text" name="work_experience[0][duration]" placeholder="Duration" required class="form-input">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 block ml-1">Last Salary</label>
                                        <input type="text" name="work_experience[0][salary]" placeholder="Last Salary" required class="form-input">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Professional References -->
                        <div class="p-5 md:p-8 bg-slate-50 border border-slate-100 rounded-3xl md:rounded-[32px] shadow-sm">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6 md:mb-8">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 bg-white text-slate-800 rounded-xl flex items-center justify-center shadow-sm">
                                        <i data-lucide="users-2" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg md:text-xl font-black text-slate-900 tracking-tight">Professional References</h3>
                                        <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold">People who can vouch for you</p>
                                    </div>
                                </div>
                                <button type="button" onclick="addRow('ref_table')" class="w-full sm:w-auto bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 px-5 py-3 rounded-2xl text-xs font-black transition-all flex items-center justify-center gap-2">
                                    <i data-lucide="plus" class="w-4 h-4"></i> Add Reference
                                </button>
                            </div>

                            <!-- Desktop Header -->
                            <div class="hidden md:grid grid-cols-3 gap-4 px-4 mb-2 text-[10px] font-black uppercase tracking-widest text-slate-400">
                                <div>Name</div>
                                <div>Relationship</div>
                                <div>Phone / Contact</div>
                            </div>

                            <div class="space-y-4" id="ref_container">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pb-4">
                                    <div class="space-y-1">
                                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 block md:hidden ml-1">Name</label>
                                        <input type="text" name="references[0][name]" placeholder="e.g. Ex-Manager Name" class="form-input">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 block md:hidden ml-1">Relationship</label>
                                        <input type="text" name="references[0][rel]" placeholder="e.g. Manager" class="form-input">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 block md:hidden ml-1">Phone / Contact</label>
                                        <input type="tel" name="references[0][phone]" placeholder="Contact Number" class="form-input">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 4: BANK & EMERGENCY -->
            <div class="step" id="step_4">
                <div class="bg-white/70 backdrop-blur-xl rounded-3xl md:rounded-[40px] shadow-2xl shadow-slate-200/50 border border-white p-5 md:p-12">
                    <div class="mb-6 md:mb-10 text-center">
                        <span class="inline-block px-4 py-1 rounded-full bg-emerald-50 text-emerald-600 text-xs font-black uppercase tracking-widest mb-3 md:mb-4">Section F & G</span>
                        <h2 class="text-2xl md:text-4xl font-black text-slate-900 mb-2 md:mb-3 tracking-tight">Bank & Emergency</h2>
                        <p class="text-slate-500 text-sm md:text-lg">Secure your payroll and let us know who to call in emergency.</p>
                    </div>

                    <div class="space-y-6 md:space-y-8">
                        <!-- Bank -->
                        <div class="p-5 md:p-8 bg-emerald-50 rounded-3xl md:rounded-[40px] border border-emerald-100 relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-100 rounded-full blur-3xl -mr-16 -mt-16"></div>
                            <h3 class="text-lg md:text-xl font-black text-emerald-900 mb-6 md:mb-8 flex items-center gap-3">
                                <i data-lucide="credit-card" class="w-6 h-6"></i> Salary Account
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-8">
                                <div class="space-y-2">
                                    <label class="text-[10px] md:text-xs font-black text-emerald-900/50 uppercase tracking-widest">Account Holder Name</label>
                                    <input type="text" name="account_holder_name" value="{{ old('account_holder_name') }}" required class="form-input border-emerald-200 focus:border-emerald-500 focus:ring-emerald-500/10">
                                    @error('account_holder_name')
                                        <span class="text-xs text-rose-600 font-bold ml-1">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] md:text-xs font-black text-emerald-900/50 uppercase tracking-widest">Bank Name</label>
                                    <input type="text" name="bank_name" value="{{ old('bank_name') }}" required class="form-input border-emerald-200 focus:border-emerald-500 focus:ring-emerald-500/10">
                                    @error('bank_name')
                                        <span class="text-xs text-rose-600 font-bold ml-1">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="md:col-span-1 space-y-2">
                                    <label class="text-[10px] md:text-xs font-black text-emerald-900/50 uppercase tracking-widest">Account Number</label>
                                    <input type="text" name="account_number" value="{{ old('account_number') }}" required class="form-input border-emerald-200 focus:border-emerald-500 focus:ring-emerald-500/10 font-mono tracking-wider">
                                    @error('account_number')
                                        <span class="text-xs text-rose-600 font-bold ml-1">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] md:text-xs font-black text-emerald-900/50 uppercase tracking-widest">IFSC Code</label>
                                    <input type="text" name="ifsc_code" value="{{ old('ifsc_code') }}" required class="form-input border-emerald-200 focus:border-emerald-500 focus:ring-emerald-500/10 font-bold uppercase">
                                    @error('ifsc_code')
                                        <span class="text-xs text-rose-600 font-bold ml-1">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="md:col-span-2">
                                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 sm:gap-8 bg-white/40 p-4 rounded-2xl border border-emerald-200">
                                        <span class="text-sm font-bold text-emerald-800">Account Type:</span>
                                        <div class="flex gap-6">
                                            <label class="flex items-center gap-2 cursor-pointer group">
                                                <input type="radio" name="account_type" value="Savings" checked class="w-5 h-5 text-emerald-600 focus:ring-emerald-500">
                                                <span class="text-sm font-black text-emerald-900 group-hover:text-emerald-600">Savings</span>
                                            </label>
                                            <label class="flex items-center gap-2 cursor-pointer group">
                                                <input type="radio" name="account_type" value="Current" class="w-5 h-5 text-emerald-600 focus:ring-emerald-500">
                                                <span class="text-sm font-black text-emerald-900 group-hover:text-emerald-600">Current</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Emergency -->
                        <div class="p-5 md:p-8 bg-rose-50 rounded-3xl md:rounded-[40px] border border-rose-100">
                            <h3 class="text-lg md:text-xl font-black text-rose-900 mb-6 md:mb-8 flex items-center gap-3">
                                <i data-lucide="phone-forwarded" class="w-6 h-6"></i> In Case of Emergency
                            </h3>
                            <div class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">
                                    <div class="space-y-2">
                                        <label class="text-[10px] md:text-xs font-black text-rose-400 uppercase tracking-widest">Primary Contact Name</label>
                                        <input type="text" name="emergency_contacts_json[0][name]" value="{{ old('emergency_contacts_json.0.name') }}" required class="form-input border-rose-200" placeholder="e.g. Spouse/Parent">
                                        @error('emergency_contacts_json.0.name')
                                            <span class="text-xs text-rose-600 font-bold ml-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] md:text-xs font-black text-rose-400 uppercase tracking-widest">Relationship</label>
                                        <input type="text" name="emergency_contacts_json[0][relation]" value="{{ old('emergency_contacts_json.0.relation') }}" required class="form-input border-rose-200">
                                        @error('emergency_contacts_json.0.relation')
                                            <span class="text-xs text-rose-600 font-bold ml-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] md:text-xs font-black text-rose-400 uppercase tracking-widest">Primary Mobile</label>
                                        <input type="tel" name="emergency_contacts_json[0][mobile]" value="{{ old('emergency_contacts_json.0.mobile') }}" required class="form-input border-rose-200">
                                        @error('emergency_contacts_json.0.mobile')
                                            <span class="text-xs text-rose-600 font-bold ml-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6 pt-6 border-t border-rose-100">
                                    <div class="space-y-2">
                                        <label class="text-[10px] md:text-xs font-bold text-slate-400 uppercase tracking-widest">Secondary Contact</label>
                                        <input type="text" name="emergency_contacts_json[1][name]" class="form-input border-slate-200" placeholder="Optional">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] md:text-xs font-bold text-slate-400 uppercase tracking-widest">Relationship</label>
                                        <input type="text" name="emergency_contacts_json[1][relation]" class="form-input border-slate-200">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] md:text-xs font-bold text-slate-400 uppercase tracking-widest">Secondary Mobile</label>
                                        <input type="tel" name="emergency_contacts_json[1][mobile]" class="form-input border-slate-200">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- STEP 5: NOMINEE -->
            <div class="step" id="step_5">
                <div class="bg-white/70 backdrop-blur-xl rounded-3xl md:rounded-[40px] shadow-2xl shadow-slate-200/50 border border-white p-5 md:p-12">
                    <div class="mb-6 md:mb-12">
                        <span class="inline-block px-4 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-black uppercase tracking-widest mb-3 md:mb-4">Section H</span>
                        <h2 class="text-2xl md:text-4xl font-black text-slate-900 mb-2 md:mb-3 tracking-tight">Legal Beneficiary</h2>
                        <p class="text-slate-500 text-sm md:text-lg">Nominate your legal beneficiary for PF, Gratuity, and Insurance benefits.</p>
                    </div>

                    <div class="flex flex-col md:flex-row gap-8 md:gap-12 items-start">
                        <div class="w-full md:w-1/2 space-y-6 md:space-y-8">
                            <div class="space-y-2">
                                <label class="text-xs md:text-sm font-black text-slate-900 uppercase tracking-tighter">Nominee Full Name</label>
                                <input type="text" name="nominee_details[name]" value="{{ old('nominee_details.name') }}" required class="form-input" placeholder="Legal Name">
                                @error('nominee_details.name')
                                    <span class="text-xs text-rose-600 font-bold ml-1">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs md:text-sm font-black text-slate-900 uppercase tracking-tighter">Relationship</label>
                                <input type="text" name="nominee_details[relation]" value="{{ old('nominee_details.relation') }}" required class="form-input" placeholder="e.g. Daughter, Husband">
                                @error('nominee_details.relation')
                                    <span class="text-xs text-rose-600 font-bold ml-1">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs md:text-sm font-black text-slate-900 uppercase tracking-tighter">Date of Birth</label>
                                <input type="date" name="nominee_details[dob]" value="{{ old('nominee_details.dob') }}" required class="form-input">
                                @error('nominee_details.dob')
                                    <span class="text-xs text-rose-600 font-bold ml-1">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="space-y-4 p-5 md:p-6 bg-slate-50 rounded-2xl md:rounded-3xl border border-slate-100">
                                <label class="text-xs md:text-sm font-black text-slate-900 uppercase tracking-tighter flex justify-between">
                                    Percentage Share
                                    <span class="text-rose-600" id="share_label">100%</span>
                                </label>
                                <input type="range" name="nominee_details[share]" value="100" min="1" max="100" class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-rose-600" oninput="document.getElementById('share_label').innerText = this.value + '%'">
                                <p class="text-[9px] md:text-[10px] text-slate-400 font-bold uppercase tracking-widest text-center">Benefit allocation for this nominee</p>
                            </div>
                        </div>

                        <div class="w-full md:w-1/2 md:sticky md:top-32">
                            <div class="p-6 md:p-10 bg-slate-900 rounded-3xl md:rounded-[48px] text-white shadow-2xl relative overflow-hidden group">
                                <div class="absolute -top-10 -right-10 w-40 h-40 bg-rose-600/20 rounded-full blur-[80px] group-hover:scale-150 transition-all duration-1000"></div>
                                <div class="relative z-10 flex flex-col gap-4 md:gap-6">
                                    <div class="w-12 h-12 md:w-14 md:h-14 bg-white/10 rounded-2xl flex items-center justify-center text-rose-500">
                                        <i data-lucide="shield-check" class="w-6 h-6 md:w-8 md:h-8"></i>
                                    </div>
                                    <h3 class="text-xl md:text-2xl font-black tracking-tight">Why we ask for this?</h3>
                                    <p class="text-slate-400 text-xs md:text-sm leading-relaxed">This data is mandatory for compliance under ESIC and EPF schemes. It ensures your hard-earned benefits reach your loved ones securely.</p>
                                    <div class="pt-4 md:pt-6 mt-4 md:mt-6 border-t border-white/10 text-[9px] md:text-[10px] font-black uppercase tracking-[0.2em] text-white/40">Legal Documentation Step 3/5</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 6: UNIFORM & SHOP -->
            <div class="step" id="step_6">
                <div class="bg-white/70 backdrop-blur-xl rounded-3xl md:rounded-[40px] shadow-2xl shadow-slate-200/50 border border-white p-5 md:p-12">
                    <div class="mb-6 md:mb-10">
                        <span class="inline-block px-4 py-1 rounded-full bg-rose-50 text-rose-600 text-xs font-black uppercase tracking-widest mb-3 md:mb-4">Section I</span>
                        <h2 class="text-2xl md:text-4xl font-black text-slate-900 mb-2 md:mb-3 tracking-tight">Dress for Success</h2>
                        <p class="text-slate-500 text-sm md:text-lg">We want you to look sharp and stay safe. Pick your sizes carefully.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8 mb-8 md:mb-12">
                        <div class="p-6 md:p-8 bg-white rounded-2xl md:rounded-[32px] border-2 border-slate-100 hover:border-rose-400 transition-all group shadow-sm text-center space-y-4">
                            <div class="w-12 h-12 md:w-16 md:h-16 bg-rose-50 rounded-full flex items-center justify-center mx-auto text-rose-600 group-hover:scale-110 transition-transform">
                                <i data-lucide="shirt" class="w-6 h-6 md:w-8 md:h-8"></i>
                            </div>
                            <select name="uniform_details[shirt]" required class="form-input text-center font-black">
                                @foreach(['S', 'M', 'L', 'XL', 'XXL', '3XL'] as $size)
                                    <option value="{{ $size }}" {{ old('uniform_details.shirt') == $size ? 'selected' : '' }}>{{ $size }}</option>
                                @endforeach
                            </select>
                            @error('uniform_details.shirt')
                                <span class="text-xs text-rose-600 font-bold">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="p-6 md:p-8 bg-white rounded-2xl md:rounded-[32px] border-2 border-slate-100 hover:border-rose-400 transition-all group shadow-sm text-center space-y-4">
                            <div class="w-12 h-12 md:w-16 md:h-16 bg-rose-50 rounded-full flex items-center justify-center mx-auto text-rose-600 group-hover:scale-110 transition-transform">
                                <i data-lucide="square" class="w-6 h-6 md:w-8 md:h-8"></i>
                            </div>
                            <h3 class="font-black text-slate-900 text-sm md:text-base">Trouser / Pants</h3>
                            <input type="text" name="uniform_details[trouser]" value="{{ old('uniform_details.trouser') }}" required class="form-input text-center font-black" placeholder="Waist (e.g. 32)">
                            @error('uniform_details.trouser')
                                <span class="text-xs text-rose-600 font-bold">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="p-6 md:p-8 bg-white rounded-2xl md:rounded-[32px] border-2 border-slate-100 hover:border-rose-400 transition-all group shadow-sm text-center space-y-4">
                            <div class="w-12 h-12 md:w-16 md:h-16 bg-rose-50 rounded-full flex items-center justify-center mx-auto text-rose-600 group-hover:scale-110 transition-transform">
                                <i data-lucide="footprints" class="w-6 h-6 md:w-8 md:h-8"></i>
                            </div>
                            <h3 class="font-black text-slate-900 text-sm md:text-base">Safety Shoes</h3>
                            <input type="number" name="uniform_details[shoes]" value="{{ old('uniform_details.shoes') }}" required min="4" max="13" class="form-input text-center font-black" placeholder="UK / IND size">
                            @error('uniform_details.shoes')
                                <span class="text-xs text-rose-600 font-bold">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="p-6 md:p-8 bg-slate-900 rounded-3xl md:rounded-[40px] text-white">
                        <h3 class="text-lg md:text-xl font-black mb-4 md:mb-6">Asset Responsibility Acknowledgment</h3>
                        <div class="bg-white/5 border border-white/10 p-5 md:p-6 rounded-2xl md:rounded-3xl mb-6 md:mb-8 space-y-3 opacity-80">
                            @foreach([
                                'All uniform and equipment remain property of Mischief Food Pvt Ltd.',
                                'I am responsible for laundry and maintenance of my uniform.',
                                'Any loss of assets (ID Card, Equipment) will be reported in 24h.',
                                'Damaged assets due to negligence may be deducted from final settlement.'
                            ] as $text)
                                <div class="flex gap-4 text-xs md:text-sm font-medium">
                                    <span class="text-rose-500 flex-shrink-0">•</span>
                                    <span>{{ $text }}</span>
                                </div>
                            @endforeach
                        </div>
                        <label class="flex items-center gap-4 cursor-pointer group p-2">
                            <div class="relative flex items-center justify-center">
                                <input type="checkbox" name="asset_acknowledged" required {{ old('asset_acknowledged') ? 'checked' : '' }} class="peer w-6 h-6 md:w-8 md:h-8 rounded-lg md:rounded-xl text-rose-600 focus:ring-offset-slate-900 border-white/20 bg-white/10 transition-all">
                                <i data-lucide="check" class="w-4 h-4 md:w-5 md:h-5 text-white absolute opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none"></i>
                            </div>
                            <span class="text-base md:text-lg font-black text-white/50 group-hover:text-white transition-colors">I accept the asset policy</span>
                        </label>
                        @error('asset_acknowledged')
                            <span class="text-xs text-rose-400 font-bold ml-12 block mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- STEP 7: POLICY -->
            <div class="step" id="step_7">
                <div class="bg-white/70 backdrop-blur-xl rounded-2xl md:rounded-[40px] shadow-2xl shadow-slate-200/50 border border-white p-5 md:p-12">
                    <div class="mb-6 md:mb-10 text-center md:text-left">
                         <span class="inline-block px-4 py-1 rounded-full bg-rose-50 text-rose-600 text-xs font-black uppercase tracking-widest mb-3 md:mb-4">Section J</span>
                        <h2 class="text-2xl md:text-4xl font-black text-slate-900 mb-2 md:mb-3 tracking-tight">Rules of Engagement</h2>
                        <p class="text-slate-500 text-sm md:text-lg">Our culture is built on trust and excellence. Please review our core policies.</p>
                    </div>

                    <div class="space-y-4 md:space-y-6">
                        @foreach([
                            ['icon' => 'clock', 'title' => 'Punctuality', 'desc' => 'Reporting time is 15 mins before shift. Group attendance is mandatory.'],
                            ['icon' => 'ban', 'title' => 'Zero Tolerance', 'desc' => 'Strict no-alcohol/tobacco policy on premises and during transit.'],
                            ['icon' => 'smartphone', 'title' => 'Digital Detox', 'desc' => 'Mobile phones strictly in lockers during operational hours.'],
                            ['icon' => 'user-check', 'title' => 'Grooming', 'desc' => 'Clean shaved, trimmed nails, and neat uniform is mandatory daily.'],
                            ['icon' => 'help-circle', 'title' => 'Grievance Redressal', 'desc' => 'Report any issues directly to HR or Manager via formal grievance system.']
                        ] as $policy)
                            <div class="group flex items-start gap-4 md:gap-6 p-5 md:p-6 bg-white rounded-2xl md:rounded-3xl border border-slate-100 hover:border-rose-200 hover:shadow-xl hover:shadow-rose-500/5 transition-all">
                                <div class="w-10 h-10 md:w-12 md:h-12 bg-slate-50 rounded-xl md:rounded-2xl flex items-center justify-center text-slate-400 group-hover:bg-rose-600 group-hover:text-white transition-all shrink-0">
                                    <i data-lucide="{{ $policy['icon'] }}" class="w-5 h-5 md:w-6 md:h-6"></i>
                                </div>
                                <div class="space-y-1">
                                    <h3 class="font-black text-slate-900 text-sm md:text-base">{{ $policy['title'] }}</h3>
                                    <p class="text-slate-500 text-xs md:text-sm leading-relaxed">{{ $policy['desc'] }}</p>
                                </div>
                            </div>
                        @endforeach

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                            <label class="flex flex-col gap-2 p-5 bg-slate-900 text-white rounded-[24px] cursor-pointer hover:bg-slate-800 transition-all">
                                <div class="flex items-center gap-4">
                                    <div class="relative flex items-center justify-center">
                                        <input type="checkbox" name="police_verification_consent" required {{ old('police_verification_consent') ? 'checked' : '' }} class="peer w-6 h-6 rounded-lg text-rose-600 focus:ring-rose-500 border-white/20 bg-white/10">
                                        <i data-lucide="check" class="w-4 h-4 text-white absolute opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none"></i>
                                    </div>
                                    <span class="text-[13px] font-black leading-tight">Police Verification</span>
                                </div>
                                @error('police_verification_consent')
                                    <span class="text-[10px] text-rose-400 font-bold ml-10">{{ $message }}</span>
                                @enderror
                            </label>
                            
                            <label class="flex flex-col gap-2 p-5 bg-rose-600 text-white rounded-[24px] cursor-pointer hover:bg-rose-500 transition-all">
                                <div class="flex items-center gap-4">
                                    <div class="relative flex items-center justify-center">
                                        <input type="checkbox" name="leave_policy_acknowledged" required {{ old('leave_policy_acknowledged') ? 'checked' : '' }} class="peer w-6 h-6 rounded-lg text-white/40 focus:ring-white border-white/20 bg-white/10">
                                        <i data-lucide="check" class="w-4 h-4 text-white absolute opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none"></i>
                                    </div>
                                    <span class="text-[13px] font-black leading-tight">Leave Policy</span>
                                </div>
                                @error('leave_policy_acknowledged')
                                    <span class="text-[10px] text-white/70 font-bold ml-10">{{ $message }}</span>
                                @enderror
                            </label>

                            <label class="flex flex-col gap-2 p-5 bg-blue-600 text-white rounded-[24px] cursor-pointer hover:bg-blue-500 transition-all">
                                <div class="flex items-center gap-4">
                                    <div class="relative flex items-center justify-center">
                                        <input type="checkbox" name="grievance_acknowledged" required {{ old('grievance_acknowledged') ? 'checked' : '' }} class="peer w-6 h-6 rounded-lg text-white/40 focus:ring-white border-white/20 bg-white/10">
                                        <i data-lucide="check" class="w-4 h-4 text-white absolute opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none"></i>
                                    </div>
                                    <span class="text-[13px] font-black leading-tight">Grievance Support</span>
                                </div>
                                @error('grievance_acknowledged')
                                    <span class="text-[10px] text-white/70 font-bold ml-10">{{ $message }}</span>
                                @enderror
                            </label>
                        </div>
                    </div>
                </div>
            </div>



            <!-- STEP 8: HEALTH REGISTRY -->
            <div class="step" id="step_8">
                <div class="bg-white/70 backdrop-blur-xl rounded-2xl md:rounded-[40px] shadow-2xl shadow-slate-200/50 border border-white p-5 md:p-12">
                    <div class="mb-6 md:mb-12 flex flex-col md:flex-row md:items-end justify-between gap-6">
                        <div>
                            <span class="inline-block px-4 py-1 rounded-full bg-blue-50 text-blue-600 text-xs font-black uppercase tracking-widest mb-3 md:mb-4">Section K</span>
                            <h2 class="text-2xl md:text-4xl font-black text-slate-900 mb-2 md:mb-3 tracking-tight">Health Registry</h2>
                            <p class="text-slate-500 text-sm md:text-lg">Your wellbeing is vital for food safety and your own health.</p>
                        </div>
                        <div class="px-6 py-3 bg-blue-600 rounded-2xl text-white text-center">
                            <span class="text-[10px] font-black uppercase tracking-widest block opacity-70">Blood Group</span>
                            <span class="text-xl md:text-2xl font-black" id="display_blood_group">Select in Step 1</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-10">
                        <div class="space-y-6 md:space-y-8">
                            <div class="space-y-3">
                                <label class="text-sm font-black text-slate-900 uppercase tracking-tighter ml-1">Existing Medical Conditions?</label>
                                <textarea name="medical_conditions" rows="3" class="form-input resize-none" placeholder="e.g. Asthma, Diabetes (Optional)"></textarea>
                            </div>
                            <div class="space-y-3">
                                <label class="text-sm font-black text-slate-900 uppercase tracking-tighter ml-1">Known Allergies</label>
                                <textarea name="allergies" rows="3" class="form-input resize-none" placeholder="e.g. Peanuts, Latex (Optional)"></textarea>
                            </div>
                        </div>

                        <div class="bg-slate-50 p-6 md:p-8 rounded-3xl md:rounded-[40px] border border-slate-100 space-y-6">
                            <div class="space-y-4">
                                <h3 class="font-black text-slate-900 text-sm md:text-base uppercase tracking-tighter">Food Safety Declaration</h3>
                                <div class="space-y-3">
                                    @foreach([
                                        'I do not have any contagious skin diseases.',
                                        'I am not suffering from any respiratory illness.',
                                        'I will report any illness to manager immediately.'
                                    ] as $label)
                                        <label class="flex items-center gap-3 cursor-pointer group">
                                            <input type="checkbox" name="health_declaration[]" value="{{ $label }}" class="w-5 h-5 rounded text-blue-600 border-slate-300 focus:ring-blue-500">
                                            <span class="text-[13px] md:text-sm font-bold text-slate-600 group-hover:text-blue-600 transition-colors">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] md:text-xs font-black text-slate-400 uppercase tracking-widest ml-1">Last Checkup Date</label>
                                <input type="date" name="last_checkup_date" class="form-input">
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-8">
                         <label class="flex flex-col gap-2 p-5 bg-slate-900 text-white rounded-[24px] cursor-pointer hover:bg-slate-800 transition-all">
                            <div class="flex items-center gap-4">
                                <div class="relative flex items-center justify-center shrink-0">
                                    <input type="checkbox" name="probation_terms_acknowledged" required {{ old('probation_terms_acknowledged') ? 'checked' : '' }} class="peer w-6 h-6 rounded-lg text-rose-600 focus:ring-rose-500 border-white/20 bg-white/10">
                                    <i data-lucide="check" class="w-4 h-4 text-white absolute opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none"></i>
                                </div>
                                <span class="text-xs font-black leading-tight uppercase tracking-tighter">Accept Standard 6-Month Probation Terms</span>
                            </div>
                            @error('probation_terms_acknowledged')
                                <span class="text-[10px] text-rose-400 font-bold ml-10">{{ $message }}</span>
                            @enderror
                        </label>
                    </div>
                </div>
            </div>



            <!-- STEP 9: FINAL REVIEW & SIGN -->
            <div class="step" id="step_9">
                <div class="bg-white/70 backdrop-blur-xl rounded-2xl md:rounded-[40px] shadow-2xl shadow-slate-200/50 border border-white p-5 md:p-12 text-center">
                    <div class="w-16 h-16 md:w-20 md:h-20 bg-emerald-100 text-emerald-600 rounded-[32px] flex items-center justify-center mx-auto mb-6 md:mb-10 animate-bounce">
                        <i data-lucide="check-circle" class="w-8 h-8 md:w-10 md:h-10"></i>
                    </div>
                    <h2 class="text-3xl md:text-5xl font-black text-slate-900 mb-4 md:mb-6 tracking-tight leading-none">Ready to start?</h2>
                    <p class="text-slate-500 text-sm md:text-lg mb-8 md:mb-12 max-w-lg mx-auto">By submitting this form, you declare all information provided is true to the best of your knowledge.</p>

                    <div class="max-w-xl mx-auto space-y-8 md:space-y-10">
                        <div class="text-left space-y-4">
                            <label class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Employee's Digital Signature</label>
                            <div class="group relative">
                                <div class="absolute -inset-1 bg-gradient-to-r from-rose-600 to-blue-600 rounded-[24px] blur opacity-25 group-focus-within:opacity-50 transition duration-1000"></div>
                                <input type="text" name="digital_signature" value="{{ old('digital_signature') }}" required placeholder="Type full name to sign" class="relative w-full px-6 py-5 md:px-8 md:py-6 rounded-[24px] bg-white border border-slate-100 focus:border-rose-500 focus:ring-0 transition-all outline-none text-xl md:text-2xl font-black italic text-slate-800 tracking-wider text-center">
                            </div>
                            @error('digital_signature')
                                <span class="text-xs text-rose-600 font-bold block mt-2">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="flex items-start gap-4 cursor-pointer text-left p-5 md:p-6 rounded-2xl md:rounded-3xl border border-rose-100 bg-rose-50/30">
                                <div class="relative flex items-center justify-center shrink-0 mt-1">
                                    <input type="checkbox" name="declaration_accepted" required {{ old('declaration_accepted') ? 'checked' : '' }} class="peer w-6 h-6 md:w-8 md:h-8 rounded-lg md:rounded-xl text-rose-600 focus:ring-rose-500 border-rose-200 transition-all">
                                    <i data-lucide="check" class="w-4 h-4 md:w-5 md:h-5 text-white absolute opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none"></i>
                                </div>
                                <span class="text-[13px] md:text-sm font-bold text-slate-600 leading-snug">I declare that all facts are correct. Concealment may lead to immediate termination of service.</span>
                            </label>
                            @error('declaration_accepted')
                                <span class="text-xs text-rose-600 font-bold text-left ml-14">{{ $message }}</span>
                            @enderror
                        </div>

                        <button type="button" id="submit_btn" onclick="submitForm()" class="btn-fancy btn-primary w-full py-5 md:py-6 text-xl md:text-2xl flex items-center justify-center group shadow-2xl shadow-rose-500/30">
                            <span id="submit_btn_text">SUBMIT JOINING FORM</span>
                            <i data-lucide="arrow-right" class="w-6 h-6 md:w-8 md:h-8 group-hover:translate-x-2 transition-transform" id="submit_arrow"></i>
                            <svg id="submit_spinner" class="hidden animate-spin w-6 h-6 ml-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>


            <!-- Global Footer Navigation -->
            <div class="max-w-4xl mx-auto px-6 fixed bottom-8 left-0 right-0 z-40">
                <div class="glass max-w-4xl mx-auto px-6 py-4 rounded-3xl shadow-2xl flex items-center justify-between border border-white/50">
                    <button type="button" id="prev_btn" onclick="moveStep(-1)" class="btn-fancy btn-secondary px-10 disabled:opacity-30">
                        <i data-lucide="chevron-left" class="w-5 h-5"></i> Back
                    </button>
                    
                    <div class="flex flex-col items-center">
                        <span class="text-[10px] uppercase font-black tracking-widest text-slate-400">Section <span id="current_section_label">1</span> of 9</span>
                        <div class="w-24 h-1 bg-slate-100 rounded-full mt-1.5 overflow-hidden">
                            <div id="footer_progress" class="h-full bg-rose-600 transition-all duration-700" style="width: 11%;"></div>
                        </div>
                    </div>

                    <button type="button" id="next_btn" onclick="moveStep(1)" class="btn-fancy btn-primary px-10 group">
                        Next Step <i data-lucide="chevron-right" class="w-5 h-5 group-hover:translate-x-1 transition-transform"></i>
                    </button>
                </div>
            </div>
        </form>
    </main>

    @php
        $errorStep = null;
        $fieldStepMap = [
            // Step 1: Personal
            'full_name_aadhaar' => 1, 'dob' => 1, 'gender' => 1, 'marital_status' => 1, 'blood_group' => 1,
            'father_spouse_name' => 1, 'mother_name' => 1, 'permanent_address' => 1, 'address' => 1,
            // Step 2: Docs
            'aadhaar_number' => 2, 'pan_number' => 2,
            // Step 4: Bank & Emergency
            'bank_name' => 4, 'account_holder_name' => 4, 'account_number' => 4, 'ifsc_code' => 4, 'account_type' => 4,
            'emergency_contacts_json.0.name' => 4, 'emergency_contacts_json.0.relation' => 4, 'emergency_contacts_json.0.mobile' => 4,
            // Step 5: Nominee
            'nominee_details.name' => 5, 'nominee_details.relation' => 5, 'nominee_details.dob' => 5,
            // Step 6: Uniform
            'uniform_details.shirt' => 6, 'uniform_details.trouser' => 6, 'uniform_details.shoes' => 6,
            'asset_acknowledged' => 6,
            // Step 7: Policy
            'police_verification_consent' => 7,
            'leave_policy_acknowledged' => 7,
            // Step 8: Health & Probation
            'grievance_acknowledged' => 8,
            'probation_terms_acknowledged' => 8,
            // Step 9: Final
            'digital_signature' => 9,
            'declaration_accepted' => 9,
        ];
        foreach ($fieldStepMap as $field => $stepIndex) {
            if ($errors->has($field)) {
                $errorStep = $errorStep ? min($errorStep, $stepIndex) : $stepIndex;
            }
        }
    @endphp

        <script>
        function previewImage(input, previewId) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById(previewId);
                    preview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        lucide.createIcons();
        let currentStep = {{ $errorStep ?? 1 }};
        const totalSteps = 9;

        function moveStep(delta) {
            if (currentStep + delta < 1 || currentStep + delta > totalSteps) return;
            if (delta > 0 && !validateStep(currentStep)) return;

            document.getElementById(`step_${currentStep}`).classList.remove('active');
            document.getElementById(`stepper_${currentStep}`).classList.remove('active');
            if (delta > 0) document.getElementById(`stepper_${currentStep}`).classList.add('completed');
            
            currentStep += delta;
            
            document.getElementById(`step_${currentStep}`).classList.add('active');
            document.getElementById(`stepper_${currentStep}`).classList.add('active');
            document.getElementById(`stepper_${currentStep}`).classList.remove('completed');

            window.scrollTo({ top: 0, behavior: 'smooth' });
            updateUI();
        }

        function validateStep(step) {
            const currentStepEl = document.getElementById(`step_${step}`);
            const inputs = currentStepEl.querySelectorAll('[required]');
            let isValid = true;

            inputs.forEach(input => {
                let currentInputValid = true;
                if (input.type === 'checkbox') {
                    currentInputValid = input.checked;
                } else if (input.type === 'radio') {
                    const radios = currentStepEl.querySelectorAll(`input[name="${input.name}"]`);
                    currentInputValid = Array.from(radios).some(r => r.checked);
                } else {
                    currentInputValid = input.value.trim() !== '';
                }

                // Remove any existing manual error messages
                const existingError = input.parentElement.querySelector('.js-error-msg');
                if (existingError) existingError.remove();

                if (!currentInputValid) {
                    isValid = false;
                    input.classList.add('border-rose-400', 'ring-4', 'ring-rose-400/10');
                    
                    // Add error message text
                    const errorMsg = document.createElement('span');
                    errorMsg.className = 'text-xs text-rose-600 font-bold ml-1 block mt-1 js-error-msg';
                    errorMsg.textContent = 'This field is required';
                    input.parentElement.appendChild(errorMsg);

                    setTimeout(() => input.classList.remove('ring-4', 'ring-rose-400/10'), 3000);
                } else {
                    input.classList.remove('border-rose-400');
                }
            });

            if (!isValid) {
                const firstError = currentStepEl.querySelector('.border-rose-400');
                if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            return isValid;
        }

        function updateUI() {
            const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
            
            if (document.getElementById('header_progress')) {
                document.getElementById('header_progress').style.width = `${(currentStep / totalSteps) * 100}%`;
            }
            if (document.getElementById('stepper_progress_line')) {
                document.getElementById('stepper_progress_line').style.width = `${progress}%`;
            }
            if (document.getElementById('footer_progress')) {
                document.getElementById('footer_progress').style.width = `${(currentStep / totalSteps) * 100}%`;
            }
            if (document.getElementById('current_section_label')) {
                document.getElementById('current_section_label').textContent = currentStep;
            }
            
            document.getElementById('prev_btn').disabled = currentStep === 1;
            
            const nextBtn = document.getElementById('next_btn');
            if (currentStep === totalSteps) {
                nextBtn.classList.add('invisible');
            } else {
                nextBtn.classList.remove('invisible');
            }
        }

        function submitForm() {
            const sig = document.querySelector('input[name="digital_signature"]');
            const decl = document.querySelector('input[name="declaration_accepted"]');
            
            // Validate only final step fields
            if (!sig || !sig.value.trim()) {
                sig.classList.add('border-rose-500', 'ring-4', 'ring-rose-400/20');
                sig.focus();
                sig.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
            if (!decl || !decl.checked) {
                decl.parentElement.parentElement.classList.add('ring-2', 'ring-rose-400');
                decl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
            
            // Show loading
            document.getElementById('submit_btn').disabled = true;
            document.getElementById('submit_btn_text').textContent = 'Submitting...';
            document.getElementById('submit_arrow').classList.add('hidden');
            document.getElementById('submit_spinner').classList.remove('hidden');
            
            // Submit the form
            document.getElementById('onboarding_form').submit();
        }

        function addRow(tableId) {
            const container = tableId === 'edu_table'
                ? document.getElementById('edu_container')
                : (tableId === 'exp_table' ? document.getElementById('exp_container') : document.getElementById('ref_container'));

            if (!container) return;

            const rowCount = container.children.length;
            const newRow = document.createElement('div');
            newRow.className = "grid grid-cols-1 md:grid-cols-4 gap-4 pb-4 animate-in fade-in slide-in-from-left-4 relative group border-t border-white/5 pt-4 md:border-0 md:pt-0";
            
            if (tableId === 'edu_table') {
                newRow.innerHTML = `
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase tracking-widest text-white/40 block md:hidden ml-1">Degree / Standard</label>
                        <input type="text" name="educational_qualifications[${rowCount}][name]" placeholder="e.g. B.Tech / 12th" required class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 outline-none transition-all placeholder:text-white/20">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase tracking-widest text-white/40 block md:hidden ml-1">Institution</label>
                        <input type="text" name="educational_qualifications[${rowCount}][inst]" placeholder="School/College" required class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 outline-none transition-all placeholder:text-white/20">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase tracking-widest text-white/40 block md:hidden ml-1">Passing Year</label>
                        <input type="text" name="educational_qualifications[${rowCount}][year]" placeholder="Year" required class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 outline-none transition-all placeholder:text-white/20">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase tracking-widest text-white/40 block md:hidden ml-1">Grade / %</label>
                        <div class="flex gap-2">
                            <input type="text" name="educational_qualifications[${rowCount}][grade]" placeholder="GPA / %" required class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 outline-none transition-all placeholder:text-white/20">
                            <button type="button" onclick="this.closest('div.grid').remove()" class="text-rose-500 hover:text-rose-400 shrink-0"><i data-lucide="x-circle" class="w-6 h-6"></i></button>
                        </div>
                    </div>
                `;
            } else if (tableId === 'exp_table') {
                newRow.innerHTML = `
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 block md:hidden ml-1">Company Name</label>
                        <input type="text" name="work_experience[${rowCount}][company]" placeholder="e.g. Taj Hotel" required class="form-input">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 block md:hidden ml-1">Designation</label>
                        <input type="text" name="work_experience[${rowCount}][role]" placeholder="e.g. Commi 1" required class="form-input">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 block md:hidden ml-1">Duration</label>
                        <input type="text" name="work_experience[${rowCount}][duration]" placeholder="Duration" required class="form-input">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 block md:hidden ml-1">Last Salary</label>
                        <div class="flex gap-2">
                            <input type="text" name="work_experience[${rowCount}][salary]" placeholder="Last Salary" required class="form-input">
                            <button type="button" onclick="this.closest('div.grid').remove()" class="text-rose-500 hover:text-rose-600 shrink-0"><i data-lucide="x-circle" class="w-6 h-6"></i></button>
                        </div>
                    </div>
                `;
            } else {
                newRow.className = "grid grid-cols-1 md:grid-cols-3 gap-4 pb-4 animate-in fade-in slide-in-from-left-4 relative group border-t border-slate-100 pt-4 md:border-0 md:pt-0";
                newRow.innerHTML = `
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 block ml-1">Name</label>
                        <input type="text" name="references[${rowCount}][name]" placeholder="Name" class="form-input">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 block ml-1">Relationship</label>
                        <input type="text" name="references[${rowCount}][rel]" placeholder="Relation" class="form-input">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 block ml-1">Phone</label>
                        <div class="flex gap-2">
                            <input type="tel" name="references[${rowCount}][phone]" placeholder="Phone" class="form-input">
                            <button type="button" onclick="this.closest('div.grid').remove()" class="text-rose-500 hover:text-rose-600 shrink-0"><i data-lucide="x-circle" class="w-6 h-6"></i></button>
                        </div>
                    </div>
                `;
            }

            container.appendChild(newRow);
            lucide.createIcons();
        }



        document.addEventListener('DOMContentLoaded', function () {
            // If server-side validation errors came from a later step,
            // jump directly to that step instead of always starting at step 1.
            if (currentStep !== 1) {
                for (let i = 1; i <= totalSteps; i++) {
                    const stepEl = document.getElementById(`step_${i}`);
                    const stepperEl = document.getElementById(`stepper_${i}`);
                    if (!stepEl || !stepperEl) continue;

                    if (i === currentStep) {
                        stepEl.classList.add('active');
                        stepperEl.classList.add('active');
                        stepperEl.classList.remove('completed');
                    } else {
                        stepEl.classList.remove('active');
                        stepperEl.classList.remove('active');
                        if (i < currentStep) {
                            stepperEl.classList.add('completed');
                        } else {
                            stepperEl.classList.remove('completed');
                        }
                    }
                }
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            updateUI();
        });
    </script>
</body>
</html>