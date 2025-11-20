<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Use microtime and random string for truly unique emails
        $uniqueId = microtime(true) . '_' . Str::random(8);
        
        return [
            'name' => fake()->name(),
            'email' => 'test_user_' . $uniqueId . '@example.com',
            'email_verified_at' => now(),
            'whatsapp_verified_at' => now(),
            'is_account_active' => true,
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'photo' => fake()->optional(0.3)->randomElement([
                'https://via.placeholder.com/200x200/6366f1/ffffff?text=User',
                'https://via.placeholder.com/200x200/10b981/ffffff?text=Avatar',
                'https://via.placeholder.com/200x200/f59e0b/ffffff?text=Profile'
            ]),
            'whatsapp_number' => fake()->phoneNumber(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (\App\Models\User $user) {
            // Assign student role by default for testing
            if (\Spatie\Permission\Models\Role::where('name', 'student')->exists()) {
                $user->assignRole('student');
            }
        });
    }
}
