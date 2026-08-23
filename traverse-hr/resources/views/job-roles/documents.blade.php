@extends('layouts.app')
@section('title', 'Documents for '.$jobRole->name)
@section('content')
<h1 style="font-size:1.3rem;">Documents — {{ $jobRole->name }}</h1>
<p class="muted">Choose which variant of each document type this role receives at onboarding. Leave on "Default" unless this role needs a different version (e.g. a Manager-specific policy).</p>
<div class="card" style="max-width:640px;">
    <form method="POST" action="{{ route('job-roles.documents.update', $jobRole) }}">
        @csrf @method('PUT')
        <table>
            <thead><tr><th>Document</th><th>Variant</th></tr></thead>
            <tbody>
            @foreach ($documentTemplates as $template)
                <tr>
                    <td data-label="Document">
                        {{ $template->name }}
                        @if ($template->conditional)<span class="muted">(if applicable)</span>@endif
                    </td>
                    <td data-label="Variant">
                        <select name="variant[{{ $template->id }}]">
                            <option value="">Default</option>
                            @foreach ($template->variants as $variant)
                                <option value="{{ $variant->id }}" @selected(($current[$template->id] ?? null) == $variant->id)>{{ $variant->label }}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <button type="submit" class="btn" style="margin-top:1rem;">Save assignments</button>
    </form>
</div>
<p style="margin-top:1rem;"><a href="{{ route('job-roles.index') }}">&larr; Back to job roles</a></p>
@endsection
