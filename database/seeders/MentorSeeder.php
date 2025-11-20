<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MentorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mentors = [
            [
                'name' => 'John Doe',
                'email' => 'mentor1@example.com',
                'password' => Hash::make('password'),
                'photo' => 'https://via.placeholder.com/200x200/6366f1/ffffff?text=John',
                'whatsapp_number' => '+6281234567890',
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'mentor2@example.com',
                'password' => Hash::make('password'),
                'photo' => 'https://via.placeholder.com/200x200/ec4899/ffffff?text=Jane',
                'whatsapp_number' => '+6281234567891',
            ],
            [
                'name' => 'David Wilson',
                'email' => 'mentor3@example.com',
                'password' => Hash::make('password'),
                'photo' => 'https://via.placeholder.com/200x200/10b981/ffffff?text=David',
                'whatsapp_number' => '+6281234567892',
            ],
        ];

        foreach ($mentors as $data) {
            $mentor = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => $data['password'],
                    'photo' => $data['photo'],
                    'whatsapp_number' => $data['whatsapp_number'],
                    'email_verified_at' => now(),
                    'is_account_active' => true,
                ]
            );

            $mentor->assignRole('mentor');
        }
    }
}

