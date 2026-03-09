<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function edit(string $kitchen_slug, User $user)
    {
        $permissions = Permission::orderBy('name')->get();
        $userPermissions = $user->permissions->pluck('id')->toArray();

        return view('admin.permissions.edit', compact('user', 'permissions', 'userPermissions'));
    }

    public function update(Request $request, string $kitchen_slug, User $user)
    {
        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $user->permissions()->sync($request->input('permissions', []));

        return redirect()->route('admin.staff.index')->with('success', 'Permissions updated successfully for ' . $user->name);
    }
}
