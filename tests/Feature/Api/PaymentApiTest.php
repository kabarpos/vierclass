<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Course;
use App\Models\Discount;
use App\Models\PaymentTemp;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaymentApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Course $course;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        $userRole = Role::firstOrCreate(['name' => 'user']);
        
        // Create user
        $this->user = User::factory()->create();
        $this->user->assignRole($userRole);
        
        // Create category and course
        $this->category = Category::factory()->create();
        $this->course = Course::factory()->create([
            'category_id' => $this->category->id,
            'price' => 299000,
        ]);

        // Mock Midtrans responses
        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
                'token' => 'fake-snap-token',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/fake-snap-token'
            ], 201),
        ]);
    }

    public function test_authenticated_user_can_create_payment(): void
    {
        // Arrange
        Sanctum::actingAs($this->user);
        
        $paymentData = [
            'course_id' => $this->course->id,
            'payment_method' => 'credit_card',
        ];

        // Act
        $response = $this->postJson('/api/payments', $paymentData);

        // Assert
        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'payment_id',
                    'snap_token',
                    'redirect_url',
                    'amount',
                    'course',
                ]
            ]);

        $this->assertDatabaseHas('payment_temps', [
            'user_id' => $this->user->id,
            'course_id' => $this->course->id,
            'amount' => $this->course->price,
        ]);
    }

    public function test_unauthenticated_user_cannot_create_payment(): void
    {
        // Arrange
        $paymentData = [
            'course_id' => $this->course->id,
            'payment_method' => 'credit_card',
        ];

        // Act
        $response = $this->postJson('/api/payments', $paymentData);

        // Assert
        $response->assertStatus(401);
    }

    public function test_can_create_payment_with_discount(): void
    {
        // Arrange
        Sanctum::actingAs($this->user);
        
        $discount = Discount::factory()->create([
            'code' => 'SAVE20',
            'type' => 'percentage',
            'value' => 20,
            'is_active' => true,
            'usage_limit' => 100,
            'usage_count' => 0,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addMonth(),
        ]);

        $paymentData = [
            'course_id' => $this->course->id,
            'payment_method' => 'credit_card',
            'discount_code' => 'SAVE20',
        ];

        // Act
        $response = $this->postJson('/api/payments', $paymentData);

        // Assert
        $response->assertStatus(201);
        
        $expectedAmount = $this->course->price * 0.8; // 20% discount
        
        $this->assertDatabaseHas('payment_temps', [
            'user_id' => $this->user->id,
            'course_id' => $this->course->id,
            'amount' => $expectedAmount,
            'discount_id' => $discount->id,
        ]);
    }

    public function test_cannot_use_invalid_discount_code(): void
    {
        // Arrange
        Sanctum::actingAs($this->user);
        
        $paymentData = [
            'course_id' => $this->course->id,
            'payment_method' => 'credit_card',
            'discount_code' => 'INVALID_CODE',
        ];

        // Act
        $response = $this->postJson('/api/payments', $paymentData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Invalid discount code']);
    }

    public function test_cannot_use_expired_discount(): void
    {
        // Arrange
        Sanctum::actingAs($this->user);
        
        $discount = Discount::factory()->create([
            'code' => 'EXPIRED',
            'type' => 'percentage',
            'value' => 20,
            'is_active' => true,
            'valid_from' => now()->subMonth(),
            'valid_until' => now()->subDay(),
        ]);

        $paymentData = [
            'course_id' => $this->course->id,
            'payment_method' => 'credit_card',
            'discount_code' => 'EXPIRED',
        ];

        // Act
        $response = $this->postJson('/api/payments', $paymentData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Discount code has expired']);
    }

    public function test_cannot_create_payment_for_already_owned_course(): void
    {
        // Arrange
        Sanctum::actingAs($this->user);
        
        // User already owns the course
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'course_id' => $this->course->id,
            'status' => 'success',
        ]);

        $paymentData = [
            'course_id' => $this->course->id,
            'payment_method' => 'credit_card',
        ];

        // Act
        $response = $this->postJson('/api/payments', $paymentData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'You already own this course']);
    }

    public function test_can_get_payment_status(): void
    {
        // Arrange
        Sanctum::actingAs($this->user);
        
        $paymentTemp = PaymentTemp::factory()->create([
            'user_id' => $this->user->id,
            'course_id' => $this->course->id,
            'amount' => $this->course->price,
            'midtrans_order_id' => 'ORDER-123',
        ]);

        // Act
        $response = $this->getJson("/api/payments/{$paymentTemp->id}/status");

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'payment_id',
                    'status',
                    'amount',
                    'course',
                    'created_at',
                ]
            ]);
    }

    public function test_cannot_get_other_user_payment_status(): void
    {
        // Arrange
        $otherUser = User::factory()->create();
        Sanctum::actingAs($this->user);
        
        $paymentTemp = PaymentTemp::factory()->create([
            'user_id' => $otherUser->id,
            'course_id' => $this->course->id,
        ]);

        // Act
        $response = $this->getJson("/api/payments/{$paymentTemp->id}/status");

        // Assert
        $response->assertStatus(403);
    }

    public function test_can_cancel_payment(): void
    {
        // Arrange
        Sanctum::actingAs($this->user);
        
        $paymentTemp = PaymentTemp::factory()->create([
            'user_id' => $this->user->id,
            'course_id' => $this->course->id,
            'status' => 'pending',
        ]);

        // Act
        $response = $this->deleteJson("/api/payments/{$paymentTemp->id}");

        // Assert
        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Payment cancelled successfully']);

        $this->assertDatabaseHas('payment_temps', [
            'id' => $paymentTemp->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_cannot_cancel_completed_payment(): void
    {
        // Arrange
        Sanctum::actingAs($this->user);
        
        $paymentTemp = PaymentTemp::factory()->create([
            'user_id' => $this->user->id,
            'course_id' => $this->course->id,
            'status' => 'success',
        ]);

        // Act
        $response = $this->deleteJson("/api/payments/{$paymentTemp->id}");

        // Assert
        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Cannot cancel completed payment']);
    }

    public function test_can_get_user_payment_history(): void
    {
        // Arrange
        Sanctum::actingAs($this->user);
        
        $payments = PaymentTemp::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        // Act
        $response = $this->getJson('/api/user/payments');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'amount',
                        'status',
                        'course',
                        'created_at',
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_payment_validation_errors(): void
    {
        // Arrange
        Sanctum::actingAs($this->user);

        // Act
        $response = $this->postJson('/api/payments', []);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'course_id',
                'payment_method',
            ]);
    }

    public function test_invalid_course_id_returns_error(): void
    {
        // Arrange
        Sanctum::actingAs($this->user);
        
        $paymentData = [
            'course_id' => 999999,
            'payment_method' => 'credit_card',
        ];

        // Act
        $response = $this->postJson('/api/payments', $paymentData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['course_id']);
    }

    public function test_can_verify_payment_callback(): void
    {
        // Arrange
        $paymentTemp = PaymentTemp::factory()->create([
            'user_id' => $this->user->id,
            'course_id' => $this->course->id,
            'midtrans_order_id' => 'ORDER-123',
            'status' => 'pending',
        ]);

        $callbackData = [
            'order_id' => 'ORDER-123',
            'status_code' => '200',
            'transaction_status' => 'settlement',
            'signature_key' => 'fake-signature',
        ];

        // Mock Midtrans notification verification
        Http::fake([
            'https://api.sandbox.midtrans.com/v2/ORDER-123/status' => Http::response([
                'order_id' => 'ORDER-123',
                'transaction_status' => 'settlement',
                'fraud_status' => 'accept',
                'status_code' => '200',
            ], 200),
        ]);

        // Act
        $response = $this->postJson('/api/payments/callback', $callbackData);

        // Assert
        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Payment verified successfully']);

        $this->assertDatabaseHas('payment_temps', [
            'id' => $paymentTemp->id,
            'status' => 'success',
        ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'course_id' => $this->course->id,
            'status' => 'success',
        ]);
    }

    public function test_failed_payment_callback(): void
    {
        // Arrange
        $paymentTemp = PaymentTemp::factory()->create([
            'user_id' => $this->user->id,
            'course_id' => $this->course->id,
            'midtrans_order_id' => 'ORDER-123',
            'status' => 'pending',
        ]);

        $callbackData = [
            'order_id' => 'ORDER-123',
            'status_code' => '202',
            'transaction_status' => 'deny',
            'signature_key' => 'fake-signature',
        ];

        // Mock Midtrans notification verification
        Http::fake([
            'https://api.sandbox.midtrans.com/v2/ORDER-123/status' => Http::response([
                'order_id' => 'ORDER-123',
                'transaction_status' => 'deny',
                'fraud_status' => 'deny',
                'status_code' => '202',
            ], 200),
        ]);

        // Act
        $response = $this->postJson('/api/payments/callback', $callbackData);

        // Assert
        $response->assertStatus(200);

        $this->assertDatabaseHas('payment_temps', [
            'id' => $paymentTemp->id,
            'status' => 'failed',
        ]);
    }

    public function test_can_retry_failed_payment(): void
    {
        // Arrange
        Sanctum::actingAs($this->user);
        
        $paymentTemp = PaymentTemp::factory()->create([
            'user_id' => $this->user->id,
            'course_id' => $this->course->id,
            'status' => 'failed',
        ]);

        // Act
        $response = $this->postJson("/api/payments/{$paymentTemp->id}/retry");

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'payment_id',
                    'snap_token',
                    'redirect_url',
                ]
            ]);

        $this->assertDatabaseHas('payment_temps', [
            'id' => $paymentTemp->id,
            'status' => 'pending',
        ]);
    }

    public function test_cannot_retry_successful_payment(): void
    {
        // Arrange
        Sanctum::actingAs($this->user);
        
        $paymentTemp = PaymentTemp::factory()->create([
            'user_id' => $this->user->id,
            'course_id' => $this->course->id,
            'status' => 'success',
        ]);

        // Act
        $response = $this->postJson("/api/payments/{$paymentTemp->id}/retry");

        // Assert
        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Cannot retry successful payment']);
    }

    public function test_payment_timeout_handling(): void
    {
        // Arrange
        Sanctum::actingAs($this->user);
        
        $paymentTemp = PaymentTemp::factory()->create([
            'user_id' => $this->user->id,
            'course_id' => $this->course->id,
            'status' => 'pending',
            'created_at' => now()->subHours(25), // Expired
        ]);

        // Act
        $response = $this->getJson("/api/payments/{$paymentTemp->id}/status");

        // Assert
        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'expired']);
    }

    public function test_can_apply_multiple_discounts(): void
    {
        // Arrange
        Sanctum::actingAs($this->user);
        
        $discount1 = Discount::factory()->create([
            'code' => 'SAVE10',
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
            'stackable' => true,
        ]);

        $discount2 = Discount::factory()->create([
            'code' => 'EXTRA5',
            'type' => 'fixed',
            'value' => 50000,
            'is_active' => true,
            'stackable' => true,
        ]);

        $paymentData = [
            'course_id' => $this->course->id,
            'payment_method' => 'credit_card',
            'discount_codes' => ['SAVE10', 'EXTRA5'],
        ];

        // Act
        $response = $this->postJson('/api/payments', $paymentData);

        // Assert
        $response->assertStatus(201);
        
        // 299000 - 10% = 269100, then - 50000 = 219100
        $expectedAmount = ($this->course->price * 0.9) - 50000;
        
        $this->assertDatabaseHas('payment_temps', [
            'user_id' => $this->user->id,
            'course_id' => $this->course->id,
            'amount' => $expectedAmount,
        ]);
    }
}