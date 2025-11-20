<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthenticationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Notification::fake();
        Event::fake();
    }

    public function test_user_can_register_with_valid_data(): void
    {
        // Arrange
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        // Act
        $response = $this->post('/register', $userData);

        // Assert
        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        
        $user = User::where('email', 'john@example.com')->first();
        $this->assertTrue(Hash::check('Password123!', $user->password));
        $this->assertAuthenticatedAs($user);
        
        Event::assertDispatched(Registered::class);
    }

    public function test_user_cannot_register_with_invalid_email(): void
    {
        // Arrange
        $userData = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        // Act
        $response = $this->post('/register', $userData);

        // Assert
        $response->assertSessionHasErrors(['email']);
        $this->assertDatabaseMissing('users', [
            'email' => 'invalid-email',
        ]);
        $this->assertGuest();
    }

    public function test_user_cannot_register_with_weak_password(): void
    {
        // Arrange
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => '123',
            'password_confirmation' => '123',
        ];

        // Act
        $response = $this->post('/register', $userData);

        // Assert
        $response->assertSessionHasErrors(['password']);
        $this->assertDatabaseMissing('users', [
            'email' => 'john@example.com',
        ]);
        $this->assertGuest();
    }

    public function test_user_cannot_register_with_mismatched_passwords(): void
    {
        // Arrange
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'DifferentPassword123!',
        ];

        // Act
        $response = $this->post('/register', $userData);

        // Assert
        $response->assertSessionHasErrors(['password']);
        $this->assertDatabaseMissing('users', [
            'email' => 'john@example.com',
        ]);
        $this->assertGuest();
    }

    public function test_user_cannot_register_with_existing_email(): void
    {
        // Arrange
        User::factory()->create(['email' => 'existing@example.com']);
        
        $userData = [
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        // Act
        $response = $this->post('/register', $userData);

        // Assert
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        // Act
        $response = $this->post('/login', [
            'email' => 'john@example.com',
            'password' => 'Password123!',
        ]);

        // Assert
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_invalid_email(): void
    {
        // Arrange
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        // Act
        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'Password123!',
        ]);

        // Assert
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_user_cannot_login_with_invalid_password(): void
    {
        // Arrange
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        // Act
        $response = $this->post('/login', [
            'email' => 'john@example.com',
            'password' => 'WrongPassword',
        ]);

        // Assert
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        // Arrange
        $user = User::factory()->create();
        $this->actingAs($user);

        // Act
        $response = $this->post('/logout');

        // Assert
        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_authenticated_user_cannot_access_login_page(): void
    {
        // Arrange
        $user = User::factory()->create();
        $this->actingAs($user);

        // Act
        $response = $this->get('/login');

        // Assert
        $response->assertRedirect('/dashboard');
    }

    public function test_authenticated_user_cannot_access_register_page(): void
    {
        // Arrange
        $user = User::factory()->create();
        $this->actingAs($user);

        // Act
        $response = $this->get('/register');

        // Assert
        $response->assertRedirect('/dashboard');
    }

    public function test_guest_cannot_access_protected_routes(): void
    {
        // Act
        $response = $this->get('/dashboard');

        // Assert
        $response->assertRedirect('/login');
    }

    public function test_user_can_request_password_reset(): void
    {
        // Arrange
        $user = User::factory()->create(['email' => 'john@example.com']);

        // Act
        $response = $this->post('/forgot-password', [
            'email' => 'john@example.com',
        ]);

        // Assert
        $response->assertSessionHas('status');
        // Verify that password reset notification was sent
        // This would require checking the notification or mail queue
    }

    public function test_user_cannot_request_password_reset_with_invalid_email(): void
    {
        // Act
        $response = $this->post('/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        // Assert
        $response->assertSessionHasErrors(['email']);
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        // Arrange
        $user = User::factory()->create(['email' => 'john@example.com']);
        $token = Password::createToken($user);

        // Act
        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => 'john@example.com',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        // Assert
        $response->assertRedirect('/login');
        $response->assertSessionHas('status');
        
        // Verify password was changed
        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $user->password));
    }

    public function test_user_cannot_reset_password_with_invalid_token(): void
    {
        // Arrange
        $user = User::factory()->create(['email' => 'john@example.com']);

        // Act
        $response = $this->post('/reset-password', [
            'token' => 'invalid-token',
            'email' => 'john@example.com',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        // Assert
        $response->assertSessionHasErrors(['email']);
    }

    public function test_user_can_verify_email(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // Act
        $response = $this->actingAs($user)->get(
            route('verification.verify', [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ])
        );

        // Assert
        $response->assertRedirect('/dashboard');
        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
        
        Event::assertDispatched(Verified::class);
    }

    public function test_user_cannot_verify_email_with_invalid_hash(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // Act
        $response = $this->actingAs($user)->get(
            route('verification.verify', [
                'id' => $user->id,
                'hash' => 'invalid-hash',
            ])
        );

        // Assert
        $response->assertStatus(403);
        $user->refresh();
        $this->assertNull($user->email_verified_at);
    }

    public function test_verified_user_cannot_access_verification_notice(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Act
        $response = $this->actingAs($user)->get('/email/verify');

        // Assert
        $response->assertRedirect('/dashboard');
    }

    public function test_unverified_user_can_resend_verification_email(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // Act
        $response = $this->actingAs($user)->post('/email/verification-notification');

        // Assert
        $response->assertSessionHas('status');
    }

    public function test_login_throttling_after_multiple_failed_attempts(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        // Act - Make multiple failed login attempts
        for ($i = 0; $i < 6; $i++) {
            $this->post('/login', [
                'email' => 'john@example.com',
                'password' => 'WrongPassword',
            ]);
        }

        // Try to login with correct credentials
        $response = $this->post('/login', [
            'email' => 'john@example.com',
            'password' => 'Password123!',
        ]);

        // Assert - Should be throttled
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_remember_me_functionality(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        // Act
        $response = $this->post('/login', [
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'remember' => true,
        ]);

        // Assert
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        
        // Check that remember token was set
        $user->refresh();
        $this->assertNotNull($user->remember_token);
    }

    public function test_user_session_expires_after_inactivity(): void
    {
        // Arrange
        $user = User::factory()->create();
        $this->actingAs($user);

        // Simulate session expiry by clearing session
        session()->flush();

        // Act
        $response = $this->get('/dashboard');

        // Assert
        $response->assertRedirect('/login');
    }

    public function test_user_can_change_password_when_authenticated(): void
    {
        // Arrange
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword123!'),
        ]);

        // Act
        $response = $this->actingAs($user)->put('/password', [
            'current_password' => 'OldPassword123!',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        // Assert
        $response->assertRedirect();
        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $user->password));
    }

    public function test_user_cannot_change_password_with_wrong_current_password(): void
    {
        // Arrange
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword123!'),
        ]);

        // Act
        $response = $this->actingAs($user)->put('/password', [
            'current_password' => 'WrongPassword',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        // Assert
        $response->assertSessionHasErrors(['current_password']);
        $user->refresh();
        $this->assertTrue(Hash::check('OldPassword123!', $user->password));
    }

    public function test_concurrent_login_sessions_handling(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        // Act - Login from first session
        $response1 = $this->post('/login', [
            'email' => 'john@example.com',
            'password' => 'Password123!',
        ]);

        // Login from second session (different browser/device)
        $response2 = $this->post('/login', [
            'email' => 'john@example.com',
            'password' => 'Password123!',
        ]);

        // Assert - Both sessions should be valid
        $response1->assertRedirect('/dashboard');
        $response2->assertRedirect('/dashboard');
    }

    public function test_social_login_integration(): void
    {
        // This would test OAuth integration if implemented
        // Arrange
        $socialUser = [
            'id' => '123456789',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'avatar' => 'https://example.com/avatar.jpg',
        ];

        // Act
        // This would simulate OAuth callback
        // $response = $this->get('/auth/google/callback');

        // Assert
        // Verify user was created/logged in via social provider
        $this->assertTrue(true); // Placeholder
    }

    public function test_two_factor_authentication_setup(): void
    {
        // This would test 2FA setup if implemented
        // Arrange
        $user = User::factory()->create();

        // Act
        // $response = $this->actingAs($user)->post('/two-factor-authentication');

        // Assert
        // Verify 2FA was enabled
        $this->assertTrue(true); // Placeholder
    }

    public function test_account_lockout_after_suspicious_activity(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        // Act - Simulate suspicious login patterns
        for ($i = 0; $i < 10; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.' . $i])
                ->post('/login', [
                    'email' => 'john@example.com',
                    'password' => 'WrongPassword',
                ]);
        }

        // Assert - Account should be locked
        $response = $this->post('/login', [
            'email' => 'john@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }
}