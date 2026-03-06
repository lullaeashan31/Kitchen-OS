@extends('layouts.app')

@section('header')
    <div class="flex items-center gap-3">
        <a href="{{ route('sop.index') }}" class="p-2 hover:bg-gray-100 rounded-lg">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h2 class="text-xl font-semibold">{{ $checklist->name }}</h2>
            <p class="text-sm text-gray-500">{{ $checklist->items->count() }} Tasks to complete today</p>
        </div>
    </div>
@endsection

@section('content')
    <div class="max-w-3xl mx-auto space-y-6 pb-20">
        <div id="progress-bar-container" class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm sticky top-4 z-10">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-bold text-gray-700">Checklist Progress</span>
                <span id="progress-text" class="text-sm font-bold text-primary">0%</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2.5">
                <div id="progress-bar" class="bg-primary h-2.5 rounded-full transition-all duration-500" style="width: 0%">
                </div>
            </div>
        </div>

        <div class="space-y-4">
            @foreach($checklist->items as $item)
                @php
                    $completion = $completions->get($item->id);
                    $isDone = $completion && ($completion->is_completed || $completion->status === 'resubmitted');
                    $isRejected = $completion && $completion->status === 'rejected';
                @endphp
                <div class="item-card bg-white p-5 rounded-xl border {{ $isDone ? 'border-green-200 bg-green-50/30' : ($isRejected ? 'border-red-200 bg-red-50/30' : 'border-gray-200') }} shadow-sm transition-all"
                    data-item-id="{{ $item->id }}" data-photo-required="{{ $item->is_photo_required ? 'true' : 'false' }}"
                    data-completed="{{ $isDone ? 'true' : 'false' }}">
                    <div class="flex items-start gap-4">
                        <div class="mt-1">
                            <div class="checkbox-container w-6 h-6 rounded-md border-2 
                                                    {{ $isDone ? 'bg-green-500 border-green-500 text-white' : ($isRejected ? 'bg-red-500 border-red-500 text-white' : 'border-gray-300') }} 
                                                    flex items-center justify-center transition-colors">
                                @if($isRejected)
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                @else
                                    <i data-lucide="check" class="w-4 h-4 {{ $isDone ? '' : 'hidden' }}"></i>
                                @endif
                            </div>
                        </div>

                        <div class="flex-1">
                            <h4 class="font-bold text-gray-900 {{ $isDone ? 'line-through text-gray-500' : '' }}">
                                {{ $item->name }}
                            </h4>
                            @if($item->description)
                                <p class="text-sm text-gray-500 mt-1">{{ $item->description }}</p>
                            @endif

                            @if($isRejected)
                                <div class="mt-2 p-3 bg-red-100/50 rounded-lg border border-red-200 text-sm">
                                    <span class="font-bold text-red-700">Rejected:</span>
                                    <p class="text-red-600 mt-0.5">{{ $completion->rejection_reason }}</p>
                                </div>
                            @endif

                            @if($item->is_photo_required)
                                <div class="mt-4">
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        <i data-lucide="camera" class="w-3 h-3 inline mr-1"></i> Photo Proof Required
                                    </label>

                                    <div class="flex items-center gap-4">
                                        <div
                                            class="photo-preview w-20 h-20 bg-gray-100 rounded-lg border-2 border-dashed border-gray-200 flex items-center justify-center overflow-hidden">
                                            @if(($isDone || $isRejected) && $completion->photo_path)
                                                <img src="{{ $completion->photo_url }}" class="w-full h-full object-cover">
                                            @else
                                                <i data-lucide="image" class="w-6 h-6 text-gray-300"></i>
                                            @endif
                                        </div>

                                        @if(!$isDone || $isRejected)
                                            <label
                                                class="cursor-pointer bg-white border border-gray-200 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-50">
                                                <span>{{ $isRejected ? 'Retake Photo' : 'Take/Upload Photo' }}</span>
                                                <input type="file" class="photo-input hidden" accept="image/*" capture="environment">
                                            </label>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if(!$item->is_photo_required && (!$isDone || $isRejected))
                                <button onclick="toggleItem({{ $item->id }})"
                                    class="mt-3 px-4 py-1.5 {{ $isRejected ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700' }} text-xs font-bold rounded-lg hover:opacity-90">
                                    {{ $isRejected ? 'Mark Re-Completed' : 'Mark as Complete' }}
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Submit Section -->
        <div class="fixed bottom-0 left-0 right-0 p-4 bg-white border-t border-gray-200 lg:left-[310px]">
            <div class="max-w-3xl mx-auto flex justify-between items-center">
                <div class="text-sm font-medium text-gray-500">
                    <span id="items-done-count">0</span> of {{ $checklist->items->count() }} completed
                </div>
                <form action="{{ route('sop.complete', $checklist) }}" method="POST">
                    @csrf
                    <button type="submit" id="submit-btn" disabled
                        class="px-8 py-3 bg-gray-200 text-gray-400 font-bold rounded-xl cursor-not-allowed transition-all">
                        Finish & Submit
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function updateProgress() {
                const total = {{ $checklist->items->count() }};
                const done = document.querySelectorAll('.item-card[data-completed="true"]').length;
                const percent = Math.round((done / total) * 100);

                const progressBar = document.getElementById('progress-bar');
                if (progressBar) progressBar.style.width = percent + '%';

                const progressText = document.getElementById('progress-text');
                if (progressText) progressText.textContent = percent + '%';

                const itemsDoneCount = document.getElementById('items-done-count');
                if (itemsDoneCount) itemsDoneCount.textContent = done;

                const btn = document.getElementById('submit-btn');
                if (!btn) return;

                if (percent === 100) {
                    btn.disabled = false;
                    btn.classList.remove('bg-gray-200', 'text-gray-400', 'cursor-not-allowed');
                    btn.classList.add('bg-success', 'text-white', 'shadow-lg', 'shadow-green-100');
                } else {
                    btn.disabled = true;
                    btn.classList.add('bg-gray-200', 'text-gray-400', 'cursor-not-allowed');
                    btn.classList.remove('bg-success', 'text-white', 'shadow-lg', 'shadow-green-100');
                }
            }

            async function toggleItem(itemId, photoFile = null) {
                const card = document.querySelector(`.item-card[data-item-id="${itemId}"]`);
                const formData = new FormData();
                if (photoFile) formData.append('photo', photoFile);

                try {
                    const response = await fetch(`{{ url('sop/' . $checklist->id . '/item') }}/${itemId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const result = await response.json();
                    if (result.success) {
                        // Update UI state
                        card.setAttribute('data-completed', 'true');
                        card.classList.add('border-green-200', 'bg-green-50/30');
                        card.classList.remove('border-gray-200', 'border-red-200', 'bg-red-50/30');

                        const checkbox = card.querySelector('.checkbox-container');
                        checkbox.classList.add('bg-green-500', 'border-green-500', 'text-white');
                        checkbox.classList.remove('border-gray-300', 'bg-red-500', 'border-red-500');

                        // Update Icon
                        checkbox.innerHTML = '<i data-lucide="check" class="w-4 h-4"></i>';
                        if (window.lucide) window.lucide.createIcons();

                        card.querySelector('h4').classList.add('line-through', 'text-gray-500');

                        // Remove buttons/inputs
                        const btn = card.querySelector('button');
                        if (btn) btn.remove();
                        const label = card.querySelector('label.cursor-pointer');
                        if (label) label.remove();

                        // Hide rejection reason if present
                        const rejectionBox = card.querySelector('.bg-red-100\\/50');
                        if (rejectionBox) rejectionBox.remove();

                        updateProgress();

                        // Reliability fallback: if server says all_done, force enable submit
                        if (result.all_done) {
                            const submitBtn = document.getElementById('submit-btn');
                            if (submitBtn) {
                                submitBtn.disabled = false;
                                submitBtn.classList.remove('bg-gray-200', 'text-gray-400', 'cursor-not-allowed');
                                submitBtn.classList.add('bg-success', 'text-white', 'shadow-lg', 'shadow-green-100');
                            }
                        }
                    } else {
                        alert(result.message || 'Something went wrong.');
                    }
                } catch (error) {
                    console.error('Error updating item:', error);
                    alert('Failed to update item. Please try again.');
                }
            }

            // Handle Photo Uploads
            document.querySelectorAll('.photo-input').forEach(input => {
                input.addEventListener('change', function (e) {
                    if (this.files && this.files[0]) {
                        const itemId = this.closest('.item-card').dataset.itemId;
                        const preview = this.closest('.item-card').querySelector('.photo-preview');

                        // Show local preview immediately for weightless feel
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            preview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                        };
                        reader.readAsDataURL(this.files[0]);

                        toggleItem(itemId, this.files[0]);
                    }
                });
            });

            // Initial check
            updateProgress();
        </script>
    @endpush
@endsection