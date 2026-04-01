<?php

namespace Database\Factories;

use App\Models\Clinic;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Clinic>
 */
class ClinicFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->company() . ' Clínica';
        
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'email' => fake()->unique()->companyEmail(),
            'phone' => '+244 9' . fake()->numberBetween(12, 99) . ' ' . fake()->numberBetween(100, 999) . ' ' . fake()->numberBetween(1000, 9999),
            'address' => fake()->address(),
            'nif' => '500' . fake()->unique()->numberBetween(100000, 999999),
            'logo' => null,
            'status' => 'active',
            'plan' => fake()->randomElement(['basic', 'professional', 'enterprise']),
        ];
    }

    /**
     * Indicate that the clinic is basic plan.
     */
    public function basic(): static
    {
        return $this->state(fn (array $attributes) => [
            'plan' => 'basic',
        ]);
    }

    /**
     * Indicate that the clinic is professional plan.
     */
    public function professional(): static
    {
        return $this->state(fn (array $attributes) => [
            'plan' => 'professional',
        ]);
    }

    /**
     * Indicate that the clinic is enterprise plan.
     */
    public function enterprise(): static
    {
        return $this->state(fn (array $attributes) => [
            'plan' => 'enterprise',
        ]);
    }

    /**
     * Indicate that the clinic is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the clinic is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
