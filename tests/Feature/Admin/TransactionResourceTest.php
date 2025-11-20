<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\TransactionResource;
use App\Models\Category;
use App\Models\Course;
use App\Models\Transaction;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TransactionResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $customer;
    private Course $course;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin role and user
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole($adminRole);
        
        // Create test customer and course
        $this->customer = User::factory()->create();
        $category = Category::factory()->create();
        $this->course = Course::factory()->create(['category_id' => $category->id]);
        
        $this->actingAs($this->adminUser);
    }

    public function test_can_render_transaction_resource_list_page(): void
    {
        // Arrange
        Transaction::factory()->count(5)->create([
            'user_id' => $this->customer->id,
            'course_id' => $this->course->id,
        ]);
        
        // Act & Assert
        Livewire::test(TransactionResource\Pages\ListTransactions::class)
            ->assertSuccessful();
    }

    public function test_can_list_transactions(): void
    {
        // Arrange
        $transactions = Transaction::factory()->count(3)->create([
            'user_id' => $this->customer->id,
            'course_id' => $this->course->id,
        ]);
        
        // Act & Assert
        Livewire::test(TransactionResource\Pages\ListTransactions::class)
            ->assertCanSeeTableRecords($transactions);
    }

    public function test_can_render_transaction_resource_create_page(): void
    {
        // Act & Assert
        Livewire::test(TransactionResource\Pages\CreateTransaction::class)
            ->assertSuccessful();
    }

    public function test_can_create_transaction_form_validation(): void
    {
        // Arrange
        $transactionData = [
            'user_id' => $this->customer->id,
            'course_id' => $this->course->id,
            'admin_fee_amount' => '0',
            'started_at' => now()->format('Y-m-d'),
            'is_paid' => 0,
            'payment_type' => 'Manual',
        ];
        
        // Act & Assert
        Livewire::test(TransactionResource\Pages\CreateTransaction::class)
            ->fillForm($transactionData)
            ->call('create')
            ->assertHasNoFormErrors();
    }

    public function test_transaction_admin_fee_calculation(): void
    {
        // Arrange
        $course = Course::factory()->create([
            'price' => 299000,
            'admin_fee_amount' => 5000,
            'category_id' => Category::factory()->create()->id,
        ]);
        
        // Act & Assert
        Livewire::test(TransactionResource\Pages\CreateTransaction::class)
            ->fillForm([
                'user_id' => $this->customer->id,
                'course_id' => $course->id,
                'sub_total_amount' => 299000,
            ])
            ->assertFormSet([
                'grand_total_amount' => 304000, // 299000 + 5000 admin fee from course
            ]);
    }

    public function test_can_complete_transaction_creation(): void
    {
        // Arrange
        $course = Course::factory()->create([
            'admin_fee_amount' => 5000,
            'category_id' => Category::factory()->create()->id,
        ]);
        
        $transactionData = [
            'user_id' => $this->customer->id,
            'course_id' => $course->id,
            'started_at' => now()->format('Y-m-d'),
            'is_paid' => 0,
            'payment_type' => 'Manual',
        ];
        
        // Act & Assert
        Livewire::test(TransactionResource\Pages\CreateTransaction::class)
            ->fillForm($transactionData)
            ->call('create')
            ->assertHasNoFormErrors();
            
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->customer->id,
            'course_id' => $course->id,
            'admin_fee_amount' => 5000,
            'is_paid' => false,
            'payment_type' => 'Manual',
        ]);
    }

    public function test_can_validate_transaction_creation(): void
    {
        // Act & Assert
        Livewire::test(TransactionResource\Pages\CreateTransaction::class)
            ->fillForm([
                'user_id' => null,
                'course_id' => null,
                'sub_total_amount' => 0,
                'grand_total_amount' => 0,
                'payment_type' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'user_id' => 'required',
                'course_id' => 'required',
                'payment_type' => 'required',
            ]);
    }

    public function test_can_render_transaction_resource_edit_page(): void
    {
        // Arrange
        $transaction = Transaction::factory()->create([
            'user_id' => $this->customer->id,
            'course_id' => $this->course->id,
        ]);
        
        // Act & Assert
        Livewire::test(TransactionResource\Pages\EditTransaction::class, [
            'record' => $transaction->getRouteKey(),
        ])
            ->assertSuccessful();
    }

    public function test_can_retrieve_transaction_data_for_editing(): void
    {
        // Arrange
        $course = Course::factory()->create([
            'admin_fee_amount' => 29900,
            'category_id' => Category::factory()->create()->id,
        ]);
        
        $transaction = Transaction::factory()->create([
            'user_id' => $this->customer->id,
            'course_id' => $course->id,
            'sub_total_amount' => 299000,
            'admin_fee_amount' => 29900,
            'grand_total_amount' => 328900,
            'is_paid' => false,
            'payment_type' => 'Manual',
        ]);
        
        // Act & Assert
        Livewire::test(TransactionResource\Pages\EditTransaction::class, [
            'record' => $transaction->getRouteKey(),
        ])
            ->assertFormSet([
                'user_id' => $this->customer->id,
                'course_id' => $course->id,
                'sub_total_amount' => 299000,
                'grand_total_amount' => 328900,
                'is_paid' => false,
            ]);
    }

    public function test_can_save_transaction_changes(): void
    {
        // Arrange
        $transaction = Transaction::factory()->create([
            'user_id' => $this->customer->id,
            'course_id' => $this->course->id,
            'is_paid' => false,
            'payment_type' => 'Manual',
        ]);
        
        // Act & Assert
        Livewire::test(TransactionResource\Pages\EditTransaction::class, [
            'record' => $transaction->getRouteKey(),
        ])
            ->fillForm([
                'is_paid' => true,
                'payment_type' => 'Midtrans',
            ])
            ->call('save')
            ->assertHasNoFormErrors();
            
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'is_paid' => true,
            'payment_type' => 'Midtrans',
        ]);
    }

    public function test_can_delete_transaction(): void
    {
        // Arrange
        $transaction = Transaction::factory()->create([
            'user_id' => $this->customer->id,
            'course_id' => $this->course->id,
        ]);
        
        // Act & Assert
        Livewire::test(TransactionResource\Pages\EditTransaction::class, [
            'record' => $transaction->getRouteKey(),
        ])
            ->callAction(DeleteAction::class)
            ->assertSuccessful();
            
        $this->assertSoftDeleted($transaction);
    }

    public function test_can_search_transactions(): void
    {
        // Arrange
        $user1 = User::factory()->create(['name' => 'John Doe']);
        $user2 = User::factory()->create(['name' => 'Jane Smith']);
        
        $transaction1 = Transaction::factory()->create([
            'user_id' => $user1->id,
            'course_id' => $this->course->id,
        ]);
        $transaction2 = Transaction::factory()->create([
            'user_id' => $user2->id,
            'course_id' => $this->course->id,
        ]);
        
        // Act & Assert
        Livewire::test(TransactionResource\Pages\ListTransactions::class)
            ->searchTable('John')
            ->assertCanSeeTableRecords([$transaction1])
            ->assertCanNotSeeTableRecords([$transaction2]);
    }

    public function test_can_filter_transactions_by_trashed(): void
    {
        // Arrange
        $activeTransaction = Transaction::factory()->create([
            'user_id' => $this->customer->id,
            'course_id' => $this->course->id,
            'is_paid' => false,
            'payment_type' => 'Manual',
        ]);
        $trashedTransaction = Transaction::factory()->create([
            'user_id' => $this->customer->id,
            'course_id' => $this->course->id,
            'is_paid' => true,
            'payment_type' => 'Manual',
        ]);
        $trashedTransaction->delete();
        
        // Act & Assert
        Livewire::test(TransactionResource\Pages\ListTransactions::class)
            ->assertCanSeeTableRecords([$activeTransaction])
            ->assertCanNotSeeTableRecords([$trashedTransaction]);
    }

    public function test_can_view_transactions_list(): void
    {
        // Arrange
        $transaction = Transaction::factory()->create([
            'user_id' => $this->customer->id,
            'course_id' => $this->course->id,
            'payment_type' => 'bank_transfer',
        ]);
        
        // Act & Assert
        Livewire::test(TransactionResource\Pages\ListTransactions::class)
            ->assertCanSeeTableRecords([$transaction])
            ->assertSuccessful();
    }

    public function test_can_access_transaction_edit_page(): void
    {
        // Arrange
        $transaction = Transaction::factory()->create([
            'user_id' => $this->customer->id,
            'course_id' => $this->course->id,
        ]);
        
        // Act & Assert
        Livewire::test(TransactionResource\Pages\EditTransaction::class, [
            'record' => $transaction->getRouteKey(),
        ])
            ->assertFormExists()
            ->assertSuccessful();
    }

    public function test_transaction_form_renders_correctly(): void
    {
        // Act & Assert - Test form rendering
        Livewire::test(TransactionResource\Pages\CreateTransaction::class)
            ->assertFormExists()
            ->assertSuccessful();
    }

    public function test_transaction_can_be_created_with_admin_fee(): void
    {
        // Arrange
        $course = Course::factory()->create([
            'price' => 500000,
            'admin_fee_amount' => 10000,
            'category_id' => Category::factory()->create()->id,
        ]);
        
        // Act
        $transaction = Transaction::create([
            'user_id' => $this->customer->id,
            'course_id' => $course->id,
            'sub_total_amount' => 500000,
            'admin_fee_amount' => $course->admin_fee_amount,
            'grand_total_amount' => 510000,
            'is_paid' => false,
            'payment_type' => 'Manual',
            'started_at' => now(),
        ]);
        
        // Assert
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'sub_total_amount' => 500000,
            'admin_fee_amount' => 10000,
            'grand_total_amount' => 510000,
        ]);
    }
}