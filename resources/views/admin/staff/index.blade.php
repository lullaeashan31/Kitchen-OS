@extends('layouts.app')

@php
    use Illuminate\Support\Str;
@endphp

@section('header')
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">Staff Management</h1>
            <p class="text-gray-500 mt-1">Manage kitchen team members, roles, and access.</p>
        </div>
        <a href="{{ route('admin.staff.create') }}" 
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg transform hover:scale-105 transition-all text-sm">
            <i data-lucide="user-plus" class="w-5 h-5"></i>
            <span>Add New Staff</span>
        </a>
    </div>
@endsection

@section('content')
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
        
        @if($staff->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                        <th class="p-6 font-semibold">Employee Details</th>
                        <th class="p-6 font-semibold">Staff Code</th>
                        <th class="p-6 font-semibold">Status</th>
                        <th class="p-6 font-semibold">Onboarding Data</th>
                        <th class="p-6 font-semibold">Role &amp; Access</th>
                        <th class="p-6 font-semibold">Joined Date</th>
                        <th class="p-6 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($staff as $user)
                        <tr class="group hover:bg-blue-50/30 transition-colors duration-200">
                            <td class="p-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-md overflow-hidden bg-gray-200 border-2 border-white ring-2 ring-gray-50">
                                        @if($user->profile_photo_path)
                                            <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
                                                {{ substr($user->name, 0, 2) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-900 text-lg group-hover:text-blue-600 transition-colors">{{ $user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $user->phone ?? 'N/A' }}</div>
                                        @if($user->employeeProfile)
                                            <div class="text-xs text-gray-400 mt-1">
                                                @if($user->employeeProfile->address)
                                                    <i data-lucide="map-pin" class="w-3 h-3 inline"></i> {{ Str::limit($user->employeeProfile->address, 30) }}
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="p-6">
                                <span class="font-mono text-gray-600 bg-gray-100 px-3 py-1 rounded-lg text-sm font-bold tracking-widest border border-gray-200">
                                    {{ $user->staff_code }}
                                </span>
                            </td>
                            <td class="p-6">
                                @if($user->onboarding_status === 'pending')
                                    <div class="flex flex-col gap-1">
                                        <span class="inline-flex items-center w-fit gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-yellow-100 text-yellow-700 uppercase">Pending Form</span>
                                        @php $token = $user->onboardingTokens()->where('is_used', false)->latest()->first(); @endphp
                                        @if($token)
                                            <button onclick="copyToClipboard('{{ route('onboarding.wizard', $token->token) }}')" class="text-[10px] text-blue-600 hover:underline flex items-center gap-1">
                                                <i data-lucide="copy" class="w-2.5 h-2.5"></i> Copy Link
                                            </button>
                                        @endif
                                    </div>
                                @elseif($user->onboarding_status === 'onboarding_completed')
                                    <div class="flex flex-col gap-1">
                                        <span class="inline-flex items-center w-fit gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-100 text-orange-700 uppercase">Wait Approval</span>
                                        @if($user->employeeProfile)
                                            <span class="text-[10px] text-gray-500 mt-1">✓ Form Completed</span>
                                        @endif
                                    </div>
                                @else
                                    <div class="flex flex-col gap-1">
                                        @if($user->attendance_status === 'active')
                                            <span class="inline-flex items-center w-fit gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase animate-pulse border border-emerald-200 shadow-sm">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-0.5"></span>
                                                Clocked In
                                            </span>
                                        @else
                                            <span class="inline-flex items-center w-fit gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-500 uppercase border border-gray-200">
                                                Clocked Out
                                            </span>
                                        @endif
                                        @if($user->employeeProfile)
                                            <span class="text-[10px] text-gray-400 mt-1 italic">Profile Verified</span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="p-6">
                                @if($user->employeeProfile)
                                    <div class="flex flex-col gap-1 text-xs">
                                        @if($user->employeeProfile->emergency_contact_name)
                                            <div class="text-gray-600">
                                                <i data-lucide="phone" class="w-3 h-3 inline"></i> 
                                                {{ $user->employeeProfile->emergency_contact_name }}
                                            </div>
                                        @endif
                                        @if($user->employeeProfile->bank_name)
                                            <div class="text-gray-500">
                                                <i data-lucide="credit-card" class="w-3 h-3 inline"></i> 
                                                {{ $user->employeeProfile->bank_name }}
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">No data yet</span>
                                @endif
                            </td>
                            <td class="p-6">
                                <div class="flex flex-col gap-1">
                                    @if($user->jobRole)
                                        <span class="inline-flex items-center w-fit gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700">
                                            <i data-lucide="briefcase" class="w-3 h-3"></i>
                                            {{ $user->jobRole->name }}
                                        </span>
                                    @endif
                                    <span class="inline-flex items-center w-fit gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700">
                                        <i data-lucide="badge-check" class="w-3 h-3"></i>
                                        {{ $user->role->label() }}
                                    </span>
                                    @if($user->permissions->count() > 0)
                                        <span class="text-xs text-gray-400 mt-1">+{{ $user->permissions->count() }} Permissions</span>
                                    @endif
                                </div>
                            </td>
                            <td class="p-6 text-sm text-gray-500">
                                <div class="flex items-center gap-1">
                                    <i data-lucide="calendar" class="w-3 h-3"></i>
                                    {{ $user->created_at->format('M d, Y') }}
                                </div>
                            </td>
                            <td class="p-6 text-right">
                                <div class="flex items-center justify-end gap-3 opacity-100 transition-opacity">
                                    <button onclick="showStaffDetails('{{ route('admin.staff.show', $user->id) }}')"
                                            class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-purple-50 text-purple-600 hover:bg-purple-100 hover:text-purple-700 transition-colors border border-purple-200 shadow-sm"
                                            title="View Details">
                                        <i data-lucide="eye" class="w-5 h-5"></i>
                                    </button>
                                    @if($user->onboarding_status === 'onboarding_completed')
                                        <form action="{{ route('admin.staff.approve', $user->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            <button type="submit" 
                                                    class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 hover:text-green-700 transition-colors border border-green-200 shadow-sm"
                                                    title="Approve Onboarding">
                                                <i data-lucide="user-check" class="w-5 h-5"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.staff.permissions.edit', $user->id) }}"
                                       class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 transition-colors border border-blue-200 shadow-sm"
                                       title="Manage Permissions">
                                        <i data-lucide="lock" class="w-5 h-5"></i>
                                    </a>
                                    <a href="{{ route('admin.staff.edit', $user->id) }}"
                                       class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-orange-50 text-orange-600 hover:bg-orange-100 hover:text-orange-700 transition-colors border border-orange-200 shadow-sm"
                                       title="Edit details">
                                        <i data-lucide="pencil" class="w-5 h-5"></i>
                                    </a>
                                    
                                    <form action="{{ route('admin.staff.destroy', $user->id) }}" method="POST" class="inline-block" 
                                          onsubmit="return confirm('Are you sure you want to delete this staff member?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="inline-flex items-center justify-center w-10 h-10 rounded-lg btn-danger"
                                                title="Delete user">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"/>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                                <line x1="10" y1="11" x2="10" y2="17"/>
                                                <line x1="14" y1="11" x2="14" y2="17"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
            <div class="p-16 text-center">
                <div class="w-24 h-24 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i data-lucide="users" class="w-10 h-10"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">No Staff Members Yet</h3>
                <p class="text-gray-500 max-w-sm mx-auto mb-8">Get started by adding your first kitchen staff member to manage attendance and recipes.</p>
                <a href="{{ route('admin.staff.create') }}" class="btn btn-primary bg-blue-600 text-white px-8 py-3 rounded-xl shadow-lg hover:bg-blue-700 transition-all">
                    Add Staff Member
                </a>
            </div>
        @endif
    </div>

    <!-- Staff Details Modal -->
    <div id="staffDetailsModal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 hidden flex flex-col items-center justify-center p-4 sm:p-6 opacity-0 transition-opacity duration-300">
        <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full h-[85vh] flex flex-col overflow-hidden transform scale-95 transition-transform duration-300" id="staffDetailsModalBox">
            <div class="bg-gray-50 border-b border-gray-100 p-6 flex justify-between items-center shrink-0">
                <h2 class="text-2xl font-bold text-gray-800">Staff Member Details</h2>
                <button onclick="closeStaffDetails()" class="text-gray-400 hover:text-gray-600 hover:bg-gray-200 p-2 rounded-lg transition-colors">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <div id="staffDetailsContent" class="p-6 overflow-y-auto flex-1 custom-scrollbar">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Onboarding link copied to clipboard!');
            });
        }

        function showStaffDetails(url) {
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('staffDetailsContent').innerHTML = html;
                    const modal = document.getElementById('staffDetailsModal');
                    const modalBox = document.getElementById('staffDetailsModalBox');
                    
                    modal.classList.remove('hidden');
                    
                    setTimeout(() => {
                        modal.classList.remove('opacity-0');
                        modal.classList.add('opacity-100');
                        modalBox.classList.remove('scale-95');
                        modalBox.classList.add('scale-100');
                    }, 10);
                    
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                })
                .catch(error => {
                    console.error('Error loading staff details:', error);
                    alert('Error loading staff details. Please try again.');
                });
        }

        function closeStaffDetails() {
            const modal = document.getElementById('staffDetailsModal');
            const modalBox = document.getElementById('staffDetailsModalBox');
            
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0');
            modalBox.classList.remove('scale-100');
            modalBox.classList.add('scale-95');
            
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        document.getElementById('staffDetailsModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeStaffDetails();
            }
        });
    </script>
@endsection