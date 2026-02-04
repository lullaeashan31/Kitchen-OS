<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class TestAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('role', \App\Enums\UserRole::Staff)->first();

        if (!$user) {
            $user = User::create([
                'name' => 'John Doe',
                'role' => \App\Enums\UserRole::Staff,
                'staff_code' => '123456',
                'email' => 'staff@example.com',
                'password' => '$2y$12$e/a/b/c/d/e/f/g/h/i/j/k' // dummy hash
            ]);
        }

        // Ensure staff_code is set if using existing user
        if (!$user->staff_code) {
            $user->update(['staff_code' => '999999']);
        }

        Attendance::create([
            'staff_code' => $user->staff_code,
            'user_id' => $user->id,
            'clock_in_time' => Carbon::now()->subHours(4),
            'gps_latitude_in' => 23.0225,
            'gps_longitude_in' => 72.5714,
            'status' => 'pending',
            'device_id' => 'test-device'
        ]);

        $this->command->info('Test attendance record created for today.');
    }
}
