<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $adminUser;

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
        
        // Fake storage
        Storage::fake('public');
    }

    public function test_can_get_all_categories(): void
    {
        // Arrange
        $categories = Category::factory()->count(3)->create();

        // Act
        $response = $this->getJson('/api/categories');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'icon',
                        'created_at',
                        'updated_at',
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_can_get_single_category(): void
    {
        // Arrange
        $category = Category::factory()->create();

        // Act
        $response = $this->getJson("/api/categories/{$category->id}");

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'icon',
                    'courses_count',
                    'created_at',
                    'updated_at',
                ]
            ])
            ->assertJson([
                'data' => [
                    'id' => $category->id,
                    'name' => $category->name,
                ]
            ]);
    }

    public function test_can_get_category_with_courses(): void
    {
        // Arrange
        $category = Category::factory()->create();
        $courses = Course::factory()->count(3)->create([
            'category_id' => $category->id,
        ]);

        // Act
        $response = $this->getJson("/api/categories/{$category->id}?include=courses");

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'icon',
                    'courses' => [
                        '*' => [
                            'id',
                            'name',
                            'thumbnail',
                            'price',
                        ]
                    ],
                ]
            ])
            ->assertJsonCount(3, 'data.courses');
    }

    public function test_admin_can_create_category(): void
    {
        // Arrange
        Sanctum::actingAs($this->adminUser);
        $icon = UploadedFile::fake()->image('icon.svg');
        
        $categoryData = [
            'name' => 'Web Development',
            'icon' => $icon,
        ];

        // Act
        $response = $this->postJson('/api/admin/categories', $categoryData);

        // Assert
        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'icon',
                    'created_at',
                    'updated_at',
                ]
            ])
            ->assertJsonFragment([
                'name' => 'Web Development',
            ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Web Development',
        ]);
    }

    public function test_regular_user_cannot_create_category(): void
    {
        // Arrange
        Sanctum::actingAs($this->user);
        $categoryData = [
            'name' => 'Unauthorized Category',
        ];

        // Act
        $response = $this->postJson('/api/admin/categories', $categoryData);

        // Assert
        $response->assertStatus(403);
    }

    public function test_admin_can_update_category(): void
    {
        // Arrange
        Sanctum::actingAs($this->adminUser);
        $category = Category::factory()->create();

        $updateData = [
            'name' => 'Updated Category Name',
        ];

        // Act
        $response = $this->putJson("/api/admin/categories/{$category->id}", $updateData);

        // Assert
        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Updated Category Name',
            ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category Name',
        ]);
    }

    public function test_admin_can_delete_category(): void
    {
        // Arrange
        Sanctum::actingAs($this->adminUser);
        $category = Category::factory()->create();

        // Act
        $response = $this->deleteJson("/api/admin/categories/{$category->id}");

        // Assert
        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Category deleted successfully']);

        $this->assertSoftDeleted('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_cannot_delete_category_with_courses(): void
    {
        // Arrange
        Sanctum::actingAs($this->adminUser);
        $category = Category::factory()->create();
        Course::factory()->create(['category_id' => $category->id]);

        // Act
        $response = $this->deleteJson("/api/admin/categories/{$category->id}");

        // Assert
        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Cannot delete category that has courses'
            ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'deleted_at' => null,
        ]);
    }

    public function test_category_validation_errors(): void
    {
        // Arrange
        Sanctum::actingAs($this->adminUser);

        // Act
        $response = $this->postJson('/api/admin/categories', []);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_category_name_must_be_unique(): void
    {
        // Arrange
        Sanctum::actingAs($this->adminUser);
        $existingCategory = Category::factory()->create(['name' => 'Web Development']);

        $categoryData = [
            'name' => 'Web Development',
        ];

        // Act
        $response = $this->postJson('/api/admin/categories', $categoryData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_category_not_found_returns_404(): void
    {
        // Act
        $response = $this->getJson('/api/categories/999999');

        // Assert
        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Category not found']);
    }

    public function test_can_search_categories(): void
    {
        // Arrange
        $category1 = Category::factory()->create(['name' => 'Web Development']);
        $category2 = Category::factory()->create(['name' => 'Mobile Development']);
        $category3 = Category::factory()->create(['name' => 'Data Science']);

        // Act
        $response = $this->getJson('/api/categories?search=Development');

        // Assert
        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['name' => 'Web Development'])
            ->assertJsonFragment(['name' => 'Mobile Development']);
    }

    public function test_can_get_categories_with_course_count(): void
    {
        // Arrange
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();
        
        Course::factory()->count(3)->create(['category_id' => $category1->id]);
        Course::factory()->count(1)->create(['category_id' => $category2->id]);

        // Act
        $response = $this->getJson('/api/categories?with_course_count=true');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'icon',
                        'courses_count',
                    ]
                ]
            ]);

        $data = $response->json('data');
        $category1Data = collect($data)->firstWhere('id', $category1->id);
        $category2Data = collect($data)->firstWhere('id', $category2->id);

        $this->assertEquals(3, $category1Data['courses_count']);
        $this->assertEquals(1, $category2Data['courses_count']);
    }

    public function test_can_get_popular_categories(): void
    {
        // Arrange
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();
        $category3 = Category::factory()->create();
        
        // Create courses with different enrollment counts
        $course1 = Course::factory()->create(['category_id' => $category1->id]);
        $course2 = Course::factory()->create(['category_id' => $category2->id]);
        $course3 = Course::factory()->create(['category_id' => $category3->id]);

        // Create enrollments
        \App\Models\CourseStudent::factory()->count(10)->create(['course_id' => $course1->id]);
        \App\Models\CourseStudent::factory()->count(5)->create(['course_id' => $course2->id]);
        \App\Models\CourseStudent::factory()->count(1)->create(['course_id' => $course3->id]);

        // Act
        $response = $this->getJson('/api/categories?popular=true&limit=2');

        // Assert
        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');

        $data = $response->json('data');
        $this->assertEquals($category1->id, $data[0]['id']); // Most popular first
        $this->assertEquals($category2->id, $data[1]['id']); // Second most popular
    }

    public function test_pagination_works_correctly(): void
    {
        // Arrange
        Category::factory()->count(15)->create();

        // Act
        $response = $this->getJson('/api/categories?per_page=5&page=1');

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
            ->assertJsonCount(5, 'data');
    }

    public function test_category_sorting_works(): void
    {
        // Arrange
        $category1 = Category::factory()->create([
            'name' => 'A Category',
            'created_at' => now()->subDays(2),
        ]);
        $category2 = Category::factory()->create([
            'name' => 'B Category',
            'created_at' => now()->subDay(),
        ]);

        // Act - Sort by name ascending
        $response = $this->getJson('/api/categories?sort=name&order=asc');

        // Assert
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('A Category', $data[0]['name']);
        $this->assertEquals('B Category', $data[1]['name']);

        // Act - Sort by created_at descending
        $response = $this->getJson('/api/categories?sort=created_at&order=desc');

        // Assert
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals($category2->id, $data[0]['id']); // Newer first
        $this->assertEquals($category1->id, $data[1]['id']); // Older second
    }

    public function test_can_get_category_statistics(): void
    {
        // Arrange
        Sanctum::actingAs($this->adminUser);
        $category = Category::factory()->create();
        $courses = Course::factory()->count(3)->create(['category_id' => $category->id]);

        // Create some enrollments
        foreach ($courses as $course) {
            \App\Models\CourseStudent::factory()->count(2)->create(['course_id' => $course->id]);
        }

        // Act
        $response = $this->getJson("/api/admin/categories/{$category->id}/statistics");

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_courses',
                    'total_students',
                    'total_revenue',
                    'average_course_price',
                ]
            ]);
    }

    public function test_icon_upload_validation(): void
    {
        // Arrange
        Sanctum::actingAs($this->adminUser);
        $invalidFile = UploadedFile::fake()->create('document.pdf', 1000);

        $categoryData = [
            'name' => 'Test Category',
            'icon' => $invalidFile,
        ];

        // Act
        $response = $this->postJson('/api/admin/categories', $categoryData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['icon']);
    }

    public function test_can_bulk_delete_categories(): void
    {
        // Arrange
        Sanctum::actingAs($this->adminUser);
        $categories = Category::factory()->count(3)->create();
        $categoryIds = $categories->pluck('id')->toArray();

        // Act
        $response = $this->deleteJson('/api/admin/categories/bulk', [
            'ids' => $categoryIds
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Categories deleted successfully']);

        foreach ($categoryIds as $id) {
            $this->assertSoftDeleted('categories', ['id' => $id]);
        }
    }
}