@extends('layouts.app')

@section('header')
    <h1 class="text-2xl font-bold text-gray-800">Registered Devices</h1>
@endsection

@section('content')
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h2 class="font-bold text-gray-700">Authorized Tablets</h2>
            <button onclick="openAddDeviceModal()" class="btn btn-sm btn-secondary">
                <i data-lucide="plus" class="w-4 h-4"></i> Add Device
            </button>
        </div>

        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                    <th class="p-4">Device Name</th>
                    <th class="p-4">IP Address</th>
                    <th class="p-4">Last Active</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <tr>
                    <td class="p-4 font-bold text-gray-700">Main Kitchen Tablet</td>
                    <td class="p-4 font-mono text-gray-500">192.168.1.45</td>
                    <td class="p-4 text-green-600 text-sm">Just now</td>
                    <td class="p-4"><span
                            class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold">Authorized</span></td>
                    <td class="p-4 text-right"><button
                            class="text-red-500 hover:text-red-700 font-medium text-sm">Revoke</button></td>
                </tr>
                <!-- Placeholder row -->
                <tr>
                    <td class="p-4 font-bold text-gray-700">Pastry Section Tab</td>
                    <td class="p-4 font-mono text-gray-500">192.168.1.46</td>
                    <td class="p-4 text-gray-400 text-sm">2 hours ago</td>
                    <td class="p-4"><span
                            class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold">Authorized</span></td>
                    <td class="p-4 text-right"><button
                            class="text-red-500 hover:text-red-700 font-medium text-sm">Revoke</button></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Add Device Modal -->
    <div id="addDeviceModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all scale-100">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800">Add New Device</h3>
                <button type="button" onclick="closeAddDeviceModal()" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="p-6">
                <form id="addDeviceForm" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Device Name <span class="text-red-500">*</span></label>
                        <input type="text" name="device_name" id="device_name" required
                            class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 outline-none"
                            placeholder="e.g. Main Kitchen Tablet">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">IP Address <span class="text-red-500">*</span></label>
                        <input type="text" name="ip_address" id="ip_address" required
                            class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 outline-none"
                            placeholder="e.g. 192.168.1.45"
                            pattern="^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$">
                        <p class="text-xs text-gray-400 mt-1">Format: xxx.xxx.xxx.xxx</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Description</label>
                        <textarea name="description" id="device_description" rows="2"
                            class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 outline-none resize-none"
                            placeholder="Optional description or location..."></textarea>
                    </div>
                    <div id="deviceError" class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm"></div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
                <button type="button" onclick="closeAddDeviceModal()"
                    class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 font-medium transition-colors">
                    Cancel
                </button>
                <button type="button" onclick="submitAddDevice()"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-colors">
                    <i data-lucide="plus" class="w-4 h-4 inline mr-1"></i> Add Device
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openAddDeviceModal() {
            const modal = document.getElementById('addDeviceModal');
            if (modal) {
                modal.classList.remove('hidden');
                lucide.createIcons();
            }
        }

        function closeAddDeviceModal() {
            const modal = document.getElementById('addDeviceModal');
            if (modal) {
                modal.classList.add('hidden');
                document.getElementById('addDeviceForm').reset();
                document.getElementById('deviceError').classList.add('hidden');
            }
        }

        function submitAddDevice() {
            const form = document.getElementById('addDeviceForm');
            const formData = new FormData(form);
            const errorDiv = document.getElementById('deviceError');

            // Basic validation
            const deviceName = document.getElementById('device_name').value.trim();
            const ipAddress = document.getElementById('ip_address').value.trim();

            if (!deviceName || !ipAddress) {
                errorDiv.textContent = 'Please fill in all required fields.';
                errorDiv.classList.remove('hidden');
                return;
            }

            // IP address format validation
            const ipPattern = /^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$/;
            if (!ipPattern.test(ipAddress)) {
                errorDiv.textContent = 'Please enter a valid IP address format (e.g., 192.168.1.45).';
                errorDiv.classList.remove('hidden');
                return;
            }

            // For now, just show success message (backend implementation needed)
            // TODO: Implement backend route and controller method
            errorDiv.classList.remove('hidden');
            errorDiv.classList.remove('bg-red-50', 'border-red-200', 'text-red-700');
            errorDiv.classList.add('bg-green-50', 'border-green-200', 'text-green-700');
            errorDiv.textContent = 'Device added successfully! (Backend implementation pending)';
            
            // Reset form after 2 seconds
            setTimeout(() => {
                closeAddDeviceModal();
            }, 2000);

            // Backend route implementation pending - uncomment when route is created
            // Route name should be: admin.devices.store
        }

        // Initialize modal event listeners when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Close modal on outside click
            const modal = document.getElementById('addDeviceModal');
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeAddDeviceModal();
                    }
                });
            }

            // Close modal on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeAddDeviceModal();
                }
            });
        });
    </script>
@endpush