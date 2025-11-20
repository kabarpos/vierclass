<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    protected $model = Course::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $courses = [
            'Complete Laravel Development Course',
            'React Native Mobile App Development',
            'Python Data Science Bootcamp',
            'Advanced JavaScript Programming',
            'UI/UX Design Fundamentals',
            'Vue.js Complete Guide',
            'Node.js Backend Development',
            'Flutter Mobile Development',
            'Machine Learning with Python',
            'Digital Marketing Strategy'
        ];

        return [
            'name' => $this->faker->randomElement($courses) . ' ' . $this->faker->numberBetween(1, 10000),
            'thumbnail' => 'https://via.placeholder.com/400x300/4f46e5/ffffff?text=Course+Thumbnail',
            'about' => $this->faker->paragraphs(3, true),
            'is_popular' => $this->faker->boolean(30), // 30% chance to be popular
            'category_id' => Category::factory(),
            'price' => $this->faker->numberBetween(199000, 499000), // Random price between 199k - 499k
            'admin_fee_amount' => $this->faker->numberBetween(3000, 10000), // Random admin fee between 3k - 10k
        ];
    }

    /**
     * Indicate that the course should be popular.
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_popular' => true,
        ]);
    }
}