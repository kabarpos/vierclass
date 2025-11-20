<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\User;
use App\Models\Transaction;
use App\Mail\CoursePurchaseConfirmation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestCourseEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:course-email {user_id} {course_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test course purchase confirmation email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        $courseId = $this->argument('course_id');
        
        $user = User::find($userId);
        $course = Course::find($courseId);
        
        if (!$user) {
            $this->error("User with ID {$userId} not found");
            return 1;
        }
        
        if (!$course) {
            $this->error("Course with ID {$courseId} not found");
            return 1;
        }
        
        // Create a mock transaction for testing
        $mockTransaction = new Transaction();
        $mockTransaction->user_id = $user->id;
        $mockTransaction->course_id = $course->id;
        $mockTransaction->booking_trx_id = 'TEST-' . time();
        $mockTransaction->sub_total_amount = $course->price;
        $mockTransaction->admin_fee_amount = 0;
        $mockTransaction->grand_total_amount = $course->price;
        $mockTransaction->payment_type = 'Test';
        $mockTransaction->is_paid = true;
        $mockTransaction->started_at = now();
        $mockTransaction->ended_at = null;
        $mockTransaction->created_at = now();
        $mockTransaction->updated_at = now();
        
        try {
            $this->info("Sending test email to {$user->email} for course '{$course->name}'");
            
        Mail::mailer('smtp')->to($user->email)->send(new CoursePurchaseConfirmation($user, $course, $mockTransaction));
            
            $this->info('✅ Course purchase confirmation email sent successfully!');
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to send email: ' . $e->getMessage());
            return 1;
        }
    }
}
