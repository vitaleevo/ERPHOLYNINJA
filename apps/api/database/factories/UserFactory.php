<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $roles = ['admin', 'doctor', 'receptionist', 'pharmacist', 'accountant'];
        
        return [
            'clinic_id' => \App\Models\Clinic::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => fake()->randomElement($roles),
            'specialty_id' => null,
            'phone' => '+244 9' . fake()->numberBetween(12, 99) . ' ' . fake()->numberBetween(100, 999) . ' ' . fake()->numberBetween(1000, 9999),
            'avatar' => null,
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the user is a doctor.
     */
    public function doctor(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'doctor',
        ]);
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }

    /**
     * Indicate that the user is a receptionist.
     */
    public function receptionist(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'receptionist',
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
