@extends('layouts.app')

@section('header')
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('profile.show') }}"
            class="p-2 bg-white rounded-lg border border-gray-200 text-gray-500 hover:text-blue-600 hover:border-blue-300 transition-all shadow-sm">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Edit My Profile</h1>
            <p class="text-sm text-gray-500">Update your name, contact, and onboarding information.</p>
        </div>
    </div>
@endsection

@section('content')
    <div class="w-full pb-10">
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 flex items-center gap-2">
                <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data"
            class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            @csrf
            @method('PUT')

            <div class="p-8">
                <!-- Profile Photo & Basic -->
                <div class="flex flex-col md:flex-row items-center gap-8 mb-10 pb-8 border-b border-gray-100">
                    <div class="shrink-0 relative group">
                        <div
                            class="w-32 h-32 rounded-full overflow-hidden border-4 border-white shadow-2xl bg-gray-100 ring-4 ring-blue-50">
                            @if($user->profile_photo_path)
                                <img id="photo_preview" src="{{ $user->profile_photo_url }}" class="w-full h-full object-cover">
                            @else
                                <div id="photo_placeholder"
                                    class="w-full h-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-4xl font-bold">
                                    {{ substr($user->name, 0, 2) }}
                                </div>
                                <img id="photo_preview" class="hidden w-full h-full object-cover">
                            @endif
                        </div>
                        <div class="absolute bottom-0 right-0 flex gap-1">
                            <label for="profile_photo"
                                class="bg-blue-600 text-white p-2.5 rounded-full cursor-pointer hover:bg-blue-700 shadow-lg transition-all border-2 border-white"
                                title="Upload photo">
                                <i data-lucide="upload" class="w-5 h-5"></i>
                            </label>
                            <button type="button" onclick="openLiveCamera()"
                                class="bg-green-600 text-white p-2.5 rounded-full cursor-pointer hover:bg-green-700 shadow-lg transition-all border-2 border-white"
                                title="Take live photo">
                                <i data-lucide="camera" class="w-5 h-5"></i>
                            </button>
                        </div>
                        <input type="file" name="profile_photo" id="profile_photo" class="hidden" accept="image/*"
                            onchange="previewImage(this)">
                    </div>
                    <div class="flex-1 w-full md:max-w-md space-y-4">
                        <div class="flex items-center gap-2 mb-2">
                            <i data-lucide="user" class="w-5 h-5 text-blue-500"></i>
                            <h3 class="font-bold text-gray-700">Basic Information</h3>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Full Name</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 outline-none text-gray-800">
                            @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Phone Number</label>
                            <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" required
                                inputmode="numeric" pattern="[0-9]*"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').substring(0, 10)"
                                title="Enter 10 digit phone number" maxlength="10" minlength="10"
                                placeholder="e.g. 9876543210"
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 outline-none text-gray-800">
                            @error('phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <p class="text-xs text-gray-500 flex items-center gap-1">
                            <i data-lucide="info" class="w-3 h-3"></i>
                            Staff Code: <span class="font-mono font-bold">{{ $user->staff_code }}</span> (cannot be changed)
                        </p>
                    </div>
                </div>

                <!-- Password -->
                <div class="mb-10 pb-8 border-b border-gray-100">
                    <div class="flex items-center gap-2 mb-4">
                        <i data-lucide="lock" class="w-5 h-5 text-orange-500"></i>
                        <h3 class="font-bold text-gray-700">Change Password</h3>
                    </div>
                    <div class="bg-orange-50/50 p-6 rounded-xl border border-orange-100 max-w-md">
                        <label class="block text-sm font-bold text-gray-700 mb-2">New Password</label>
                        <input type="password" name="password" placeholder="Leave blank to keep current"
                            class="w-full px-4 py-3 rounded-xl bg-white border border-gray-200 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 outline-none mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Confirm Password</label>
                        <input type="password" name="password_confirmation" placeholder="Confirm new password"
                            class="w-full px-4 py-3 rounded-xl bg-white border border-gray-200 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 outline-none">
                        @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <!-- Onboarding / Employee Profile -->
                <div class="mb-10">
                    <div class="flex items-center gap-2 mb-4">
                        <i data-lucide="file-text" class="w-5 h-5 text-green-600"></i>
                        <h3 class="font-bold text-gray-700">Profile & Onboarding Information</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Current Address</label>
                            <textarea name="address" rows="3"
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 outline-none text-gray-800">{{ old('address', $user->employeeProfile?->address) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Secondary Phone (10 Digits)</label>
                            <input type="tel" name="secondary_phone"
                                value="{{ old('secondary_phone', $user->employeeProfile?->secondary_phone) }}"
                                inputmode="numeric" pattern="[0-9]*"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').substring(0, 10)"
                                title="Enter 10 digits" maxlength="10" minlength="10" placeholder="e.g. 9876500000"
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 outline-none text-gray-800">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Joining Date</label>
                            <input type="date" name="joining_date"
                                value="{{ old('joining_date', $user->employeeProfile?->joining_date?->format('Y-m-d')) }}"
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 outline-none text-gray-800">
                        </div>
                        <div class="md:col-span-2 flex items-center gap-2 mt-2 mb-2">
                            <i data-lucide="phone-call" class="w-4 h-4 text-red-500"></i>
                            <span class="font-bold text-gray-700">Emergency Contact</span>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Emergency Contact Name</label>
                            <input type="text" name="emergency_contact_name"
                                value="{{ old('emergency_contact_name', $user->employeeProfile?->emergency_contact_name) }}"
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 outline-none text-gray-800">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Emergency Contact Phone</label>
                            <input type="tel" name="emergency_contact_phone"
                                value="{{ old('emergency_contact_phone', $user->employeeProfile?->emergency_contact_phone) }}"
                                inputmode="numeric" pattern="[0-9]*"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').substring(0, 10)"
                                title="Enter 10 digits" maxlength="10" minlength="10" placeholder="e.g. 9876512345"
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 outline-none text-gray-800">
                        </div>
                        <div class="md:col-span-2 flex items-center gap-2 mt-4 mb-2">
                            <i data-lucide="credit-card" class="w-4 h-4 text-green-500"></i>
                            <span class="font-bold text-gray-700">Bank Account Details</span>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Bank Name</label>
                            <input type="text" name="bank_name"
                                value="{{ old('bank_name', $user->employeeProfile?->bank_name) }}"
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 outline-none text-gray-800">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Account Number</label>
                            <input type="text" name="account_number"
                                value="{{ old('account_number', $user->employeeProfile?->account_number) }}"
                                inputmode="numeric" pattern="[0-9]*"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')" title="Numbers only" maxlength="50"
                                placeholder="e.g. 123456789012"
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 outline-none text-gray-800 font-mono">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">IFSC Code</label>
                            <input type="text" name="ifsc_code"
                                value="{{ old('ifsc_code', $user->employeeProfile?->ifsc_code) }}"
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 outline-none text-gray-800 font-mono">
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-8 py-6 border-t border-gray-100 flex justify-end items-center gap-4">
                <a href="{{ route('profile.show') }}"
                    class="px-6 py-3 rounded-xl border border-gray-300 text-gray-700 font-bold hover:bg-gray-100 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                    class="px-8 py-3 rounded-xl bg-blue-600 text-white font-bold shadow-lg hover:bg-blue-700 transition-all flex items-center gap-2">
                    <i data-lucide="save" class="w-5 h-5"></i>
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    <!-- Live Camera Modal -->
    <div id="liveCameraModal" class="fixed inset-0 bg-black/60 z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden">
            <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="font-bold text-gray-800">Take Live Photo</h3>
                <button type="button" onclick="closeLiveCamera()" class="text-gray-400 hover:text-gray-600 p-1">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <div class="p-4">
                <div class="aspect-square max-h-80 mx-auto bg-gray-900 rounded-xl overflow-hidden">
                    <video id="liveVideo" autoplay playsinline class="w-full h-full object-cover"
                        style="transform: scaleX(-1);"></video>
                    <div id="liveVideoPlaceholder" class="w-full h-full flex items-center justify-center text-gray-500">
                        <span>Camera starting...</span>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-2 text-center">Position your face in the frame, then click Capture.</p>
                <div class="flex gap-3 mt-4">
                    <button type="button" onclick="captureLivePhoto()"
                        class="flex-1 py-3 rounded-xl bg-green-600 text-white font-bold hover:bg-green-700 flex items-center justify-center gap-2">
                        <i data-lucide="camera" class="w-5 h-5"></i>
                        Capture
                    </button>
                    <button type="button" onclick="closeLiveCamera()"
                        class="px-6 py-3 rounded-xl border border-gray-300 text-gray-700 font-bold hover:bg-gray-100">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        if (typeof lucide !== 'undefined') lucide.createIcons();
        function previewImage(input) {
            var preview = document.getElementById('photo_preview');
            var placeholder = document.getElementById('photo_placeholder');
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    if (placeholder) placeholder.classList.add('hidden');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
        var liveStream = null;
        function openLiveCamera() {
            var modal = document.getElementById('liveCameraModal');
            var video = document.getElementById('liveVideo');
            var placeholder = document.getElementById('liveVideoPlaceholder');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } })
                    .then(function (stream) {
                        liveStream = stream;
                        video.srcObject = stream;
                        placeholder.classList.add('hidden');
                        video.classList.remove('hidden');
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    })
                    .catch(function (err) {
                        placeholder.innerHTML = '<span class="text-red-500 text-center px-4">Camera access denied or not available.</span>';
                        console.error('Camera error:', err);
                    });
            } else {
                placeholder.innerHTML = '<span class="text-red-500 text-center px-4">Camera not supported in this browser.</span>';
            }
        }
        function closeLiveCamera() {
            var modal = document.getElementById('liveCameraModal');
            var video = document.getElementById('liveVideo');
            var placeholder = document.getElementById('liveVideoPlaceholder');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            if (liveStream) {
                liveStream.getTracks().forEach(function (t) { t.stop(); });
                liveStream = null;
            }
            video.srcObject = null;
            placeholder.classList.remove('hidden');
            placeholder.innerHTML = 'Camera starting...';
        }
        function captureLivePhoto() {
            var video = document.getElementById('liveVideo');
            var canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            var ctx = canvas.getContext('2d');
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0);
            canvas.toBlob(function (blob) {
                var fileInput = document.getElementById('profile_photo');
                var file = new File([blob], 'profile-photo.jpg', { type: 'image/jpeg' });
                var dt = new DataTransfer();
                dt.items.add(file);
                fileInput.files = dt.files;
                var preview = document.getElementById('photo_preview');
                var placeholder = document.getElementById('photo_placeholder');
                preview.src = URL.createObjectURL(blob);
                preview.classList.remove('hidden');
                if (placeholder) placeholder.classList.add('hidden');
                closeLiveCamera();
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }, 'image/jpeg', 0.9);
        }
    </script>
@endsection