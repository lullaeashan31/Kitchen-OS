<?php

namespace App\Http\Controllers;

use App\Http\Requests\JobRoleRequest;
use App\Models\Department;
use App\Models\JobRole;
use App\Models\Outlet;
use Illuminate\Http\RedirectResponse;

class JobRoleController extends Controller
{
    public function index()
    {
        return view('job-roles.index', [
            'jobRoles' => JobRole::with('outlet', 'department')->orderBy('sort_order')->orderBy('name')->paginate(20),
        ]);
    }

    public function create()
    {
        return $this->form(new JobRole);
    }

    public function store(JobRoleRequest $request)
    {
        JobRole::create($request->validated());

        return redirect()->route('job-roles.index')->with('status', 'Job role created.');
    }

    public function edit(JobRole $jobRole)
    {
        return $this->form($jobRole);
    }

    public function update(JobRoleRequest $request, JobRole $jobRole)
    {
        $jobRole->update($request->validated());

        return redirect()->route('job-roles.index')->with('status', 'Job role updated.');
    }

    public function destroy(JobRole $jobRole): RedirectResponse
    {
        try {
            $jobRole->delete();
        } catch (\RuntimeException $e) {
            return back()->withErrors(['job_role' => $e->getMessage()]);
        }

        return redirect()->route('job-roles.index')->with('status', 'Job role deactivated.');
    }

    private function form(JobRole $jobRole)
    {
        return view('job-roles.form', [
            'jobRole' => $jobRole,
            'outlets' => Outlet::where('active', true)->orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
        ]);
    }
}
