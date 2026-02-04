@extends('layouts.app')

@section('header')
    <div class="flex items-center gap-2">
        <a href="{{ route('categories.index') }}" class="text-muted"><i data-lucide="arrow-left"></i></a>
        <h1>Create Category</h1>
    </div>
@endsection

@section('content')
    <div class="card" style="max-width: 500px; margin: 0 auto;">
        <form action="{{ route('categories.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Category Name</label>
                <input type="text" name="name" class="form-control" required value="{{ old('name') }}"
                    placeholder="e.g. Appetizers">
            </div>
            <button type="submit" class="btn btn-primary w-full">Create Category</button>
        </form>
    </div>
@endsection