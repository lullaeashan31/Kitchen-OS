@extends('layouts.app')

@section('header')
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.sop.reviews.index') }}" class="p-2 hover:bg-gray-100 rounded-lg">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div class="flex-1">
            <h2 class="text-xl font-semibold">Review: {{ $run->checklist->name }}</h2>
            <p class="text-sm text-gray-500">
                Submitted by {{ $run->user->name }} on {{ $run->date->format('M d, Y') }} at
                {{ $run->completed_at ? $run->completed_at->format('g:i A') : 'N/A' }}
            </p>
        </div>
        @if($run->status !== 'approved')
            <form action="{{ route('admin.sop.reviews.approve_run', $run) }}" method="POST">
                @csrf
                <button type="submit"
                    class="bg-emerald-600 text-white px-6 py-2.5 rounded-xl font-bold flex items-center gap-2 hover:bg-emerald-700 shadow-sm transition-all text-sm">
                    <i data-lucide="check-circle" class="w-4 h-4"></i> Approve Full Run
                </button>
            </form>
        @endif
    </div>
@endsection

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        @foreach($run->checklist->items as $item)
            @php
                $completion = $completions->get($item->id);
            @endphp
            <div
                class="bg-white rounded-xl border {{ $completion && $completion->status === 'rejected' ? 'border-red-200' : 'border-gray-200' }} shadow-sm overflow-hidden">
                <div class="p-5 flex flex-col md:flex-row gap-6">
                    <!-- Item Info -->
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="font-bold text-gray-900">{{ $item->name }}</span>
                            @if($completion)
                                <span
                                    class="px-2 py-0.5 rounded-full text-xs font-bold 
                                                        {{ $completion->status === 'completed' ? 'bg-green-100 text-green-700' : '' }}
                                                        {{ $completion->status === 'rejected' ? 'bg-red-100 text-red-700' : '' }}
                                                        {{ $completion->status === 'resubmitted' ? 'bg-blue-100 text-blue-700' : '' }}">
                                    {{ ucfirst($completion->status) }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-500">Missing</span>
                            @endif
                        </div>
                        @if($item->description)
                            <p class="text-sm text-gray-500">{{ $item->description }}</p>
                        @endif

                        @if($completion && $completion->rejection_reason)
                            <div class="mt-4 p-3 bg-red-50 rounded-lg border border-red-100 text-sm">
                                <span class="font-bold text-red-700">Rejection Reason:</span>
                                <p class="text-red-600 mt-1">{{ $completion->rejection_reason }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Photo Evidence -->
                    @if($completion && $completion->photo_path)
                        <div class="w-full md:w-48">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Photo Proof</label>
                            <a href="{{ Storage::url($completion->photo_path) }}" target="_blank"
                                class="block bg-gray-100 rounded-lg overflow-hidden border border-gray-200 group">
                                <img src="{{ Storage::url($completion->photo_path) }}"
                                    class="w-full h-32 object-cover group-hover:opacity-90 transition-opacity">
                            </a>

                            @if($completion->photo_history)
                                <button type="button" onclick="showHistory('{{ $item->id }}')"
                                    class="mt-2 text-xs font-bold text-primary hover:underline flex items-center gap-1">
                                    <i data-lucide="history" class="w-3 h-3"></i> View History
                                </button>
                            @endif
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="md:border-l md:pl-6 flex items-center gap-3">
                        @if($completion && $completion->status !== 'rejected')
                            <button type="button" onclick="openRejectModal('{{ $completion->id }}')"
                                class="bg-white border border-red-200 text-red-600 px-4 py-2 rounded-lg text-sm font-bold hover:bg-red-50 transition-colors">
                                Reject
                            </button>
                        @endif

                        @if($completion && $completion->status === 'rejected')
                            <form action="{{ route('admin.sop.reviews.approve_item', [$run, $completion]) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-emerald-700 transition-colors">
                                    Approve Item
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Rejection Modal -->
    <div id="rejectModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 mx-4">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Reject SOP Item</h3>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Reason for Rejection</label>
                    <textarea name="reason" rows="4" required
                        class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary outline-none"
                        placeholder="Explain why this item is being rejected..."></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeRejectModal()"
                        class="px-4 py-2 text-gray-500 font-bold">Cancel</button>
                    <button type="submit"
                        class="px-6 py-2 bg-red-600 text-white font-bold rounded-lg hover:bg-red-700">Reject Item</button>
                </div>
            </form>
        </div>
    </div>

    <!-- History Modal -->
    <div id="historyModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full p-6 mx-4 max-h-[80vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-gray-900">Photo History</h3>
                <button onclick="closeHistoryModal()" class="p-2 hover:bg-gray-100 rounded-lg"><i data-lucide="x"
                        class="w-5 h-5"></i></button>
            </div>
            <div id="historyContent" class="space-y-6">
                <!-- Dynamic Content -->
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            const itemData = @json($run->completions->keyBy('item_id'));

            function openRejectModal(completionId) {
                const form = document.getElementById('rejectForm');
                form.action = `{{ url('admin/sop/reviews/' . $run->id . '/reject') }}/${completionId}`;
                document.getElementById('rejectModal').classList.remove('hidden');
                document.getElementById('rejectModal').classList.add('flex');
            }

            function closeRejectModal() {
                document.getElementById('rejectModal').classList.add('hidden');
                document.getElementById('rejectModal').classList.remove('flex');
            }

            function showHistory(itemId) {
                const completion = itemData[itemId];
                if (!completion || !completion.photo_history) return;

                const content = document.getElementById('historyContent');
                content.innerHTML = '';

                completion.photo_history.forEach((h, index) => {
                    content.innerHTML += `
                                <div class="border-b border-gray-100 pb-4 last:border-0">
                                    <div class="flex justify-between items-start mb-3">
                                        <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Version ${index + 1}</span>
                                        <span class="text-xs text-gray-400">${h.rejected_at}</span>
                                    </div>
                                    <div class="flex gap-4">
                                        <img src="/storage/${h.path.replace('public/', '')}" class="w-32 h-32 object-cover rounded-lg border">
                                        <div class="flex-1">
                                            <span class="text-xs font-bold text-red-500">Rejection Reason:</span>
                                            <p class="text-sm text-gray-600 mt-1">${h.reason}</p>
                                        </div>
                                    </div>
                                </div>
                            `;
                });

                document.getElementById('historyModal').classList.remove('hidden');
                document.getElementById('historyModal').classList.add('flex');
            }

            function closeHistoryModal() {
                document.getElementById('historyModal').classList.add('hidden');
                document.getElementById('historyModal').classList.remove('flex');
            }
        </script>
    @endpush
@endsection