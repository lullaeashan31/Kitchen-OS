@extends('layouts.app')

@section('header')
    <h1>Import / Export Data</h1>
@endsection

@section('content')
    <div class="flex gap-4" style="flex-wrap: wrap;">
        <!-- Import -->
        <div style="flex: 1; min-width: 300px;">
            <div class="card">
                <h2><i data-lucide="upload-cloud"></i> Import Recipes</h2>
                <br>
                <p class="text-muted text-sm mb-4">Upload an Excel file to bulk create or update recipes. Use the template
                    to ensure correct formatting.</p>

                @if(session('warning'))
                    <div class="alert alert-warning">
                        {{ session('warning') }}
                        <br>
                        @if(session('error_download_id'))
                            <a href="{{ route('excel.download_errors', session('error_download_id')) }}"
                                style="text-decoration: underline; font-weight: 600;">Download Error Report</a>
                        @endif
                    </div>
                @endif

                <form action="{{ route('excel.import') }}" method="POST" enctype="multipart/form-data"
                    style="margin-top: 1rem;">
                    @csrf
                    <div class="form-group">
                        <input type="file" name="file" class="form-control" required accept=".xlsx,.xls,.csv">
                    </div>
                    <button type="submit" class="btn btn-primary w-full" style="justify-content: center;">Upload &
                        Import</button>
                </form>

                <div style="margin-top: 2rem; border-top: 1px solid var(--border-color); padding-top: 1rem;">
                    <h3>Resources</h3>
                    <div class="flex flex-col gap-2 mt-2">
                        <a href="{{ route('excel.template') }}" class="btn btn-secondary w-full"
                            style="justify-content: center;">
                            <i data-lucide="download"></i> Download Template
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Export -->
        <div style="flex: 1; min-width: 300px;">
            <div class="card">
                <h2><i data-lucide="download-cloud"></i> Export Data</h2>
                <br>
                <p class="text-muted text-sm mb-4">Download system data for backup or analysis.</p>

                <div class="flex flex-col gap-2">
                    <a href="{{ route('excel.export_recipes') }}" class="btn btn-secondary w-full"
                        style="justify-content: center;">
                        <i data-lucide="file-spreadsheet"></i> Export All Recipes
                    </a>
                    <a href="{{ route('excel.export_cost') }}" class="btn btn-secondary w-full"
                        style="justify-content: center;">
                        <i data-lucide="dollar-sign"></i> Export Cost Summary
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection