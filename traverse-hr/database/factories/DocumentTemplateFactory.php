<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentTemplateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->sentence(3),
            'kind' => 'acknowledge_only',
            'active' => true,
        ];
    }
}
