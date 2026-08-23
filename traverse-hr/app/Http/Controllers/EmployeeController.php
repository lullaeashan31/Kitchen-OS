<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobRole;
use App\Models\Outlet;
use App\Services\AuditLogger;
use App\Services\EmployeeCodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index()
    {
        // OutletScope on the Employee model already restricts this for
        // outlet_manager users — no manual filtering needed here.
        return view('employees.index', [
            'employees' => Employee::with('outlet', 'jobRole')->orderBy('name')->paginate(20),
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

        AuditLogger::log('employee_updated', $employee);

        return redirect()->route('employees.index')->with('status', 'Employee updated.');
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
