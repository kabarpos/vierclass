<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Tables\Actions\BulkActionGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin role and user
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole($adminRole);
        
        $this->actingAs($this->adminUser);
    }

    public function test_can_render_user_resource_list_page(): void
    {
        // Arrange
        User::factory()->count(5)->create();
        
        // Act & Assert
        Livewire::test(UserResource\Pages\ListUsers::class)
            ->assertSuccessful();
    }

    public function test_can_list_users(): void
    {
        // Arrange
        $users = User::factory()->count(3)->create();
        
        // Act & Assert
        Livewire::test(UserResource\Pages\ListUsers::class)
            ->assertCanSeeTableRecords($users);
    }

    public function test_can_render_user_resource_create_page(): void
    {
        // Act & Assert
        Livewire::test(UserResource\Pages\CreateUser::class)
            ->assertSuccessful();
    }

    public function test_can_create_user(): void
    {
        // Arrange
        $role = Role::firstOrCreate(['name' => 'customer']);
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'whatsapp_number' => '+6281234567890',
            'roles' => [$role->id],
            'email_verified_at' => true,
            'is_account_active' => true,
        ];
        
        // Act & Assert
        Livewire::test(UserResource\Pages\CreateUser::class)
            ->fillForm($userData)
            ->call('create')
            ->assertHasNoFormErrors();
            
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'whatsapp_number' => '+6281234567890',
            'is_account_active' => 1,
        ]);
    }

    public function test_can_validate_user_creation(): void
    {
        // Act & Assert
        Livewire::test(UserResource\Pages\CreateUser::class)
            ->fillForm([
                'name' => '',
                'email' => 'invalid-email',
                'password' => '123', // Too short
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
                'email' => 'email',
                'password' => 'min',
            ]);
    }

    public function test_can_render_user_resource_edit_page(): void
    {
        // Arrange
        $user = User::factory()->create();
        
        // Act & Assert
        Livewire::test(UserResource\Pages\EditUser::class, [
            'record' => $user->getRouteKey(),
        ])
            ->assertSuccessful();
    }

    public function test_can_retrieve_user_data_for_editing(): void
    {
        // Arrange
        $user = User::factory()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'whatsapp_number' => '+6281234567890',
        ]);
        
        // Act & Assert
        Livewire::test(UserResource\Pages\EditUser::class, [
            'record' => $user->getRouteKey(),
        ])
            ->assertFormSet([
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'whatsapp_number' => '+6281234567890',
            ]);
    }

    public function test_can_save_user_changes(): void
    {
        // Arrange
        $user = User::factory()->create();
        $newData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'whatsapp_number' => '+6289876543210',
        ];
        
        // Act & Assert
        Livewire::test(UserResource\Pages\EditUser::class, [
            'record' => $user->getRouteKey(),
        ])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();
            
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'whatsapp_number' => '+6289876543210',
        ]);
    }

    public function test_can_delete_user(): void
    {
        // Arrange
        $user = User::factory()->create();
        
        // Act & Assert
        Livewire::test(UserResource\Pages\EditUser::class, [
            'record' => $user->getRouteKey(),
        ])
            ->callAction('delete')
            ->assertSuccessful();
            
        $this->assertModelMissing($user);
    }

    public function test_can_search_users(): void
    {
        // Arrange
        $user1 = User::factory()->create(['name' => 'John Doe']);
        $user2 = User::factory()->create(['name' => 'Jane Smith']);
        $user3 = User::factory()->create(['name' => 'Bob Johnson']);
        
        // Act & Assert
        Livewire::test(UserResource\Pages\ListUsers::class)
            ->searchTable('John')
            ->assertCanSeeTableRecords([$user1, $user3])
            ->assertCanNotSeeTableRecords([$user2]);
    }

    public function test_can_filter_users_by_status(): void
    {
        // Arrange
        $activeUser = User::factory()->create(['is_account_active' => true]);
        $inactiveUser = User::factory()->create(['is_account_active' => false]);
        
        // Act & Assert
        Livewire::test(UserResource\Pages\ListUsers::class)
            ->assertCanSeeTableRecords([$activeUser, $inactiveUser]);
    }

    public function test_can_bulk_delete_users(): void
    {
        // Arrange
        $users = User::factory()->count(3)->create();
        
        // Act & Assert
        Livewire::test(UserResource\Pages\ListUsers::class)
            ->callTableBulkAction('delete', $users)
            ->assertSuccessful();
            
        foreach ($users as $user) {
            $this->assertModelMissing($user);
        }
    }

    public function test_cannot_delete_own_account(): void
    {
        // Act & Assert
        Livewire::test(UserResource\Pages\EditUser::class, [
            'record' => $this->adminUser->getRouteKey(),
        ])
            ->assertActionHidden('delete');
    }

    public function test_password_is_hashed_when_creating_user(): void
    {
        // Arrange
        $role = Role::firstOrCreate(['name' => 'customer']);
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'plaintext-password',
            'whatsapp_number' => '+6281234567890',
            'roles' => [$role->id],
            'is_account_active' => true,
        ];
        
        // Act
        Livewire::test(UserResource\Pages\CreateUser::class)
            ->fillForm($userData)
            ->call('create')
            ->assertHasNoFormErrors();
            
        // Assert
        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user, 'User should be created successfully');
        $this->assertNotEquals('plaintext-password', $user->password);
        $this->assertTrue(\Hash::check('plaintext-password', $user->password));
    }
}