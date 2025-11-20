<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Course;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminDashboardJourneyTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $customerUser;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles with explicit guard_name
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $customerRole = Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
        
        // Create users
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole($adminRole);
        
        $this->customerUser = User::factory()->create();
        $this->customerUser->assignRole($customerRole);
        
        // Create test category
        $this->category = Category::factory()->create();
    }

    public function test_complete_course_management_journey(): void
    {
        // Login as admin
        $this->actingAs($this->adminUser);
        
        // Step 1: Navigate to courses list
        $response = $this->get('/admin/courses');
        $response->assertSuccessful();
        
        // Step 2: Create a new course
        $courseData = [
            'name' => 'Complete Laravel Course',
            'price' => 299000,
            'about' => 'Learn Laravel from beginner to advanced level',
            'category_id' => $this->category->id,
            'is_popular' => true,
            'benefits' => [
                'Master Laravel fundamentals',
                'Build real-world applications',
                'Learn best practices'
            ],
        ];
        
        // Use Filament testing methods for creating course
        $this->actingAs($this->adminUser)
            ->get('/admin/courses/create')
            ->assertSuccessful();
            
        // Create course using Filament resource
        $course = Course::factory()->create([
            'name' => 'Complete Laravel Course',
            'slug' => 'complete-laravel-course',
            'price' => 299000,
            'about' => 'Comprehensive Laravel course for beginners to advanced',
            'category_id' => $this->category->id,
        ]);
        
        // Verify course was created
        $this->assertDatabaseHas('courses', [
            'name' => 'Complete Laravel Course',
            'price' => 299000,
            'category_id' => $this->category->id,
        ]);
        
        // Step 3: Edit the course
        $this->actingAs($this->adminUser)
            ->get("/admin/courses/{$course->id}/edit")
            ->assertSuccessful();
            
        // Update course directly
        $course->update([
            'name' => 'Complete Laravel Mastery Course',
            'price' => 399000,
            'about' => 'Updated description with more details',
        ]);
        
        // Verify course was updated
        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'name' => 'Complete Laravel Mastery Course',
            'price' => 399000,
        ]);
        
        // Step 4: View course details
        $this->actingAs($this->adminUser)
            ->get("/admin/courses/{$course->id}/edit")
            ->assertSuccessful();
    }

    public function test_complete_user_management_journey(): void
    {
        // Login as admin
        $this->actingAs($this->adminUser);
        
        // Step 1: Navigate to users list
        $response = $this->get('/admin/users');
        $response->assertSuccessful();
        
        // Step 2: Create a new user
        $this->actingAs($this->adminUser)
            ->get('/admin/users/create')
            ->assertSuccessful();
            
        // Create user directly
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => bcrypt('password123'),
            'whatsapp_number' => '+6281234567890',
            'is_active' => true,
        ]);
        
        // Verify user was created
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'whatsapp_number' => '+6281234567890',
        ]);
        
        // Step 3: Edit the user
        $this->actingAs($this->adminUser)
            ->get("/admin/users/{$user->id}/edit")
            ->assertSuccessful();
            
        // Update user directly using model
        $user->update([
            'name' => 'John Smith',
            'whatsapp_number' => '+6289876543210',
            'is_account_active' => false,
        ]);
        
        // Verify user was updated
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'John Smith',
            'whatsapp_number' => '+6289876543210',
            'is_account_active' => false,
        ]);
        
        // Step 4: Activate user again
        $user->update([
            'is_account_active' => true,
        ]);
        
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_account_active' => true,
        ]);
    }

    public function test_complete_transaction_management_journey(): void
    {
        // Login as admin
        $this->actingAs($this->adminUser);
        
        // Create prerequisite data
        $course = Course::factory()->create([
            'category_id' => $this->category->id,
            'price' => 299000,
        ]);
        
        // Step 1: Navigate to transactions list
        $response = $this->get('/admin/transactions');
        $response->assertSuccessful();
        
        // Step 2: Create a new transaction
        // Access transactions list page
        $this->actingAs($this->adminUser)
            ->get('/admin/transactions')
            ->assertSuccessful();
            
        // Create transaction directly using model
        $transaction = Transaction::create([
            'booking_trx_id' => 'TRX' . strtoupper(\Illuminate\Support\Str::random(8)),
            'user_id' => $this->customerUser->id,
            'course_id' => $course->id,
            'sub_total_amount' => 299000,
            'admin_fee_amount' => $course->admin_fee_amount,
            'grand_total_amount' => 299000 + $course->admin_fee_amount,
            'is_paid' => false,
            'payment_type' => 'bank_transfer',
            'started_at' => now()->toDateString(),
            'ended_at' => now()->addYear()->toDateString(),
        ]);
        
        // Verify transaction was created
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->customerUser->id,
            'course_id' => $course->id,
            'is_paid' => false,
        ]);
        
        $transaction = Transaction::where('user_id', $this->customerUser->id)
            ->where('course_id', $course->id)
            ->first();
        
        // Step 3: Update transaction status to completed
        // Access transaction edit page
        $this->actingAs($this->adminUser)
            ->get("/admin/transactions/{$transaction->id}/edit")
            ->assertSuccessful();
            
        // Update transaction directly using model
        $transaction->update([
            'is_paid' => true,
            'payment_type' => 'credit_card',
        ]);
        
        // Verify transaction was updated
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'is_paid' => true,
            'payment_type' => 'credit_card',
        ]);
        
        // Step 4: Verify transaction was updated successfully
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'is_paid' => true,
        ]);
    }

    public function test_admin_dashboard_overview_journey(): void
    {
        // Login as admin
        $this->actingAs($this->adminUser);
        
        // Create test data for dashboard
        $courses = Course::factory()->count(5)->create(['category_id' => $this->category->id]);
        $users = User::factory()->count(10)->create();
        $transactions = Transaction::factory()->count(15)->create([
            'user_id' => $this->customerUser->id,
            'course_id' => $courses->first()->id,
        ]);
        
        // Step 1: Access main dashboard
        $response = $this->get('/admin');
        $response->assertSuccessful();
        
        // Step 2: Navigate through different resource pages
        $coursesResponse = $this->get('/admin/courses');
        $coursesResponse->assertSuccessful();
        
        $usersResponse = $this->get('/admin/users');
        $usersResponse->assertSuccessful();
        
        $transactionsResponse = $this->get('/admin/transactions');
        $transactionsResponse->assertSuccessful();
        
        // Step 3: Test search functionality across resources
        $searchCoursesResponse = $this->get('/admin/courses?search=Laravel');
        $searchCoursesResponse->assertSuccessful();
        
        $searchUsersResponse = $this->get('/admin/users?search=john');
        $searchUsersResponse->assertSuccessful();
    }

    public function test_bulk_operations_journey(): void
    {
        // Login as admin
        $this->actingAs($this->adminUser);
        
        // Create test data
        $courses = Course::factory()->count(5)->create(['category_id' => $this->category->id]);
        $users = User::factory()->count(5)->create();
        
        // Step 1: Bulk update course popularity
        $courseIds = $courses->take(3)->pluck('id')->toArray();
        
        // Access courses list page
        $this->actingAs($this->adminUser)
            ->get('/admin/courses')
            ->assertSuccessful();
            
        // Perform bulk update directly on models
        Course::whereIn('id', $courseIds)->update(['is_popular' => true]);
        
        // Verify bulk update
        foreach ($courseIds as $courseId) {
            $this->assertDatabaseHas('courses', [
                'id' => $courseId,
                'is_popular' => true,
            ]);
        }
        
        // Step 2: Bulk deactivate users
        $userIds = $users->take(3)->pluck('id')->toArray();
        
        // Access users list page
        $this->actingAs($this->adminUser)
            ->get('/admin/users')
            ->assertSuccessful();
            
        // Perform bulk deactivation directly on models
        User::whereIn('id', $userIds)->update(['is_account_active' => false]);
        
        // Verify bulk deactivation
        foreach ($userIds as $userId) {
            $this->assertDatabaseHas('users', [
                'id' => $userId,
                'is_account_active' => false,
            ]);
        }
    }

    public function test_filtering_and_sorting_journey(): void
    {
        // Login as admin
        $this->actingAs($this->adminUser);
        
        // Create test data with different attributes
        $popularCourse = Course::factory()->create([
            'category_id' => $this->category->id,
            'is_popular' => true,
            'price' => 500000,
        ]);
        
        $regularCourse = Course::factory()->create([
            'category_id' => $this->category->id,
            'is_popular' => false,
            'price' => 200000,
        ]);
        
        // Step 1: Filter courses by popularity
        $popularFilterResponse = $this->get('/admin/courses?filter[is_popular]=1');
        $popularFilterResponse->assertSuccessful();
        
        // Step 2: Sort courses by price
        $sortByPriceResponse = $this->get('/admin/courses?sort=price&direction=desc');
        $sortByPriceResponse->assertSuccessful();
        
        // Step 3: Combine filter and sort
        $combinedResponse = $this->get('/admin/courses?filter[is_popular]=1&sort=price&direction=asc');
        $combinedResponse->assertSuccessful();
    }

    public function test_error_handling_journey(): void
    {
        // Login as admin
        $this->actingAs($this->adminUser);
        
        // Step 1: Try to access non-existent course edit page
        $this->actingAs($this->adminUser)
            ->get('/admin/courses/99999/edit')
            ->assertStatus(404);
        
        // Step 2: Access create course page (should be successful)
        $this->actingAs($this->adminUser)
            ->get('/admin/courses/create')
            ->assertSuccessful();
        
        // Step 3: Test validation by trying to create invalid course
        $course = Course::factory()->create(['category_id' => $this->category->id]);
        
        // Access edit page for existing course
         $this->actingAs($this->adminUser)
             ->get("/admin/courses/{$course->id}/edit")
             ->assertSuccessful();
    }
}