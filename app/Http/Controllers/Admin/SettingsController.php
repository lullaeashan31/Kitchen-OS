<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function location(string $kitchen_slug)
    {
        $kitchen = auth()->user()->kitchen;
        return view('admin.settings.location', compact('kitchen'));
    }

    public function updateLocation(Request $request, string $kitchen_slug)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'geofence_radius' => 'required|integer|min:0',
        ]);

        $kitchen = auth()->user()->kitchen;
        $kitchen->update($request->only(['latitude', 'longitude', 'geofence_radius']));

        return redirect()->back()->with('success', 'Location settings updated.');
    }

    public function devices(string $kitchen_slug)
    {
        // Placeholder for device management (Registered tablets, IPs, etc.)
        return view('admin.settings.devices');
    }
}
