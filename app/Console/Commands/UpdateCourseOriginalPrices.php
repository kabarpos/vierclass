<?php

namespace App\Console\Commands;

use App\Models\Course;
use Illuminate\Console\Command;

class UpdateCourseOriginalPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'course:update-original-prices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update original prices for courses to test discount functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating course original prices for testing...');
        
        // Get first 5 courses that have prices
        $courses = Course::where('price', '>', 0)
            ->whereNull('original_price')
            ->limit(5)
            ->get();
            
        if ($courses->isEmpty()) {
            $this->warn('No courses found to update.');
            return;
        }
        
        $updated = 0;
        
        foreach ($courses as $course) {
            // Set original price to be 20-50% higher than current price
            $increasePercentage = rand(20, 50);
            $originalPrice = $course->price + ($course->price * $increasePercentage / 100);
            
            $course->update([
                'original_price' => round($originalPrice, -3) // Round to nearest thousand
            ]);
            
            $this->line(sprintf(
                'Updated %s: Price %s -> Original Price %s (%.0f%% discount)',
                $course->name,
                number_format($course->price, 0, '', '.'),
                number_format($course->original_price, 0, '', '.'),
                (($course->original_price - $course->price) / $course->original_price) * 100
            ));
            
            $updated++;
        }
        
        $this->info("Successfully updated {$updated} courses with original prices.");
        
        // Show summary
        $this->newLine();
        $this->info('Courses with discounts:');
        $coursesWithDiscounts = Course::where('original_price', '>', 0)
            ->whereColumn('original_price', '>', 'price')
            ->get(['name', 'price', 'original_price']);
            
        foreach ($coursesWithDiscounts as $course) {
            $discountPercent = round((($course->original_price - $course->price) / $course->original_price) * 100);
            $this->line(sprintf(
                '- %s: Rp %s (was Rp %s) - %d%% OFF',
                $course->name,
                number_format($course->price, 0, '', '.'),
                number_format($course->original_price, 0, '', '.'),
                $discountPercent
            ));
        }
        
        return 0;
    }
}
