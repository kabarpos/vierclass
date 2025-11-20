<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Pricing;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AnalyzeSubscriptionDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analyze:subscription-data 
                            {--export-csv : Export results to CSV file}
                            {--detailed : Show detailed breakdown}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analyze existing subscription data before migration to per-course model';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('📊 Analyzing subscription data for migration planning...');
        $this->line('');
        
        $exportCsv = $this->option('export-csv');
        $detailed = $this->option('detailed');
        
        try {
            // Analyze users
            $userAnalysis = $this->analyzeUsers();
            $this->displayUserAnalysis($userAnalysis);
            
            // Analyze subscriptions
            $subscriptionAnalysis = $this->analyzeSubscriptions();
            $this->displaySubscriptionAnalysis($subscriptionAnalysis);
            
            // Analyze courses
            $courseAnalysis = $this->analyzeCourses();
            $this->displayCourseAnalysis($courseAnalysis);
            
            // Migration impact analysis
            $migrationImpact = $this->analyzeMigrationImpact();
            $this->displayMigrationImpact($migrationImpact);
            
            if ($detailed) {
                $this->displayDetailedBreakdown();
            }
            
            if ($exportCsv) {
                $this->exportToCsv([
                    'users' => $userAnalysis,
                    'subscriptions' => $subscriptionAnalysis,
                    'courses' => $courseAnalysis,
                    'migration' => $migrationImpact
                ]);
            }
            
            $this->line('');
            $this->info('✅ Analysis completed successfully!');
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Analysis failed: ' . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * Analyze user data
     */
    private function analyzeUsers()
    {
        return [
            'total_users' => User::count(),
            'users_with_whatsapp' => User::whereNotNull('whatsapp_number')->count(),
            'active_subscribers' => User::whereHas('transactions', function($query) {
                $query->where('is_paid', true)
                      ->whereNotNull('pricing_id')
                      ->whereNull('course_id')
                      ->where('ended_at', '>', now());
            })->count(),
            'expired_subscribers' => User::whereHas('transactions', function($query) {
                $query->where('is_paid', true)
                      ->whereNotNull('pricing_id')
                      ->whereNull('course_id')
                      ->where('ended_at', '<=', now());
            })->count(),
            'never_subscribed' => User::whereDoesntHave('transactions', function($query) {
                $query->where('is_paid', true)->whereNotNull('pricing_id');
            })->count(),
        ];
    }
    
    /**
     * Analyze subscription data
     */
    private function analyzeSubscriptions()
    {
        $activeSubscriptions = Transaction::where('is_paid', true)
            ->whereNotNull('pricing_id')
            ->whereNull('course_id')
            ->where('ended_at', '>', now())
            ->count();
            
        $expiredSubscriptions = Transaction::where('is_paid', true)
            ->whereNotNull('pricing_id')
            ->whereNull('course_id')
            ->where('ended_at', '<=', now())
            ->count();
            
        $totalRevenue = Transaction::where('is_paid', true)
            ->whereNotNull('pricing_id')
            ->whereNull('course_id')
            ->sum('grand_total_amount');
            
        $subscriptionsByPlan = Transaction::select('pricing_id', DB::raw('count(*) as count'))
            ->where('is_paid', true)
            ->whereNotNull('pricing_id')
            ->whereNull('course_id')
            ->with('pricing')
            ->groupBy('pricing_id')
            ->get();
            
        return [
            'active_subscriptions' => $activeSubscriptions,
            'expired_subscriptions' => $expiredSubscriptions,
            'total_subscriptions' => $activeSubscriptions + $expiredSubscriptions,
            'total_revenue' => $totalRevenue,
            'by_plan' => $subscriptionsByPlan
        ];
    }
    
    /**
     * Analyze course data
     */
    private function analyzeCourses()
    {
        return [
            'total_courses' => Course::count(),
            'courses_with_price' => Course::where('price', '>', 0)->count(),
            'free_courses' => Course::where('price', '=', 0)->count(),
            'average_price' => Course::where('price', '>', 0)->avg('price'),
            'total_potential_value' => Course::sum('price'),
            'price_range' => [
                'min' => Course::where('price', '>', 0)->min('price'),
                'max' => Course::where('price', '>', 0)->max('price')
            ]
        ];
    }
    
    /**
     * Analyze migration impact
     */
    private function analyzeMigrationImpact()
    {
        $activeSubscribers = User::whereHas('transactions', function($query) {
            $query->where('is_paid', true)
                  ->whereNotNull('pricing_id')
                  ->whereNull('course_id')
                  ->where('ended_at', '>', now());
        })->count();
        
        $totalCourses = Course::count();
        $totalTransactionsToCreate = $activeSubscribers * $totalCourses;
        
        $estimatedValue = $activeSubscribers * Course::sum('price');
        
        return [
            'users_to_migrate' => $activeSubscribers,
            'courses_per_user' => $totalCourses,
            'total_transactions_to_create' => $totalTransactionsToCreate,
            'estimated_total_value' => $estimatedValue,
            'estimated_storage_increase' => $totalTransactionsToCreate * 1000, // Rough estimate in bytes
        ];
    }
    
    /**
     * Display user analysis
     */
    private function displayUserAnalysis($analysis)
    {
        $this->info('👥 USER ANALYSIS');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line("📊 Total Users: {$analysis['total_users']}");
        $this->line("📱 Users with WhatsApp: {$analysis['users_with_whatsapp']}");
        $this->line("✅ Active Subscribers: {$analysis['active_subscribers']}");
        $this->line("⏰ Expired Subscribers: {$analysis['expired_subscribers']}");
        $this->line("❌ Never Subscribed: {$analysis['never_subscribed']}");
        $this->line('');
    }
    
    /**
     * Display subscription analysis
     */
    private function displaySubscriptionAnalysis($analysis)
    {
        $this->info('📋 SUBSCRIPTION ANALYSIS');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line("✅ Active Subscriptions: {$analysis['active_subscriptions']}");
        $this->line("⏰ Expired Subscriptions: {$analysis['expired_subscriptions']}");
        $this->line("📊 Total Subscriptions: {$analysis['total_subscriptions']}");
        $this->line("💰 Total Revenue: Rp " . number_format($analysis['total_revenue'], 0, ',', '.'));
        
        $this->line('');
        $this->line('📈 By Subscription Plan:');
        foreach ($analysis['by_plan'] as $plan) {
            $planName = $plan->pricing ? $plan->pricing->name : 'Unknown Plan';
            $this->line("   • {$planName}: {$plan->count} transactions");
        }
        $this->line('');
    }
    
    /**
     * Display course analysis
     */
    private function displayCourseAnalysis($analysis)
    {
        $this->info('📚 COURSE ANALYSIS');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line("📊 Total Courses: {$analysis['total_courses']}");
        $this->line("💰 Paid Courses: {$analysis['courses_with_price']}");
        $this->line("🆓 Free Courses: {$analysis['free_courses']}");
        
        if ($analysis['average_price']) {
            $this->line("📈 Average Price: Rp " . number_format($analysis['average_price'], 0, ',', '.'));
        }
        
        $this->line("💎 Total Potential Value: Rp " . number_format($analysis['total_potential_value'], 0, ',', '.'));
        
        if ($analysis['price_range']['min'] && $analysis['price_range']['max']) {
            $this->line("📊 Price Range: Rp " . number_format($analysis['price_range']['min'], 0, ',', '.') . 
                       " - Rp " . number_format($analysis['price_range']['max'], 0, ',', '.'));
        }
        $this->line('');
    }
    
    /**
     * Display migration impact
     */
    private function displayMigrationImpact($analysis)
    {
        $this->info('🚀 MIGRATION IMPACT ANALYSIS');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line("👥 Users to Migrate: {$analysis['users_to_migrate']}");
        $this->line("📚 Courses per User: {$analysis['courses_per_user']}");
        $this->line("💳 Total Transactions to Create: {$analysis['total_transactions_to_create']}");
        $this->line("💰 Estimated Total Value: Rp " . number_format($analysis['estimated_total_value'], 0, ',', '.'));
        $this->line("💾 Estimated Storage Increase: ~" . number_format($analysis['estimated_storage_increase'] / 1024, 0) . " KB");
        $this->line('');
        
        // Warnings and recommendations
        if ($analysis['total_transactions_to_create'] > 1000) {
            $this->warn('⚠️  Large migration detected - consider using batch processing');
        }
        
        if ($analysis['users_to_migrate'] > 100) {
            $this->warn('⚠️  Many users to migrate - plan for potential downtime');
        }
    }
    
    /**
     * Display detailed breakdown
     */
    private function displayDetailedBreakdown()
    {
        $this->info('🔍 DETAILED BREAKDOWN');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        
        // Active subscriptions with user details
        $activeSubscriptions = Transaction::where('is_paid', true)
            ->whereNotNull('pricing_id')
            ->whereNull('course_id')
            ->where('ended_at', '>', now())
            ->with(['student', 'pricing'])
            ->get();
            
        $this->line('✅ Active Subscriptions Details:');
        foreach ($activeSubscriptions->take(10) as $transaction) {
            $user = $transaction->student;
            $pricing = $transaction->pricing;
            $this->line("   • {$user->name} ({$user->email}) - " . ($pricing->name ?? 'Unknown') . 
                       " - Expires: " . $transaction->ended_at->format('Y-m-d'));
        }
        
        if ($activeSubscriptions->count() > 10) {
            $remaining = $activeSubscriptions->count() - 10;
            $this->line("   ... and {$remaining} more");
        }
        
        $this->line('');
        
        // Course pricing breakdown
        $this->line('💰 Course Pricing Breakdown:');
        $courses = Course::where('price', '>', 0)->orderBy('price', 'desc')->take(10)->get();
        foreach ($courses as $course) {
            $this->line("   • {$course->name}: Rp " . number_format($course->price, 0, ',', '.'));
        }
        
        if (Course::where('price', '>', 0)->count() > 10) {
            $remaining = Course::where('price', '>', 0)->count() - 10;
            $this->line("   ... and {$remaining} more");
        }
    }
    
    /**
     * Export analysis to CSV
     */
    private function exportToCsv($data)
    {
        $filename = 'subscription_analysis_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $filepath = storage_path('app/exports/' . $filename);
        
        // Ensure directory exists
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }
        
        $file = fopen($filepath, 'w');
        
        // Write headers and data
        fputcsv($file, ['Metric', 'Value']);
        
        // User data
        fputcsv($file, ['=== USER ANALYSIS ===', '']);
        foreach ($data['users'] as $key => $value) {
            fputcsv($file, [ucfirst(str_replace('_', ' ', $key)), $value]);
        }
        
        // Subscription data
        fputcsv($file, ['=== SUBSCRIPTION ANALYSIS ===', '']);
        foreach ($data['subscriptions'] as $key => $value) {
            if ($key !== 'by_plan') {
                fputcsv($file, [ucfirst(str_replace('_', ' ', $key)), $value]);
            }
        }
        
        // Course data
        fputcsv($file, ['=== COURSE ANALYSIS ===', '']);
        foreach ($data['courses'] as $key => $value) {
            if (!is_array($value)) {
                fputcsv($file, [ucfirst(str_replace('_', ' ', $key)), $value]);
            }
        }
        
        // Migration data
        fputcsv($file, ['=== MIGRATION IMPACT ===', '']);
        foreach ($data['migration'] as $key => $value) {
            fputcsv($file, [ucfirst(str_replace('_', ' ', $key)), $value]);
        }
        
        fclose($file);
        
        $this->info("📄 Analysis exported to: {$filepath}");
    }
}