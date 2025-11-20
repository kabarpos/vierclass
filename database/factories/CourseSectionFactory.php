<?php

namespace Database\Factories;

use App\Models\CourseSection;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CourseSection>
 */
class CourseSectionFactory extends Factory
{
    protected $model = CourseSection::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sectionNames = [
            'Introduction and Setup',
            'Basic Concepts',
            'Advanced Techniques',
            'Practical Projects',
            'Best Practices',
            'Testing and Debugging',
            'Deployment and Production',
            'Final Project'
        ];

        return [
            'name' => $this->faker->randomElement($sectionNames),
            'course_id' => Course::factory(),
            'position' => $this->faker->numberBetween(1, 10),
        ];
    }
}