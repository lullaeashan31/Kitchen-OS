<?php

namespace Database\Factories;

use App\Models\JobRole;
use App\Models\Outlet;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        $outlet = Outlet::factory();

        return [
            'employee_code' => strtoupper($this->faker->unique()->bothify('EMP-####')),
            'name' => $this->faker->name(),
            'phone' => $this->faker->numerify('9#########'),
            'date_of_joining' => now()->subMonths(2),
            'job_role_id' => JobRole::factory(),
            'outlet_id' => $outlet,
            'employment_type' => 'full_time',
            'status' => 'active',
        ];
    }
}
