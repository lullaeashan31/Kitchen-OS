@extends('layouts.app')

@section('header')
    <h1 class="text-2xl font-bold text-gray-800">POS Integration</h1>
    <p class="text-sm text-gray-500">Upload daily sales report to deduct inventory.</p>
@endsection

@section('content')
    <div class="max-w-xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="upload-cloud" class="w-8 h-8"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-800">Upload Sales Report</h2>
                <p class="text-gray-500 text-sm mt-1">Supports .csv files with 'Item Name' and 'Quantity' columns.</p>
            </div>

            <form action="{{ route('pos.parse') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <div
                    class="border-2 border-dashed border-gray-200 rounded-xl p-8 hover:bg-gray-50 transition-colors text-center cursor-pointer relative">
                    <input type="file" name="file" accept=".csv" required
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                        onchange="document.getElementById('fileName').innerText = this.files[0].name">

                    <div class="pointer-events-none">
                        <i data-lucide="file-spreadsheet" class="w-8 h-8 text-gray-400 mx-auto mb-2"></i>
                        <span class="block text-sm font-medium text-gray-600" id="fileName">Click to select file</span>
                    </div>
                </div>

                <button type="submit"
                    class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-md transition-all flex items-center justify-center gap-2">
                    <i data-lucide="search"></i> Analyze & Preview
                </button>
            </form>
        </div>
    </div>
@endsection