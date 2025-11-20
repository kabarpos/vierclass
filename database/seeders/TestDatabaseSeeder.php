<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Course;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class TestDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database for testing.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'view_courses',
            'create_courses',
            'edit_courses',
            'delete_courses',
            'view_transactions',
            'create_transactions',
            'edit_transactions',
            'delete_transactions',
            'view_categories',
            'create_categories',
            'edit_categories',
            'delete_categories',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $customerRole = Role::firstOrCreate(['name' => 'customer']);
        $mentorRole = Role::firstOrCreate(['name' => 'mentor']);

        // Assign all permissions to admin role
        $adminRole->syncPermissions(Permission::all());

        // Assign limited permissions to mentor role
        $mentorRole->syncPermissions([
            'view_courses',
            'create_courses',
            'edit_courses',
            'view_transactions',
            'view_users',
        ]);

        // Create admin user
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Test Admin',
                'password' => bcrypt('password'),
                'whatsapp_number' => '+6281234567890',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $adminUser->assignRole($adminRole);

        // Create mentor user
        $instructorUser = User::firstOrCreate(
            ['email' => 'mentor@test.com'],
            [
                'name' => 'Test Mentor',
                'password' => bcrypt('password'),
                'whatsapp_number' => '+6281234567891',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $instructorUser->assignRole($mentorRole);

        // Create customer users
        $customerUsers = [];
        for ($i = 1; $i <= 5; $i++) {
            $customerUser = User::firstOrCreate(
                ['email' => "customer{$i}@test.com"],
                [
                    'name' => "Test Customer {$i}",
                    'password' => bcrypt('password'),
                    'whatsapp_number' => "+628123456789{$i}",
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
            $customerUser->assignRole($customerRole);
            $customerUsers[] = $customerUser;
        }

        // Create categories
        $categories = [
            [
                'name' => 'Web Development',
                'description' => 'Learn web development technologies',
                'is_active' => true,
            ],
            [
                'name' => 'Mobile Development',
                'description' => 'Learn mobile app development',
                'is_active' => true,
            ],
            [
                'name' => 'Data Science',
                'description' => 'Learn data science and analytics',
                'is_active' => true,
            ],
            [
                'name' => 'DevOps',
                'description' => 'Learn DevOps practices and tools',
                'is_active' => false,
            ],
        ];

        $createdCategories = [];
        foreach ($categories as $categoryData) {
            $category = Category::firstOrCreate(
                ['name' => $categoryData['name']],
                $categoryData
            );
            $createdCategories[] = $category;
        }

        // Create courses
        $courses = [
            [
                'name' => 'Complete Laravel Course',
                'description' => 'Learn Laravel from beginner to advanced level',
                'price' => 299000,
                'is_popular' => true,
                'benefits' => [
                    'Master Laravel fundamentals',
                    'Build real-world applications',
                    'Learn best practices',
                ],
                'category_id' => $createdCategories[0]->id,
            ],
            [
                'name' => 'React Native Mastery',
                'description' => 'Build mobile apps with React Native',
                'price' => 399000,
                'is_popular' => true,
                'benefits' => [
                    'Cross-platform development',
                    'Native performance',
                    'Code reusability',
                ],
                'category_id' => $createdCategories[1]->id,
            ],
            [
                'name' => 'Python Data Analysis',
                'description' => 'Analyze data with Python and pandas',
                'price' => 249000,
                'is_popular' => false,
                'benefits' => [
                    'Data manipulation',
                    'Statistical analysis',
                    'Data visualization',
                ],
                'category_id' => $createdCategories[2]->id,
            ],
            [
                'name' => 'Vue.js Fundamentals',
                'description' => 'Learn Vue.js framework basics',
                'price' => 199000,
                'is_popular' => false,
                'benefits' => [
                    'Component-based architecture',
                    'Reactive data binding',
                    'Modern JavaScript',
                ],
                'category_id' => $createdCategories[0]->id,
            ],
            [
                'name' => 'Docker for Developers',
                'description' => 'Containerize your applications',
                'price' => 179000,
                'is_popular' => false,
                'benefits' => [
                    'Container fundamentals',
                    'Development workflow',
                    'Production deployment',
                ],
                'category_id' => $createdCategories[3]->id,
            ],
        ];

        $createdCourses = [];
        foreach ($courses as $courseData) {
            $course = Course::firstOrCreate(
                ['name' => $courseData['name']],
                $courseData
            );
            $createdCourses[] = $course;
        }

        // Create transactions
        $transactionStatuses = ['pending', 'completed', 'failed', 'cancelled'];
        $paymentMethods = ['bank_transfer', 'credit_card', 'e_wallet', 'cash'];

        foreach ($customerUsers as $index => $customer) {
            // Each customer gets 2-3 transactions
            $transactionCount = rand(2, 3);
            
            for ($i = 0; $i < $transactionCount; $i++) {
                $course = $createdCourses[array_rand($createdCourses)];
                $subtotal = $course->price;
                $totalAmount = $subtotal; // No tax calculation
                
                Transaction::firstOrCreate(
                    [
                        'user_id' => $customer->id,
                        'course_id' => $course->id,
                    ],
                    [
                        'subtotal' => $subtotal,
                        'total_amount' => $totalAmount,
                        'payment_status' => $transactionStatuses[array_rand($transactionStatuses)],
                        'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                        'created_at' => now()->subDays(rand(1, 30)),
                        'updated_at' => now()->subDays(rand(0, 5)),
                    ]
                );
            }
        }

        // Create some additional test data for edge cases
        
        // Inactive user
        $inactiveUser = User::firstOrCreate(
            ['email' => 'inactive@test.com'],
            [
                'name' => 'Inactive User',
                'password' => bcrypt('password'),
                'whatsapp_number' => '+6281234567899',
                'is_active' => false,
                'email_verified_at' => now(),
            ]
        );
        $inactiveUser->assignRole($customerRole);

        // High-priced course
        $premiumCourse = Course::firstOrCreate(
            ['name' => 'Premium Full-Stack Bootcamp'],
            [
                'name' => 'Premium Full-Stack Bootcamp',
                'description' => 'Comprehensive full-stack development course',
                'price' => 999000,
                'is_popular' => true,
                'benefits' => [
                    'Complete full-stack curriculum',
                    'Personal mentorship',
                    'Job placement assistance',
                    'Lifetime access',
                ],
                'category_id' => $createdCategories[0]->id,
            ]
        );

        // Free course
        $freeCourse = Course::firstOrCreate(
            ['name' => 'Introduction to Programming'],
            [
                'name' => 'Introduction to Programming',
                'description' => 'Basic programming concepts for beginners',
                'price' => 0,
                'is_popular' => true,
                'benefits' => [
                    'Programming fundamentals',
                    'Problem-solving skills',
                    'Code structure basics',
                ],
                'category_id' => $createdCategories[0]->id,
            ]
        );
    }
}
