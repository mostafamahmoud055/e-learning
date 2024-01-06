<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
            return [
                'name' => $this->faker->name, 
                'subject' => $this->faker->name, 
                'description' => $this->faker->sentence(15),
                'rate' => rand(1, 5),
                'grade' => rand(1, 12),
                'active' => 1,
                'hours' => '3 month of year',
                'target'=>'a set of words that is complete in itself, typically containing a subject and predicate',
                'user_id' => 1
            ];
    }
}
