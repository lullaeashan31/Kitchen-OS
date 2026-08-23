<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class OutletFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->company(),
            'code' => strtoupper($this->faker->unique()->lexify('???')),
            'timezone' => 'Asia/Kolkata',
            'payroll_divisor_setting' => 'calendar',
            'active' => true,
        ];
    }
}
