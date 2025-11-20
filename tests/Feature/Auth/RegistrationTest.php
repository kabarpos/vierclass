<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;



    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'whatsapp_number' => '+6281234567890',
        ]);

        // User should not be authenticated immediately after registration
        // They need to verify their account first
        $this->assertGuest();
        
        // Should redirect to login with success message
        $response->assertRedirect(route('login'));
        
        // Verify user was created in database
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'is_account_active' => false, // Account should be inactive until verified
        ]);
        
        // Verify user has student role
        $user = \App\Models\User::where('email', 'test@example.com')->first();
        $this->assertTrue($user->hasRole('student'));
    }
}
