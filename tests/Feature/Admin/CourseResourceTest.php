<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\CourseResource;
use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CourseResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin role and user
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole($adminRole);
        
        // Create test category
        $this->category = Category::factory()->create();
        
        $this->actingAs($this->adminUser);
        
        // Fake storage for file uploads
        Storage::fake('public');
    }

    public function test_can_render_course_resource_list_page(): void
    {
        // Arrange
        Course::factory()->count(5)->create(['category_id' => $this->category->id]);
        
        // Act & Assert
        Livewire::test(CourseResource\Pages\ListCourses::class)
            ->assertSuccessful();
    }

    public function test_can_list_courses(): void
    {
        // Arrange
        $courses = Course::factory()->count(3)->create(['category_id' => $this->category->id]);
        
        // Act & Assert
        Livewire::test(CourseResource\Pages\ListCourses::class)
            ->assertCanSeeTableRecords($courses);
    }

    public function test_can_render_course_resource_create_page(): void
    {
        // Act & Assert
        Livewire::test(CourseResource\Pages\CreateCourse::class)
            ->assertSuccessful();
    }

    public function test_can_create_course(): void
    {
        // Arrange
        $thumbnail = UploadedFile::fake()->image('thumbnail.jpg');
        $courseData = [
            'name' => 'Laravel Mastery Course',
            'thumbnail' => $thumbnail,
            'price' => 299000,
            'about' => 'Complete Laravel course for beginners to advanced',
            'is_popular' => true,
            'category_id' => $this->category->id,
            'benefits' => [],
        ];
        
        // Act & Assert
        Livewire::test(CourseResource\Pages\CreateCourse::class)
            ->fillForm($courseData)
            ->call('create')
            ->assertHasNoFormErrors();
            
        $this->assertDatabaseHas('courses', [
            'name' => 'Laravel Mastery Course',
            'price' => 299000,
            'about' => 'Complete Laravel course for beginners to advanced',
            'is_popular' => true,
            'category_id' => $this->category->id,
        ]);
        
        // Assert file was uploaded
        $course = Course::where('name', 'Laravel Mastery Course')->first();
        $this->assertNotNull($course->thumbnail);
        Storage::disk('public')->assertExists($course->thumbnail);
    }

    public function test_can_validate_course_creation(): void
    {
        // Act & Assert
        Livewire::test(CourseResource\Pages\CreateCourse::class)
            ->fillForm([
                'name' => '',
                'price' => -100, // Invalid price
                'about' => '', // Changed from 'description' to 'about'
                'category_id' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
                'price' => 'min',
                'about' => 'required', // Changed from 'description' to 'about'
                'category_id' => 'required',
            ]);
    }

    public function test_can_render_course_resource_edit_page(): void
    {
        // Arrange
        $course = Course::factory()->create(['category_id' => $this->category->id]);
        
        // Act & Assert
        Livewire::test(CourseResource\Pages\EditCourse::class, [
            'record' => $course->getRouteKey(),
        ])
            ->assertSuccessful();
    }

    public function test_can_retrieve_course_data_for_editing(): void
    {
        // Arrange
        $course = Course::factory()->create([
            'name' => 'React Fundamentals',
            'price' => 199000,
            'admin_fee_amount' => 5000,
            'about' => 'Learn React from scratch',
            'category_id' => $this->category->id,
        ]);
        
        // Act & Assert
        Livewire::test(CourseResource\Pages\EditCourse::class, [
            'record' => $course->getRouteKey(),
        ])
            ->assertFormSet([
                'name' => 'React Fundamentals',
                'price' => 199000,
                'admin_fee_amount' => 5000,
                'about' => 'Learn React from scratch',
                'category_id' => $this->category->id,
            ]);
    }

    public function test_can_save_course_changes(): void
    {
        // Arrange
        $course = Course::factory()->create(['category_id' => $this->category->id]);
        $thumbnail = UploadedFile::fake()->image('updated-thumbnail.jpg');
        $newData = [
            'name' => 'Updated Course Name',
            'thumbnail' => $thumbnail,
            'price' => 399000,
            'admin_fee_amount' => 7500,
            'about' => 'Updated course description',
            'is_popular' => true,
        ];
        
        // Act & Assert
        Livewire::test(CourseResource\Pages\EditCourse::class, [
            'record' => $course->getRouteKey(),
        ])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();
            
        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'name' => 'Updated Course Name',
            'price' => 399000,
            'admin_fee_amount' => 7500,
            'about' => 'Updated course description',
            'is_popular' => true,
        ]);
    }

    public function test_can_delete_course(): void
    {
        // Arrange
        $course = Course::factory()->create(['category_id' => $this->category->id]);
        
        // Act & Assert
        Livewire::test(CourseResource\Pages\EditCourse::class, [
            'record' => $course->getRouteKey(),
        ])
            ->callAction(DeleteAction::class)
            ->assertSuccessful();
            
        $this->assertSoftDeleted($course);
    }

    public function test_can_search_courses(): void
    {
        // Arrange
        $course1 = Course::factory()->create([
            'name' => 'Laravel Advanced',
            'category_id' => $this->category->id
        ]);
        $course2 = Course::factory()->create([
            'name' => 'React Basics',
            'category_id' => $this->category->id
        ]);
        $course3 = Course::factory()->create([
            'name' => 'Vue.js Fundamentals',
            'category_id' => $this->category->id
        ]);
        
        // Act & Assert
        Livewire::test(CourseResource\Pages\ListCourses::class)
            ->searchTable('Laravel')
            ->assertCanSeeTableRecords([$course1])
            ->assertCanNotSeeTableRecords([$course2, $course3]);
    }

    public function test_can_filter_courses_by_category(): void
    {
        // Arrange
        $category2 = Category::factory()->create();
        $course1 = Course::factory()->create(['category_id' => $this->category->id]);
        $course2 = Course::factory()->create(['category_id' => $category2->id]);
        
        // Act & Assert
        Livewire::test(CourseResource\Pages\ListCourses::class)
            ->filterTable('category_id', $this->category->id)
            ->assertCanSeeTableRecords([$course1])
            ->assertCanNotSeeTableRecords([$course2]);
    }

    public function test_can_filter_courses_by_popularity(): void
    {
        // Arrange
        $popularCourse = Course::factory()->create([
            'is_popular' => true,
            'category_id' => $this->category->id
        ]);
        $regularCourse = Course::factory()->create([
            'is_popular' => false,
            'category_id' => $this->category->id
        ]);
        
        // Act & Assert
        Livewire::test(CourseResource\Pages\ListCourses::class)
            ->filterTable('is_popular', true)
            ->assertCanSeeTableRecords([$popularCourse])
            ->assertCanNotSeeTableRecords([$regularCourse]);
    }

    public function test_can_bulk_delete_courses(): void
    {
        // Arrange
        $courses = Course::factory()->count(3)->create(['category_id' => $this->category->id]);
        
        // Act & Assert
        Livewire::test(CourseResource\Pages\ListCourses::class)
            ->callTableBulkAction('delete', $courses)
            ->assertSuccessful();
            
        foreach ($courses as $course) {
            $this->assertSoftDeleted($course);
        }
    }

    public function test_can_sort_courses_by_price(): void
    {
        // Arrange
        $expensiveCourse = Course::factory()->create([
            'price' => 500000,
            'category_id' => $this->category->id
        ]);
        $cheapCourse = Course::factory()->create([
            'price' => 100000,
            'category_id' => $this->category->id
        ]);
        
        // Act & Assert
        Livewire::test(CourseResource\Pages\ListCourses::class)
            ->sortTable('price')
            ->assertCanSeeTableRecords([$cheapCourse, $expensiveCourse], inOrder: true);
    }


}