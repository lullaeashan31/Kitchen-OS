@extends('layouts.app')
@section('title', $employee->exists ? 'Edit employee' : 'Add employee')
@section('content')
<h1 style="font-size:1.3rem;">{{ $employee->exists ? 'Edit employee' : 'Add employee' }}</h1>
<div class="card" style="max-width:560px;">
    <form method="POST" enctype="multipart/form-data" action="{{ $employee->exists ? route('employees.update', $employee) : route('employees.store') }}">
        @csrf
        @if ($employee->exists) @method('PUT') @endif

        <label for="name">Full name</label>
        <input id="name" name="name" value="{{ old('name', $employee->name) }}" required>
        <label for="phone">Phone</label>
        <input id="phone" name="phone" value="{{ old('phone', $employee->phone) }}" required>
        <label for="emergency_contact_name">Emergency contact name</label>
        <input id="emergency_contact_name" name="emergency_contact_name" value="{{ old('emergency_contact_name', $employee->emergency_contact_name) }}">
        <label for="emergency_contact_phone">Emergency contact phone</label>
        <input id="emergency_contact_phone" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $employee->emergency_contact_phone) }}">
        <label for="address">Address</label>
        <textarea id="address" name="address">{{ old('address', $employee->address) }}</textarea>
        <label for="dob">Date of birth</label>
        <input id="dob" name="dob" type="date" value="{{ old('dob', optional($employee->dob)->format('Y-m-d')) }}">
        <label for="date_of_joining">Date of joining</label>
        <input id="date_of_joining" name="date_of_joining" type="date" value="{{ old('date_of_joining', optional($employee->date_of_joining)->format('Y-m-d')) }}" required>

        <label for="outlet_id">Outlet</label>
        <select id="outlet_id" name="outlet_id" required>
            @foreach ($outlets as $outlet)
                <option value="{{ $outlet->id }}" @selected(old('outlet_id', $employee->outlet_id) == $outlet->id)>{{ $outlet->name }}</option>
            @endforeach
        </select>
        <label for="job_role_id">Job role</label>
        <select id="job_role_id" name="job_role_id" required>
            @foreach ($jobRoles as $role)
                <option value="{{ $role->id }}" @selected(old('job_role_id', $employee->job_role_id) == $role->id)>{{ $role->name }}</option>
            @endforeach
        </select>
        <label for="designation">Designation</label>
        <input id="designation" name="designation" value="{{ old('designation', $employee->designation) }}">
        <label for="department_id">Department</label>
        <select id="department_id" name="department_id">
            <option value="">—</option>
            @foreach ($departments as $department)
                <option value="{{ $department->id }}" @selected(old('department_id', $employee->department_id) == $department->id)>{{ $department->name }}</option>
            @endforeach
        </select>
        <label for="employment_type">Employment type</label>
        <select id="employment_type" name="employment_type" required>
            @foreach (['full_time' => 'Full-time', 'part_time' => 'Part-time', 'trainee' => 'Trainee', 'contract' => 'Contract'] as $value => $label)
                <option value="{{ $value }}" @selected(old('employment_type', $employee->employment_type ?? 'full_time') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <label for="probation_end_date">Probation end date</label>
        <input id="probation_end_date" name="probation_end_date" type="date" value="{{ old('probation_end_date', optional($employee->probation_end_date)->format('Y-m-d')) }}">
        <label for="status">Status</label>
        <select id="status" name="status" required>
            @foreach (['active' => 'Active', 'on_notice' => 'On notice', 'exited' => 'Exited'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $employee->status ?? 'active') === $value)>{{ $label }}</option>
            @endforeach
        </select>


        <h2 style="font-size:1rem; margin-top:1.2rem;">Photo</h2>
        <p class="muted">Take it now with the camera, or upload one. Stored privately &mdash; never a public link.</p>

        <div id="photo-block" style="display:flex; gap:1rem; align-items:flex-start; flex-wrap:wrap;">
            <div>
                <img id="photo-preview"
                     src="{{ $employee->exists && $employee->photo_path ? route('employees.photo', $employee) : '' }}"
                     alt=""
                     style="width:104px;height:104px;border-radius:10px;object-fit:cover;border:1px solid var(--line);background:#eef1f5;{{ $employee->exists && $employee->photo_path ? '' : 'display:none;' }}">
                <div id="photo-placeholder"
                     style="width:104px;height:104px;border-radius:10px;border:1px dashed var(--line);background:#f4f6f9;
                            display:{{ $employee->exists && $employee->photo_path ? 'none' : 'flex' }};
                            align-items:center;justify-content:center;color:var(--muted);font-size:.75rem;text-align:center;">No photo</div>
            </div>

            <div style="flex:1; min-width:210px;">
                <video id="photo-video" playsinline autoplay muted
                       style="display:none;width:100%;max-width:260px;border-radius:10px;background:#000;"></video>
                <div style="display:flex; gap:.5rem; flex-wrap:wrap; margin-top:.4rem;">
                    <button type="button" id="photo-start" class="btn secondary">Use camera</button>
                    <button type="button" id="photo-shoot" class="btn" style="display:none;">Capture</button>
                    <button type="button" id="photo-retake" class="btn secondary" style="display:none;">Retake</button>
                </div>
                <label for="photo" style="margin-top:.6rem;">or upload a file</label>
                <input id="photo" type="file" name="photo" accept="image/*">
                <p class="muted" id="photo-msg" style="margin-top:.4rem;"></p>
            </div>
        </div>
        <input type="hidden" name="photo_capture" id="photo_capture">

        <h2 style="font-size:1rem; margin-top:1.2rem;">Statutory identifiers</h2>
        <p class="muted">Stored encrypted. Only Admin/HR/Accounts can view these unmasked, and every unmask is logged with a reason.</p>
        @if ($employee->exists)
            <p class="masked">PAN: {{ $employee->maskedPan() ?? '—' }} &middot; Bank a/c: {{ $employee->maskedBankAccount() ?? '—' }}</p>
        @endif
        <label for="pan">PAN</label>
        <input id="pan" name="pan" placeholder="{{ $employee->exists ? 'Leave blank to keep current value' : '' }}">
        <label for="uan">UAN</label>
        <input id="uan" name="uan" placeholder="{{ $employee->exists ? 'Leave blank to keep current value' : '' }}">
        <label for="esic_number">ESIC number</label>
        <input id="esic_number" name="esic_number" placeholder="{{ $employee->exists ? 'Leave blank to keep current value' : '' }}">
        <label for="bank_account">Bank account number</label>
        <input id="bank_account" name="bank_account" placeholder="{{ $employee->exists ? 'Leave blank to keep current value' : '' }}">
        <label for="ifsc">IFSC</label>
        <input id="ifsc" name="ifsc" placeholder="{{ $employee->exists ? 'Leave blank to keep current value' : '' }}">

        <h2 style="font-size:1rem; margin-top:1.2rem;">Aadhaar (restricted)</h2>
        <p class="muted">Only the last 4 digits are stored, per the Aadhaar Act / DPDP Act constraint — no full Aadhaar number field exists in this system.</p>
        <label for="aadhaar_last_four">Aadhaar last 4 digits</label>
        <input id="aadhaar_last_four" name="aadhaar_last_four" maxlength="4" value="{{ old('aadhaar_last_four', $employee->aadhaar_last_four) }}">

        <button type="submit" class="btn" style="margin-top:1rem;">Save</button>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
  var video = document.getElementById('photo-video'),
      startBtn = document.getElementById('photo-start'),
      shootBtn = document.getElementById('photo-shoot'),
      retakeBtn = document.getElementById('photo-retake'),
      preview = document.getElementById('photo-preview'),
      placeholder = document.getElementById('photo-placeholder'),
      field = document.getElementById('photo_capture'),
      msg = document.getElementById('photo-msg'),
      stream = null;

  function stop() { if (stream) { stream.getTracks().forEach(function (t) { t.stop(); }); stream = null; } }

  startBtn.addEventListener('click', function () {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      msg.textContent = 'This browser cannot use the camera here. Upload a file instead.';
      return;
    }
    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: 640, height: 640 }, audio: false })
      .then(function (s) {
        stream = s; video.srcObject = s; video.style.display = 'block';
        startBtn.style.display = 'none'; shootBtn.style.display = 'inline-block'; msg.textContent = '';
      })
      .catch(function () {
        // Camera needs HTTPS in most browsers, and permission from the user.
        msg.textContent = 'Camera unavailable — allow camera access, or use the file upload below. (Cameras need a secure https:// connection.)';
      });
  });

  shootBtn.addEventListener('click', function () {
    var side = Math.min(video.videoWidth, video.videoHeight) || 480,
        c = document.createElement('canvas');
    c.width = c.height = side;
    c.getContext('2d').drawImage(video,
      (video.videoWidth - side) / 2, (video.videoHeight - side) / 2, side, side, 0, 0, side, side);
    field.value = c.toDataURL('image/jpeg', 0.85);
    preview.src = field.value; preview.style.display = 'block'; placeholder.style.display = 'none';
    stop(); video.style.display = 'none';
    shootBtn.style.display = 'none'; retakeBtn.style.display = 'inline-block';
    msg.textContent = 'Captured. Save the form to keep it.';
  });

  retakeBtn.addEventListener('click', function () {
    field.value = ''; retakeBtn.style.display = 'none'; startBtn.style.display = 'inline-block';
    msg.textContent = '';
  });

  document.getElementById('photo').addEventListener('change', function (e) {
    var f = e.target.files[0]; if (!f) return;
    field.value = '';
    var r = new FileReader();
    r.onload = function (ev) { preview.src = ev.target.result; preview.style.display = 'block'; placeholder.style.display = 'none'; };
    r.readAsDataURL(f);
  });

  window.addEventListener('pagehide', stop);
})();
</script>
@endpush
