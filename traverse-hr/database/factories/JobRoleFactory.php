<?php

namespace Database\Factories;

use App\Models\Outlet;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobRoleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'outlet_id' => Outlet::factory(),
            'name' => $this->faker->unique()->jobTitle(),
            'probation_months' => 3,
            'notice_period_days' => 30,
            'sort_order' => 0,
            'active' => true,
        ];
    }
}
