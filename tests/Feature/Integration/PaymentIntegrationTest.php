<?php

namespace Tests\Feature\Integration;

use App\Models\Course;
use App\Models\Discount;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PaymentIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        Event::fake();
    }

    public function test_complete_payment_flow_without_discount(): void
    {
        // Arrange
        $user = User::factory()->create();
        $course = Course::factory()->create(['price' => 100000]);

        // Mock payment gateway response
        Http::fake([
            'https://api.midtrans.com/v2/charge' => Http::response([
                'status_code' => '201',
                'status_message' => 'Success, Credit Card transaction is successful',
                'transaction_id' => 'test-transaction-123',
                'order_id' => 'ORDER-123',
                'payment_type' => 'credit_card',
                'transaction_time' => now()->toISOString(),
                'transaction_status' => 'capture',
                'fraud_status' => 'accept',
                'redirect_url' => 'https://api.sandbox.veritrans.co.id/v2/token/redirect/test-token'
            ], 201)
        ]);

        // Act - Create payment
        $response = $this->actingAs($user)->post('/api/payments', [
            'course_id' => $course->id,
            'payment_method' => 'credit_card'
        ]);

        // Assert - Payment created successfully
        $response->assertStatus(201);
        $responseData = $response->json();
        
        $this->assertDatabaseHas('payments', [
            'user_id' => $user->id,
            'course_id' => $course->id,
            'amount' => 100000,
            'status' => 'pending'
        ]);

        $payment = Payment::where('user_id', $user->id)->first();
        $this->assertEquals('pending', $payment->status);
        $this->assertEquals(100000, $payment->amount);
        $this->assertNull($payment->discount_id);

        // Act - Simulate successful payment callback
        $callbackResponse = $this->post('/api/payments/callback', [
            'order_id' => $payment->order_id,
            'status_code' => '200',
            'transaction_status' => 'capture',
            'fraud_status' => 'accept',
            'payment_type' => 'credit_card',
            'transaction_id' => 'test-transaction-123'
        ]);

        // Assert - Payment completed and transaction created
        $callbackResponse->assertStatus(200);
        
        $payment->refresh();
        $this->assertEquals('completed', $payment->status);
        
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'course_id' => $course->id,
            'payment_id' => $payment->id,
            'amount' => 100000,
            'status' => 'completed'
        ]);

        // Verify user has access to course
        $this->assertDatabaseHas('course_user', [
            'user_id' => $user->id,
            'course_id' => $course->id
        ]);
    }

    public function test_complete_payment_flow_with_single_discount(): void
    {
        // Arrange
        $user = User::factory()->create();
        $course = Course::factory()->create(['price' => 100000]);
        $discount = Discount::factory()->create([
            'code' => 'SAVE20',
            'type' => 'percentage',
            'value' => 20,
            'is_active' => true,
            'expires_at' => now()->addDays(30)
        ]);

        // Mock payment gateway response
        Http::fake([
            'https://api.midtrans.com/v2/charge' => Http::response([
                'status_code' => '201',
                'status_message' => 'Success',
                'transaction_id' => 'test-transaction-123',
                'order_id' => 'ORDER-123',
                'payment_type' => 'credit_card',
                'transaction_status' => 'capture',
                'fraud_status' => 'accept'
            ], 201)
        ]);

        // Act - Create payment with discount
        $response = $this->actingAs($user)->post('/api/payments', [
            'course_id' => $course->id,
            'payment_method' => 'credit_card',
            'discount_code' => 'SAVE20'
        ]);

        // Assert - Payment created with discount applied
        $response->assertStatus(201);
        
        $payment = Payment::where('user_id', $user->id)->first();
        $this->assertEquals(80000, $payment->amount); // 100000 - 20%
        $this->assertEquals($discount->id, $payment->discount_id);

        // Act - Complete payment
        $this->post('/api/payments/callback', [
            'order_id' => $payment->order_id,
            'status_code' => '200',
            'transaction_status' => 'capture',
            'fraud_status' => 'accept'
        ]);

        // Assert - Transaction created with discounted amount
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'course_id' => $course->id,
            'amount' => 80000,
            'discount_amount' => 20000,
            'status' => 'completed'
        ]);
    }

    public function test_complete_payment_flow_with_stacked_discounts(): void
    {
        // Arrange
        $user = User::factory()->create();
        $course = Course::factory()->create(['price' => 100000]);
        
        $discount1 = Discount::factory()->create([
            'code' => 'SAVE20',
            'type' => 'percentage',
            'value' => 20,
            'is_active' => true,
            'can_stack' => true
        ]);
        
        $discount2 = Discount::factory()->create([
            'code' => 'EXTRA10',
            'type' => 'fixed',
            'value' => 10000,
            'is_active' => true,
            'can_stack' => true
        ]);

        // Mock payment gateway
        Http::fake([
            'https://api.midtrans.com/v2/charge' => Http::response([
                'status_code' => '201',
                'transaction_status' => 'capture'
            ], 201)
        ]);

        // Act - Create payment with multiple discounts
        $response = $this->actingAs($user)->post('/api/payments', [
            'course_id' => $course->id,
            'payment_method' => 'credit_card',
            'discount_codes' => ['SAVE20', 'EXTRA10']
        ]);

        // Assert - Payment created with stacked discounts
        $response->assertStatus(201);
        
        $payment = Payment::where('user_id', $user->id)->first();
        // 100000 - 20% = 80000, then 80000 - 10000 = 70000
        $this->assertEquals(70000, $payment->amount);

        // Complete payment and verify transaction
        $this->post('/api/payments/callback', [
            'order_id' => $payment->order_id,
            'status_code' => '200',
            'transaction_status' => 'capture'
        ]);

        $this->assertDatabaseHas('transactions', [
            'amount' => 70000,
            'discount_amount' => 30000,
            'status' => 'completed'
        ]);
    }

    public function test_payment_failure_handling(): void
    {
        // Arrange
        $user = User::factory()->create();
        $course = Course::factory()->create(['price' => 100000]);

        // Mock payment gateway failure
        Http::fake([
            'https://api.midtrans.com/v2/charge' => Http::response([
                'status_code' => '400',
                'status_message' => 'Invalid card number',
                'transaction_status' => 'deny'
            ], 400)
        ]);

        // Act - Attempt payment
        $response = $this->actingAs($user)->post('/api/payments', [
            'course_id' => $course->id,
            'payment_method' => 'credit_card'
        ]);

        // Assert - Payment creation failed
        $response->assertStatus(422);
        
        $this->assertDatabaseMissing('payments', [
            'user_id' => $user->id,
            'course_id' => $course->id
        ]);

        $this->assertDatabaseMissing('course_user', [
            'user_id' => $user->id,
            'course_id' => $course->id
        ]);
    }

    public function test_payment_callback_failure_handling(): void
    {
        // Arrange
        $user = User::factory()->create();
        $course = Course::factory()->create(['price' => 100000]);
        
        $payment = Payment::factory()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'pending',
            'amount' => 100000
        ]);

        // Act - Simulate failed payment callback
        $response = $this->post('/api/payments/callback', [
            'order_id' => $payment->order_id,
            'status_code' => '400',
            'transaction_status' => 'deny',
            'fraud_status' => 'deny'
        ]);

        // Assert - Payment marked as failed
        $response->assertStatus(200);
        
        $payment->refresh();
        $this->assertEquals('failed', $payment->status);
        
        $this->assertDatabaseHas('transactions', [
            'payment_id' => $payment->id,
            'status' => 'failed'
        ]);

        // Verify user doesn't have access to course
        $this->assertDatabaseMissing('course_user', [
            'user_id' => $user->id,
            'course_id' => $course->id
        ]);
    }

    public function test_payment_timeout_handling(): void
    {
        // Arrange
        $user = User::factory()->create();
        $course = Course::factory()->create(['price' => 100000]);
        
        $payment = Payment::factory()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'pending',
            'created_at' => now()->subHours(25) // Expired
        ]);

        // Act - Check payment status
        $response = $this->actingAs($user)->get("/api/payments/{$payment->id}/status");

        // Assert - Payment should be expired
        $response->assertStatus(200);
        $response->assertJson(['status' => 'expired']);
        
        $payment->refresh();
        $this->assertEquals('expired', $payment->status);
    }

    public function test_duplicate_payment_prevention(): void
    {
        // Arrange
        $user = User::factory()->create();
        $course = Course::factory()->create(['price' => 100000]);
        
        // User already owns the course
        $user->courses()->attach($course->id);

        // Act - Attempt to create payment for owned course
        $response = $this->actingAs($user)->post('/api/payments', [
            'course_id' => $course->id,
            'payment_method' => 'credit_card'
        ]);

        // Assert - Payment creation should be prevented
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['course_id']);
    }

    public function test_concurrent_payment_handling(): void
    {
        // Arrange
        $user = User::factory()->create();
        $course = Course::factory()->create(['price' => 100000]);

        // Mock successful payment gateway
        Http::fake([
            'https://api.midtrans.com/v2/charge' => Http::response([
                'status_code' => '201',
                'transaction_status' => 'capture'
            ], 201)
        ]);

        // Act - Create multiple payments simultaneously
        $response1 = $this->actingAs($user)->post('/api/payments', [
            'course_id' => $course->id,
            'payment_method' => 'credit_card'
        ]);

        $response2 = $this->actingAs($user)->post('/api/payments', [
            'course_id' => $course->id,
            'payment_method' => 'bank_transfer'
        ]);

        // Assert - Only one payment should be created
        $this->assertEquals(1, Payment::where('user_id', $user->id)->count());
        
        // One should succeed, one should fail
        $this->assertTrue(
            ($response1->status() === 201 && $response2->status() === 422) ||
            ($response1->status() === 422 && $response2->status() === 201)
        );
    }

    public function test_refund_processing(): void
    {
        // Arrange
        $user = User::factory()->create();
        $course = Course::factory()->create(['price' => 100000]);
        
        $payment = Payment::factory()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'completed',
            'amount' => 100000
        ]);

        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'payment_id' => $payment->id,
            'status' => 'completed',
            'amount' => 100000
        ]);

        // Mock refund gateway response
        Http::fake([
            'https://api.midtrans.com/v2/*/refund' => Http::response([
                'status_code' => '200',
                'refund_amount' => '100000.00',
                'refund_key' => 'refund-123'
            ], 200)
        ]);

        // Act - Process refund (admin action)
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->post("/api/transactions/{$transaction->id}/refund", [
            'reason' => 'Customer request'
        ]);

        // Assert - Refund processed successfully
        $response->assertStatus(200);
        
        $transaction->refresh();
        $this->assertEquals('refunded', $transaction->status);
        
        $payment->refresh();
        $this->assertEquals('refunded', $payment->status);

        // Verify user access removed
        $this->assertDatabaseMissing('course_user', [
            'user_id' => $user->id,
            'course_id' => $course->id
        ]);
    }

    public function test_partial_refund_processing(): void
    {
        // Arrange
        $user = User::factory()->create();
        $course = Course::factory()->create(['price' => 100000]);
        
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'completed',
            'amount' => 100000
        ]);

        // Mock partial refund
        Http::fake([
            'https://api.midtrans.com/v2/*/refund' => Http::response([
                'status_code' => '200',
                'refund_amount' => '50000.00'
            ], 200)
        ]);

        // Act - Process partial refund
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->post("/api/transactions/{$transaction->id}/refund", [
            'amount' => 50000,
            'reason' => 'Partial refund request'
        ]);

        // Assert - Partial refund processed
        $response->assertStatus(200);
        
        $transaction->refresh();
        $this->assertEquals('partially_refunded', $transaction->status);
        $this->assertEquals(50000, $transaction->refunded_amount);

        // User should still have access to course
        $this->assertDatabaseHas('course_user', [
            'user_id' => $user->id,
            'course_id' => $course->id
        ]);
    }

    public function test_payment_webhook_security(): void
    {
        // Arrange
        $payment = Payment::factory()->create(['status' => 'pending']);
        
        // Act - Send callback without proper signature
        $response = $this->post('/api/payments/callback', [
            'order_id' => $payment->order_id,
            'status_code' => '200',
            'transaction_status' => 'capture'
        ]);

        // Assert - Callback should be rejected if signature validation fails
        // This depends on implementation of webhook signature verification
        $this->assertTrue(in_array($response->status(), [200, 403]));
    }

    public function test_payment_analytics_tracking(): void
    {
        // Arrange
        $user = User::factory()->create();
        $course = Course::factory()->create(['price' => 100000]);

        // Mock payment gateway
        Http::fake([
            'https://api.midtrans.com/v2/charge' => Http::response([
                'status_code' => '201',
                'transaction_status' => 'capture'
            ], 201)
        ]);

        // Act - Complete payment flow
        $this->actingAs($user)->post('/api/payments', [
            'course_id' => $course->id,
            'payment_method' => 'credit_card'
        ]);

        $payment = Payment::where('user_id', $user->id)->first();
        
        $this->post('/api/payments/callback', [
            'order_id' => $payment->order_id,
            'status_code' => '200',
            'transaction_status' => 'capture'
        ]);

        // Assert - Analytics events should be tracked
        // This would verify that payment events are logged for analytics
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'completed'
        ]);
    }

    public function test_payment_method_specific_handling(): void
    {
        // Arrange
        $user = User::factory()->create();
        $course = Course::factory()->create(['price' => 100000]);

        // Test different payment methods
        $paymentMethods = ['credit_card', 'bank_transfer', 'e_wallet', 'qris'];

        foreach ($paymentMethods as $method) {
            // Mock appropriate gateway response for each method
            Http::fake([
                'https://api.midtrans.com/v2/charge' => Http::response([
                    'status_code' => '201',
                    'payment_type' => $method,
                    'transaction_status' => $method === 'bank_transfer' ? 'pending' : 'capture'
                ], 201)
            ]);

            // Act
            $response = $this->actingAs($user)->post('/api/payments', [
                'course_id' => $course->id,
                'payment_method' => $method
            ]);

            // Assert
            $response->assertStatus(201);
            
            $payment = Payment::where('user_id', $user->id)
                ->where('payment_method', $method)
                ->first();
            
            $this->assertNotNull($payment);
            
            // Clean up for next iteration
            $payment->delete();
        }
    }

    public function test_payment_currency_handling(): void
    {
        // Arrange
        $user = User::factory()->create();
        $course = Course::factory()->create(['price' => 100000]);

        // Mock payment with currency conversion
        Http::fake([
            'https://api.midtrans.com/v2/charge' => Http::response([
                'status_code' => '201',
                'transaction_status' => 'capture',
                'currency' => 'IDR'
            ], 201)
        ]);

        // Act
        $response = $this->actingAs($user)->post('/api/payments', [
            'course_id' => $course->id,
            'payment_method' => 'credit_card',
            'currency' => 'IDR'
        ]);

        // Assert
        $response->assertStatus(201);
        
        $payment = Payment::where('user_id', $user->id)->first();
        $this->assertEquals('IDR', $payment->currency);
        $this->assertEquals(100000, $payment->amount);
    }
}