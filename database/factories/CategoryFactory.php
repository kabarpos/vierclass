<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            'Web Development',
            'Mobile Development',
            'Data Science',
            'Machine Learning',
            'UI/UX Design',
            'Digital Marketing',
            'Cloud Computing',
            'Cybersecurity',
            'DevOps',
            'Backend Development',
            'Frontend Development',
            'Full Stack Development',
            'Game Development',
            'Blockchain',
            'Artificial Intelligence',
            'Internet of Things',
            'Software Testing',
            'Database Management',
            'Network Administration',
            'Project Management',
            'Business Analysis',
            'Quality Assurance',
            'System Administration',
            'Technical Writing',
            'Product Management',
            'Agile Methodology',
            'Software Architecture',
            'API Development',
            'Microservices',
            'Containerization'
        ];

        return [
            'name' => $this->faker->randomElement($categories) . ' ' . $this->faker->numberBetween(1, 1000),
        ];
    }
}