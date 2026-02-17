@extends('layouts.app')

@section('header')
    <h1 class="text-2xl font-bold text-gray-800">Time Clock Terminal</h1>
@endsection

@section('content')
    <!-- Tablet/Terminal UI Container -->
    <div
        class="bg-[#0f172a] text-white rounded-3xl overflow-hidden relative shadow-2xl min-h-[600px] flex items-center justify-center p-6">

        <!-- Background Elements -->
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden -z-10 bg-[#0f172a]">
            <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-blue-600 rounded-full blur-[120px] opacity-20">
            </div>
            <div
                class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-purple-600 rounded-full blur-[120px] opacity-20">
            </div>
        </div>

        <div class="w-full max-w-6xl grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

            <!-- Left Side: Camera & Status -->
            <div class="space-y-6">
                <div class="text-center lg:text-left mb-8">
                    <h2 class="text-4xl lg:text-5xl font-bold mb-2 tracking-tight">Clock In/Out</h2>
                    <p class="text-gray-400 text-lg">Identity Verification Active</p>
                </div>

                <!-- Photo Mode Toggle -->
                <div class="flex items-center justify-center lg:justify-start gap-3 mb-4">
                    <button id="mode-live" onclick="switchPhotoMode('live')" 
                        class="px-4 py-2 rounded-xl bg-blue-600 text-white font-semibold text-sm flex items-center gap-2 transition-all">
                        <i data-lucide="camera" class="w-4 h-4"></i>
                        Live Camera
                    </button>
                    <button id="mode-upload" onclick="switchPhotoMode('upload')" 
                        class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold text-sm flex items-center gap-2 transition-all">
                        <i data-lucide="upload" class="w-4 h-4"></i>
                        Upload Photo
                    </button>
                </div>

                <!-- Live Camera View -->
                <div id="camera-view"
                    class="relative w-full aspect-[4/3] rounded-3xl overflow-hidden shadow-2xl border-4 border-white/5 bg-black">
                    <video id="camera" autoplay playsinline
                        class="w-full h-full object-cover transform -scale-x-100"></video>
                    <div id="loading-camera"
                        class="absolute inset-0 flex items-center justify-center bg-gray-900 text-gray-400">
                        <div class="flex flex-col items-center gap-2">
                            <i data-lucide="camera" class="w-8 h-8 animate-pulse"></i>
                            <span>Initializing Camera...</span>
                        </div>
                    </div>
                    <!-- Face Overlay Guide -->
                    <div
                        class="absolute inset-0 border-[3px] border-dashed border-white/20 m-12 rounded-3xl pointer-events-none opacity-50">
                    </div>
                </div>

                <!-- Upload Photo View -->
                <div id="upload-view" class="hidden relative w-full aspect-[4/3] rounded-3xl overflow-hidden shadow-2xl border-4 border-white/5 bg-black" 
                     ondrop="handleDrop(event)" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)">
                    <img id="uploaded-preview" class="hidden w-full h-full object-cover" alt="Uploaded photo">
                    <div id="upload-placeholder" class="absolute inset-0 flex flex-col items-center justify-center bg-gray-900 text-gray-400 p-6 border-2 border-dashed border-white/20 rounded-3xl transition-all">
                        <i data-lucide="image" class="w-16 h-16 mb-4 opacity-50"></i>
                        <p class="text-center mb-2">Click to select photo</p>
                        <p class="text-center text-xs mb-4 text-gray-500">or drag & drop image here</p>
                        <label for="photo-upload" class="px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white rounded-xl cursor-pointer font-semibold transition-all">
                            Choose Photo
                        </label>
                        <input type="file" id="photo-upload" accept="image/*" class="hidden" onchange="handleFileUpload(event)">
                    </div>
                </div>

                <div class="flex items-center justify-center lg:justify-start gap-3 text-sm text-gray-500">
                    <i data-lucide="map-pin" class="w-4 h-4 text-green-500"></i>
                    <span id="gps-status">Locating device...</span>
                </div>
            </div>

            <!-- Right Side: Numpad & Actions -->
            <div
                class="bg-white/5 backdrop-blur-xl border border-white/10 p-8 rounded-3xl shadow-2xl max-w-md mx-auto w-full">
                <!-- Code Display -->
                <div class="mb-8 relative">
                    <input type="password" id="staff-code" readonly
                        class="w-full bg-black/40 border-2 border-white/10 text-center text-5xl py-6 rounded-2xl tracking-[0.5em] focus:outline-none focus:border-blue-500/50 transition-colors text-white placeholder-gray-700 font-mono"
                        placeholder="••••••" maxlength="6">
                    <button onclick="clearCode()"
                        class="absolute right-4 top-1/2 -translate-y-1/2 p-2 hover:bg-white/10 rounded-full transition-colors text-gray-400">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>

                <!-- Buttons -->
                <div class="grid grid-cols-3 gap-3 mb-8">
                    @foreach([1, 2, 3, 4, 5, 6, 7, 8, 9] as $num)
                        <button
                            class="h-16 text-2xl font-medium rounded-2xl bg-white/10 hover:bg-white/20 active:scale-95 transition-all text-white border border-white/5 flex items-center justify-center"
                            onclick="appendNumber('{{ $num }}')">{{ $num }}</button>
                    @endforeach
                    <button class="bg-transparent border-0"></button>
                    <button
                        class="h-16 text-2xl font-medium rounded-2xl bg-white/10 hover:bg-white/20 active:scale-95 transition-all text-white border border-white/5 flex items-center justify-center"
                        onclick="appendNumber('0')">0</button>
                    <button
                        class="h-16 text-2xl font-medium rounded-2xl hover:bg-white/10 active:scale-95 transition-all text-gray-400 flex items-center justify-center"
                        onclick="backspace()">
                        <i data-lucide="delete" class="w-8 h-8"></i>
                    </button>
                </div>

                <!-- Actions -->
                <div class="grid grid-cols-2 gap-4">
                    <button onclick="submitAttendance('CLOCK_IN')" id="btn-in"
                        class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold h-16 rounded-2xl shadow-lg active:scale-95 transition-all flex items-center justify-center gap-2">
                        <i data-lucide="log-in" class="w-6 h-6"></i> IN
                    </button>
                    <button onclick="submitAttendance('CLOCK_OUT')" id="btn-out"
                        class="bg-rose-600 hover:bg-rose-500 text-white font-bold h-16 rounded-2xl shadow-lg active:scale-95 transition-all flex items-center justify-center gap-2">
                        <i data-lucide="log-out" class="w-6 h-6"></i> OUT
                    </button>
                </div>
            </div>
        </div>

        <canvas id="snapshot" class="hidden"></canvas>
    </div>

    <!-- Notification Toast (Injected into main body via JS or Portal) -->
    <div id="toast" class="fixed top-24 right-6 transform transition-all duration-300 translate-x-[150%] z-[60]">
        <div class="bg-gray-900 border border-gray-700 text-white p-4 rounded-xl shadow-2xl flex items-center gap-4 min-w-[320px] border-l-4"
            id="toast-border">
            <div class="p-2 rounded-full bg-white/10" id="toast-icon"></div>
            <div>
                <h3 class="font-bold text-lg" id="toast-title"></h3>
                <p class="text-sm text-gray-300" id="toast-message"></p>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loading"
        class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[70] hidden flex flex-col items-center justify-center">
        <div class="w-16 h-16 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-4"></div>
        <p class="text-white text-xl font-medium tracking-wide animate-pulse">Processing Request...</p>
    </div>
@endsection

@push('scripts')
    <script>
        lucide.createIcons();
        let currentCode = '';
        let photoMode = 'live'; // 'live' or 'upload'
        let uploadedPhotoFile = null;
        const codeInput = document.getElementById('staff-code');
        const video = document.getElementById('camera');
        const canvas = document.getElementById('snapshot');
        let cameraStream = null;

        // Photo Mode Switch
        function switchPhotoMode(mode) {
            photoMode = mode;
            const liveBtn = document.getElementById('mode-live');
            const uploadBtn = document.getElementById('mode-upload');
            const cameraView = document.getElementById('camera-view');
            const uploadView = document.getElementById('upload-view');

            if (mode === 'live') {
                liveBtn.className = 'px-4 py-2 rounded-xl bg-blue-600 text-white font-semibold text-sm flex items-center gap-2 transition-all';
                uploadBtn.className = 'px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold text-sm flex items-center gap-2 transition-all';
                cameraView.classList.remove('hidden');
                uploadView.classList.add('hidden');
                startCamera();
            } else {
                liveBtn.className = 'px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold text-sm flex items-center gap-2 transition-all';
                uploadBtn.className = 'px-4 py-2 rounded-xl bg-blue-600 text-white font-semibold text-sm flex items-center gap-2 transition-all';
                cameraView.classList.add('hidden');
                uploadView.classList.remove('hidden');
                stopCamera();
            }
        }

        // Camera Setup
        async function startCamera() {
            try {
                if (cameraStream) {
                    cameraStream.getTracks().forEach(track => track.stop());
                }
                const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
                cameraStream = stream;
                video.srcObject = stream;
                document.getElementById('loading-camera').classList.add('hidden');
            } catch (err) {
                console.error("Camera error:", err);
                showToast('error', 'Camera Blocked', 'Allow camera access to enable system.');
            }
        }

        function stopCamera() {
            if (cameraStream) {
                cameraStream.getTracks().forEach(track => track.stop());
                cameraStream = null;
            }
        }

        startCamera();

        // GPS Setup
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                () => document.getElementById('gps-status').innerText = "GPS Signal Active",
                () => document.getElementById('gps-status').innerText = "GPS Signal Lost"
            );
        }

        // Numpad Functions
        function appendNumber(num) {
            if (currentCode.length < 6) {
                currentCode += num;
                updateDisplay();
            }
        }

        function clearCode() {
            currentCode = '';
            updateDisplay();
        }

        function backspace() {
            currentCode = currentCode.slice(0, -1);
            updateDisplay();
        }

        function updateDisplay() {
            codeInput.value = currentCode;
        }

        // Handle File Upload
        function handleFileUpload(event) {
            const file = event.target.files[0];
            processUploadedFile(file);
        }

        // Handle Drag & Drop
        function handleDragOver(event) {
            event.preventDefault();
            event.stopPropagation();
            const placeholder = document.getElementById('upload-placeholder');
            if (placeholder) {
                placeholder.classList.add('border-blue-500', 'bg-gray-800');
            }
        }

        function handleDragLeave(event) {
            event.preventDefault();
            event.stopPropagation();
            const placeholder = document.getElementById('upload-placeholder');
            if (placeholder) {
                placeholder.classList.remove('border-blue-500', 'bg-gray-800');
            }
        }

        function handleDrop(event) {
            event.preventDefault();
            event.stopPropagation();
            const placeholder = document.getElementById('upload-placeholder');
            if (placeholder) {
                placeholder.classList.remove('border-blue-500', 'bg-gray-800');
            }

            const files = event.dataTransfer.files;
            if (files.length > 0) {
                processUploadedFile(files[0]);
            }
        }

        function processUploadedFile(file) {
            if (!file) return;

            if (!file.type.startsWith('image/')) {
                showToast('error', 'Invalid File', 'Please select an image file.');
                return;
            }

            if (file.size > 5 * 1024 * 1024) { // 5MB
                showToast('error', 'File Too Large', 'Please select an image smaller than 5MB.');
                return;
            }

            uploadedPhotoFile = file;
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('uploaded-preview');
                const placeholder = document.getElementById('upload-placeholder');
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                placeholder.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        }

        // Main Action
        async function submitAttendance(actionType) {
            if (currentCode.length !== 6) {
                showToast('error', 'Code Required', 'Enter your 6-digit staff code.');
                return;
            }

            let photoBlob = null;

            if (photoMode === 'live') {
                // Capture from live camera
                if (!video.videoWidth || !video.videoHeight) {
                    showToast('error', 'Camera Error', 'Camera not ready. Please wait or switch to upload mode.');
                    return;
                }

                const context = canvas.getContext('2d');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                context.drawImage(video, 0, 0, canvas.width, canvas.height);

                photoBlob = await new Promise((resolve) => {
                    canvas.toBlob((blob) => {
                        if (!blob) {
                            handleError('Camera capture failed');
                            resolve(null);
                        } else {
                            resolve(blob);
                        }
                    }, 'image/jpeg', 0.8);
                });

                if (!photoBlob) return;

            } else {
                // Use uploaded photo
                if (!uploadedPhotoFile) {
                    showToast('error', 'Photo Required', 'Please upload a photo or switch to live camera mode.');
                    return;
                }
                photoBlob = uploadedPhotoFile;
            }

            document.getElementById('loading').classList.remove('hidden');

            if (!navigator.geolocation) {
                handleError('GPS not supported');
                return;
            }

            navigator.geolocation.getCurrentPosition(async (position) => {
                const formData = new FormData();
                formData.append('staff_code', currentCode);
                formData.append('action_type', actionType);
                formData.append('gps_latitude', position.coords.latitude);
                formData.append('gps_longitude', position.coords.longitude);
                formData.append('device_id', 'tablet-web-001');
                formData.append('selfie_image', photoBlob, photoMode === 'live' ? 'capture.jpg' : uploadedPhotoFile.name);

                // Add CSRF Token
                formData.append('_token', '{{ csrf_token() }}');

                try {
                    const res = await fetch('/api/attendance/clock', {
                        method: 'POST',
                        headers: { 'Accept': 'application/json' },
                        body: formData
                    });
                    const data = await res.json();

                    if (res.ok) {
                        showToast('success', actionType === 'CLOCK_IN' ? 'Welcome Back!' : 'See You Later!', data.message);
                        clearCode();
                        // Reset photo if upload mode
                        if (photoMode === 'upload') {
                            uploadedPhotoFile = null;
                            document.getElementById('photo-upload').value = '';
                            document.getElementById('uploaded-preview').classList.add('hidden');
                            document.getElementById('upload-placeholder').classList.remove('hidden');
                        }
                    } else {
                        showToast('error', 'Action Failed', data.message || 'Verification failed');
                    }
                } catch (e) {
                    showToast('error', 'System Error', e.message);
                } finally {
                    document.getElementById('loading').classList.add('hidden');
                }
            }, () => {
                handleError('GPS Permission Denied. Enable location services.');
                document.getElementById('loading').classList.add('hidden');
            });
        }

        function handleError(msg) {
            showToast('error', 'Error', msg);
            document.getElementById('loading').classList.add('hidden');
        }

        function showToast(type, title, message) {
            const toast = document.getElementById('toast');
            const border = document.getElementById('toast-border');
            const icon = document.getElementById('toast-icon');

            document.getElementById('toast-title').innerText = title;
            document.getElementById('toast-message').innerText = message;

            if (type === 'success') {
                border.className = 'bg-gray-900 border border-gray-700 text-white p-4 rounded-xl shadow-2xl flex items-center gap-4 min-w-[320px] border-l-4 border-l-emerald-500';
                icon.innerHTML = '<i data-lucide="check-circle" class="text-emerald-400 w-8 h-8"></i>';
            } else {
                border.className = 'bg-gray-900 border border-gray-700 text-white p-4 rounded-xl shadow-2xl flex items-center gap-4 min-w-[320px] border-l-4 border-l-rose-500';
                icon.innerHTML = '<i data-lucide="alert-triangle" class="text-rose-400 w-8 h-8"></i>';
            }

            lucide.createIcons();
            toast.style.transform = 'translateX(0)';
            setTimeout(() => toast.style.transform = 'translateX(150%)', 3000);
        }
    </script>
@endpush