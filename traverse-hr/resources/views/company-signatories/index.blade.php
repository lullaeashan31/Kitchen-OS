@extends('layouts.app')
@section('title', 'Company signatories')
@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap;">
    <h1 style="font-size:1.3rem;">Company signatories</h1>
    <a href="{{ route('company-signatories.create') }}" class="btn">Add signatory</a>
</div>
<p class="muted">People who sign on behalf of Traverse Inc. Their signature is placed on the document automatically when an employee signs. The default is used unless another is chosen during the session.</p>

<div class="card">
    <table>
        <thead><tr><th>Signature</th><th>Name</th><th>Designation</th><th>Default</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse ($signatories as $s)
            <tr>
                <td data-label="Signature">
                    @if ($s->signature_image_path)
                        <img src="{{ route('company-signatories.image', $s) }}" alt=""
                             style="height:38px;max-width:150px;object-fit:contain;display:block;">
                    @else
                        <span class="muted">none uploaded</span>
                    @endif
                </td>
                <td data-label="Name">{{ $s->name }}</td>
                <td data-label="Designation">{{ $s->designation }}</td>
                <td data-label="Default">
                    @if ($s->is_default)
                        <span style="color:#146c43;">Default</span>
                    @else
                        <form method="POST" action="{{ route('company-signatories.make-default', $s) }}">
                            @csrf
                            <button type="submit" style="background:none;border:none;color:var(--accent);cursor:pointer;padding:0;font:inherit;">Make default</button>
                        </form>
                    @endif
                </td>
                <td data-label="Status">{{ $s->active ? 'Active' : 'Inactive' }}</td>
                <td data-label="Edit"><a href="{{ route('company-signatories.edit', $s) }}">Edit</a></td>
            </tr>
        @empty
            <tr><td colspan="6" class="muted">No signatories yet. Add yourself first — you'll be the default.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
