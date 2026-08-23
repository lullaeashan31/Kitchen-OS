@extends('layouts.app')
@section('title', $outlet->exists ? 'Edit outlet' : 'Add outlet')
@section('content')
<h1 style="font-size:1.3rem;">{{ $outlet->exists ? 'Edit outlet' : 'Add outlet' }}</h1>
<div class="card" style="max-width:480px;">
    <form method="POST" action="{{ $outlet->exists ? route('outlets.update', $outlet) : route('outlets.store') }}">
        @csrf
        @if ($outlet->exists) @method('PUT') @endif
        <label for="name">Name</label>
        <input id="name" name="name" value="{{ old('name', $outlet->name) }}" required>
        <label for="code">Code (employee-code prefix)</label>
        <input id="code" name="code" maxlength="10" value="{{ old('code', $outlet->code) }}" required>
        <label for="address">Address</label>
        <textarea id="address" name="address">{{ old('address', $outlet->address) }}</textarea>
        <label for="timezone">Timezone</label>
        <input id="timezone" name="timezone" value="{{ old('timezone', $outlet->timezone ?? 'Asia/Kolkata') }}" required>
        <label for="payroll_divisor_setting">Payroll divisor</label>
        <select id="payroll_divisor_setting" name="payroll_divisor_setting" required>
            @foreach (['calendar' => 'Calendar days in month', 'fixed_26' => 'Fixed 26', 'fixed_30' => 'Fixed 30'] as $value => $label)
                <option value="{{ $value }}" @selected(old('payroll_divisor_setting', $outlet->payroll_divisor_setting) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <label style="display:flex; align-items:center; gap:.4rem; flex-direction:row;">
            <input type="checkbox" name="active" value="1" style="width:auto;" @checked(old('active', $outlet->active ?? true))> <span>Active</span>
        </label>
        <button type="submit" class="btn" style="margin-top:1rem;">Save</button>
    </form>
</div>
@endsection
