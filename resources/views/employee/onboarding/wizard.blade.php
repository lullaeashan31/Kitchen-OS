<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Onboarding | Kitchen OS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .step {
            display: none;
        }

        .step.active {
            display: block;
        }
    </style>
</head>

<body class="bg-gray-50 min-height-screen flex items-center justify-center p-4">
    <div class="max-w-2xl w-full bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden">
        <!-- Progress Bar -->
        <div class="h-2 bg-gray-100 w-full flex">
            <div id="progress_bar" class="h-full bg-blue-600 transition-all duration-500" style="width: 25%;"></div>
        </div>

        <div class="p-8 md:p-12">
            <div class="mb-10 text-center">
                <i data-lucide="chef-hat" class="w-12 h-12 text-blue-600 mx-auto mb-4"></i>
                <h1 class="text-3xl font-bold text-gray-800">Welcome to Kitchen OS</h1>
                <p class="text-gray-500 mt-2">Let's get your profile set up, {{ $user->name }}!</p>
            </div>

            <form action="{{ route('onboarding.submit', ['token' => $token]) }}" method="POST" id="wizard_form">
                @csrf

                <!-- Step 1: Personal Info -->
                <div class="step active" id="step_1">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                        <span
                            class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-bold">1</span>
                        Personal Information
                    </h2>
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Current Address</label>
                            <textarea name="address" required rows="3" placeholder="Flat No, Building, Area, City"
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all outline-none text-gray-800"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Secondary Phone (Optional)</label>
                            <input type="tel" name="secondary_phone" inputmode="numeric" pattern="[0-9]{10,15}" title="Numbers only" maxlength="15" placeholder="e.g. 9876500000"
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all outline-none text-gray-800">
                        </div>
                    </div>
                </div>

                <!-- Step 2: Emergency Contact -->
                <div class="step" id="step_2">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                        <span
                            class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-bold">2</span>
                        Emergency Contact
                    </h2>
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Contact Name</label>
                            <input type="text" name="emergency_contact_name" required
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all outline-none text-gray-800">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Contact Phone</label>
                            <input type="tel" name="emergency_contact_phone" required inputmode="numeric" pattern="[0-9]{10,15}" title="Enter 10-15 digit number" maxlength="15" placeholder="e.g. 9876512345"
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all outline-none text-gray-800">
                        </div>
                    </div>
                </div>

                <!-- Step 3: Bank Details -->
                <div class="step" id="step_3">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                        <span
                            class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-bold">3</span>
                        Bank Details
                    </h2>
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Bank Name</label>
                            <input type="text" name="bank_name" required
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all outline-none text-gray-800">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Account Number</label>
                                <input type="text" name="account_number" required inputmode="numeric" pattern="[0-9]*" title="Numbers only" maxlength="24" placeholder="e.g. 123456789012"
                                    class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all outline-none text-gray-800">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">IFSC Code</label>
                                <input type="text" name="ifsc_code" required
                                    class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all outline-none text-gray-800">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: HR Policy -->
                <div class="step" id="step_4">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                        <span
                            class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-bold">4</span>
                        HR Policy Acceptance
                    </h2>
                    <div class="space-y-6">
                        <div class="bg-gray-50 p-6 rounded-2xl border border-gray-200 max-h-60 overflow-y-auto text-sm text-gray-600 leading-relaxed"
                            id="policy_viewer">
                            <h3 class="font-bold text-gray-800 mb-4 uppercase tracking-wider text-center border-b pb-2">
                                Kitchen OS HR Policy</h3>
                            <p class="mb-4 font-bold">1. ATTENDANCE & PUNCTUALITY</p>
                            <p class="mb-6">All staff must clock in using the GPS-verified system. Late arrival for more
                                than 3 days in a month may lead to salary deductions. Absences without 24h notice will
                                be marked as Loss of Pay (LOP).</p>

                            <p class="mb-4 font-bold">2. SALARY & PAYROLL</p>
                            <p class="mb-6">Salaries are calculated based on registered attendance. Monthly base salary
                                is divided by scheduled working days. Performance bonuses are awarded at the discretion
                                of the management via monthly scoring.</p>

                            <p class="mb-4 font-bold">3. LEAVE POLICY</p>
                            <p class="mb-6">Approved leaves must be requested 2 weeks in advance via the Employee
                                Portal. Emergency leaves require immediate notification via Phone/WhatsApp.</p>

                            <p class="mb-4 font-bold">4. CODE OF CONDUCT</p>
                            <p class="mb-6">Maintain high standards of hygiene and professionalism. Any misuse of
                                inventory or financial data will lead to immediate termination.</p>

                            <div class="text-center italic mt-4 text-xs">Scroll to the bottom to accept.</div>
                        </div>

                        <label
                            class="flex items-center space-x-3 p-4 bg-blue-50 rounded-xl border border-blue-200 cursor-pointer group">
                            <input type="checkbox" name="policy_accepted" value="1" id="policy_checkbox" disabled
                                required
                                class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500 border-gray-300 transition-colors">
                            <div>
                                <span class="text-sm font-bold text-blue-800">I have read and accept the HR
                                    Policy</span>
                                <p class="text-xs text-blue-600">This will log your timestamp and IP address.</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="flex justify-between items-center mt-12 pt-8 border-t border-gray-100">
                    <button type="button" id="prev_btn" onclick="moveStep(-1)"
                        class="px-6 py-3 rounded-xl border border-gray-300 text-gray-700 font-bold hover:bg-gray-100 transition-all invisible">
                        Previous
                    </button>
                    <button type="button" id="next_btn" onclick="moveStep(1)"
                        class="px-8 py-3 rounded-xl bg-blue-600 text-white font-bold shadow-lg hover:bg-blue-700 transition-all flex items-center gap-2">
                        Next <i data-lucide="chevron-right" class="w-5 h-5"></i>
                    </button>
                    <button type="submit" id="submit_btn"
                        class="px-8 py-3 rounded-xl bg-green-600 text-white font-bold shadow-lg hover:bg-green-700 transition-all hidden">
                        Complete Onboarding
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        lucide.createIcons();
        let currentStep = 1;
        const totalSteps = 4;

        function moveStep(delta) {
            if (currentStep + delta < 1 || currentStep + delta > totalSteps) return;

            // Validate before moving next
            if (delta > 0 && !validateStep(currentStep)) return;

            document.getElementById(`step_${currentStep}`).classList.remove('active');
            currentStep += delta;
            document.getElementById(`step_${currentStep}`).classList.add('active');

            updateUI();
        }

        function validateStep(step) {
            const inputs = document.querySelectorAll(`#step_${step} [required]`);
            for (let input of inputs) {
                if (!input.value) {
                    input.classList.add('border-red-500');
                    alert('Please fill all required fields.');
                    return false;
                }
                input.classList.remove('border-red-500');
            }
            return true;
        }

        function updateUI() {
            // Update Progress Bar
            const progress = (currentStep / totalSteps) * 100;
            document.getElementById('progress_bar').style.width = `${progress}%`;

            // Update Buttons
            document.getElementById('prev_btn').style.visibility = currentStep === 1 ? 'hidden' : 'visible';

            if (currentStep === totalSteps) {
                document.getElementById('next_btn').classList.add('hidden');
                document.getElementById('submit_btn').classList.remove('hidden');
            } else {
                document.getElementById('next_btn').classList.remove('hidden');
                document.getElementById('submit_btn').classList.add('hidden');
            }
        }

        // Policy Scroll Logic
        const policyViewer = document.getElementById('policy_viewer');
        const policyCheckbox = document.getElementById('policy_checkbox');

        policyViewer.addEventListener('scroll', function () {
            if (policyViewer.scrollHeight - policyViewer.scrollTop <= policyViewer.clientHeight + 10) {
                policyCheckbox.disabled = false;
            }
        });
    </script>
</body>

</html>