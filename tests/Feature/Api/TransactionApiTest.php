<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Course;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TransactionApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $adminUser;
    private Course $course;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        $userRole = Role::firstOrCreate(['name' => 'user']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        
        // Create users
        $this->user = User::factory()->create();
        $this->user->assignRole($userRole);
        
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole($adminRole);
        
        // Create category and course
        $this->category = Category::factory()->create();
        $this->course = Course::factory()->create([
            'category_id' => $this->category->id,
            'price' => 299000,
        ]);
    }

    public function test_user_can_get_own_transactions(): void
    {
        // Arrange
        Sanctum::actingAs($this->user);
        
        $transactions = Transaction::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        // Act
        $response = $this->getJson('/api/user/transactions');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'course',
                        'amount',
                        'status',
                        'payment_method',
                        'created_at',
                        'updated_at',
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_get_single_transaction(): void
    {
        // Arrange
        Sanctum::actingAs($this->user);
        
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'course_id' => $this->course->id,
        ]);

        // Act
        $response = $this->getJson("/api/user/transactions/{$transaction->id}");

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'course',
                    'amount',
                    'status',
                    'payment_method',
                    'midtrans_order_id',
                    'midtrans_transaction_id',
                    'created_at',
                    'updated_at',
                ]
            ])
            ->assertJson([
                'data' => [
                    'id' => $transaction->id,
                ]
            ]);
    }

    public function test_user_cannot_access_other_user_transaction(): void
    {
        // Arrange
        $otherUser = User::factory()->create();
        Sanctum::actingAs($this->user);
        
        $transaction = Transaction::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        // Act
        $response = $this->getJson("/api/user/transactions/{$transaction->id}");

        // Assert
        $response->assertStatus(403);
    }

    public function test_admin_can_get_all_transactions(): void
    {
        // Arrange
        Sanctum::actingAs($this->adminUser);
        
        $transactions = Transaction::factory()->count(5)->create();

        // Act
        $response = $this->getJson('/api/admin/transactions');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user',
                        'course',
                        'amount',
                        'status',
                        'payment_method',
                        'created_at',
                        'updated_at',
                    ]
                ]
            ])
            ->assertJsonCount(5, 'data');
    }

    public function test_regular_user_cannot_access_admin_transactions(): void
    {
        // Arrange
        Sanctum::actingAs($this->user);

        // Act
        $response = $this->getJson('/api/admin/transactions');

        // Assert
        $response->assertStatus(403);
    }

    public function test_can_filter_transactions_by_status(): void
    {
        // Arrange
        Sanctum::actingAs($this->adminUser);
        
        $successTransaction = Transaction::factory()->create(['status' => 'success']);
        $pendingTransaction = Transaction::factory()->create(['status' => 'pending']);
        $failedTransaction = Transaction::factory()->create(['status' => 'failed']);

        // Act
        $response = $this->getJson('/api/admin/transactions?status=success');

        // Assert
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $successTransaction->id]);
    }

    public function test_can_filter_transactions_by_user(): void
    {
        // Arrange
        Sanctum::actingAs($this->adminUser);
        
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $transaction1 = Transaction::factory()->create(['user_id' => $user1->id]);
        $transaction2 = Transaction::factory()->create(['user_id' => $user2->id]);

        // Act
        $response = $this->getJson("/api/admin/transactions?user_id={$user1->id}");

        // Assert
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $transaction1->id]);
    }

    public function test_can_filter_transactions_by_course(): void
    {
        // Arrange
        Sanctum::actingAs($this->adminUser);
        
        $course1 = Course::factory()->create(['category_id' => $this->category->id]);
        $course2 = Course::factory()->create(['category_id' => $this->category->id]);
        
        $transaction1 = Transaction::factory()->create(['course_id' => $course1->id]);
        $transaction2 = Transaction::factory()->create(['course_id' => $course2->id]);

        // Act
        $response = $this->getJson("/api/admin/transactions?course_id={$course1->id}");

        // Assert
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $transaction1->id]);
    }

    public function test_can_filter_transactions_by_date_range(): void
    {
        // Arrange
        Sanctum::actingAs($this->adminUser);
        
        $oldTransaction = Transaction::factory()->create([
            'started_at' => now()->subWeek(),
        ]);
        $recentTransaction = Transaction::factory()->create([
            'started_at' => now()->subDay(),
        ]);

        $fromDate = now()->subDays(3)->format('Y-m-d');
        $toDate = now()->format('Y-m-d');

        // Act
        $response = $this->getJson("/api/admin/transactions?from_date={$fromDate}&to_date={$toDate}");

        // Assert
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $recentTransaction->id]);
    }

    public function test_can_search_transactions(): void
    {
        // Arrange
        Sanctum::actingAs($this->adminUser);
        
        $user = User::factory()->create(['name' => 'John Doe']);
        $course = Course::factory()->create([
            'name' => 'Laravel Advanced Course',
            'category_id' => $this->category->id,
        ]);
        
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);

        // Act
        $response = $this->getJson('/api/admin/transactions?search=John');

        // Assert
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $transaction->id]);
    }

    public function test_admin_can_update_transaction_status(): void
    {
        // Arrange
        Sanctum::actingAs($this->adminUser);
        
        $transaction = Transaction::factory()->create([
            'status' => 'pending',
        ]);

        $updateData = [
            'status' => 'success',
            'notes' => 'Payment verified manually',
        ];

        // Act
        $response = $this->putJson("/api/admin/transactions/{$transaction->id}", $updateData);

        // Assert
        $response->assertStatus(200)
            ->assertJsonFragment([
                'status' => 'success',
            ]);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'success',
        ]);
    }

    public function test_regular_user_cannot_update_transaction(): void
    {
        // Arrange
        Sanctum::actingAs($this->user);
        
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $updateData = ['status' => 'success'];

        // Act
        $response = $this->putJson("/api/admin/transactions/{$transaction->id}", $updateData);

        // Assert
        $response->assertStatus(403);
    }

    public function test_can_get_transaction_statistics(): void
    {
        // Arrange
        Sanctum::actingAs($this->adminUser);
        
        Transaction::factory()->count(3)->create(['status' => 'success', 'amount' => 100000]);
        Transaction::factory()->count(2)->create(['status' => 'pending', 'amount' => 200000]);
        Transaction::factory()->count(1)->create(['status' => 'failed', 'amount' => 150000]);

        // Act
        $response = $this->getJson('/api/admin/transactions/statistics');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_transactions',
                    'successful_transactions',
                    'pending_transactions',
                    'failed_transactions',
                    'total_revenue',
                    'success_rate',
                    'average_transaction_amount',
                ]
            ]);

        $data = $response->json('data');
        $this->assertEquals(6, $data['total_transactions']);
        $this->assertEquals(3, $data['successful_transactions']);
        $this->assertEquals(300000, $data['total_revenue']); // 3 * 100000
    }

    public function test_can_get_monthly_revenue_chart(): void
    {
        // Arrange
        Sanctum::actingAs($this->adminUser);
        
        // Create transactions for different months
        Transaction::factory()->create([
            'status' => 'success',
            'amount' => 100000,
            'started_at' => now()->subMonth(),
        ]);
        Transaction::factory()->create([
            'status' => 'success',
            'amount' => 200000,
            'started_at' => now(),
        ]);

        // Act
        $response = $this->getJson('/api/admin/transactions/revenue-chart?period=6months');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'labels',
                    'datasets' => [
                        '*' => [
                            'label',
                            'data',
                        ]
                    ]
                ]
            ]);
    }

    public function test_can_export_transactions(): void
    {
        // Arrange
        Sanctum::actingAs($this->adminUser);
        
        Transaction::factory()->count(5)->create();

        // Act
        $response = $this->getJson('/api/admin/transactions/export?format=csv');

        // Assert
        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->assertHeader('Content-Disposition', 'attachment; filename="transactions.csv"');
    }

    public function test_can_refund_transaction(): void
    {
        // Arrange
        Sanctum::actingAs($this->adminUser);
        
        $transaction = Transaction::factory()->create([
            'status' => 'success',
            'amount' => 299000,
            'midtrans_transaction_id' => 'TXN-123',
        ]);

        $refundData = [
            'amount' => 299000,
            'reason' => 'Customer request',
        ];

        // Act
        $response = $this->postJson("/api/admin/transactions/{$transaction->id}/refund", $refundData);

        // Assert
        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Refund processed successfully']);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'refunded',
        ]);
    }

    public function test_cannot_refund_non_successful_transaction(): void
    {
        // Arrange
        Sanctum::actingAs($this->adminUser);
        
        $transaction = Transaction::factory()->create([
            'status' => 'pending',
        ]);

        $refundData = [
            'amount' => 299000,
            'reason' => 'Customer request',
        ];

        // Act
        $response = $this->postJson("/api/admin/transactions/{$transaction->id}/refund", $refundData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Can only refund successful transactions']);
    }

    public function test_pagination_works_correctly(): void
    {
        // Arrange
        Sanctum::actingAs($this->adminUser);
        Transaction::factory()->count(25)->create();

        // Act
        $response = $this->getJson('/api/admin/transactions?per_page=10&page=1');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'links',
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ]
            ])
            ->assertJsonCount(10, 'data');
    }

    public function test_transaction_sorting_works(): void
    {
        // Arrange
        Sanctum::actingAs($this->adminUser);
        
        $transaction1 = Transaction::factory()->create([
            'amount' => 100000,
            'started_at' => now()->subDays(2),
        ]);
        $transaction2 = Transaction::factory()->create([
            'amount' => 200000,
            'started_at' => now()->subDay(),
        ]);

        // Act - Sort by amount descending
        $response = $this->getJson('/api/admin/transactions?sort=amount&order=desc');

        // Assert
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals(200000, $data[0]['amount']);
        $this->assertEquals(100000, $data[1]['amount']);

        // Act - Sort by started_at ascending
        $response = $this->getJson('/api/admin/transactions?sort=started_at&order=asc');

        // Assert
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals($transaction1->id, $data[0]['id']); // Older first
        $this->assertEquals($transaction2->id, $data[1]['id']); // Newer second
    }

    public function test_transaction_not_found_returns_404(): void
    {
        // Arrange
        Sanctum::actingAs($this->user);

        // Act
        $response = $this->getJson('/api/user/transactions/999999');

        // Assert
        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Transaction not found']);
    }

    public function test_can_get_user_transaction_summary(): void
    {
        // Arrange
        Sanctum::actingAs($this->user);
        
        Transaction::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'status' => 'success',
            'amount' => 150000,
        ]);
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
            'amount' => 100000,
        ]);

        // Act
        $response = $this->getJson('/api/user/transactions/summary');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_transactions',
                    'successful_transactions',
                    'total_spent',
                    'courses_owned',
                ]
            ]);

        $data = $response->json('data');
        $this->assertEquals(3, $data['total_transactions']);
        $this->assertEquals(2, $data['successful_transactions']);
        $this->assertEquals(300000, $data['total_spent']);
    }

    public function test_can_download_transaction_receipt(): void
    {
        // Arrange
        Sanctum::actingAs($this->user);
        
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'success',
        ]);

        // Act
        $response = $this->getJson("/api/user/transactions/{$transaction->id}/receipt");

        // Assert
        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', "attachment; filename=\"receipt-{$transaction->id}.pdf\"");
    }

    public function test_cannot_download_receipt_for_non_successful_transaction(): void
    {
        // Arrange
        Sanctum::actingAs($this->user);
        
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        // Act
        $response = $this->getJson("/api/user/transactions/{$transaction->id}/receipt");

        // Assert
        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Receipt only available for successful transactions']);
    }
}
