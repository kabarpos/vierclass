<?php

namespace Tests\Unit\Repositories;

use App\Models\Category;
use App\Models\Course;
use App\Models\CourseStudent;
use App\Repositories\CourseRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private CourseRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new CourseRepository();
    }

    public function test_search_courses_returns_matching_results(): void
    {
        // Arrange
        $category = Category::factory()->create();
        $matchingCourse = Course::factory()->create([
            'name' => 'Laravel Advanced Course',
            'category_id' => $category->id,
        ]);
        $nonMatchingCourse = Course::factory()->create([
            'name' => 'React Basics',
            'category_id' => $category->id,
        ]);

        // Act
        $results = $this->repository->searchByKeyword('Laravel');

        // Assert
        $this->assertCount(1, $results);
        $this->assertEquals($matchingCourse->id, $results->first()->id);
    }

    public function test_search_courses_returns_empty_for_no_matches(): void
    {
        // Arrange
        $category = Category::factory()->create();
        Course::factory()->create([
            'name' => 'React Basics',
            'category_id' => $category->id,
        ]);

        // Act
        $results = $this->repository->searchByKeyword('Vue.js');

        // Assert
        $this->assertCount(0, $results);
    }

    public function test_get_all_courses_with_category_includes_category_data(): void
    {
        // Arrange
        $category = Category::factory()->create(['name' => 'Programming']);
        $course = Course::factory()->create(['category_id' => $category->id]);

        // Act
        $results = $this->repository->getAllWithCategory();

        // Assert
        $this->assertCount(1, $results);
        $this->assertEquals($category->name, $results->first()->category->name);
    }

    public function test_get_featured_courses_returns_courses_ordered_by_student_count(): void
    {
        // Arrange
        $category = Category::factory()->create();
        $course1 = Course::factory()->create(['category_id' => $category->id]);
        $course2 = Course::factory()->create(['category_id' => $category->id]);
        $course3 = Course::factory()->create(['category_id' => $category->id]);

        // Create different numbers of students for each course
        CourseStudent::factory()->count(5)->create(['course_id' => $course1->id]);
        CourseStudent::factory()->count(10)->create(['course_id' => $course2->id]);
        CourseStudent::factory()->count(3)->create(['course_id' => $course3->id]);

        // Act
        $results = $this->repository->getFeaturedCourses(2);

        // Assert
        $this->assertCount(2, $results);
        $this->assertEquals($course2->id, $results->first()->id); // Most students
        $this->assertEquals($course1->id, $results->last()->id); // Second most students
    }

    public function test_get_featured_courses_respects_limit(): void
    {
        // Arrange
        $category = Category::factory()->create();
        Course::factory()->count(5)->create(['category_id' => $category->id]);

        // Act
        $results = $this->repository->getFeaturedCourses(3);

        // Assert
        $this->assertCount(3, $results);
    }

    public function test_get_featured_courses_handles_no_students(): void
    {
        // Arrange
        $category = Category::factory()->create();
        $course = Course::factory()->create(['category_id' => $category->id]);

        // Act
        $results = $this->repository->getFeaturedCourses(5);

        // Assert
        $this->assertCount(1, $results);
        $this->assertEquals($course->id, $results->first()->id);
    }

    public function test_search_courses_is_case_insensitive(): void
    {
        // Arrange
        $category = Category::factory()->create();
        $course = Course::factory()->create([
            'name' => 'Laravel Advanced Course',
            'category_id' => $category->id,
        ]);

        // Act
        $results = $this->repository->searchByKeyword('laravel');

        // Assert
        $this->assertCount(1, $results);
        $this->assertEquals($course->id, $results->first()->id);
    }

    public function test_search_courses_searches_in_about(): void
    {
        // Arrange
        $category = Category::factory()->create();
        $course = Course::factory()->create([
            'name' => 'Web Development',
            'about' => 'Learn Laravel framework from scratch',
            'category_id' => $category->id,
        ]);

        // Act
        $results = $this->repository->searchByKeyword('Laravel');

        // Assert
        $this->assertCount(1, $results);
        $this->assertEquals($course->id, $results->first()->id);
    }
}