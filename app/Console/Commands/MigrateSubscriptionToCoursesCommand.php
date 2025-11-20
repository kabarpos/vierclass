<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Pricing;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrateSubscriptionToCoursesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:subscription-to-courses 
                            {--dry-run : Preview changes without executing}
                            {--subscription-id= : Migrate specific subscription ID}
                            {--user-id= : Migrate specific user ID}
                            {--course-id= : Grant specific course to all active subscribers}
                            {--batch-size=100 : Number of records to process per batch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing subscription users to per-course ownership model';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting subscription to course ownership migration...');
        
        $dryRun = $this->option('dry-run');
        $subscriptionId = $this->option('subscription-id');
        $userId = $this->option('user-id');
        $courseId = $this->option('course-id');
        $batchSize = (int) $this->option('batch-size');
        
        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be made to the database');
        }
        
        try {
            DB::beginTransaction();
            
            // Get active subscription transactions
            $activeSubscriptions = $this->getActiveSubscriptions($subscriptionId, $userId);
            
            if ($activeSubscriptions->isEmpty()) {
                $this->error('❌ No active subscriptions found to migrate');
                return 1;
            }
            
            $this->info("📊 Found {$activeSubscriptions->count()} active subscription(s) to migrate");
            
            // Get courses to grant access to
            $courses = $this->getCoursesToGrant($courseId);
            
            if ($courses->isEmpty()) {
                $this->error('❌ No courses found to grant access to');
                return 1;
            }
            
            $this->info("📚 Will grant access to {$courses->count()} course(s)");
            
            // Display summary
            $this->displayMigrationSummary($activeSubscriptions, $courses, $dryRun);
            
            if (!$dryRun && !$this->option('no-interaction') && !$this->confirm('Do you want to proceed with the migration?')) {
                $this->info('Migration cancelled by user');
                return 0;
            }
            
            // Perform migration
            $results = $this->performMigration($activeSubscriptions, $courses, $dryRun, $batchSize);
            
            // Display results
            $this->displayResults($results, $dryRun);
            
            if (!$dryRun) {
                DB::commit();
                $this->info('✅ Migration completed successfully!');
            } else {
                DB::rollBack();
                $this->info('🔍 Dry run completed - no changes made');
            }
            
            return 0;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Migration failed: ' . $e->getMessage());
            Log::error('Subscription migration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
    
    /**
     * Get active subscription transactions
     */
    private function getActiveSubscriptions($subscriptionId = null, $userId = null)
    {
        $query = Transaction::query()
            ->with(['student', 'pricing'])
            ->where('is_paid', true)
            ->whereNotNull('pricing_id')
            ->whereNull('course_id') // Only subscription transactions
            ->where('ended_at', '>', now()); // Active subscriptions
            
        if ($subscriptionId) {
            $query->where('id', $subscriptionId);
        }
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        return $query->get();
    }
    
    /**
     * Get courses to grant access to
     */
    private function getCoursesToGrant($courseId = null)
    {
        $query = Course::query(); // Grant access to all courses
        
        if ($courseId) {
            $query->where('id', $courseId);
        }
        
        return $query->get();
    }
    
    /**
     * Display migration summary
     */
    private function displayMigrationSummary($subscriptions, $courses, $dryRun)
    {
        $this->line('');
        $this->info('📋 Migration Summary:');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        
        // Subscription summary
        $this->line("📊 Subscriptions to migrate: {$subscriptions->count()}");
        foreach ($subscriptions->take(5) as $subscription) {
            $user = $subscription->student;
            $pricing = $subscription->pricing;
            $this->line("   • User: {$user->name} ({$user->email}) - " . ($pricing->name ?? 'Unknown Plan'));
        }
        
        if ($subscriptions->count() > 5) {
            $remaining = $subscriptions->count() - 5;
            $this->line("   ... and {$remaining} more");
        }
        
        $this->line('');
        
        // Courses summary
        $this->line("📚 Courses to grant access: {$courses->count()}");
        foreach ($courses->take(5) as $course) {
            $price = $course->price > 0 ? 'Rp ' . number_format($course->price) : 'Free';
            $this->line("   • {$course->name} ({$price})");
        }
        
        if ($courses->count() > 5) {
            $remaining = $courses->count() - 5;
            $this->line("   ... and {$remaining} more");
        }
        
        $this->line('');
        $totalTransactions = $subscriptions->count() * $courses->count();
        $this->line("🔢 Total course transactions to create: {$totalTransactions}");
        
        if ($dryRun) {
            $this->warn('🔍 This is a DRY RUN - no actual changes will be made');
        }
        
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line('');
    }
    
    /**
     * Perform the actual migration
     */
    private function performMigration($subscriptions, $courses, $dryRun, $batchSize)
    {
        $results = [
            'users_migrated' => 0,
            'transactions_created' => 0,
            'courses_granted' => 0,
            'errors' => []
        ];
        
        $progressBar = $this->output->createProgressBar($subscriptions->count());
        $progressBar->setFormat('🔄 Processing: %current%/%max% [%bar%] %percent:3s%% - %message%');
        $progressBar->setMessage('Starting migration...');
        
        foreach ($subscriptions as $subscription) {
            try {
                $user = $subscription->student;
                $progressBar->setMessage("Migrating user: {$user->name}");
                
                $userCourseCount = 0;
                
                foreach ($courses as $course) {
                    // Check if user already has access to this course
                    $existingAccess = Transaction::where('user_id', $user->id)
                        ->where('course_id', $course->id)
                        ->where('is_paid', true)
                        ->exists();
                        
                    if ($existingAccess) {
                        continue; // Skip if user already has access
                    }
                    
                    if (!$dryRun) {
                        // Create course ownership transaction
                        Transaction::create([
                            'user_id' => $user->id,
                            'course_id' => $course->id,
                            'pricing_id' => null,
                            'booking_trx_id' => 'MIGRATE-' . now()->format('Ymd') . '-' . $user->id . '-' . $course->id,
                            'sub_total_amount' => $course->price,
                            'admin_fee_amount' => 0,
                            'grand_total_amount' => $course->price,
                            'is_paid' => true,
                            'payment_type' => 'Migration',
                            'started_at' => $subscription->started_at,
                            'ended_at' => now()->addYears(100), // Far future date for lifetime access
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                    
                    $results['transactions_created']++;
                    $userCourseCount++;
                }
                
                if ($userCourseCount > 0) {
                    $results['users_migrated']++;
                    $results['courses_granted'] += $userCourseCount;
                }
                
                // Log migration for this user
                if (!$dryRun) {
                    Log::info('User migrated from subscription to course ownership', [
                        'user_id' => $user->id,
                        'user_email' => $user->email,
                        'original_subscription_id' => $subscription->id,
                        'courses_granted' => $userCourseCount,
                        'migration_date' => now()
                    ]);
                }
                
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'user_id' => $subscription->user_id,
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage()
                ];
                
                Log::error('Failed to migrate user subscription', [
                    'user_id' => $subscription->user_id,
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage()
                ]);
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->line('');
        
        return $results;
    }
    
    /**
     * Display migration results
     */
    private function displayResults($results, $dryRun)
    {
        $this->line('');
        $this->info('📊 Migration Results:');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        
        if ($dryRun) {
            $this->line('🔍 DRY RUN RESULTS (no changes made):');
        }
        
        $this->line("✅ Users migrated: {$results['users_migrated']}");
        $this->line("📚 Total courses granted: {$results['courses_granted']}");
        $this->line("💳 Course transactions created: {$results['transactions_created']}");
        
        if (!empty($results['errors'])) {
            $this->line("❌ Errors encountered: " . count($results['errors']));
            foreach ($results['errors'] as $error) {
                $this->error("   • User ID {$error['user_id']}: {$error['error']}");
            }
        }
        
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        
        if (!$dryRun && $results['users_migrated'] > 0) {
            $this->line('');
            $this->info('🎉 Migration completed successfully!');
            $this->line('📝 Next steps:');
            $this->line('   • Verify user course access in the admin panel');
            $this->line('   • Test course access for migrated users');
            $this->line('   • Monitor system logs for any issues');
            $this->line('   • Consider deactivating old subscription plans');
        }
    }
}
