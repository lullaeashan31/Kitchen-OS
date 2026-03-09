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
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .glass {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .form-input { 
            @apply w-full px-5 py-3.5 rounded-2xl bg-white border border-slate-200 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 transition-all outline-none text-slate-800 shadow-sm ;
        }

        .table-input {
            @apply w-full px-3 py-2 rounded-xl bg-white border border-slate-100 focus:border-rose-400 focus:ring-4 focus:ring-rose-500/10 transition-all outline-none text-sm;
        }

        .btn-fancy {
            @apply relative overflow-hidden px-8 py-4 rounded-2xl font-bold transition-all active:scale-95 flex items-center gap-2 ;
        }

        .btn-primary {
            @apply bg-rose-600 text-white shadow-xl shadow-rose-500/20 hover:bg-rose-700 hover:shadow-rose-500/30;
        }

        .btn-secondary {
            @apply bg-slate-100 text-slate-600 hover:bg-slate-200;
        }

        .stepper-item {
            @apply flex flex-col items-center gap-2 relative z-10 opacity-30 transition-all duration-500;
        }

        .stepper-item.completed { @apply opacity-100; }
        .stepper-item.active { @apply opacity-100 scale-110 font-bold; }

        .stepper-circle {
            @apply w-10 h-10 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center transition-all duration-500;
        }

        .stepper-item.active .stepper-circle {
            @apply bg-rose-600 text-white shadow-lg shadow-rose-500/40;
        }

        .stepper-item.completed .stepper-circle {
            @apply bg-emerald-500 text-white;
        }

        .stepper-line-bg {
            @apply absolute top-5 left-8 right-8 h-[2px] bg-slate-100 -z-10;
        }

        .stepper-line-progress {
            @apply absolute top-5 left-8 h-[2px] bg-rose-600 -z-10 transition-all duration-700 ease-out;
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
    <header class="glass sticky top-0 z-50 py-4 mb-8">
        <div class="max-w-6xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-rose-600 rounded-2xl flex items-center justify-center text-white rotate-3 shadow-xl shadow-rose-500/30">
                    <i data-lucide="utensils" class="w-7 h-7"></i>
                </div>
                <div>
                    <h1 class="text-xl font-black text-slate-900 leading-none tracking-tight uppercase">Mischief Food</h1>
                    <p class="text-[10px] text-rose-500 font-black tracking-[0.2em] mt-1.5 uppercase opacity-80">Onboarding portal</p>
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
                                <i data-lucide="{{ $step['icon'] }}" class="w-5 h-5"></i>
                            </div>
                            <span class="text-[10px] hidden lg:block uppercase tracking-wider font-bold">{{ $step['name'] }}</span>
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

            <!-- STEP 1: PERSONAL INFORMATION -->
            <div class="step active" id="step_1">
                <div class="bg-white/70 backdrop-blur-xl rounded-[40px] shadow-2xl shadow-slate-200/50 border border-white p-8 md:p-12">
                    <div class="mb-10 text-center md:text-left">
                        <span class="inline-block px-4 py-1 rounded-full bg-rose-50 text-rose-600 text-xs font-black uppercase tracking-widest mb-4">Section A</span>
                        <h2 class="text-4xl font-black text-slate-900 mb-3 tracking-tight">Personal Information</h2>
                        <p class="text-slate-500 text-lg">Let's start with the basics. Please answer as per your Aadhaar details.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 ml-1">Full Name (as per Aadhaar)</label>
                            <input type="text" name="full_name_aadhaar" value="{{ $user->name }}" required class="form-input" placeholder="Enter full name">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 ml-1">Date of Birth</label>
                            <input type="date" name="dob" required class="form-input">
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 ml-1">Gender</label>
                            <select name="gender" required class="form-input appearance-none">
                                <option value="">Identify Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 ml-1">Blood Group</label>
                            <select name="blood_group" required class="form-input">
                                <option value="">Select Group</option>
                                @foreach(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'] as $bg)
                                    <option value="{{ $bg }}">{{ $bg }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 ml-1">Marital Status</label>
                            <select name="marital_status" required class="form-input">
                                <option value="">Current Status</option>
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Widowed">Widowed</option>
                                <option value="Divorced">Divorced</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700 ml-1">Father's/Spouse's Name</label>
                            <input type="text" name="father_spouse_name" required class="form-input" placeholder="Relation Name">
                        </div>

                        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-8 mt-4 border-t border-slate-50 pt-8">
                            <div class="space-y-2">
                                <label class="text-sm font-bold text-slate-700 ml-1">Permanent Address</label>
                                <textarea name="permanent_address" required rows="4" class="form-input resize-none" placeholder="H.No, Street, Landmark, City, State, PIN"></textarea>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-bold text-slate-700 ml-1">Current Address</label>
                                <textarea name="address" required rows="4" class="form-input resize-none" placeholder="Same as permanent or different?"></textarea>
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
                <div class="bg-white/70 backdrop-blur-xl rounded-[40px] shadow-2xl shadow-slate-200/50 border border-white p-8 md:p-12">
                    <div class="mb-10">
                        <span class="inline-block px-4 py-1 rounded-full bg-rose-50 text-rose-600 text-xs font-black uppercase tracking-widest mb-4">Section B</span>
                        <h2 class="text-4xl font-black text-slate-900 mb-3 tracking-tight">Identity Verification</h2>
                        <p class="text-slate-500 text-lg">Security is our priority. Please provide your official document handles.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="p-6 bg-slate-50 rounded-[32px] border border-slate-100 space-y-4">
                            <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-slate-400 shadow-sm">
                                <i data-lucide="fingerprint" class="w-5 h-5"></i>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-black text-slate-900 uppercase tracking-tighter">Aadhaar Card (12 Digits)</label>
                                <input type="text" name="aadhaar_number" required maxlength="12" pattern="\d{12}" class="form-input font-bold tracking-[0.3em] text-center" placeholder="0000 0000 0000">
                            </div>
                        </div>

                        <div class="p-6 bg-slate-50 rounded-[32px] border border-slate-100 space-y-4">
                            <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-slate-400 shadow-sm">
                                <i data-lucide="file-text" class="w-5 h-5"></i>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-black text-slate-900 uppercase tracking-tighter">PAN Card (10 Chars)</label>
                                <input type="text" name="pan_number" required maxlength="10" class="form-input font-bold tracking-[0.3em] text-center uppercase" placeholder="ABCDE1234F">
                            </div>
                        </div>

                        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
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

                        <div class="md:col-span-2 mt-8 pt-8 border-t border-slate-100">
                            <label class="block text-xl font-black text-slate-900 mb-6">Physical Documents checklist</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach(['Aadhaar Card Copy', 'PAN Card Copy', 'Passport Size Photos (2)', 'Educational Certificates', 'Experience Certificates', 'Address Proof'] as $doc)
                                    <label class="group flex items-center gap-4 p-5 bg-white rounded-2xl border border-slate-100 cursor-pointer hover:border-rose-300 hover:shadow-md transition-all">
                                        <div class="relative flex items-center justify-center">
                                            <input type="checkbox" name="submitted_documents[]" value="{{ $doc }}" class="peer w-6 h-6 rounded-lg text-rose-600 focus:ring-rose-500 border-slate-200 transition-all">
                                            <i data-lucide="check" class="w-4 h-4 text-white absolute opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none"></i>
                                        </div>
                                        <span class="text-sm font-bold text-slate-600 group-hover:text-rose-600 transition-colors">{{ $doc }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 3: PROFESSIONAL JOURNEY -->
            <div class="step" id="step_3">
                <div class="bg-white/70 backdrop-blur-xl rounded-[40px] shadow-2xl shadow-slate-200/50 border border-white p-8 md:p-12">
                    <div class="mb-10">
                        <span class="inline-block px-4 py-1 rounded-full bg-rose-50 text-rose-600 text-xs font-black uppercase tracking-widest mb-4">Section D & E</span>
                        <h2 class="text-4xl font-black text-slate-900 mb-3 tracking-tight">Professional Journey</h2>
                        <p class="text-slate-500 text-lg">Showcase your skills, education, and where you've cooked before.</p>
                    </div>

                    <div class="space-y-12">
                        <!-- Education -->
                        <div class="p-8 bg-slate-900 rounded-[32px] text-white">
                            <div class="flex items-center justify-between mb-8">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center">
                                        <i data-lucide="graduation-cap" class="w-5 h-5"></i>
                                    </div>
                                    <h3 class="text-xl font-black">Educational Milestone</h3>
                                </div>
                                <button type="button" onclick="addRow('edu_table')" class="bg-white/10 hover:bg-white/20 px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                                    <i data-lucide="plus" class="w-4 h-4"></i> Add Qualification
                                </button>
                            </div>
                            <div class="space-y-4" id="edu_container">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 pb-4">
                                    <input type="text" name="educational_qualifications[0][name]" placeholder="Degree / Std" class="table-input bg-white/5 border-white/10 text-white placeholder:text-white/30">
                                    <input type="text" name="educational_qualifications[0][inst]" placeholder="Institution" class="table-input bg-white/5 border-white/10 text-white placeholder:text-white/30">
                                    <input type="text" name="educational_qualifications[0][year]" placeholder="Year" class="table-input bg-white/5 border-white/10 text-white placeholder:text-white/30">
                                    <input type="text" name="educational_qualifications[0][grade]" placeholder="GPA / %" class="table-input bg-white/5 border-white/10 text-white placeholder:text-white/30">
                                </div>
                            </div>
                        </div>

                        <!-- Work Experience -->
                        <div class="p-8 bg-white border border-slate-100 rounded-[32px] shadow-sm">
                            <div class="flex items-center justify-between mb-8">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 bg-rose-50 text-rose-500 rounded-xl flex items-center justify-center">
                                        <i data-lucide="briefcase" class="w-5 h-5"></i>
                                    </div>
                                    <h3 class="text-xl font-black text-slate-900">Past Experience</h3>
                                </div>
                                <button type="button" onclick="addRow('exp_table')" class="bg-rose-50 hover:bg-rose-100 text-rose-600 px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                                    <i data-lucide="plus" class="w-4 h-4"></i> Add Past Role
                                </button>
                            </div>
                            <div class="space-y-4" id="exp_container">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 pb-4">
                                    <input type="text" name="work_experience[0][company]" placeholder="Company Name" class="table-input">
                                    <input type="text" name="work_experience[0][role]" placeholder="Designation" class="table-input">
                                    <input type="text" name="work_experience[0][duration]" placeholder="Duration (e.g. 2Y)" class="table-input">
                                    <input type="text" name="work_experience[0][salary]" placeholder="Last Salary" class="table-input">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 4: BANK & EMERGENCY -->
            <div class="step" id="step_4">
                <div class="bg-white/70 backdrop-blur-xl rounded-[40px] shadow-2xl shadow-slate-200/50 border border-white p-8 md:p-12">
                    <div class="mb-10 text-center">
                        <span class="inline-block px-4 py-1 rounded-full bg-emerald-50 text-emerald-600 text-xs font-black uppercase tracking-widest mb-4">Section F & G</span>
                        <h2 class="text-4xl font-black text-slate-900 mb-3 tracking-tight">Bank & Emergency</h2>
                        <p class="text-slate-500 text-lg">Secure your payroll and let us know who to call in emergency.</p>
                    </div>

                    <div class="space-y-8">
                        <!-- Bank -->
                        <div class="p-8 bg-emerald-50 rounded-[40px] border border-emerald-100 relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-100 rounded-full blur-3xl -mr-16 -mt-16"></div>
                            <h3 class="text-xl font-black text-emerald-900 mb-8 flex items-center gap-3">
                                <i data-lucide="credit-card" class="w-6 h-6"></i> Salary Account
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-2">
                                    <label class="text-xs font-black text-emerald-900/50 uppercase tracking-widest">Account Holder Name</label>
                                    <input type="text" name="account_holder_name" required class="form-input border-emerald-200 focus:border-emerald-500 focus:ring-emerald-500/10">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-black text-emerald-900/50 uppercase tracking-widest">Bank Name</label>
                                    <input type="text" name="bank_name" required class="form-input border-emerald-200 focus:border-emerald-500 focus:ring-emerald-500/10">
                                </div>
                                <div class="md:col-span-1 space-y-2">
                                    <label class="text-xs font-black text-emerald-900/50 uppercase tracking-widest">Account Number</label>
                                    <input type="text" name="account_number" required class="form-input border-emerald-200 focus:border-emerald-500 focus:ring-emerald-500/10 font-mono tracking-wider">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-black text-emerald-900/50 uppercase tracking-widest">IFSC Code</label>
                                    <input type="text" name="ifsc_code" required class="form-input border-emerald-200 focus:border-emerald-500 focus:ring-emerald-500/10 font-bold uppercase">
                                </div>
                                <div class="md:col-span-2">
                                    <div class="flex items-center gap-8 bg-white/40 p-4 rounded-2xl border border-emerald-200">
                                        <span class="text-sm font-bold text-emerald-800">Account Type:</span>
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

                        <!-- Emergency -->
                        <div class="p-8 bg-rose-50 rounded-[40px] border border-rose-100">
                            <h3 class="text-xl font-black text-rose-900 mb-8 flex items-center gap-3">
                                <i data-lucide="phone-forwarded" class="w-6 h-6"></i> In Case of Emergency
                            </h3>
                            <div class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div class="space-y-2">
                                        <label class="text-xs font-black text-rose-400 uppercase tracking-widest">Primary Contact Name</label>
                                        <input type="text" name="emergency_contacts_json[0][name]" required class="form-input border-rose-200" placeholder="e.g. Spouse/Parent">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-xs font-black text-rose-400 uppercase tracking-widest">Relationship</label>
                                        <input type="text" name="emergency_contacts_json[0][relation]" required class="form-input border-rose-200">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-xs font-black text-rose-400 uppercase tracking-widest">Primary Mobile</label>
                                        <input type="tel" name="emergency_contacts_json[0][mobile]" required class="form-input border-rose-200">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-rose-100">
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Secondary Contact</label>
                                        <input type="text" name="emergency_contacts_json[1][name]" class="form-input border-slate-200" placeholder="Optional">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Relationship</label>
                                        <input type="text" name="emergency_contacts_json[1][relation]" class="form-input border-slate-200">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Secondary Mobile</label>
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
                <div class="bg-white/70 backdrop-blur-xl rounded-[40px] shadow-2xl shadow-slate-200/50 border border-white p-8 md:p-12">
                    <div class="mb-12">
                        <span class="inline-block px-4 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-black uppercase tracking-widest mb-4">Section H</span>
                        <h2 class="text-4xl font-black text-slate-900 mb-3 tracking-tight">Legal Beneficiary</h2>
                        <p class="text-slate-500 text-lg">Nominate your legal beneficiary for PF, Gratuity, and Insurance benefits.</p>
                    </div>

                    <div class="flex flex-col md:flex-row gap-12 items-start">
                        <div class="w-full md:w-1/2 space-y-8">
                            <div class="space-y-2">
                                <label class="text-sm font-black text-slate-900 uppercase tracking-tighter">Nominee Full Name</label>
                                <input type="text" name="nominee_details[name]" required class="form-input" placeholder="Legal Name">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-black text-slate-900 uppercase tracking-tighter">Relationship</label>
                                <input type="text" name="nominee_details[relation]" required class="form-input" placeholder="e.g. Daughter, Husband">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-black text-slate-900 uppercase tracking-tighter">Date of Birth</label>
                                <input type="date" name="nominee_details[dob]" required class="form-input">
                            </div>
                            <div class="space-y-4 p-6 bg-slate-50 rounded-3xl border border-slate-100">
                                <label class="text-sm font-black text-slate-900 uppercase tracking-tighter flex justify-between">
                                    Percentage Share
                                    <span class="text-rose-600" id="share_label">100%</span>
                                </label>
                                <input type="range" name="nominee_details[share]" value="100" min="1" max="100" class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-rose-600" oninput="document.getElementById('share_label').innerText = this.value + '%'">
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest text-center">Benefit allocation for this nominee</p>
                            </div>
                        </div>

                        <div class="hidden md:block w-1/2 sticky top-32">
                            <div class="p-10 bg-slate-900 rounded-[48px] text-white shadow-2xl relative overflow-hidden group">
                                <div class="absolute -top-10 -right-10 w-40 h-40 bg-rose-600/20 rounded-full blur-[80px] group-hover:scale-150 transition-all duration-1000"></div>
                                <div class="relative z-10 flex flex-col gap-6">
                                    <div class="w-14 h-14 bg-white/10 rounded-2xl flex items-center justify-center text-rose-500">
                                        <i data-lucide="shield-check" class="w-8 h-8"></i>
                                    </div>
                                    <h3 class="text-2xl font-black tracking-tight">Why we ask for this?</h3>
                                    <p class="text-slate-400 text-sm leading-relaxed">This data is mandatory for compliance under ESIC and EPF schemes. It ensures your hard-earned benefits reach your loved ones securely.</p>
                                    <div class="pt-6 mt-6 border-t border-white/10 text-[10px] font-black uppercase tracking-[0.2em] text-white/40">Legal Documentation Step 3/5</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 6: UNIFORM & SHOP -->
            <div class="step" id="step_6">
                <div class="bg-white/70 backdrop-blur-xl rounded-[40px] shadow-2xl shadow-slate-200/50 border border-white p-8 md:p-12">
                    <div class="mb-10">
                        <span class="inline-block px-4 py-1 rounded-full bg-rose-50 text-rose-600 text-xs font-black uppercase tracking-widest mb-4">Section I</span>
                        <h2 class="text-4xl font-black text-slate-900 mb-3 tracking-tight">Dress for Success</h2>
                        <p class="text-slate-500 text-lg">We want you to look sharp and stay safe. Pick your sizes carefully.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                        <div class="p-8 bg-white rounded-[32px] border-2 border-slate-100 hover:border-rose-400 transition-all group shadow-sm text-center space-y-4">
                            <div class="w-16 h-16 bg-rose-50 rounded-full flex items-center justify-center mx-auto text-rose-600 group-hover:scale-110 transition-transform">
                                <i data-lucide="shirt" class="w-8 h-8"></i>
                            </div>
                            <h3 class="font-black text-slate-900">Shirt / Kurta</h3>
                            <select name="uniform_details[shirt]" required class="form-input text-center font-black">
                                @foreach(['S', 'M', 'L', 'XL', 'XXL', '3XL'] as $size)
                                    <option value="{{ $size }}">{{ $size }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="p-8 bg-white rounded-[32px] border-2 border-slate-100 hover:border-rose-400 transition-all group shadow-sm text-center space-y-4">
                            <div class="w-16 h-16 bg-rose-50 rounded-full flex items-center justify-center mx-auto text-rose-600 group-hover:scale-110 transition-transform">
                                <i data-lucide="square" class="w-8 h-8"></i>
                            </div>
                            <h3 class="font-black text-slate-900">Trouser / Pants</h3>
                            <input type="text" name="uniform_details[trouser]" required class="form-input text-center font-black" placeholder="Waist (e.g. 32)">
                        </div>

                        <div class="p-8 bg-white rounded-[32px] border-2 border-slate-100 hover:border-rose-400 transition-all group shadow-sm text-center space-y-4">
                            <div class="w-16 h-16 bg-rose-50 rounded-full flex items-center justify-center mx-auto text-rose-600 group-hover:scale-110 transition-transform">
                                <i data-lucide="footprints" class="w-8 h-8"></i>
                            </div>
                            <h3 class="font-black text-slate-900">Safety Shoes</h3>
                            <input type="number" name="uniform_details[shoes]" required min="4" max="13" class="form-input text-center font-black" placeholder="UK / IND size">
                        </div>
                    </div>

                    <div class="p-8 bg-slate-900 rounded-[40px] text-white">
                        <h3 class="text-xl font-black mb-6">Asset Responsibility Acknowledgment</h3>
                        <div class="bg-white/5 border border-white/10 p-6 rounded-3xl mb-8 space-y-3 opacity-80">
                            @foreach([
                                'All uniform and equipment remain property of Mischief Food Pvt Ltd.',
                                'I am responsible for laundry and maintenance of my uniform.',
                                'Any loss of assets (ID Card, Equipment) will be reported in 24h.',
                                'Damaged assets due to negligence may be deducted from final settlement.'
                            ] as $text)
                                <div class="flex gap-4 text-sm font-medium">
                                    <span class="text-rose-500 flex-shrink-0">•</span>
                                    <span>{{ $text }}</span>
                                </div>
                            @endforeach
                        </div>
                        <label class="flex items-center gap-4 cursor-pointer group p-2">
                            <div class="relative flex items-center justify-center">
                                <input type="checkbox" name="asset_acknowledged" required class="peer w-8 h-8 rounded-xl text-rose-600 focus:ring-offset-slate-900 border-white/20 bg-white/10 transition-all">
                                <i data-lucide="check" class="w-5 h-5 text-white absolute opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none"></i>
                            </div>
                            <span class="text-lg font-black text-white/50 group-hover:text-white transition-colors">I accept the asset policy</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- STEP 7: POLICY -->
            <div class="step" id="step_7">
                <div class="bg-white/70 backdrop-blur-xl rounded-[40px] shadow-2xl shadow-slate-200/50 border border-white p-8 md:p-12">
                    <div class="mb-10">
                        <span class="inline-block px-4 py-1 rounded-full bg-blue-50 text-blue-600 text-xs font-black uppercase tracking-widest mb-4">Section M</span>
                        <h2 class="text-4xl font-black text-slate-900 mb-3 tracking-tight">Code of Conduct</h2>
                        <p class="text-slate-500 text-lg">Our culture is built on respect and punctuality. Please acknowledge our core policies.</p>
                    </div>

                    <div class="space-y-6">
                        @foreach([
                            [
                                'title' => 'Shift Timings & Punctuality',
                                'text' => 'Employees must be present 15 minutes before the shift start. Late arrival for more than 3 days in a month without prior notice will attract disciplinary action.',
                                'icon' => 'clock'
                            ],
                            [
                                'title' => 'Leave & Absence',
                                'text' => 'Planned leaves must be requested 2 weeks in advance via the Portal. Unauthorized absence for more than 48h will be treated as voluntary abandonment of work.',
                                'icon' => 'calendar-days'
                            ],
                            [
                                'title' => 'Hygiene Standards',
                                'text' => 'In the food industry, hygiene is non-negotiable. Clean uniform, trimmed nails, and hair-nets are mandatory at all times in the kitchen zone.',
                                'icon' => 'sparkles'
                            ],
                            [
                                'title' => 'Background Verification',
                                'text' => 'You consent to background and police verification. Any false information provided will lead to immediate termination of service.',
                                'icon' => 'shield-alert'
                            ]
                        ] as $policy)
                            <div class="p-8 bg-white border border-slate-100 rounded-[32px] hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                                <div class="flex gap-6">
                                    <div class="w-16 h-16 bg-slate-50 rounded-[24px] flex items-center justify-center text-rose-500 shrink-0">
                                        <i data-lucide="{{ $policy['icon'] }}" class="w-7 h-7"></i>
                                    </div>
                                    <div class="space-y-2">
                                        <h3 class="text-xl font-black text-slate-900">{{ $policy['title'] }}</h3>
                                        <p class="text-slate-500 text-sm leading-relaxed">{{ $policy['text'] }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            <label class="flex flex-col gap-4 p-8 bg-slate-900 text-white rounded-[40px] cursor-pointer shadow-xl shadow-slate-900/20 active:scale-[0.98] transition-all">
                                <div class="flex justify-between items-center">
                                    <i data-lucide="verified" class="w-8 h-8 text-rose-500"></i>
                                    <input type="checkbox" name="police_verification_consent" required class="peer w-6 h-6 rounded-lg text-rose-600 focus:ring-rose-500 border-white/20 bg-white/10">
                                </div>
                                <span class="text-lg font-black tracking-tight leading-none">Consent to Background Checks</span>
                            </label>
                            
                            <label class="flex flex-col gap-4 p-8 bg-rose-600 text-white rounded-[40px] cursor-pointer shadow-xl shadow-rose-600/20 active:scale-[0.98] transition-all">
                                <div class="flex justify-between items-center">
                                    <i data-lucide="check-circle" class="w-8 h-8 text-white"></i>
                                    <input type="checkbox" name="leave_policy_acknowledged" required class="peer w-6 h-6 rounded-lg text-white/40 focus:ring-white border-white/20 bg-white/10">
                                </div>
                                <span class="text-lg font-black tracking-tight leading-none">Agree to Leave Policy</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 8: HEALTH & SUPPORT -->
            <div class="step" id="step_8">
                <div class="bg-white/70 backdrop-blur-xl rounded-[40px] shadow-2xl shadow-slate-200/50 border border-white p-8 md:p-12">
                    <div class="mb-10">
                        <span class="inline-block px-4 py-1 rounded-full bg-slate-50 text-slate-600 text-xs font-black uppercase tracking-widest mb-4">Section I & O</span>
                        <h2 class="text-4xl font-black text-slate-900 mb-3 tracking-tight">Health & Support</h2>
                        <p class="text-slate-500 text-lg">Your wellbeing matters. Tell us about your health and know your support system.</p>
                    </div>

                    <div class="space-y-12">
                        <!-- Medical -->
                        <div class="p-8 bg-white border-2 border-slate-100 rounded-[40px] shadow-sm">
                            <h3 class="text-2xl font-black text-slate-900 mb-8 flex items-center gap-3">
                                <i data-lucide="stethoscope" class="w-7 h-7 text-rose-600"></i> Medical Declaration
                            </h3>
                            <div class="space-y-8">
                                <div class="p-6 bg-slate-50 rounded-3xl flex flex-col md:flex-row items-center justify-between gap-6 border border-slate-100">
                                    <span class="text-sm font-bold text-slate-700">Are you medically cleared to work in a commercial kitchen?</span>
                                    <div class="flex bg-white p-1.5 rounded-2xl shadow-sm border border-slate-200">
                                        <label class="px-6 py-2.5 rounded-xl cursor-pointer transition-all has-[:checked]:bg-rose-600 has-[:checked]:text-white">
                                            <input type="radio" name="medical_info[fitness_checked]" value="Yes" class="hidden">
                                            <span class="text-sm font-black uppercase tracking-widest">Yes</span>
                                        </label>
                                        <label class="px-6 py-2.5 rounded-xl cursor-pointer transition-all has-[:checked]:bg-rose-600 has-[:checked]:text-white">
                                            <input type="radio" name="medical_info[fitness_checked]" value="No" checked class="hidden">
                                            <span class="text-sm font-black uppercase tracking-widest">No</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-bold text-slate-700 ml-1">Do you have any known allergies (especially food-related)?</label>
                                    <textarea name="medical_info[conditions]" rows="3" class="form-input" placeholder="e.g. Peanuts, Latex, Dust..."></textarea>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label class="text-sm font-bold text-slate-700 ml-1">FSSAI Certificate Serial (if any)</label>
                                        <input type="text" name="medical_info[fssai_cert]" class="form-input uppercase font-bold" placeholder="FSSAI-000000">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-bold text-slate-700 ml-1">Certification Expiry</label>
                                        <input type="date" name="medical_info[fssai_expiry]" class="form-input">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Grief / Support -->
                        <div class="bg-slate-900 rounded-[40px] p-8 md:p-12 text-white relative overflow-hidden group">
                           <div class="absolute bottom-0 right-0 w-64 h-64 bg-rose-600/10 rounded-full blur-[100px] group-hover:bg-rose-600/20 transition-all duration-1000"></div>
                           <div class="relative z-10">
                                <h3 class="text-2xl font-black mb-8 flex items-center gap-3">
                                    <i data-lucide="heart-handshake" class="w-7 h-7 text-rose-500"></i> Grievance Redressal
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                                    <div class="space-y-4">
                                        <p class="text-slate-400 text-sm leading-relaxed">We treat every voice with respect. Harassment, discrimination, or safety concerns can be reported directly to HR or using our anonymous portal.</p>
                                        <div class="flex items-center gap-3 text-rose-400 font-black text-sm uppercase">
                                            <i data-lucide="mail" class="w-4 h-4"></i> help@mischieffood.com
                                        </div>
                                    </div>
                                    <div class="space-y-6">
                                        <label class="flex items-start gap-4 cursor-pointer group p-4 border border-white/10 rounded-3xl hover:bg-white/5 transition-all">
                                            <div class="relative flex items-center justify-center mt-1">
                                                <input type="checkbox" name="grievance_acknowledged" required class="peer w-6 h-6 rounded-lg text-rose-600 focus:ring-offset-slate-900 border-white/20 bg-white/10">
                                                <i data-lucide="check" class="w-4 h-4 text-white absolute opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none"></i>
                                            </div>
                                            <div class="space-y-1">
                                                <span class="text-sm font-black block">Aware of Support System</span>
                                                <span class="text-[10px] text-slate-400 uppercase tracking-widest font-bold">Acknowledgment required</span>
                                            </div>
                                        </label>
                                        
                                        <label class="flex items-start gap-4 cursor-pointer group p-4 border border-white/10 rounded-3xl hover:bg-white/5 transition-all">
                                            <div class="relative flex items-center justify-center mt-1">
                                                <input type="checkbox" name="probation_terms_acknowledged" required class="peer w-6 h-6 rounded-lg text-rose-600 focus:ring-offset-slate-900 border-white/20 bg-white/10">
                                                <i data-lucide="check" class="w-4 h-4 text-white absolute opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none"></i>
                                            </div>
                                            <div class="space-y-1">
                                                <span class="text-sm font-black block">Accept Probation Terms</span>
                                                <span class="text-[10px] text-slate-400 uppercase tracking-widest font-bold">Standard 6 months evaluation</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                           </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 9: FINAL DECLARATION -->
            <div class="step" id="step_9">
                <div class="bg-white/70 backdrop-blur-xl rounded-[40px] shadow-2xl shadow-slate-200/50 border border-white p-8 md:p-20 text-center relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-80 h-80 bg-rose-600 animate-pulse rounded-full blur-[150px] opacity-10"></div>
                    
                    <div class="relative z-10 max-w-lg mx-auto">
                        <div class="w-24 h-24 bg-rose-50 rounded-[32px] flex items-center justify-center mx-auto mb-10 text-rose-600 shadow-xl shadow-rose-500/10 rotate-12">
                            <i data-lucide="shield-check" class="w-12 h-12"></i>
                        </div>
                        <h2 class="text-5xl font-black text-slate-900 mb-6 tracking-tight leading-none">Ready to start?</h2>
                        <p class="text-slate-500 text-lg mb-12">By submitting this form, you declare all information provided is true to the best of your knowledge.</p>

                        <div class="space-y-10">
                            <div class="text-left space-y-4">
                                <label class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Employee's Digital Signature</label>
                                <div class="group relative">
                                    <div class="absolute -inset-1 bg-gradient-to-r from-rose-600 to-blue-600 rounded-[28px] blur opacity-25 group-focus-within:opacity-50 transition duration-1000"></div>
                                    <input type="text" name="digital_signature" required placeholder="Type full name to sign" class="relative w-full px-8 py-6 rounded-[24px] bg-white border border-slate-100 focus:border-rose-500 focus:ring-0 transition-all outline-none text-2xl font-black italic text-slate-800 tracking-wider text-center">
                                </div>
                            </div>

                            <label class="flex items-center gap-4 cursor-pointer text-left p-6 rounded-3xl border border-rose-100 bg-rose-50/30">
                                <div class="relative flex items-center justify-center shrink-0">
                                    <input type="checkbox" name="declaration_accepted" required class="peer w-8 h-8 rounded-xl text-rose-600 focus:ring-rose-500 border-rose-200 transition-all">
                                    <i data-lucide="check" class="w-5 h-5 text-white absolute opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none"></i>
                                </div>
                                <span class="text-sm font-bold text-slate-600">I declare that all facts are correct. Concealment may lead to immediate termination.</span>
                            </label>

                            <button type="submit" class="btn-fancy btn-primary w-full py-6 text-2xl flex items-center justify-center group">
                                SUBMIT JOINING FORM
                                <i data-lucide="arrow-right" class="w-6 h-6 group-hover:translate-x-2 transition-transform"></i>
                            </button>
                            
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-[0.3em] pb-4">Secured by Kitchen OS Verification System</p>
                        </div>
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

    <script>
        lucide.createIcons();
        let currentStep = 1;
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

                if (!currentInputValid) {
                    isValid = false;
                    input.classList.add('border-rose-400', 'ring-4', 'ring-rose-400/10');
                    setTimeout(() => input.classList.remove('ring-4', 'ring-rose-400/10'), 2000);
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

        function addRow(tableId) {
            const container = tableId === 'edu_table' ? document.getElementById('edu_container') : document.getElementById('exp_container');
            const rowCount = container.children.length;
            const newRow = document.createElement('div');
            newRow.className = "grid grid-cols-1 md:grid-cols-4 gap-4 pb-4 animate-in fade-in slide-in-from-left-4 relative group";
            
            if (tableId === 'edu_table') {
                newRow.innerHTML = `
                    <input type="text" name="educational_qualifications[${rowCount}][name]" placeholder="Degree" class="table-input bg-white/5 border-white/10 text-white placeholder:text-white/30">
                    <input type="text" name="educational_qualifications[${rowCount}][inst]" placeholder="Institution" class="table-input bg-white/5 border-white/10 text-white placeholder:text-white/30">
                    <input type="text" name="educational_qualifications[${rowCount}][year]" placeholder="Year" class="table-input bg-white/5 border-white/10 text-white placeholder:text-white/30">
                    <div class="flex gap-2">
                        <input type="text" name="educational_qualifications[${rowCount}][grade]" placeholder="GPA" class="table-input bg-white/5 border-white/10 text-white placeholder:text-white/30">
                        <button type="button" onclick="this.closest('div.grid').remove()" class="text-rose-500 hover:text-rose-400"><i data-lucide="x-circle" class="w-5 h-5"></i></button>
                    </div>
                `;
            } else {
                newRow.innerHTML = `
                    <input type="text" name="work_experience[${rowCount}][company]" placeholder="Company" class="table-input">
                    <input type="text" name="work_experience[${rowCount}][role]" placeholder="Designation" class="table-input">
                    <input type="text" name="work_experience[${rowCount}][duration]" placeholder="Duration" class="table-input">
                    <div class="flex gap-2">
                        <input type="text" name="work_experience[${rowCount}][salary]" placeholder="Salary" class="table-input">
                        <button type="button" onclick="this.closest('div.grid').remove()" class="text-rose-500 hover:text-rose-600"><i data-lucide="x-circle" class="w-5 h-5"></i></button>
                    </div>
                `;
            }
            container.appendChild(newRow);
            lucide.createIcons();
        }

        updateUI();
    </script>
</body>
</html>