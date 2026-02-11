@extends('layouts.app')

@section('header')
    <div class="flex items-center gap-4">
        <a href="{{ route('sop.index') }}" class="text-gray-500 hover:text-gray-700">
            <i data-lucide="arrow-left" class="w-6 h-6"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $checklist->name }}</h1>
            <p class="text-sm text-gray-500">Photo evidence mandatory for each step.</p>
        </div>
    </div>
@endsection

@section('content')
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 max-w-3xl mx-auto">
        <div class="space-y-6">
            @foreach($items as $item)
                @php
                    $log = $itemLogs[$item->id] ?? null;
                    $isDone = $log && $log->is_completed;
                @endphp

                <div class="border rounded-xl p-4 {{ $isDone ? 'bg-green-50 border-green-100' : 'bg-white border-gray-200' }}">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="font-bold text-gray-800 {{ $isDone ? 'line-through text-gray-500' : '' }}">
                                {{ $item->task }}
                            </h3>
                            @if($item->description)
                                <p class="text-sm text-gray-500 mt-1">{{ $item->description }}</p>
                            @endif
                        </div>
                        @if($isDone)
                            <span
                                class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold flex items-center gap-1">
                                <i data-lucide="check" class="w-3 h-3"></i> Done
                            </span>
                        @else
                            <span class="bg-orange-100 text-orange-700 px-3 py-1 rounded-full text-xs font-bold">Pending</span>
                        @endif
                    </div>

                    @if($isDone)
                        <div class="mt-2">
                            <span class="text-xs text-gray-500 mb-2 block">Photo Evidence:</span>
                            <div class="w-24 h-24 rounded-lg overflow-hidden border border-gray-200">
                                <img src="{{ asset('storage/' . $log->photo_path) }}" class="w-full h-full object-cover">
                            </div>
                            <div class="text-xs text-gray-400 mt-1">Completed: {{ $log->completed_at->format('h:i A') }}</div>
                        </div>
                    @else
                        <form action="{{ route('sop.update_item', ['checklist' => $checklist->id, 'itemId' => $item->id]) }}"
                            method="POST" enctype="multipart/form-data" class="mt-4">
                            @csrf
                            <div class="flex items-center gap-4">
                                <div class="flex-1">
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">
                                        Upload Proof <span class="text-red-500">*</span>
                                    </label>
                                    <input type="file" name="photo" accept="image/*" capture="environment" required
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                </div>
                                <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-4 py-2 font-bold shadow-md transition-all">
                                    Complete
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endsection