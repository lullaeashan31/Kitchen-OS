<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:vendors,name',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|digits:10',
        ]);

        $vendor = \App\Models\Vendor::create($request->only(['name', 'contact_person', 'phone']));

        return response()->json([
            'success' => true,
            'vendor' => $vendor,
            'message' => 'Vendor created successfully.'
        ]);
    }
}
