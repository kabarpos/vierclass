<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\User;
use App\Models\Transaction;
use App\Services\WhatsappNotificationService;
use Illuminate\Console\Command;

class TestCourseWhatsAppCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:course-whatsapp {user_id} {course_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test course purchase WhatsApp notification';

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
        
        if (!$user->whatsapp_number) {
            $this->error("User {$user->name} doesn't have a WhatsApp number");
            return 1;
        }
        
        // Create a mock transaction for testing
        $mockTransaction = new Transaction();
        $mockTransaction->user_id = $user->id;
        $mockTransaction->course_id = $course->id;
        $mockTransaction->booking_trx_id = 'TEST-WA-' . time();
        $mockTransaction->sub_total_amount = $course->price;
        $mockTransaction->admin_fee_amount = 0;
        $mockTransaction->grand_total_amount = $course->price;
        $mockTransaction->payment_type = 'Test';
        $mockTransaction->is_paid = true;
        $mockTransaction->started_at = now();
        $mockTransaction->ended_at = null;
        $mockTransaction->created_at = now();
        $mockTransaction->updated_at = now();
        
        // Manually set the student relationship
        $mockTransaction->setRelation('student', $user);
        $mockTransaction->setRelation('course', $course);
        
        try {
            $this->info("Sending test WhatsApp to {$user->whatsapp_number} for course '{$course->name}'");
            
            $whatsappService = app(WhatsappNotificationService::class);
            $result = $whatsappService->sendCoursePurchaseNotification($mockTransaction, $course);
            
            if ($result['success']) {
                $this->info('✅ Course purchase WhatsApp notification sent successfully!');
                $this->info('Response: ' . ($result['message'] ?? 'Message sent'));
            } else {
                $this->error('❌ Failed to send WhatsApp: ' . ($result['message'] ?? 'Unknown error'));
                if (isset($result['error'])) {
                    $this->error('Error details: ' . $result['error']);
                }
            }
            
            return $result['success'] ? 0 : 1;
            
        } catch (\Exception $e) {
            $this->error('❌ Exception occurred: ' . $e->getMessage());
            return 1;
        }
    }
}
