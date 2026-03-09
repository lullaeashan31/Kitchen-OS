<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Kitchen;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class KitchenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kitchens = Kitchen::latest()->paginate(10);
        return view('superadmin.kitchens.index', compact('kitchens'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('superadmin.kitchens.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:kitchens,slug',
            'admin_name' => 'required|string|max:255',
            'admin_phone' => 'required|digits:10|unique:users,phone',
            'admin_code' => 'required|numeric|digits:6|unique:users,staff_code',
            'admin_password' => 'required|string|min:8',
        ]);

        DB::transaction(function () use ($request) {
            $kitchen = Kitchen::create([
                'name' => $request->name,
                'slug' => Str::slug($request->slug),
            ]);

            User::create([
                'name' => $request->admin_name,
                'phone' => $request->admin_phone,
                'staff_code' => $request->admin_code,
                'password' => Hash::make($request->admin_password),
                'role' => UserRole::Admin,
                'kitchen_id' => $kitchen->id,
                'is_password_changed' => true,
                'monthly_salary' => 0,
                'weekly_off_day' => 'Sunday',
                'onboarding_status' => 'active',
            ]);
        });

        return redirect()->route('superadmin.kitchens.index')->with('success', 'Kitchen and Admin created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $kitchen = Kitchen::findOrFail($id);
        // Find the first admin of this kitchen
        $admin = User::where('kitchen_id', $kitchen->id)
            ->where('role', UserRole::Admin)
            ->first();

        return view('superadmin.kitchens.edit', compact('kitchen', 'admin'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $kitchen = Kitchen::findOrFail($id);
        $admin = User::where('kitchen_id', $kitchen->id)
            ->where('role', UserRole::Admin)
            ->first();

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:kitchens,slug,' . $kitchen->id,
            'is_active' => 'required|boolean',
            'admin_name' => 'required|string|max:255',
            'admin_phone' => 'required|digits:10|unique:users,phone,' . ($admin ? $admin->id : 'NULL'),
            'admin_code' => 'required|numeric|digits:6|unique:users,staff_code,' . ($admin ? $admin->id : 'NULL'),
            'admin_password' => 'nullable|string|min:8',
        ]);

        DB::transaction(function () use ($request, $kitchen, $admin) {
            $kitchen->update([
                'name' => $request->name,
                'slug' => Str::slug($request->slug),
                'is_active' => $request->is_active,
            ]);

            if ($admin) {
                $adminData = [
                    'name' => $request->admin_name,
                    'phone' => $request->admin_phone,
                    'staff_code' => $request->admin_code,
                ];

                if ($request->admin_password) {
                    $adminData['password'] = Hash::make($request->admin_password);
                }

                $admin->update($adminData);
            }
        });

        return redirect()->route('superadmin.kitchens.index')->with('success', 'Kitchen updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
