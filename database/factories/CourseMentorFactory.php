<?php

namespace Database\Factories;

use App\Models\CourseMentor;
use App\Models\User;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CourseMentor>
 */
class CourseMentorFactory extends Factory
{
    protected $model = CourseMentor::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $mentorAbouts = [
            'Experienced software developer with 5+ years in the industry. Passionate about teaching and helping students achieve their goals.',
            'Senior full-stack developer specializing in modern web technologies. Former tech lead at major startups.',
            'Industry expert with extensive experience in mobile app development. Published author and conference speaker.',
            'Data scientist with PhD in Computer Science. Worked with Fortune 500 companies on AI projects.',
            'UI/UX designer with 8+ years experience. Worked with top brands and agencies worldwide.'
        ];

        return [
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'about' => $this->faker->randomElement($mentorAbouts),
            'is_active' => $this->faker->boolean(85), // 85% chance to be active
        ];
    }

    /**
     * Indicate that the mentor should be active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }
}