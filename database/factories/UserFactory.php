<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    private $class = array("A", "B", "C", "D", "E");

    public function definition(): array
    {
        $name = explode(' ', fake()->name());
        return [
            'first_name' => $name[0],
            'last_name' => $name[1],
            'email' => fake()->unique()->safeEmail(),
            'username' => $name[0]."_".$name[1],
            'email_verified_at' => now(),
            'code' => 1,
            'phone_number' => 123456789,
            'role' => "student",
            'grade' => rand(1,12),
            'class' => $this->class[array_rand($this->class)],
            'online' => 1,
            // 'active' => 1,
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ];
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
