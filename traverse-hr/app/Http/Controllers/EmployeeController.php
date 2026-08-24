<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeRequest;
use App\Models\Department;
use App\Models\DocumentTemplate;
use App\Models\Employee;
use App\Models\JobRole;
use App\Models\Outlet;
use App\Services\AuditLogger;
use App\Services\EmployeeCodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        // OutletScope on the Employee model already restricts this for
        // outlet_manager users — no manual filtering needed here.
        $query = Employee::with('outlet', 'jobRole')
            ->withCount([
                'documents as signed_documents_count' => fn ($q) => $q->where('status', 'signed')->whereNull('superseded_at'),
            ]);

        if ($search = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%");
            });
        }
        if ($request->filled('outlet_id')) {
            $query->where('outlet_id', $request->input('outlet_id'));
        }
        if ($request->filled('job_role_id')) {
            $query->where('job_role_id', $request->input('job_role_id'));
        }
        // Default to active staff — an exited employee shouldn't clutter the
        // day-to-day list, but must stay reachable via the filter.
        $status = $request->input('status', 'active');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $sort = in_array($request->input('sort'), ['name', 'employee_code', 'date_of_joining'], true)
            ? $request->input('sort') : 'name';
        $query->orderBy($sort, $sort === 'date_of_joining' ? 'desc' : 'asc');

        // How many documents each employee is *expected* to have, so the
        // list can show who still has paperwork outstanding.
        $expectedDocuments = DocumentTemplate::where('active', true)->count();

        return view('employees.index', [
            'employees' => $query->paginate(25)->withQueryString(),
            'outlets' => Outlet::orderBy('name')->get(),
            'jobRoles' => JobRole::orderBy('name')->get(),
            'expectedDocuments' => $expectedDocuments,
            'filters' => [
                'q' => $search, 'outlet_id' => $request->input('outlet_id'),
                'job_role_id' => $request->input('job_role_id'), 'status' => $status, 'sort' => $sort,
            ],
            'counts' => [
                'active' => Employee::where('status', 'active')->count(),
                'on_notice' => Employee::where('status', 'on_notice')->count(),
                'exited' => Employee::where('status', 'exited')->count(),
            ],
        ]);
    }

    public function create()
    {
        return $this->form(new Employee);
    }

    public function store(EmployeeRequest $request)
    {
        $outlet = Outlet::findOrFail($request->validated('outlet_id'));
        $data = $request->safe()->except(['pan', 'uan', 'esic_number', 'bank_account', 'ifsc']);
        $data['employee_code'] = EmployeeCodeGenerator::next($outlet);

        $employee = Employee::create($data);
        $this->fillStatutory($employee, $request);
        $this->storePhoto($request, $employee);

        AuditLogger::log('employee_created', $employee);

        return redirect()->route('employees.index')->with('status', 'Employee created.');
    }

    public function edit(Employee $employee)
    {
        return $this->form($employee);
    }

    public function update(EmployeeRequest $request, Employee $employee)
    {
        $data = $request->safe()->except(['pan', 'uan', 'esic_number', 'bank_account', 'ifsc']);
        $employee->update($data);
        $this->fillStatutory($employee, $request);
        $this->storePhoto($request, $employee);

        AuditLogger::log('employee_updated', $employee);

        return redirect()->route('employees.index')->with('status', 'Employee updated.');
    }

    /** Photos live on the private disk; this is the only way they are served. */
    public function photo(Request $request, Employee $employee)
    {
        abort_unless($request->user()->can('employee.view'), 403);
        abort_unless($employee->photo_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($employee->photo_path), 404);

        return \Illuminate\Support\Facades\Storage::disk('local')->response($employee->photo_path);
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        AuditLogger::log('employee_deleted', $employee);

        return redirect()->route('employees.index')->with('status', 'Employee removed.');
    }

    /**
     * §3.3/§6: unmasking a statutory identifier requires a reason and is
     * logged on every call. Route is gated by `password.confirm` +
     * `permission:employee.view-unmasked` in routes/web.php.
     */
    public function reveal(Request $request, Employee $employee, string $field)
    {
        $map = [
            'pan' => 'pan_encrypted',
            'uan' => 'uan_encrypted',
            'esic_number' => 'esic_number_encrypted',
            'bank_account' => 'bank_account_encrypted',
            'ifsc' => 'ifsc_encrypted',
        ];

        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
            'field' => [Rule::in(array_keys($map))],
        ]);

        abort_unless(array_key_exists($field, $map), 404);

        AuditLogger::logUnmask($employee, $field, $request->input('reason'));

        return response()->json(['value' => $employee->{$map[$field]}]);
    }

    private function fillStatutory(Employee $employee, EmployeeRequest $request): void
    {
        $mapped = array_filter([
            'pan_encrypted' => $request->validated('pan'),
            'uan_encrypted' => $request->validated('uan'),
            'esic_number_encrypted' => $request->validated('esic_number'),
            'bank_account_encrypted' => $request->validated('bank_account'),
            'ifsc_encrypted' => $request->validated('ifsc'),
        ], fn ($v) => ! is_null($v));

        if ($mapped) {
            $employee->forceFill($mapped)->save();
        }
    }

    /**
     * Onboarding photo. Accepts either a webcam capture (a base64 data URL
     * posted from the browser) or a normal file upload, so the same form
     * works on a laptop with a camera and on a phone. Stored on the private
     * disk and served through an authorising controller — never a public URL.
     */
    private function storePhoto(Request $request, Employee $employee): void
    {
        $captured = $request->input('photo_capture');
        $binary = null;
        if (is_string($captured) && str_starts_with($captured, 'data:image/')) {
            [$meta, $payload] = explode(',', $captured, 2);
            if (str_contains($meta, 'base64')) {
                $decoded = base64_decode($payload, true);
                // Guard against an oversized or non-image payload being posted.
                if ($decoded !== false && strlen($decoded) <= 4 * 1024 * 1024) {
                    $binary = $decoded;
                }
            }
        }

        if ($binary === null && $request->hasFile('photo')) {
            $file = $request->file('photo');
            if ($file->isValid()) {
                $binary = file_get_contents($file->getRealPath());
            }
        }

        if ($binary === null) {
            return;
        }

        // Verify it really is an image before it lands on disk.
        if (@getimagesizefromstring($binary) === false) {
            return;
        }

        $path = 'employee-photos/'.$employee->id.'-'.\Illuminate\Support\Str::random(12).'.jpg';
        \Illuminate\Support\Facades\Storage::disk('local')->put($path, $binary);

        $previous = $employee->photo_path;
        $employee->forceFill(['photo_path' => $path])->save();

        if ($previous && $previous !== $path) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($previous);
        }

        AuditLogger::log('employee_photo_updated', $employee);
    }

    private function form(Employee $employee)
    {
        return view('employees.form', [
            'employee' => $employee,
            'outlets' => Outlet::where('active', true)->orderBy('name')->get(),
            'jobRoles' => JobRole::where('active', true)->orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
        ]);
    }
}
