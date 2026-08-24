@extends('layouts.app')
@section('title', $signatory->exists ? 'Edit signatory' : 'Add signatory')
@section('content')
<h1 style="font-size:1.3rem;">{{ $signatory->exists ? 'Edit signatory' : 'Add signatory' }}</h1>
<div class="card" style="max-width:560px;">
    <form method="POST" enctype="multipart/form-data"
          action="{{ $signatory->exists ? route('company-signatories.update', $signatory) : route('company-signatories.store') }}">
        @csrf
        @if ($signatory->exists) @method('PUT') @endif

        <label for="name">Name</label>
        <input id="name" name="name" value="{{ old('name', $signatory->name) }}" required>

        <label for="designation">Designation</label>
        <input id="designation" name="designation" value="{{ old('designation', $signatory->designation) }}"
               placeholder="e.g. Founder, General Manager" required>

        @if ($signatory->exists && $signatory->signature_image_path)
            <label>Current signature</label>
            <img src="{{ route('company-signatories.image', $signatory) }}" alt=""
                 style="height:60px;max-width:100%;object-fit:contain;border:1px solid var(--line);border-radius:8px;background:#fff;padding:.4rem;">
        @endif

        <h2 style="font-size:1rem; margin-top:1.2rem;">Signature</h2>
        <p class="muted">Sign on the pad below, or upload a scan/photo of your signature. Uploading works best on a plain white background.</p>

        @include('partials.signature-pad', ['label' => 'Draw signature'])

        <label for="signature_file" style="margin-top:.6rem;">or upload an image</label>
        <input id="signature_file" type="file" name="signature_file" accept="image/*">

        @if ($signatory->exists)
            <label style="display:flex; align-items:center; gap:.4rem; flex-direction:row; margin-top:.8rem;">
                <input type="checkbox" name="active" value="1" style="width:auto;" @checked(old('active', $signatory->active))> <span>Active</span>
            </label>
        @endif

        <button type="submit" class="btn" style="margin-top:1rem;">Save</button>
    </form>
</div>
<p><a href="{{ route('company-signatories.index') }}">&larr; Back to signatories</a></p>
@endsection
