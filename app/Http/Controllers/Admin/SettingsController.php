<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function location(string $kitchen_slug)
    {
        // Placeholder for location logic (Google Maps API key, Geofencing radius, etc.)
        // For now, static config or just a view
        return view('admin.settings.location');
    }

    public function devices(string $kitchen_slug)
    {
        // Placeholder for device management (Registered tablets, IPs, etc.)
        return view('admin.settings.devices');
    }
}
