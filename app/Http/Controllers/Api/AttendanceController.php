<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Services\DriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    protected $driveService;

    public function __construct(DriveService $driveService)
    {
        $this->driveService = $driveService;
    }

    /**
     * Handle Clock In / Clock Out from Tablet.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // 1. Validation
        $request->validate([
            'staff_code' => 'required|string|exists:users,staff_code',
            'action_type' => 'required|in:CLOCK_IN,CLOCK_OUT',
            'gps_latitude' => 'required|numeric',
            'gps_longitude' => 'required|numeric',
            'device_id' => 'required|string',
            'selfie_image' => 'required|image|max:5120', // Max 5MB
            'location_id' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $user = User::where('staff_code', $request->staff_code)->firstOrFail();

            // 2. Upload Selfie
            // Folder name could be: attendance/{year}/{month}/{staff_code}
            $folderName = 'attendance/' . date('Y/m') . '/' . $user->staff_code;
            $selfieUrl = $this->driveService->uploadFile($request->file('selfie_image'), $folderName);

            // 3. Process Logic
            $latestAttendance = Attendance::where('user_id', $user->id)
                ->whereNull('clock_out_time')
                ->latest()
                ->first();

            if ($request->action_type === 'CLOCK_IN') {
                if ($latestAttendance) {
                    throw new \Exception('User is already clocked in. Please clock out first.');
                }

                // Check GPS distance (100 meter rule)
                $targetLat = $user->target_latitude;
                $targetLng = $user->target_longitude;

                if ($targetLat && $targetLng) {
                    $distance = $this->calculateDistance(
                        $request->gps_latitude,
                        $request->gps_longitude,
                        $targetLat,
                        $targetLng
                    );

                    if ($distance > 100) { // 100 meters
                        throw new \Exception("Location verification failed. You are {$distance}m away (Limit: 100m).");
                    }
                } else {
                    Log::warning("Staff {$user->staff_code} clocked in without assigned target location.");
                }

                Attendance::create([
                    'kitchen_id' => $user->kitchen_id, // CRITICAL FIX: Ensure belongs to correct kitchen
                    'staff_code' => $user->staff_code,
                    'user_id' => $user->id,
                    'clock_in_time' => now(),
                    'gps_latitude_in' => $request->gps_latitude,
                    'gps_longitude_in' => $request->gps_longitude,
                    'selfie_path_in' => $selfieUrl,
                    'device_id' => $request->device_id,
                    'location_id' => $request->location_id,
                    'status' => 'success',
                ]);

                // Update User Status
                $user->update(['attendance_status' => 'active']);

                $message = 'Clocked In Successfully';

            } else { // CLOCK_OUT
                if (!$latestAttendance) {
                    throw new \Exception('No active session found. Please clock in first.');
                }

                $latestAttendance->update([
                    'clock_out_time' => now(),
                    'gps_latitude_out' => $request->gps_latitude,
                    'gps_longitude_out' => $request->gps_longitude,
                    'selfie_path_out' => $selfieUrl,
                ]);

                // Update User Status
                $user->update(['attendance_status' => 'inactive']);

                $message = 'Clocked Out Successfully';

            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => [
                    'staff_name' => $user->name,
                    'attendance_status' => $user->attendance_status,
                    'timestamp' => now()->toDateTimeString(),
                ]
            ]);


        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Attendance Error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }
    /**
     * Calculate distance between two coordinates in meters (Haversine Formula)
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Meters

        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $latDelta = $lat2 - $lat1;
        $lonDelta = $lon2 - $lon1;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos($lat1) * cos($lat2) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c);
    }
}
