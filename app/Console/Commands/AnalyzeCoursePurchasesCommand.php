<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Course;
use App\Models\User;
use App\Models\Transaction;

class AnalyzeCoursePurchasesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analyze:course-purchases {--detailed} {--export-csv}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analyze course purchase data and system metrics';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('📊 Analyzing course purchase data...');
        $this->line('');

        // Analyze users
        $userAnalysis = $this->analyzeUsers();
        
        // Analyze courses
        $courseAnalysis = $this->analyzeCourses();
        
        // Analyze transactions
        $transactionAnalysis = $this->analyzeTransactions();
        
        // Display results
        $this->displayUserAnalysis($userAnalysis);
        $this->displayCourseAnalysis($courseAnalysis);
        $this->displayTransactionAnalysis($transactionAnalysis);
        
        // Detailed breakdown if requested
        if ($this->option('detailed')) {
            $this->displayDetailedBreakdown();
        }
        
        // Export to CSV if requested
        if ($this->option('export-csv')) {
            $data = [
                'users' => $userAnalysis,
                'courses' => $courseAnalysis,
                'transactions' => $transactionAnalysis
            ];
            $this->exportToCsv($data);
        }
        
        return 0;
    }
    
    /**
     * Analyze user data
     */
    private function analyzeUsers()
    {
        $totalUsers = User::count();
        $usersWithWhatsApp = User::whereNotNull('whatsapp_number')->count();
        $usersWithPurchases = User::whereHas('transactions', function($query) {
            $query->where('is_paid', true)
                  ->whereNotNull('course_id');
        })->count();
        
        $usersWithoutPurchases = $totalUsers - $usersWithPurchases;
        
        return [
            'total_users' => $totalUsers,
            'users_with_whatsapp' => $usersWithWhatsApp,
            'users_with_purchases' => $usersWithPurchases,
            'users_without_purchases' => $usersWithoutPurchases
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
     * Analyze transaction data
     */
    private function analyzeTransactions()
    {
        $totalTransactions = Transaction::where('is_paid', true)
            ->whereNotNull('course_id')
            ->count();
            
        $totalRevenue = Transaction::where('is_paid', true)
            ->whereNotNull('course_id')
            ->sum('grand_total_amount');
            
        $transactionsByCourse = Transaction::where('is_paid', true)
            ->whereNotNull('course_id')
            ->selectRaw('course_id, count(*) as count, sum(grand_total_amount) as revenue')
            ->groupBy('course_id')
            ->orderBy('revenue', 'desc')
            ->with('course')
            ->take(10)
            ->get();
            
        return [
            'total_transactions' => $totalTransactions,
            'total_revenue' => $totalRevenue,
            'by_course' => $transactionsByCourse
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
        $this->line("✅ Users with Purchases: {$analysis['users_with_purchases']}");
        $this->line("❌ Users without Purchases: {$analysis['users_without_purchases']}");
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
     * Display transaction analysis
     */
    private function displayTransactionAnalysis($analysis)
    {
        $this->info('💳 TRANSACTION ANALYSIS');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line("📊 Total Course Purchases: {$analysis['total_transactions']}");
        $this->line("💰 Total Revenue: Rp " . number_format($analysis['total_revenue'], 0, ',', '.'));
        
        $this->line('');
        $this->line('📈 Top Selling Courses:');
        foreach ($analysis['by_course'] as $courseData) {
            $courseName = $courseData->course ? $courseData->course->name : 'Unknown Course';
            $this->line("   • {$courseName}: {$courseData->count} purchases (Rp " . number_format($courseData->revenue, 0, ',', '.') . ")");
        }
        $this->line('');
    }
    
    /**
     * Display detailed breakdown
     */
    private function displayDetailedBreakdown()
    {
        $this->info('🔍 DETAILED BREAKDOWN');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        
        // Recent course purchases with user details
        $recentPurchases = Transaction::where('is_paid', true)
            ->whereNotNull('course_id')
            ->with(['student', 'course'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
            
        $this->line('✅ Recent Course Purchases:');
        foreach ($recentPurchases as $transaction) {
            $user = $transaction->student;
            $course = $transaction->course;
            $this->line("   • {$user->name} ({$user->email}) - " . ($course->name ?? 'Unknown Course') . 
                       " - Rp " . number_format($transaction->grand_total_amount, 0, ',', '.') .
                       " - " . $transaction->created_at->format('Y-m-d H:i'));
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
        $filename = 'course_purchase_analysis_' . now()->format('Y-m-d_H-i-s') . '.csv';
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
        
        // Course data
        fputcsv($file, ['=== COURSE ANALYSIS ===', '']);
        foreach ($data['courses'] as $key => $value) {
            if ($key !== 'price_range') {
                fputcsv($file, [ucfirst(str_replace('_', ' ', $key)), $value]);
            }
        }
        
        // Transaction data
        fputcsv($file, ['=== TRANSACTION ANALYSIS ===', '']);
        fputcsv($file, ['Total Course Purchases', $data['transactions']['total_transactions']]);
        fputcsv($file, ['Total Revenue', $data['transactions']['total_revenue']]);
        
        fclose($file);
        
        $this->info("📊 Analysis exported to: {$filepath}");
    }
}