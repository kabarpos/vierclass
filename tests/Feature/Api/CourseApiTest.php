<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Course;
use App\Models\CourseStudent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CourseApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $adminUser;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        $userRole = Role::firstOrCreate(['name' => 'user']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        
        // Create users
        $this->user = User::factory()->create();
        $this->user->assignRole($userRole);
        
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole($adminRole);
        
        // Create category
        $this->category = Category::factory()->create();
        
        // Fake storage
        Storage::fake('public');
    }

    public function test_can_get_all_courses(): void
    {
        // Arrange
        $courses = Course::factory()->count(3)->create([
            'category_id' => $this->category->id,
        ]);

        // Act
        $response = $this->getJson('/api/courses');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'thumbnail',
                        'price',
                        'description',
                        'is_popular',
                        'category',
                        'created_at',
                        'updated_at',
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_can_get_single_course(): void
    {
        // Arrange
        $course = Course::factory()->create([
            'category_id' => $this->category->id,
        ]);

        // Act
        $response = $this->getJson("/api/courses/{$course->id}");

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'thumbnail',
                    'price',
                    'description',
                    'is_popular',
                    'category',
                    'sections',
                    'created_at',
                    'updated_at',
                ]
            ])
            ->assertJson([
                'data' => [
                    'id' => $course->id,
                    'name' => $course->name,
                ]
            ]);
    }

    public function test_can_search_courses(): void
    {
        // Arrange
        $course1 = Course::factory()->create([
            'name' => 'Laravel Advanced Course',
            'category_id' => $this->category->id,
        ]);
        $course2 = Course::factory()->create([
            'name' => 'React Basics',
            'category_id' => $this->category->id,
        ]);

        // Act
        $response = $this->getJson('/api/courses?search=Laravel');

        // Assert
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['name' => 'Laravel Advanced Course']);
    }

    public function test_can_filter_courses_by_category(): void
    {
        // Arrange
        $category2 = Category::factory()->create();
        $course1 = Course::factory()->create(['category_id' => $this->category->id]);
        $course2 = Course::factory()->create(['category_id' => $category2->id]);

        // Act
        $response = $this->getJson("/api/courses?category_id={$this->category->id}");

        // Assert
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $course1->id]);
    }

    public function test_can_get_popular_courses(): void
    {
        // Arrange
        $popularCourse = Course::factory()->create([
            'is_popular' => true,
            'category_id' => $this->category->id,
        ]);
        $regularCourse = Course::factory()->create([
            'is_popular' => false,
            'category_id' => $this->category->id,
        ]);

        // Act
        $response = $this->getJson('/api/courses?popular=true');

        // Assert
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $popularCourse->id]);
    }

    public function test_authenticated_user_can_enroll_in_course(): void
    {
        // Arrange
        Sanctum::actingAs($this->user);
        $course = Course::factory()->create([
            'category_id' => $this->category->id,
            'price' => 100000,
        ]);

        // Act
        $response = $this->postJson("/api/courses/{$course->id}/enroll");

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'enrollment_id',
            ]);

        $this->assertDatabaseHas('course_students', [
            'user_id' => $this->user->id,
            'course_id' => $course->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_enroll_in_course(): void
    {
        // Arrange
        $course = Course::factory()->create([
            'category_id' => $this->category->id,
        ]);

        // Act
        $response = $this->postJson("/api/courses/{$course->id}/enroll");

        // Assert
        $response->assertStatus(401);
    }

    public function test_user_cannot_enroll_in_same_course_twice(): void
    {
        // Arrange
        Sanctum::actingAs($this->user);
        $course = Course::factory()->create([
            'category_id' => $this->category->id,
        ]);

        CourseStudent::create([
            'user_id' => $this->user->id,
            'course_id' => $course->id,
        ]);

        // Act
        $response = $this->postJson("/api/courses/{$course->id}/enroll");

        // Assert
        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'You are already enrolled in this course']);
    }

    public function test_can_get_user_enrolled_courses(): void
    {
        // Arrange
        Sanctum::actingAs($this->user);
        $course1 = Course::factory()->create(['category_id' => $this->category->id]);
        $course2 = Course::factory()->create(['category_id' => $this->category->id]);
        $course3 = Course::factory()->create(['category_id' => $this->category->id]);

        // Enroll user in course1 and course2
        CourseStudent::create(['user_id' => $this->user->id, 'course_id' => $course1->id]);
        CourseStudent::create(['user_id' => $this->user->id, 'course_id' => $course2->id]);

        // Act
        $response = $this->getJson('/api/user/courses');

        // Assert
        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['id' => $course1->id])
            ->assertJsonFragment(['id' => $course2->id]);
    }

    public function test_admin_can_create_course(): void
    {
        // Arrange
        Sanctum::actingAs($this->adminUser);
        $thumbnail = UploadedFile::fake()->image('thumbnail.jpg');
        
        $courseData = [
            'name' => 'New Laravel Course',
            'thumbnail' => $thumbnail,
            'price' => 299000,
            'description' => 'Complete Laravel course',
            'is_popular' => true,
            'category_id' => $this->category->id,
            'benefits' => [
                'Learn Laravel fundamentals',
                'Build real applications',
            ],
        ];

        // Act
        $response = $this->postJson('/api/admin/courses', $courseData);

        // Assert
        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'thumbnail',
                    'price',
                    'description',
                    'is_popular',
                    'category_id',
                ]
            ]);

        $this->assertDatabaseHas('courses', [
            'name' => 'New Laravel Course',
            'price' => 299000,
            'category_id' => $this->category->id,
        ]);
    }

    public function test_regular_user_cannot_create_course(): void
    {
        // Arrange
        Sanctum::actingAs($this->user);
        $courseData = [
            'name' => 'Unauthorized Course',
            'price' => 100000,
            'category_id' => $this->category->id,
        ];

        // Act
        $response = $this->postJson('/api/admin/courses', $courseData);

        // Assert
        $response->assertStatus(403);
    }

    public function test_admin_can_update_course(): void
    {
        // Arrange
        Sanctum::actingAs($this->adminUser);
        $course = Course::factory()->create([
            'category_id' => $this->category->id,
        ]);

        $updateData = [
            'name' => 'Updated Course Name',
            'price' => 399000,
            'description' => 'Updated description',
        ];

        // Act
        $response = $this->putJson("/api/admin/courses/{$course->id}", $updateData);

        // Assert
        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Updated Course Name',
                'price' => 399000,
            ]);

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'name' => 'Updated Course Name',
            'price' => 399000,
        ]);
    }

    public function test_admin_can_delete_course(): void
    {
        // Arrange
        Sanctum::actingAs($this->adminUser);
        $course = Course::factory()->create([
            'category_id' => $this->category->id,
        ]);

        // Act
        $response = $this->deleteJson("/api/admin/courses/{$course->id}");

        // Assert
        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Course deleted successfully']);

        $this->assertSoftDeleted('courses', [
            'id' => $course->id,
        ]);
    }

    public function test_course_validation_errors(): void
    {
        // Arrange
        Sanctum::actingAs($this->adminUser);

        // Act
        $response = $this->postJson('/api/admin/courses', []);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'price',
                'category_id',
            ]);
    }

    public function test_course_not_found_returns_404(): void
    {
        // Act
        $response = $this->getJson('/api/courses/999999');

        // Assert
        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Course not found']);
    }

    public function test_can_get_course_statistics(): void
    {
        // Arrange
        Sanctum::actingAs($this->adminUser);
        $course = Course::factory()->create([
            'category_id' => $this->category->id,
        ]);

        // Create some enrollments
        CourseStudent::factory()->count(5)->create([
            'course_id' => $course->id,
        ]);

        // Act
        $response = $this->getJson("/api/admin/courses/{$course->id}/statistics");

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_students',
                    'completion_rate',
                    'average_progress',
                    'revenue',
                ]
            ]);
    }

    public function test_pagination_works_correctly(): void
    {
        // Arrange
        Course::factory()->count(25)->create([
            'category_id' => $this->category->id,
        ]);

        // Act
        $response = $this->getJson('/api/courses?per_page=10&page=1');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'links',
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ]
            ])
            ->assertJsonCount(10, 'data');
    }

    public function test_course_sorting_works(): void
    {
        // Arrange
        $course1 = Course::factory()->create([
            'name' => 'A Course',
            'price' => 100000,
            'category_id' => $this->category->id,
            'created_at' => now()->subDays(2),
        ]);
        $course2 = Course::factory()->create([
            'name' => 'B Course',
            'price' => 200000,
            'category_id' => $this->category->id,
            'created_at' => now()->subDay(),
        ]);

        // Act - Sort by name ascending
        $response = $this->getJson('/api/courses?sort=name&order=asc');

        // Assert
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('A Course', $data[0]['name']);
        $this->assertEquals('B Course', $data[1]['name']);

        // Act - Sort by price descending
        $response = $this->getJson('/api/courses?sort=price&order=desc');

        // Assert
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals(200000, $data[0]['price']);
        $this->assertEquals(100000, $data[1]['price']);
    }
}