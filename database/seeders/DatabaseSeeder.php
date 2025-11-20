<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Role and permission setup
            RolePermissionSeeder::class,
            
            // Admin user
            AdminSeeder::class,
            
            // Master data
            CategorySeeder::class,
            WhatsappSettingSeeder::class,
            EmailMessageTemplateSeeder::class,
            
            // Mentors
            MentorSeeder::class,
            
            // Users (students)
            UserSeeder::class,
            
            // Courses with all related data
            CourseSeeder::class,
            
            // Transactions
            TransactionSeeder::class,
        ]);
    }
}
