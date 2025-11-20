<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create comprehensive permissions
        $permissions = [
            // User Management
            'view users',
            'create users',
            'edit users',
            'delete users',
            
            // Course Management
            'view courses',
            'create courses',
            'edit courses',
            'delete courses',
            'publish courses',
            
            // Category Management
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',
            
            // Transaction Management
            'view transactions',
            'create transactions',
            'edit transactions',
            'delete transactions',
            'process refunds',
            
            // Pricing Management
            'view pricing',
            'create pricing',
            'edit pricing',
            'delete pricing',
            
            // Mentor Management
            'view mentors',
            'create mentors',
            'edit mentors',
            'delete mentors',
            
            // Content Management
            'view content',
            'create content',
            'edit content',
            'delete content',
            
            // Reporting
            'view reports',
            'export reports',
            
            // System
            'system settings',
            'backup system',
            'view logs',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $mentorRole = Role::firstOrCreate(['name' => 'mentor', 'guard_name' => 'web']);
        $studentRole = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

        // Assign permissions to super admin (all permissions)
        $superAdminRole->syncPermissions(Permission::all());
        
        // Assign permissions to admin (most permissions except system critical ones)
        $adminPermissions = Permission::whereNotIn('name', [
            'backup system',
            'system settings',
            'delete users',
        ])->get();
        $adminRole->syncPermissions($adminPermissions);
        
        // Assign permissions to mentor (restricted: content-focused, no delete/publish or mentor management)
        $mentorPermissions = Permission::whereIn('name', [
            'view courses',
            'create courses',
            'edit courses',
            'view content',
            'create content',
            'edit content',
            'view users',
            'view reports',
            'view mentors',
        ])->get();
        $mentorRole->syncPermissions($mentorPermissions);
        
        // Student role has minimal permissions (handled in application logic)
        $studentPermissions = Permission::whereIn('name', [
            'view courses',
            'view content',
        ])->get();
        $studentRole->syncPermissions($studentPermissions);

        // Cleanup: konsolidasikan role legacy 'instructor' menjadi 'mentor'
        try {
            $instructorRole = Role::where(['name' => 'instructor', 'guard_name' => 'web'])->first();
            if ($instructorRole) {
                // Pindahkan semua user dengan role 'instructor' ke 'mentor'
                $legacyUsers = User::role('instructor')->get();
                foreach ($legacyUsers as $legacy) {
                    // Hindari duplikasi: hapus role lama lalu assign role mentor
                    $legacy->removeRole('instructor');
                    $legacy->assignRole($mentorRole);
                }
                // Hapus role 'instructor' agar tidak muncul di dropdown
                $instructorRole->delete();
                $this->command->info("Legacy role 'instructor' consolidated to 'mentor'.");
            }
        } catch (\Throwable $e) {
            // Logging di seeder untuk diagnosa, tanpa menggagalkan seeding utama
            $this->command->warn("Instructor role cleanup skipped: " . $e->getMessage());
        }

        // Create default admin user for backward compatibility
        $user = User::firstOrCreate(
            ['email' => 'team@LMS.com'],
            [
                'name' => 'Team LMS',
                'password' => Hash::make('123123123'),
                'email_verified_at' => now(),
                'whatsapp_number' => '+62812345678',
            ]
        );
        $user->assignRole($adminRole);

        $this->command->info('Roles and permissions created successfully!');
    }
}
