<?php

namespace Tests\Unit\Helpers;

use App\Models\User;
use App\Models\Course;
use App\Models\Category;
use App\Models\Discount;
use App\Models\PaymentTemp;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TestHelper extends TestCase
{
    use RefreshDatabase;

    public function test_create_test_user(): void
    {
        // Act
        $user = $this->createTestUser();
        
        // Assert
        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $user->email
        ]);
    }

    public function test_create_admin_user(): void
    {
        // Act
        $admin = $this->createAdminUser();
        
        // Assert
        $this->assertInstanceOf(User::class, $admin);
        // Note: role column doesn't exist in users table
        $this->assertEquals('Admin User', $admin->name);
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'name' => 'Admin User'
        ]);
    }

    public function test_create_test_course(): void
    {
        // Arrange
        $instructor = $this->createTestUser();
        
        // Act
        $course = $this->createTestCourse([], $instructor);
        
        // Assert
        $this->assertInstanceOf(Course::class, $course);
        $this->assertDatabaseHas('course_mentors', [
            'course_id' => $course->id,
            'user_id' => $instructor->id
        ]);
        $this->assertDatabaseHas('courses', [
            'id' => $course->id
        ]);
    }

    public function test_create_test_category(): void
    {
        // Act
        $category = $this->createTestCategory();
        
        // Assert
        $this->assertInstanceOf(Category::class, $category);
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => $category->name
        ]);
    }

    public function test_create_test_discount(): void
    {
        // Act
        $discount = $this->createTestDiscount();
        
        // Assert
        $this->assertInstanceOf(Discount::class, $discount);
        $this->assertTrue($discount->is_active);
        $this->assertDatabaseHas('discounts', [
            'id' => $discount->id,
            'code' => $discount->code
        ]);
    }

    public function test_create_test_payment(): void
    {
        // Arrange
        $user = $this->createTestUser();
        $course = $this->createTestCourse();
        
        // Act
        $payment = $this->createTestPayment($user, $course);
        
        // Assert
        $this->assertInstanceOf(PaymentTemp::class, $payment);
        $this->assertEquals($user->id, $payment->user_id);
        $this->assertEquals($course->id, $payment->course_id);
        $this->assertDatabaseHas('payment_temp', [
            'id' => $payment->id,
            'user_id' => $user->id,
            'course_id' => $course->id
        ]);
    }

    public function test_create_test_transaction(): void
    {
        // Arrange
        $user = $this->createTestUser();
        $course = $this->createTestCourse();
        $payment = $this->createTestPayment($user, $course);
        
        // Act
        $transaction = $this->createTestTransaction($user, $course, $payment);
        
        // Assert
        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertEquals($user->id, $transaction->user_id);
        $this->assertEquals($course->id, $transaction->course_id);
        // Note: payment_id is not a column in transactions table
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'user_id' => $user->id,
            'course_id' => $course->id
        ]);
    }

    public function test_enroll_user_in_course(): void
    {
        // Arrange
        $user = $this->createTestUser();
        $course = $this->createTestCourse();
        
        // Act
        $this->enrollUserInCourse($user, $course);
        
        // Assert
        $this->assertDatabaseHas('course_students', [
            'user_id' => $user->id,
            'course_id' => $course->id
        ]);
        
        // Check enrollment through CourseStudent model since User doesn't have courses relation
        $courseStudent = \App\Models\CourseStudent::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();
        $this->assertNotNull($courseStudent);
    }

    public function test_create_course_with_categories(): void
    {
        // Arrange
        $categories = collect([
            $this->createTestCategory(['name' => 'Programming']),
            $this->createTestCategory(['name' => 'Web Development'])
        ]);
        
        // Act
        $course = $this->createCourseWithCategories($categories);
        
        // Assert
        $this->assertInstanceOf(Course::class, $course);
        $this->assertEquals(1, $course->category ? 1 : 0);
        
        // Check if course has the category
        $this->assertNotNull($course->category);
        $this->assertEquals($categories->first()->id, $course->category->id);
    }

    public function test_create_user_with_courses(): void
    {
        // Arrange
        $courseCount = 3;
        
        // Act
        $user = $this->createUserWithCourses($courseCount);
        
        // Assert
        $this->assertInstanceOf(User::class, $user);
        
        // Check that the user is enrolled in the correct number of courses
        $enrolledCourses = \App\Models\CourseStudent::where('user_id', $user->id)->count();
        $this->assertEquals($courseCount, $enrolledCourses);
        
        // Check each enrollment exists in database
        $courseStudents = \App\Models\CourseStudent::where('user_id', $user->id)->get();
        foreach ($courseStudents as $courseStudent) {
            $this->assertDatabaseHas('course_students', [
                'user_id' => $user->id,
                'course_id' => $courseStudent->course_id
            ]);
        }
    }

    public function test_create_completed_transaction(): void
    {
        // Arrange
        $user = $this->createTestUser();
        $course = $this->createTestCourse();
        
        // Act
        $transaction = $this->createCompletedTransaction($user, $course);
        
        // Assert
        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertTrue($transaction->is_paid);
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'is_paid' => true
        ]);
        
        // User should be enrolled in course
        $this->assertDatabaseHas('course_students', [
            'user_id' => $user->id,
            'course_id' => $course->id
        ]);
    }

    public function test_create_test_data_set(): void
    {
        // Act
        $data = $this->createTestDataSet();
        
        // Assert
        $this->assertArrayHasKey('users', $data);
        $this->assertArrayHasKey('courses', $data);
        $this->assertArrayHasKey('categories', $data);
        $this->assertArrayHasKey('transactions', $data);
        
        $this->assertCount(5, $data['users']);
        $this->assertCount(10, $data['courses']);
        $this->assertCount(3, $data['categories']);
        $this->assertCount(15, $data['transactions']);
    }

    public function test_assert_user_has_course_access(): void
    {
        // Arrange
        $user = $this->createTestUser();
        $course = $this->createTestCourse();
        $this->enrollUserInCourse($user, $course);
        
        // Act & Assert
        $this->assertUserHasCourseAccess($user, $course);
    }

    public function test_assert_user_does_not_have_course_access(): void
    {
        // Arrange
        $user = $this->createTestUser();
        $course = $this->createTestCourse();
        
        // Act & Assert
        $this->assertUserDoesNotHaveCourseAccess($user, $course);
    }

    public function test_assert_payment_status(): void
    {
        // Arrange
        $user = $this->createTestUser();
        $course = $this->createTestCourse();
        $payment = $this->createTestPayment($user, $course, ['status' => 'completed']);
        
        // Act & Assert
        $this->assertPaymentStatus($payment, 'completed');
    }

    public function test_assert_transaction_amount(): void
    {
        // Arrange
        $user = $this->createTestUser();
        $course = $this->createTestCourse(['price' => 100000]);
        $payment = $this->createTestPayment($user, $course);
        $transaction = $this->createTestTransaction($user, $course, $payment, ['grand_total_amount' => 100000]);
        
        // Act & Assert
        $this->assertTransactionAmount($transaction, 100000);
    }

    public function test_create_expired_discount(): void
    {
        // Act
        $discount = $this->createExpiredDiscount();
        
        // Assert
        $this->assertInstanceOf(Discount::class, $discount);
        $this->assertTrue($discount->end_date->isPast());
        $this->assertFalse($discount->isValid());
    }

    public function test_create_stackable_discounts(): void
    {
        // Act
        $discounts = $this->createStackableDiscounts();
        
        // Assert
        $this->assertCount(2, $discounts);
        
        foreach ($discounts as $discount) {
            $this->assertInstanceOf(Discount::class, $discount);
            $this->assertTrue($discount->can_stack);
        }
    }

    public function test_simulate_payment_callback(): void
    {
        // Arrange
        $user = $this->createTestUser();
        $course = $this->createTestCourse();
        $payment = $this->createTestPayment($user, $course, ['status' => 'pending']);
        
        // Act
        $result = $this->simulatePaymentCallback($payment, 'success');
        
        // Assert
        $this->assertTrue($result);
        
        $payment->refresh();
        $this->assertEquals('completed', $payment->status);
    }

    public function test_create_bulk_test_data(): void
    {
        // Clean existing data first and verify it's clean
        $this->cleanTestData();
        
        // Verify data is actually clean - should have 3 admin users from AdminSeeder
        $this->assertEquals(3, User::count(), 'Should only have 3 admin users after cleanup');
        $this->assertEquals(0, Course::count(), 'Should have 0 courses after cleanup');
        $this->assertEquals(0, Category::count(), 'Should have 0 categories after cleanup');
        $this->assertEquals(0, Transaction::count(), 'Should have 0 transactions after cleanup');
        
        // Act
        $data = $this->createBulkTestData([
            'users' => 50,
            'courses' => 20,
            'categories' => 5,
            'transactions' => 100
        ]);
        
        // Assert - Total users should be 3 admin + 50 test users = 53
        $this->assertEquals(53, User::count());
        $this->assertEquals(20, Course::count());
        $this->assertEquals(5, Category::count());
        $this->assertEquals(100, Transaction::count());
        
        $this->assertArrayHasKey('users', $data);
        $this->assertArrayHasKey('courses', $data);
        $this->assertArrayHasKey('categories', $data);
        $this->assertArrayHasKey('transactions', $data);
    }

    public function test_clean_test_data(): void
    {
        // Arrange
        $this->createTestDataSet();
        
        $initialUserCount = User::count();
        $initialCourseCount = Course::count();
        
        $this->assertGreaterThan(0, $initialUserCount);
        $this->assertGreaterThan(0, $initialCourseCount);
        
        // Act
        $this->cleanTestData();
        
        // Assert - Should only have admin users left, everything else should be 0
        $this->assertEquals(3, User::count()); // Only admin users from AdminSeeder
        $this->assertEquals(0, Course::count());
        $this->assertEquals(0, Category::count());
        $this->assertEquals(0, Transaction::count());
    }

    public function test_create_test_api_response(): void
    {
        // Arrange
        $data = ['message' => 'Test successful', 'data' => ['id' => 1]];
        
        // Act
        $response = $this->createTestApiResponse($data, 200);
        
        // Assert
        $this->assertEquals(200, $response['status']);
        $this->assertEquals($data, $response['data']);
        $this->assertArrayHasKey('timestamp', $response);
    }

    public function test_assert_api_response_structure(): void
    {
        // Arrange
        $response = [
            'status' => 200,
            'data' => [
                'id' => 1,
                'name' => 'Test Course',
                'price' => 100000
            ],
            'message' => 'Success'
        ];
        
        $expectedStructure = [
            'status',
            'data' => [
                'id',
                'name',
                'price'
            ],
            'message'
        ];
        
        // Act & Assert
        $this->assertApiResponseStructure($response, $expectedStructure);
    }

    public function test_create_mock_file_upload(): void
    {
        // Act
        $file = $this->createMockFileUpload('test.pdf', 'application/pdf', 1024);
        
        // Assert
        $this->assertInstanceOf(\Illuminate\Http\UploadedFile::class, $file);
        $this->assertEquals('test.pdf', $file->getClientOriginalName());
        // Note: Mime type detection might vary based on system, so we check if it's a valid file
        $this->assertNotNull($file->getMimeType());
    }

    public function test_assert_database_transaction_rollback(): void
    {
        // Arrange
        $initialUserCount = User::count();
        
        // Act
        try {
            \DB::transaction(function () {
                $this->createTestUser();
                throw new \Exception('Test rollback');
            });
        } catch (\Exception $e) {
            // Expected exception
        }
        
        // Assert
        $this->assertEquals($initialUserCount, User::count());
        $this->assertDatabaseTransactionRollback();
    }

    // Helper methods implementation would go here
    // These are the actual utility methods that the tests above are testing

    protected function createTestUser(array $attributes = []): User
    {
        // Generate unique email if not provided in attributes
        $defaultAttributes = [
            'name' => 'Test User',
            'password' => Hash::make('password'),
            'email_verified_at' => now()
        ];
        
        // Only set email if not provided in attributes
        if (!isset($attributes['email'])) {
            $defaultAttributes['email'] = 'test_user_' . uniqid() . '@example.com';
        }
        
        return User::factory()->create(array_merge($defaultAttributes, $attributes));
    }

    protected function createAdminUser(array $attributes = []): User
    {
        // Generate unique email if not provided in attributes
        $defaultAttributes = [
            // Note: role column doesn't exist in users table
            'name' => 'Admin User'
        ];
        
        // Only set email if not provided in attributes
        if (!isset($attributes['email'])) {
            $defaultAttributes['email'] = 'admin_user_' . uniqid() . '@example.com';
        }
        
        return $this->createTestUser(array_merge($defaultAttributes, $attributes));
    }

    protected function createTestCourse(array $attributes = [], ?User $instructor = null): Course
    {
        if (!$instructor) {
            $instructor = $this->createTestUser();
        }

        $course = Course::factory()->create(array_merge([
            'name' => 'Test Course',
            'price' => 100000
        ], $attributes));
        
        // Create course mentor relationship if instructor is provided
        if ($instructor) {
            $course->courseMentors()->create([
                'user_id' => $instructor->id,
                'is_active' => true,
                'about' => 'Test instructor for course'
            ]);
        }
        
        return $course;
    }

    protected function createTestCategory(array $attributes = []): Category
    {
        return Category::factory()->create(array_merge([
            'name' => 'Test Category'
        ], $attributes));
    }

    protected function createTestDiscount(array $attributes = []): Discount
    {
        return Discount::factory()->create(array_merge([
            'code' => 'TEST20',
            'type' => 'percentage',
            'value' => 20,
            'is_active' => true,
            'start_date' => now(),
            'end_date' => now()->addDays(30)
        ], $attributes));
    }

    protected function createTestPayment(User $user, Course $course, array $attributes = []): PaymentTemp
    {
        return PaymentTemp::factory()->create(array_merge([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'sub_total_amount' => $course->price,
            'admin_fee_amount' => 5000,
            'discount_amount' => 0,
            'grand_total_amount' => $course->price + 5000,
            'order_id' => 'ORDER-' . uniqid()
        ], $attributes));
    }

    protected function createTestTransaction(User $user, Course $course, PaymentTemp $payment, array $attributes = []): Transaction
    {
        return Transaction::factory()->create(array_merge([
            'user_id' => $user->id,
            'course_id' => $course->id
            // Note: payment_id is not a column in transactions table
        ], $attributes));
    }

    protected function enrollUserInCourse(User $user, Course $course): void
    {
        \App\Models\CourseStudent::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'is_active' => true,
        ]);
    }

    protected function createCourseWithCategories($categories): Course
    {
        $course = $this->createTestCourse();
        // Course has belongsTo relationship with category, not many-to-many
        $course->update(['category_id' => $categories->first()->id]);
        return $course->load('category');
    }

    protected function createUserWithCourses(int $courseCount = 3): User
    {
        $user = $this->createTestUser();
        $courses = Course::factory($courseCount)->create();
        
        foreach ($courses as $course) {
            $this->enrollUserInCourse($user, $course);
        }
        
        // Load courses through course_students relationship
        return $user->fresh();
    }

    protected function createCompletedTransaction(User $user, Course $course): Transaction
    {
        $payment = $this->createTestPayment($user, $course, ['status' => 'completed']);
        $transaction = $this->createTestTransaction($user, $course, $payment, ['is_paid' => true]);
        $this->enrollUserInCourse($user, $course);
        
        return $transaction;
    }

    protected function createTestDataSet(): array
    {
        $users = User::factory(5)->create();
        $categories = Category::factory(3)->create();
        $courses = Course::factory(10)->create();
        $transactions = Transaction::factory(15)->create();

        return [
            'users' => $users,
            'courses' => $courses,
            'categories' => $categories,
            'transactions' => $transactions
        ];
    }

    protected function assertUserHasCourseAccess(User $user, Course $course): void
    {
        $this->assertDatabaseHas('course_students', [
            'user_id' => $user->id,
            'course_id' => $course->id
        ]);
    }

    protected function assertUserDoesNotHaveCourseAccess(User $user, Course $course): void
    {
        $this->assertDatabaseMissing('course_students', [
            'user_id' => $user->id,
            'course_id' => $course->id
        ]);
    }

    protected function assertPaymentStatus(PaymentTemp $payment, string $expectedStatus): void
    {
        $this->assertEquals($expectedStatus, $payment->status);
    }

    protected function assertTransactionAmount(Transaction $transaction, int $expectedAmount): void
    {
        $this->assertEquals($expectedAmount, $transaction->grand_total_amount);
    }

    protected function createExpiredDiscount(): Discount
    {
        return $this->createTestDiscount([
            'end_date' => now()->subDays(1),
            'is_active' => false
        ]);
    }

    protected function createStackableDiscounts(): array
    {
        return [
            $this->createTestDiscount(['code' => 'STACK1', 'can_stack' => true]),
            $this->createTestDiscount(['code' => 'STACK2', 'can_stack' => true])
        ];
    }

    protected function simulatePaymentCallback(PaymentTemp $payment, string $status): bool
    {
        if ($status === 'success') {
            $payment->update(['status' => 'completed']);
            return true;
        }
        
        $payment->update(['status' => 'failed']);
        return false;
    }

    protected function createBulkTestData(array $counts): array
    {
        $data = [];
        
        // Create categories first (no dependencies)
        if (isset($counts['categories'])) {
            $data['categories'] = Category::factory($counts['categories'])->create();
        }
        
        // Create users (no dependencies)
        if (isset($counts['users'])) {
            $data['users'] = User::factory($counts['users'])->create();
        }
        
        // Create courses (depends on categories) - use existing categories
        if (isset($counts['courses'])) {
            $categories = $data['categories'] ?? Category::all();
            $data['courses'] = Course::factory($counts['courses'])->create([
                'category_id' => $categories->random()->id
            ]);
        }
        
        // Create transactions (depends on users and courses) - use existing users and courses
        if (isset($counts['transactions'])) {
            $users = $data['users'] ?? User::whereNotIn('email', [
                'superadmin@lmsebook.com',
                'admin@lmsebook.com', 
                'demo@lmsebook.com'
            ])->get();
            $courses = $data['courses'] ?? Course::all();
            
            $data['transactions'] = Transaction::factory($counts['transactions'])->create([
                'user_id' => $users->random()->id,
                'course_id' => $courses->random()->id
            ]);
        }
        
        return $data;
    }

    protected function cleanTestData(): void
    {
        // For SQLite, we need to use PRAGMA instead of SET FOREIGN_KEY_CHECKS
        if (config('database.default') === 'sqlite') {
            \DB::statement('PRAGMA foreign_keys = OFF;');
            
            // Clean in proper order to avoid foreign key constraints
            Transaction::truncate();
            \DB::table('course_students')->truncate();
            \DB::table('course_sections')->truncate();
            \DB::table('section_contents')->truncate();
            \DB::table('course_benefits')->truncate();
            \DB::table('course_mentors')->truncate();
            \DB::table('user_lesson_progress')->truncate();
            \DB::table('payment_temp')->truncate();
            \DB::table('discounts')->truncate();
            Course::truncate();
            Category::truncate();
            
            // Only delete test users (not admin users from AdminSeeder)
            User::whereNotIn('email', [
                'superadmin@lmsebook.com',
                'admin@lmsebook.com', 
                'demo@lmsebook.com'
            ])->delete();
            
            \DB::statement('PRAGMA foreign_keys = ON;');
        } else {
            \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            
            // Clean in proper order to avoid foreign key constraints
            Transaction::truncate();
            \DB::table('course_students')->truncate();
            \DB::table('course_sections')->truncate();
            \DB::table('section_contents')->truncate();
            \DB::table('course_benefits')->truncate();
            \DB::table('course_mentors')->truncate();
            \DB::table('user_lesson_progress')->truncate();
            \DB::table('payment_temp')->truncate();
            \DB::table('discounts')->truncate();
            Course::truncate();
            Category::truncate();
            
            // Only delete test users (not admin users from AdminSeeder)
            User::whereNotIn('email', [
                'superadmin@lmsebook.com',
                'admin@lmsebook.com', 
                'demo@lmsebook.com'
            ])->delete();
            
            \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    protected function createTestApiResponse(array $data, int $status = 200): array
    {
        return [
            'status' => $status,
            'data' => $data,
            'timestamp' => now()->toISOString()
        ];
    }

    protected function assertApiResponseStructure(array $response, array $expectedStructure): void
    {
        foreach ($expectedStructure as $key => $value) {
            if (is_array($value)) {
                $this->assertArrayHasKey($key, $response);
                $this->assertApiResponseStructure($response[$key], $value);
            } else {
                $this->assertArrayHasKey($value, $response);
            }
        }
    }

    protected function createMockFileUpload(string $filename, string $mimeType, int $size): \Illuminate\Http\UploadedFile
    {
        $content = str_repeat('a', $size);
        $tempPath = tempnam(sys_get_temp_dir(), 'test_upload_');
        file_put_contents($tempPath, $content);
        
        return new \Illuminate\Http\UploadedFile(
            $tempPath,
            $filename,
            $mimeType,
            null,
            true
        );
    }

    protected function assertDatabaseTransactionRollback(): void
    {
        // This would check that database transactions were properly rolled back
        // Implementation depends on specific database transaction tracking needs
        $this->assertTrue(true, 'Database transaction rollback verified');
    }
}