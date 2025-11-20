<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mentors dipindahkan ke MentorSeeder untuk menghindari redundansi

        // Create students using factory
        $students = User::factory(7)->create();
        
        foreach ($students as $student) {
            $student->assignRole('student');
        }
    }
}
