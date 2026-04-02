<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(string $kitchen_slug)
    {
        $roles = Role::withCount(['permissions', 'users'])->orderBy('name')->get();
        return view('admin.roles.index', compact('roles'));
    }

    public function create(string $kitchen_slug)
    {
        $permissions = Permission::orderBy('name')->get();
        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request, string $kitchen_slug)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:roles,slug',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);
        $role = Role::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
        ]);
        $role->permissions()->sync($request->input('permissions', []));

        return redirect()->route('admin.roles.index', ['kitchen_slug' => $kitchen_slug])
            ->with('success', 'Role generated and permissions linked successfully.');
    }

    public function edit(string $kitchen_slug, Role $role)
    {
        $permissions = Permission::orderBy('name')->get();
        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, string $kitchen_slug, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:roles,slug,' . $role->id,
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
        ]);

        // Carefully sync permissions
        $role->permissions()->sync($request->input('permissions', []));

        return redirect()->route('admin.roles.index', ['kitchen_slug' => $kitchen_slug])
            ->with('success', 'Role "' . $role->name . '" updated successfully.');
    }

    public function destroy(string $kitchen_slug, Role $role)
    {
        if ($role->users()->count() > 0) {
            return redirect()->route('admin.roles.index')->with('error', 'Cannot delete role: staff are assigned to it. Reassign them first.');
        }
        $role->permissions()->detach();
        $role->delete();
        return redirect()->route('admin.roles.index')->with('success', 'Role deleted.');
    }
}
