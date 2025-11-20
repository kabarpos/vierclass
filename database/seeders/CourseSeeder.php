<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Category;
use App\Models\CourseBenefit;
use App\Models\CourseSection;
use App\Models\SectionContent;
use App\Models\CourseMentor;
use App\Models\CourseStudent;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get categories and mentors
        $categories = Category::all();
        $instructors = User::role('mentor')->get();
        $students = User::role('student')->get();

        $coursesData = [
            [
                'name' => 'Complete Laravel Development Course',
                'thumbnail' => 'https://via.placeholder.com/400x300/ef4444/ffffff?text=Laravel+Course',
                'about' => 'Master Laravel from beginner to advanced level. Learn to build modern web applications with Laravel framework, including authentication, database design, API development, and deployment.',
                'is_popular' => true,
                'category_name' => 'Web Development',
                'price' => 299000,
                'admin_fee_amount' => 5000,
            ],
            [
                'name' => 'React Native Mobile App Development',
                'thumbnail' => 'https://via.placeholder.com/400x300/3b82f6/ffffff?text=React+Native',
                'about' => 'Build cross-platform mobile applications with React Native. Learn navigation, state management, API integration, and publishing to app stores.',
                'is_popular' => true,
                'category_name' => 'Mobile Development',
                'price' => 399000,
                'admin_fee_amount' => 7500,
            ],
            [
                'name' => 'Python Data Science Bootcamp',
                'thumbnail' => 'https://via.placeholder.com/400x300/059669/ffffff?text=Python+Data+Science',
                'about' => 'Complete data science course covering Python, pandas, numpy, matplotlib, machine learning algorithms, and real-world projects.',
                'is_popular' => false,
                'category_name' => 'Data Science',
                'price' => 349000,
                'admin_fee_amount' => 6000,
            ],
            [
                'name' => 'Modern UI/UX Design Fundamentals',
                'thumbnail' => 'https://via.placeholder.com/400x300/8b5cf6/ffffff?text=UI/UX+Design',
                'about' => 'Learn design principles, user research, wireframing, prototyping, and design systems. Master Figma and Adobe XD.',
                'is_popular' => true,
                'category_name' => 'UI/UX Design',
                'price' => 249000,
                'admin_fee_amount' => 4500,
            ],
            [
                'name' => 'Digital Marketing Strategy Mastery',
                'thumbnail' => 'https://via.placeholder.com/400x300/f59e0b/ffffff?text=Digital+Marketing',
                'about' => 'Complete digital marketing course covering SEO, SEM, social media marketing, content marketing, and analytics.',
                'is_popular' => false,
                'category_name' => 'Digital Marketing',
                'price' => 199000,
                'admin_fee_amount' => 3500,
            ],
        ];

        foreach ($coursesData as $courseData) {
            // Find category
            $category = $categories->where('name', $courseData['category_name'])->first();
            
            // Create course
            $course = Course::create([
                'name' => $courseData['name'],
                'thumbnail' => $courseData['thumbnail'],
                'about' => $courseData['about'],
                'is_popular' => $courseData['is_popular'],
                'category_id' => $category->id,
                'price' => $courseData['price'],
                'admin_fee_amount' => $courseData['admin_fee_amount'],
            ]);

            // Create course benefits (3-5 benefits per course)
            $benefitsCount = rand(3, 5);
            CourseBenefit::factory($benefitsCount)->create([
                'course_id' => $course->id,
            ]);

            // Create course sections (3-6 sections per course)
            $sectionsCount = rand(3, 6);
            for ($i = 1; $i <= $sectionsCount; $i++) {
                $section = CourseSection::factory()->create([
                    'course_id' => $course->id,
                    'position' => $i,
                ]);

                // Create section contents (2-4 contents per section)
                $contentsCount = rand(2, 4);
                SectionContent::factory($contentsCount)->create([
                    'course_section_id' => $section->id,
                ]);
            }

            // Assign random mentor to course
            $randomInstructor = $instructors->random();
            CourseMentor::create([
                'user_id' => $randomInstructor->id,
                'course_id' => $course->id,
                'about' => 'Experienced mentor specializing in ' . $category->name . '. Passionate about teaching and helping students achieve their goals.',
                'is_active' => true,
            ]);

            // Enroll random students (3-7 students per course)
            $enrollmentCount = rand(3, 7);
            $randomStudents = $students->random($enrollmentCount);
            
            foreach ($randomStudents as $student) {
                CourseStudent::create([
                    'user_id' => $student->id,
                    'course_id' => $course->id,
                    'is_active' => true,
                ]);
            }
        }
    }
}
