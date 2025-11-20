<?php

namespace Database\Factories;

use App\Models\CourseBenefit;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CourseBenefit>
 */
class CourseBenefitFactory extends Factory
{
    protected $model = CourseBenefit::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $benefits = [
            'Lifetime Access to Course Materials',
            'Certificate of Completion',
            'Access to Private Community',
            'Real-world Project Portfolio',
            'Personal Mentorship Sessions',
            'Interview Preparation Guide',
            'Industry-relevant Case Studies',
            'Hands-on Practical Exercises',
            'Career Guidance and Support',
            'Regular Content Updates'
        ];

        return [
            'name' => $this->faker->randomElement($benefits),
            'course_id' => Course::factory(),
        ];
    }
}