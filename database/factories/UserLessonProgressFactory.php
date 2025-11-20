<?php

namespace Database\Factories;

use App\Models\UserLessonProgress;
use App\Models\User;
use App\Models\Course;
use App\Models\SectionContent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserLessonProgress>
 */
class UserLessonProgressFactory extends Factory
{
    protected $model = UserLessonProgress::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'section_content_id' => SectionContent::factory(),
            'is_completed' => $this->faker->boolean(70), // 70% chance to be completed
            'completed_at' => $this->faker->boolean(70) ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
            'time_spent_seconds' => $this->faker->numberBetween(60, 3600), // 1 minute to 1 hour
        ];
    }

    /**
     * Indicate that the lesson progress should be completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_completed' => true,
            'completed_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the lesson progress should not be completed.
     */
    public function incomplete(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_completed' => false,
            'completed_at' => null,
        ]);
    }
}